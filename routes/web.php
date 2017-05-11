<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use App\Http\Controllers\IndexController;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/', 'IndexController@index');

Route::get('/main', 'IndexController@index');

Route::get('/main', 'IndexController@index');

Route::get('/main/summ', 'IndexController@summ');

Route::get('/main/pay', 'IndexController@pay');

Route::get('/hello', 'IndexController@index');

Route::get('/history', 'IndexController@history');

Route::get('/newPayment', 'IndexController@newPayment');

Route::post('/newPayment', 'IndexController@newPostPayment');

Route::get('/settings', 'IndexController@settings');


Route::get('repeatPayment/{order_ID}', function ($orderID){
    $order = Order::where('order_pref',  explode('-', $orderID)[0])
        ->where('order_num',  explode('-', $orderID)[1])->first();
    $qrString = 'bitcoin:'.$order->address.'?amount='.($order->summ_btn/100000000).'&label='.'RestNewHemp'.
        '&message='.'Order#{xxx}-{xxx}'.$orderID.' '.$order->description;
    return view ('new')->with(['content' => 'repeatPayment','order_num'=>$orderID, 'summ'=>$order->summ_uah/Order::FACTOR, 'qrString' =>$qrString,
        'summ_btn'=>ceil($order->summ_btn)/Order::FACTOR, 'address'=> $order->address, 'description'=> $order->description]);
})->where('orderID','([A-Za-z],3)-([0-9]+)');

Route::get('addPayment/{order_ID}', function ($orderID){
    $order = Order::where('order_pref',  explode('-', $orderID)[0])
        ->where('order_num',  explode('-', $orderID)[1])->first();
    $arr_rates = Order::getRate();
    $balance_uah = round((floatval($arr_rates['btc_uah'])/(Order::FACTOR*Order::FACTOR))* $order->balance_btn*(-1)/Order::FACTOR ,2);
    //return $arr_rates+ ['bal'=> $balance_ua];
    $qrString = 'bitcoin:'.$order->address.'?amount='.($order->balance_btn*(-1)/100000000).'&label='.'RestNewHemp'.
        '&message='.'Order#{xxx}-{xxx}'.$orderID.' '.$order->description;
    return view ('new')->with(['content' => 'addPayment','order_num'=>$orderID, 'summ'=>$balance_uah, 'qrString' =>$qrString,
        'summ_btn'=>ceil($order->balance_btn*(-1))/Order::FACTOR, 'address'=> $order->address, 'description'=> $order->description]);
})->where('orderID','([A-Za-z],3)-([0-9]+)');


Route::get('/rate', 'IndexController@rate');

Route::get('/set23', 'IndexController@set23');

Route::get('/getrate', 'IndexController@getRate');

Route::post('/webhooks', function(Request $req, Response $res){
    $in = $req->json();
    if ($in['type']=='verify'){
        echo hash ('sha512', 'http://javelin.mk.ua/webhooks');
        return ;
    }elseif ($in['type']== 'btc_deposit'){
        echo '1';
        return ;
    }else{
        echo 'Whoops, looks like something went wrong.';
        return ;
    }
});

Route::get('/blog/articles', 'ArticlesController@index');

Route::get('/blog/article/{id}', 'ArticlesController@show');

