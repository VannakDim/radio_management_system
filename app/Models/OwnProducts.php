<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnProducts extends Model
{
    protected $table = 'own_products';
    protected $fillable = ['owner_id', 'product_id', 'serial_number'];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
