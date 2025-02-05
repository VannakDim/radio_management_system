<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOutProduct extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'stock_out_id',
        'product_id',
        'note',
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
