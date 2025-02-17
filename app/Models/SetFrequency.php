<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SetFrequency extends Model
{
    use SoftDeletes;
    protected $table = 'set_frequencies';
    protected $fillable = ['user_id', 'unit', 'name', 'purpose', 'trimester', 'date_of_setup'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasMany(SetFrequencyDetail::class);
    }
}
