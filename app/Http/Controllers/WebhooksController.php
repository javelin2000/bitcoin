<?php

namespace App\Http\Controllers;

use App\BTN_webhooks;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;


class WebhooksController extends Controller
{

    public function input(){
        $inputs = json_decode(file_get_contents('php://input'), true);
        return $inputs;
    }

    public function index (Request $req, Response $res){
        //Log::info('Log message', json_decode(file_get_contents('php://input'), true));
        //$in = $req->toArray();
        $in = json_decode(file_get_contents('php://input'), true);
        if (isset($in)){
            $order = Order::where('address', $in['address'])->first();
        }else{
            echo 'Whoops, input stream is empty.';
            return ;
        }
        //return $order->toJson();
        $in['order_id'] = $order->id;
        $arr_rates = Order::getRate();
        $in['btc_usd'] =($arr_rates['btc_usd']);
        $in['usd_uah'] = $arr_rates['usd_uah'];

        BTN_webhooks::create($in);
        //return json_encode($in);
        $pay_uah = floor($order->pay_uah + ($in['amount']*$arr_rates['btc_uah']/10000000000000000));
        $balance_uah = $pay_uah+intval($order->balance_uah);
        //return (['bal'=>intval($order->balance_uah), 'db'=>$pay_uah+intval($order->balance_uah), "pay"=>$pay_uah,  'res'=>$balance_uah ] );
        /*return ['float'=>floatval($in['amount'])*$arr_rates['btc_uah'], 'floor'=>floor(floatval($in['amount'])*$arr_rates['btc_uah']),
        'int'=>intval(round(floor(floatval($in['amount'])*$arr_rates['btc_uah']))), 'summ'=>$oo, 'sum_uah'=>$order->summ_uah];*/

        if ($in['type']=='verify'){
            echo hash ('sha512', 'http://javelin.mk.ua/api/webhooks');
            return ;
        }elseif ($in['type']== 'btc_deposit'){

            if (isset($order)){
                $order->status = $order->getStatus($order, $in+['button'=>'']);

                if ($in['amount'] != 0){
                    $order->pay_btn = $order->pay_btn + $in['amount'];
                    $order->balance_btn = $order->balance_btn + intval($in['amount']);
                    //return (['balance_btn'=>$order->balance_btn, ])
                    $order->pay_uah = $pay_uah;
                    $order->balance_uah = $balance_uah;
                }
                $order->transaction = $in['transaction'];
                $order->tx_expired = $in['tx_expired'];
                $order->save();
                //echo json_encode($order);
                return  $order->toJson();
            }

        }else{
            echo 'Whoops, looks like something went wrong.';
            return ;
        }
    }
}
