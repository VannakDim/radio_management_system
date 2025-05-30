<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BorrowAccessory extends Model
{
    use SoftDeletes;
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
