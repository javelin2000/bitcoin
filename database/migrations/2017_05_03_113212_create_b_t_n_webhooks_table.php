<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBTNWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BTN_webhooks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->char('address');
            $table->char('amount');
            $table->boolean('confirmed');
	        $table->char('transaction');
	        $table->boolean('tx_expired');
	        $table->charset('type');
            $table->timestamps('created_at');
            $table->bigInteger('btc_usd');
            $table->bigInteger('usd_uah');
            $table->foreign('order_id')->references('id')->on('BTN_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('BTN_webhooks');
    }
}
