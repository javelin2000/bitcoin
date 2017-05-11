<?php

namespace App;

use App\Library\FstxApi;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 * @package App
 * @method static Order find(integer $id)
 * @method static Order where($column, $condition, $special = null)
 * @method static Order first()
 */
class Order extends Model
{
    public $timestamps = true;
    const FACTOR = 100000000;
    protected $table = 'BTN_orders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'status',
        'order_num',
        'summ_btn',
        'pay__btn',
        'balance_btn',
        'summ_uah',
        'pay_uah',
        'balance_uah',
        'description',
        'address',
        'transaction',
        'tx_expired'
    ];

    public static function getRate()
    {
        $response = file_get_contents('https://api.privatbank.ua/p24api/pubinfo?exchange&json&coursid=11');
        $body = json_decode($response);
        $FstxApi = new FstxApi(Config('fapi.server') . '/api/v1/', OPENSSL_ALGO_SHA512, null);
        $res = $FstxApi->query_public('rate');
        if (isset($res['code']) && ($res['code'] == 0) && isset($body)) {
            $arr_rates['btc_usd'] = $res['data']['bid']  * (1 - Config('fapi.btc_usd_fee'));
            $arr_rates['usd_uah'] = round($body[0]->buy * (1 - Config('fapi.usd_uah_fee'))*100000000,0);
            $arr_rates['btc_uah'] = round($arr_rates['usd_uah'] * $arr_rates['btc_usd'],0);
        } else {
            $arr_rates['btc_usd'] = 0;
            $arr_rates['usd_uah'] = 0;
            $arr_rates['btc_uah'] = 0;
        }

        return $arr_rates;
    }

    public function getStatus(Order $order, array $in){
        /*$order = Order::where('order_pref', explode('-', $in['orderId'])[0])->
            where('order_num', explode('-', $in['orderId'])[1])->first();*/
        $status = $order->status;
        $transactions = BTN_webhooks::where('order_id', $order->id)->get();
        switch ($order->status){
            case 'NEW':
                if ($in['button']='cancel'){
                    $status = 'HISTORY_CANCELLED';
                }
                if ($in['confirmed']==true){
                    if ($order->summ_btn > intval($in['amount'])){
                        $status = 'CONFIRMED_WRONG';
                    }else{
                        $status = 'CONFIRMED_OK';
                    }
                }else{
                    if ($order->summ_btn > $in['amount']){
                        $status = 'UNCONFIRMED_WRONG';
                    }else{
                        $status = 'UNCONFIRMED_OK';
                    }
                }
                return $status;
            case 'UNCONFIRMED_OK':
                //
                if ($in['button']=='ok'){
                    $status = 'HISTORY_OK';
                }
                if ($in['confirmed']){
                    $status = 'CONFIRMED_OK';
                    return $status;
                }
                if ($in['tx_expired']){
                    if ($in['amount']==0){
                        $status = 'NEW';
                        return $status;
                    }elseif ($in['amount']>$order->summ_btn ){
                        $status = 'UNCONFIRMED_OK';
                        return $status;
                    }else {
                        $status = 'CONFIRMED_WRONG';
                        return $status;
                    }
                }
                return $status;
            case 'UNCONFIRMED_WRONG':
                if ($in['confirmed']){
                    if ($order->summ_btn > $order->pay_bnt + $in['amount']){
                        foreach ($transactions as $transaction ){
                            if ($transaction['confirmed']){
                                $tatus = 'CONFIRMED_WRONG';
                            }else {
                                $tatus = 'UNCONFIRMED_WRONG';
                                break;
                            }
                        }
                        return $status;

                    }else {
                        foreach ($transactions as $transaction ){
                            if ($transaction['confirmed']){
                                $tatus = 'CONFIRMED_OK';
                            }else {
                                $tatus = 'UNCONFIRMED_OK';
                                break;
                            }
                        }
                        return $status;
                    }
                }
                if (!$in['confirmed']){
                    if ($order->summ_btn > $order->pay_bnt + $in['amount']){
                        return 'UNCONFIRMED_WRONG';
                    }else {
                        return 'UNCONFIRMED_OK';
                    }
                }
                return $status;
            case 'CONFIRMED_OK':
                if ($in['button']=='ok'){
                    $status = 'HISTORY_OK';
                }
                return $status;
            case 'CONFIRMED_WRONG':

            case 'HISTORY_*':

        }
        return $status;
    }
}
