<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SetFrequency; // Ensure the correct namespace for SetFrequency

class Unit extends Model
{
    protected $table = 'units';
    protected $fillable = ['unit_name'];

    public function setFrequency()
    {
        return $this->hasMany(SetFrequency::class);
    }

    public function Owner()
    {
        return $this->hasMany(Owner::class, 'unit_id', 'id');
    }

}
