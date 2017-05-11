@extends('layouts.template')
@section('content')
<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
    <div class="container-fluid" >
            @if ($content=='newPayment')
                <h1 class="page-header">New Payment</h1>
                <pre>Add new order</pre>
            @elseif ($content=='repeatPayment')
                <h1 class="page-header">Repeat Payment</h1>
                <pre>Repeat payment by this order</pre>
            @elseif ($content=='addPayment')
                <h1 class="page-header">Surcharge Payment by this order</h1>
                <pre>Surcharge the order</pre>
            @endif

            <form class="form-horizontal " name="newOrder" method="post" action="newPayment">
                <div class="form-group">
                    <table class="table" border="0">
                        <tbody>
                        <tr>
                            <td class="col-xs-1">Order Id:</td>
                            <td class="col-xs-6">
                                <br><p><input type="text" name="orderId"  class="form-control" id="newOrderId" value="{{$order_num}}" style="width:95px"></p>
                            </td>
                            <td rowspan="4" valign="center">
                                <br><br>
                                <div id="qrcode" align="center">

                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-xs-1">Summa:</td>
                            <td class="col-xs-6">
                                <P><label class="control-label">UAH</label><input type="text" name="summ" class="form-control"  value="<?if (isset($summ)){echo money_format('%i',$summ);}?>" style="width:150px">
                                    <label class="control-label" for="summ_btn">BTN</label>
                                    <input type="text" disabled name="summ_btn" id="summ_btn" class="form-control"  value="<?if (isset($summ_btn)){echo $summ_btn;}?>" style="width:150px"></P>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-xs-1">Address:</td>
                            <td class="col-xs-6">
                                <P><input type="text"  name="address"  class="form-control"  value="{{$address}}" ></P>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-xs-1">Description:</td>
                            <td class="col-xs-6">
                                <br><p><textarea name="description" class="form-control vresize" style="resize:none">
                                            <?if (isset($description)){echo $description;}?></textarea></p>
                            </td>
                        </tr>
                        </tbody>
                    </table><br>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <p></p><input type="submit" class="btn btn-primary" name="Generate" value="Generate QR-code ..." style="width: 250px">
                    <input type="reset" class="btn" name="reset"></p>
                    <br>
                </div>
            </form>
        <pre>{{$qrString}}</pre>
        <?php
            if ($qrString !=''){
                ?>
        <script type="text/javascript">
            var qrString='<?=$qrString?>';
            $('#qrcode').qrcode({
                //render:"table"
                width: 256,
                height: 256,
                text: qrString
            });
        </script>
        <?php
            }
?>
    </div>
</div>

@stop
