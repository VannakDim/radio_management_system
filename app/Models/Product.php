<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = ['PID', 'model_id', 'feature', 'description', 'image'];


    public function model()
    {
        return $this->belongsTo(ProductModel::class);
    }


    public function stockIn()
    {
        return $this->belongsToMany(StockIn::class, 'stock_in_product', 'product_id', 'stock_in_id');
    }

    public function setFrequency()
    {
        return $this->hasMany(SetFrequencyDetail::class);
    }
}
