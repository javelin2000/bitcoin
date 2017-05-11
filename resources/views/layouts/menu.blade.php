<div class="col-sm-3 col-md-2 sidebar">
    <div class="navigation">
        <p><a class="{{ $content =='main' ? 'btn btn-primary active' : 'btn btn-primary ' }}" href='/main' role="button" aria-pressed="true">Orders</a></p>
        <p><a class="{{ $content =='history'  ? 'btn btn-primary active' : 'btn btn-primary ' }}" href='/history' role="button" aria-pressed="true">Order history</a></p>
        <p><a class="{{ $content =='newPayment' ? 'btn btn-primary active' : 'btn btn-primary ' }}" href='/newPayment' role="button" aria-pressed="true">New Payment</a></p>
        <p><a class="{{ $content =='rate' ? 'btn btn-primary active' : 'btn btn-primary ' }}" href='/rate' role="button" aria-pressed="true">Rate</a></p>

    </div>
</div>