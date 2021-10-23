<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiprocketDetail extends Model
{
    protected $guarded = [];

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }
}
