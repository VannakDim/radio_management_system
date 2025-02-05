<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOut extends Model
{
    use SoftDeletes;
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

    public function products()
    {
        return $this->hasMany(StockOutProduct::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
