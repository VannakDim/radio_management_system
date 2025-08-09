<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $table = 'owners';
    protected $fillable = ['name', 'phone', 'unit_id', 'pid'];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function ownProducts()
    {
        return $this->hasMany(OwnProducts::class, 'owner_id', 'id');
    }

}
