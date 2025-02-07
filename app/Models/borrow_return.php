<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class borrow_return extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'borrow_id',
        'user_id',
        'returner_name',
        'image',
        'note',
        'log',
    ];

    public function borrow()
    {
        return $this->belongsTo(Borrow::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
