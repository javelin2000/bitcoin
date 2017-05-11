<?php
namespace App;

/**
 * Class Encryption - basic encryption|decryption class, using signature
 * @package App
 */
class Encryption
{
    /*
     * Example: generating new openssl key from command line
     * #openssl genrsa -out privatekey.pem 2048
     * #openssl rsa -in privatekey.pem -out publickey.pem -outform PEM -pubout
     */
    /**
     * @var int - hashing algorithm
     */
    private $hash_alg;
    //KEYS
    /**
     * remote OpenSSL key for encryption data
     */
    private $remote_public_key;
    /**
     * Own OpenSSL key for signing data
     */
    private $own_private_key;
    /**
     * @param $remote_public_key - foreign public key for encrypting data
     * @param $own_private_key - own private key for signing data
     */
    function __construct($remote_public_key, $own_private_key, $hash_alg = OPENSSL_ALGO_SHA1)
    {
        $this->hash_alg = $hash_alg;
        if (isset($remote_public_key) && isset($own_private_key))
        {
            $this->remote_public_key = $remote_public_key;
            $this->own_private_key = $own_private_key;
        }
    }
    /**
     * Data encryption
     * @param $data - data to encrypt
     * @return array|bool - two-fields array: signature and encoded data
     */
    public function encode($data)
    {
        if (is_null($data) || is_null($this->remote_public_key))
        {
            return false;
        }
        //Compact data to JSON
        $json_data = json_encode($data);
        $data_combined = $this->e($json_data, $this->remote_public_key);
        $signature = $this->data_sign($data_combined, $this->own_private_key);
        if ($signature)
        {
            return [
                'sign' => $signature,
                'data' => $data_combined
            ];
        }
        return false;
    }
    /**
     * Data decryption
     * @param $signature - data signature
     * @param $data_combined - decryption parameters: array of unique_id of user's API-key, data signature, encrypted data
     * @return bool|mixed - JSON-encoded data on success, or false in case of error
     * @see decombine_strings
     * @see combine_strings
     */
    public function decode($signature, $data_combined)
    {
        if (is_null($signature) || is_null($data_combined) || is_null($this->remote_public_key))
        {
            return false;
        }
        if (1 == $this->data_verify($data_combined, $signature, $this->remote_public_key))
        {
            if (false === ($data = $this->d($data_combined, $this->own_private_key)))
            {
                echo 'Bad data';
                return false;
            }
            $result = json_decode($data, true);
            return $result;
        }
        return false;
    }
    /**
     * Data signature check
     * @param $signature - data signature
     * @param $data_combined - data to check signature on
     * @return bool 1 if the signature is correct, 0 if it is incorrect, and -1 on error.
     * @see data_verify
     */
    public function verify($signature, $data_combined)
    {
        return $this->data_verify($data_combined, $signature, $this->remote_public_key);
    }
/////////////////////////////////////////////////////////////////////////////////////////
//                             FUNCTIONS
/////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Compresses $data, Encrypts it with OpenSSL public $key, encodes it with MIME base64,
     * combines with encryption key and prepares to be a part of URL.
     *
     * @param $data string String to encrypt.
     * @param $key string OpenSSL public key.
     * @return string|bool FALSE if $data is empty or any error occurs,
     * otherwise OpenSSL-encrypted and encoded with MIME base64 $data,
     * combined with key.
     * @see d()
     * @see openssl_seal()
     * @see gzcompress()
     * @see combine_strings()
     * @see data_url_encode()
     */
    function e($data, $key)
    {
        if ($data)
        {
            $data_compressed = gzcompress($data);
            openssl_seal($data_compressed, $data_encrypted, $encrypted_key, array($key));
            $data_encrypted = base64_encode($data_encrypted);
            $encrypted_key = base64_encode($encrypted_key[0]);
            $data_combined = $this->combine_strings($data_encrypted, $encrypted_key);
            $data_url_ready = $this->data_url_encode($data_combined);
        }
        else
        {
            $data_url_ready = false;
        }
        return $data_url_ready;
    }
    /**
     * Decrypts $data_combined using OpenSSL public $key.
     *
     * @param $data_url_ready string URLencoded Encrypted data, combined with encrypted key.
     * @param $key string OpenSSL public key.
     * @return string|bool FALSE if $data_combined is empty or any error occurs,
     * otherwise decrypted $data.
     * @see e()
     * @see openssl_open()
     * @see gzuncompress()
     * @see decombine_strings()
     * @see data_url_decode()
     */
    function d($data_url_ready, $key)
    {
        if ($data_url_ready)
        {
            $data_combined = $this->data_url_decode($data_url_ready);
            $complex = $this->decombine_strings($data_combined);
            $data_encrypted = base64_decode($complex[0]);
            $encrypted_key = base64_decode($complex[1]);
            $pkey_id = openssl_get_privatekey($key);
            if (openssl_open($data_encrypted, $data_compressed, $encrypted_key, $pkey_id))
            {
                $data = gzuncompress($data_compressed);
            }
            else
            {
                $data = false;
            }
        }
        else
        {
            $data = false;
        }
        return $data;
    }
    /**
     * Combines two strings by some algo.
     *
     * Now simply joins via '-'-sign.
     *
     * @param $str1 string
     * @param $str2 string
     * @return string Combined string
     * @see decombine_strings()
     */
    function combine_strings($str1, $str2)
    {
        $data_combined = $str1 . '-' . $str2;
        return $data_combined;
    }
    /**
     * Extracts strings from combined one by some algo.
     *
     * Now simply explodes by '-'-sign.
     *
     * @param $data_combined string Combined strings
     * @return string Array of extracted strings.
     * @see combine_strings()
     */
    function decombine_strings($data_combined)
    {
        return explode('-', $data_combined);
    }
    /**
     * Prepares MIME Base64 encoded string to be part of URL by replacing
     * '/' with '_' and applying `urlencode()`-function.
     *
     * @param $str string MIME Base64 encoded string
     * @return string Prepared string.
     * @see data_url_decode()
     */
    function data_url_encode($str)
    {
        $str = str_replace('/', '_', $str);
        $str = urlencode($str);
        return $str;
    }
    /**
     * Makes backward changes of `data_url_encode()`
     *
     * @param $str string `data_url_encode()`-ed string
     * @return string MIME Base64 encoded string
     * @see data_url_encode()
     */
    function data_url_decode($str)
    {
        $str = urldecode($str);
        $str = str_replace('_', '/', $str);
        return $str;
    }
    /**
     * OpenSSL sign and prepare to be a part of URL.
     *
     * @param $data string Data to sign.
     * @param $key string OpenSSL PEM-formatted (private) key.
     * @return bool|string URLencoded signature on success, otherwise false.
     * @see data_verify()
     * @see openssl_sign()
     * @see data_url_encode()
     * @see base64_encode()
     */
    function data_sign($data, $key)
    {
        $signature = false;
        if (openssl_sign($data, $signature, $key, $this->hash_alg))
        {
            $signature = $this->data_url_encode(base64_encode($signature));
        }
        return $signature;
    }
    /**
     * Verifies OpenSSL signed data.
     *
     * @param $data string Data to verify.
     * @param signature string URLencoded signature.
     * @param $key string OpenSSL PEM-formatted (public) key.
     * @return bool|string whatever openssl_verify() returns.
     * @see data_sign()
     * @see openssl_verify()
     * @see data_url_decode()
     * @see base64_decode()
     */
    function data_verify($data, $signature, $key)
    {
        $a = $this->data_url_decode($signature);
        $b = base64_decode($a);
        $c = openssl_verify($data, $b, $key, intval($this->hash_alg));
        return $c;
        //return openssl_verify($data,base64_decode(data_url_decode($signature)),$key);
    }
}
