<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIn extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'supplier',
        'invoice_no',
        'note',
        'image',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasMany(StockInDetail::class);
    }

    public function product()
    {
        return $this->hasMany(Product::class);
    }
}
