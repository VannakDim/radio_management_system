<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockInDetail extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'stock_in_id',
        'product_model_id',
        'quantity',
        'note',
    ];

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class,'product_model_id','id');
    }
}
