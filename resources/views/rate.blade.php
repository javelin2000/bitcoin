@extends('layouts.template')
@section('content')
<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
    <p><h3 class="sub-header">Rate BTN to USD: <span style="color: blue">{{$arr_rates['btc_usd']/100000000}}</span></h3></p><br>
    <p><h3 class="sub-header">Rate USD to UAH: <span style="color: blue">{{$arr_rates['usd_uah']/100000000}}</span></h3></p><br>
    <p><h3 class="sub-header">Rate BTN to UAH: <span style="color: blue">{{$arr_rates['btc_uah']/10000000000000000}}</span></h3></p><br>
</div>
@stop