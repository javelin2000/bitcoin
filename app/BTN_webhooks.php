<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * Class Order
 * @package App
 * @method static BTN_webhooks find(integer $id)
 * @method static BTN_webhooks where($column, $condition, $special = null)
 * @method static BTN_webhooks first()
 * @method static BTN_webhooks create()
 */

class BTN_webhooks extends Model
{
    public $timestamps = true;
    protected $table = 'BTN_webhooks';
    protected $primaryKey = 'id';
    protected $fillable = [
	"address",
    "order_id",
	"amount",
	"confirmed",
	"transaction",
	"tx_expired",
	"type",
    "btc_usd",
    "usd_uah"
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
