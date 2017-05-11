<?php
namespace App;

use App\Encryption;

class FstxApi
{
    /**
     * Get your API key at https://dev.client.fstx.ddev.pw/a/#/settings/api
     */
    /**
     * @var string - unique API-key ID
     */
    private $my_unique_id = '';
    /**
     * @var string - public encryption key
     */
    private $my_public_key = '';
    /**
     * @var string - private encrption key
     */
    private $my_private_key = '';
    private $hash_type = OPENSSL_ALGO_SHA512;
    /**
     * @var string - server public key
     */
    private $server_public_key = '';
    /**
     * @var - request address
     */
    private $curlopt_url;
    /**
     * @var - client info
     */
    private $curlopt_useragent;
    private $nonce;
    function __construct($curlopt_url = 'https://dev.client.fstx.ddev.pw/api/v1/', $hash_type = OPENSSL_ALGO_SHA512, $curlopt_useragent = null)
    {
        $this->hash_type = $hash_type;
        $this->curlopt_url = $curlopt_url;
        $this->curlopt_useragent = isset($curlopt_useragent) ? $curlopt_useragent :
            'Mozilla/4.0 (compatible; Fstx PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')';
    }
    function set_uid($uid)
    {
        $this->my_unique_id=$uid;
    }
    function set_privkey($privkey)
    {
        $this->my_private_key=$privkey;
    }
    function set_serverpubkey($pubkey)
    {
        $this->server_public_key=$pubkey;
    }
    /**
     * Executes query to private API
     * @param $method - private API method
     * @param array $params - parameters array
     * @return mixed|string - response in JSON format on success, or error text otherwise
     */
    function query_private($method, $params = array())
    {
        $curlopt_url = $this->curlopt_url . $method;
        // Set request time
        if(!isset($this->nonce))
        {
            $this->nonce=time();
        }
        else
        {
            $this->nonce++;
        }
        $req['nonce'] = $this->nonce;
        // Add parameters(if any) to basic parameters array
        $req = array_merge($req, $params);
        // Initialize Encryption object
        $encryption = new Encryption($this->server_public_key, $this->my_private_key, $this->hash_type);
        // Encrypting data
        $encrypted_data = $encryption->encode($req);
        $data['unique_id'] = $this->my_unique_id; // unique_id of user's API-key
        $data['sign'] = $encrypted_data['sign']; // signature
        $data['data'] = $encrypted_data['data']; // encrypted data
        $data['nonce'] = $req['nonce']; // request time
        // Build POST-request data string
        $post_data = http_build_query($data, '', '&');
        // initialize curl, if needed
        static $ch = null;
        if (is_null($ch))
        {
            $ch = curl_init();
            if ($ch)
            {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, $this->curlopt_useragent);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            }
        }
        if (!$ch)
        {
            return ['code'=>-1,'message'=>'Fail init curl.'];
        }
        curl_setopt($ch, CURLOPT_URL, $curlopt_url);
        // Set headers and POST data
        curl_setopt($ch, CURLOPT_HTTPGET, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        // Send request
        $res = curl_exec($ch);
        if ($res === false)
        {
            return ['code'=>-1,'message'=>'Could not get reply: ' . curl_error($ch)];
        }
        $res_raw=$res;
        // Decode result
        $res = json_decode($res_raw, true);
        if (!isset($res['return']) || !isset($res['code']) || !isset($res['sign']))
        {
            return ['code'=>-1,'message'=>'Invalid data received','data'=>print_r($res_raw,true)];
        }
        $decrypted_data = $encryption->decode($res['sign'], $res['return']);
        $res['data'] = $decrypted_data;
        return $res;
    }
    /**
     * Executes query to public API
     * @param $method - public API method
     * @param array $params - parameters array
     * @return mixed|string - response in JSON format on success, or error text otherwise
     */
    function query_public($method, $params = array())
    {
        // Build GET-request data string
        $get_params = http_build_query($params, '', '&');
        $curlopt_url = $this->curlopt_url . $method . ($get_params?'/' . $get_params:'');
        // initialize curl, if needed
        static $ch = null;
        if (is_null($ch))
        {
            $ch = curl_init();
            if ($ch)
            {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, $this->curlopt_useragent);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }
        }
        if (!$ch)
        {
            return ['code'=>-1,'message'=>'Fail init curl.'];
        }
        curl_setopt($ch, CURLOPT_URL, $curlopt_url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        // Send request
        $res = curl_exec($ch);
        if ($res === false)
        {
            return ['code'=>-1,'message'=>'Could not get reply: ' . curl_error($ch)];
        }
        $res_raw=$res;
        // Decode result
        $res = json_decode($res_raw, true);
        if (!isset($res['code']))
        {
            return ['code'=>-1,'message'=>'Invalid data received','data'=>print_r($res_raw,true)];
        }
        return $res;
    }
}
