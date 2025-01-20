<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowDetail extends Model
{
    protected $fillable = [
        'borrow_id',
        'product_id',
        'borrowed',
    ];

    public function borrow()
    {
        return $this->belongsTo(Borrow::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
