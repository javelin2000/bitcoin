<?php


namespace App\Http\Controllers;



use App\Order;
use Illuminate\Http\Request;
use App\Library\FstxApi;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;



class IndexController extends Controller
{


    private $FACTOR = 100000000;

    public function getRate(){
        return Order::getRate();
    }

    public function index() {
        $content = 'main';
	    return view ('main')->with(['content'=>'main', 'sort' =>'id']);

    }
    public function summ() {
        $content = 'main';
        return view ('main')->with(['content'=>'main', 'sort' =>'summ_uah']);

    }
    public function pay() {
        $content = 'main';
        return view ('main')->with(['content'=>'main', 'sort' =>'pay_uah']);

    }

    public function history() {
        $content = 'history';
        return view ('main')->with(['content'=>'history', 'sort' =>'id']);
    }

    public function set23(){
        $order = Order::where('order_num','23')->first();
        $order->pay_btn = 0;
        $order->pay_uah = 0;
        $order->balance_uah = round((-1) * $order->summ_uah,0);
        $order->balance_btn = -2863471;
        $order->status = 'NEW';
        $order->save();
        return $order->toJson();
    }

    public function newPayment() {
        //$content = 'new';
        $FstxApi = new FstxApi(Config('fapi.server').'/api/v1/', OPENSSL_ALGO_SHA512, null);
        $FstxApi->set_privkey(Config('fapi.my_private_key'));
        $FstxApi->set_uid(Config('fapi.my_unique_id'));
        $FstxApi->set_serverpubkey(Config('fapi.server_public_key'));
        $res = $FstxApi->query_private('address/get/new', ['is_autoexchange' => 1]);
        if (
            !isset($res['code'])
            || $res['code'] != 0
            || !isset($res['data']['address'])
            || $res['data']['address'] == ''
        )
        {
            return false;
        }
        $order_mun = Order::max('order_num');
        $order = Order::where('order_num', $order_mun)->where('order_pref', 'ABC')->first();
        $orderID = $order->order_pref.'-'.strval($order_mun+1);
        return view ('new')->with(['content'=>'newPayment','order_num'=>$orderID, 'qrString' =>'',
            'address' =>array($res)[0]['data']['address']]);
    }

    public function settings(){
        return view('welcome')->with(['content' => 'rate']);
    }

    public  function repeatPostPayment ($orderID){
        $order = Order::where('order_pref',  explode('-', $orderID)[0])
            ->where('order_num',  explode('-', $orderID)[1])->first();
        $qrString = 'bitcoin:'.$order->address.'?amount='.($order->summ_btn/100000000).'&label='.'RestNewHemp'.
            '&message='.'Order#{xxx}-{xxx}'.$orderID.' '.$order->description;
        return view ('new')->with(['content' => 'repeatPayment','order_num'=>$orderID, 'summ'=>$order->summ_uah, 'qrString' =>$qrString,
            'summ_btn'=>ceil($order->summ_btn)/$this->FACTOR, 'address'=> $order->address, 'description'=> $order->description]);
    }

    public function newPostPayment(Request $request)
    {
        $orderID = $request->input('orderId');
        $summ = $request->input('summ');
        $response = file_get_contents('https://api.privatbank.ua/p24api/pubinfo?exchange&json&coursid=11');
        $body = json_decode($response);
        $FstxApi = new FstxApi(Config('fapi.server') . '/api/v1/', OPENSSL_ALGO_SHA512, null);
        $res = $FstxApi->query_public('rate');
        $rate = 0;
        if (isset($res['code']) && $res['code'] == 0) {
            $arr_rates['btc_usd'] = floatval($res['data']['bid']) * (1 - Config('fapi.btc_usd_fee'));
            $arr_rates['usd_uah'] = $body[0]->buy * (1 - Config('fapi.usd_uah_fee'));
            $rate = $arr_rates['btc_usd'] * $arr_rates['usd_uah'] / $this->FACTOR;
            $arr_rates = Order::getRate();
            //return view ('rate')->with(['content' => $arr_rates['btc_usd'], 'usd_uah'=>$body[0]->buy*(1-Config('fapi.usd_uah_fee'))]);
            $summ_btn = ceil(round($summ / $rate, 9) * $this->FACTOR);
            $description = trim($request->input('description'));
            $address = $request->input('address');
            $order = new Order();
            $order->status = 'NEW';
            $order->order_num = explode('-', $orderID)[1];
            $order->order_pref = explode('-', $orderID)[0];
            $order->address = $address;
            $order->description = $description;
            $order->summ_btn = $summ_btn;
            $order->summ_uah = round($summ, 8) * $this->FACTOR;
            $order->pay_btn = 0;
            $order->pay_uah = 0;
            $order->balance_btn = $summ_btn * (-1);
            $order->balance_uah = round($summ * (-1), 8) * $this->FACTOR;
            $order->transaction = null;
            $id = $order->save();
            $qrString = 'bitcoin:' . $address . '?amount=' . ($summ_btn / 100000000) . '&label=' . 'RestNewHemp' .
                '&message=' . 'Order#{xxx}-{xxx}' . $orderID . ' ' . $description;
            return view('new')->with(['content' => 'newPayment', 'order_num' => $orderID, 'summ' => $summ, 'qrString' => $qrString,
                'summ_btn' => ceil($summ_btn) / $this->FACTOR, 'address' => $address, 'description' => $description]);
        }

    }


    public function rate() {
        $arr_rates = $this->getRate();
        return view ('rate')->
            with(['content' => 'rate','arr_rates' => $arr_rates]);
    }
}
