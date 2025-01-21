<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    protected $fillable = [
        'receiver',
        'user_id',
        'purpose',
        'borrowed',
        'note',
        'image',
    ];

    public function details()
    {
        return $this->hasMany(BorrowDetail::class);
    }

    public function accessory()
    {
        return $this->hasMany(BorrowAccessory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
