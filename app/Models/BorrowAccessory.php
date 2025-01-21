<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowAccessory extends Model
{
    protected $fillable = [
        'borrow_id',
        'model_id',
        'quantity',
        'borrowed',
    ];

    public function borrow()
    {
        return $this->belongsTo(Borrow::class);
    }

    public function model()
    {
        return $this->belongsTo(ProductModel::class);
    }
}
