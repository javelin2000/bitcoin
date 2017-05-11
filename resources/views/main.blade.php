@extends('layouts.template')

@section('content')
<?use App\Order;?>
<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
    <?php
    if ($content == 'main'){
        $orders = Order::where('status', '<>','HISTORY_OK')->orderBy($sort)->paginate(5);
        echo '<h1 class="page-header">Orders</h1>';
    }else{
        $orders = Order::where('status', 'HISTORY_OK')->orderBy($sort)->paginate(5);
        echo '<h1 class="page-header">Order history</h1>';
    }
    ?>
    <div class="row placeholders">
        <div class="col-6 placeholder">
            @if ($content == 'main')
                <form class="form-inline right">
                    <label>sort by:</label>
                    <select onchange="location=value" type="text"   class="form-control" style="align:left"
                            required size = "1" name = "name[]">
                        <option <?if ($sort == 'id') {echo 'selected';}?> value="/main">Order No</option>
                        <option <?if ($sort == 'summ_uah') {echo 'selected';}?> value="/main/summ">Summ</option>
                        <option <?if ($sort == 'pay_uah') {echo 'selected';}?> value="/main/pay">Pay</option>
                    </select>
                </form>
            @endif
            <table class="table" style="width:95%">
                <thead>
                <tr style="border-bottom:solid 2px darkblue">
                    <td><b>Status</b></td><td><b>Order No</b></td><td><b>Summ, UAH</b></td><td><b>Pay, UAH</b></td><td></td><td></td>
                </tr>
                </thead>
                <tbody >
            <?
            setlocale(LC_MONETARY, 'ua_UA');

            foreach ($orders as $order){
                echo '<tr>';
                if (($order->status == 'NEW')AND($content=='main')){
                    echo '<td align="center"><div style="background-color: black;border-radius: 5px;color: white">Новый</div></td>';
                    echo '<td style="padding: 5px">'.$order->order_pref.'-'.$order->order_num.'</td>';
                    echo '<td align="right">'.money_format('%i', $order->summ_uah/100000000).'</td>';
                    echo '<td align="right">'.money_format('%i', $order->pay_uah/100000000).'</td>';
                    echo '<td align="center"><button type="button" class="btn btn-info"
                        id="'.$order->order_pref.'-'.$order->order_num.'" data-toggle="modal" data-target="#myModal-'.$order->order_pref.'-'.$order->order_num.'">i</button>';
                    if ($content == 'main'){
                        echo '<td align="right"><a href="/repeatPayment/'.$order->order_pref.'-'.$order->order_num.'" class="btn btn-info" role="button" style="border-color:black;background-color:black">Повторть</a>
                            <a href="#" class="btn btn-info" role="button" style="border-color:red;background-color:red">Закрыть</a></td>';
                    }
                }elseif (($order->status == 'UNCONFIRMED_OK' OR $order->status == 'CONFIRMED_OK')AND($content=='main')){
                    echo '<td ><div style="background-color: darkgreen;border-radius: 5px;color: white">Оплачен</div></td>';
                    echo '<td >'.$order->order_pref.'-'.$order->order_num.'</td>';
                    echo '<td align="right">'.money_format('%i', $order->summ_uah/100000000).'</td>';
                    echo '<td align="right">'.money_format('%i', $order->pay_uah/100000000).'</td>';
                    echo '<td align="center"><button type="button" class="btn btn-info"
                        id="'.$order->order_pref.'-'.$order->order_num.'" data-toggle="modal" data-target="#myModal-'.$order->order_pref.'-'.$order->order_num.'">i</button>';
                    if ($content == 'main'){
                        echo '<td align="right"><a href="#" class="btn btn-info" role="button" style="border-color:red;background-color:red">Закрыть</a></td>';
                    }
                }elseif (($order->status == 'HISTORY_OK' OR $order->status == 'HISTORY_WRONG'  OR $order->status == 'HISTORY_CANCELLED')AND ($content=='history')){
                    echo '<td ><div style="background-color: darkblue;border-radius: 5px;color: white">Закрыт</div></td>';
                    echo '<td >'.$order->order_pref.'-'.$order->order_num.'</td>';
                    echo '<td align="right">'.money_format('%i', $order->summ_uah/100000000).'</td>';
                    echo '<td align="right">'.money_format('%i', $order->pay_uah/100000000).'</td>';
                    echo '<td align="center"><button type="button" class="btn btn-info"
                        id="'.$order->order_pref.'-'.$order->order_num.'" data-toggle="modal" data-target="#myModal-'.$order->order_pref.'-'.$order->order_num.'">i</button>';
                }elseif (($order->status == 'UNCONFIRMED_WRONG' OR $order->status == 'CONFIRMED_WRONG')AND($content=='main')){
                    echo '<td ><div style="background-color: orangered;border-radius: 5px;color: white">Недоплата</div></td>';
                    echo '<td ">'.$order->order_pref.'-'.$order->order_num.'</td>';
                    echo '<td align="right">'.money_format('%i', $order->summ_uah/100000000).'</td>';
                    echo '<td align="right">'.money_format('%i', $order->pay_uah/100000000).'</td>';
                    echo '<td align="center"><button type="button" class="btn btn-info"
                        id="'.$order->order_pref.'-'.$order->order_num.'" data-toggle="modal" data-target="#myModal-'.$order->order_pref.'-'.$order->order_num.'">i</button>';
                    if ($content == 'main'){
                        echo '<td align="right"><a href="/addPayment/'.$order->order_pref.'-'.$order->order_num.'" class="btn btn-info" role="button" style="border-color:orangered;background-color:orangered">Доплатить</a>
                            <a href="#" class="btn btn-info" role="button" style="border-color:red;background-color:red">Закрыть</a></td>';
                    }
                }
                echo '</tr>';
                echo '<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
            }
            ?>
                </tbody>
            </table>
            {{$orders->links()}}
        </div>
    </div>
</div>
@foreach ($orders as $order)
    <div id="<?echo 'myModal-'.$order->order_pref.'-'.$order->order_num;?>" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Order Details</h4>
                </div>
                <div class="modal-body">
                    <p><b>Order No :</b><span style="color: darkblue"><?echo '  '.$order->order_pref.'-'.$order->order_num; ?></span></p>
                    <p><b>Status :</b><span style="color: darkblue"><?echo '  '.$order->status; ?></span></p>
                    <p><b>Created at :</b><span style="color: darkblue"><?echo '  '.$order->created_at; ?></span></p>
                    <p><b>Updated at :</b><span style="color: darkblue"><?echo '  '.$order->updated_at;?></span></p>
                    <p><b>Summ, UAH :</b><span style="color: darkblue"><?echo '  '.$order->summ_uah/100000000;?></span></p>
                    <p><b>Summ, BTC :</b><span style="color: darkblue"><?echo '  '.$order->summ_btn/100000000;?></span></p>
                    <p><b>Address :</b><span style="color: darkblue"><?echo '  '.$order->address?></span></p>
                    <br><p><h4 align="center">Transactions</h4></p><br>
                    <?
                        $transactions = \App\BTN_webhooks::where('order_id', $order->id)->get();
                        foreach ($transactions as $transaction){?>
                            <p><b>Pay, BTN : </b><span style="color: <?if ($order->summ_btn>$transaction->amount){echo 'darkred';}else{echo 'darkgreen';} ?>"><?echo '  '.$transaction->amount; ?></span></p>
                            <p><b>Date :</b><span style="color: darkblue"><?echo '  '.$transaction->created_at;?></span></p>
                            @if ($transaction->confirmed)
                                <p><b>Status :</b><span style="color: darkblue">confirmed</span></p>
                            @else
                                <p><b>Status :</b><span style="color: darkblue">unconfirmed</span></p>
                            @endif
                            <p><b>Transaction No :</b><span style="color: darkblue"><?echo '  '.$transaction->transaction;?></span></p>
                            <hr>
                        <?}
                    ?>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
@endforeach
@stop
