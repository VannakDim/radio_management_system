<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    protected $fillable = [
        'stock_out_id',
        'product_model_id',
        'quantity',
        'note',
    ];

    public function stockOutDetails()
    {
        return $this->hasMany(StockOutDetail::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
