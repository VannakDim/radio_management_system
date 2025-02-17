<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetFrequencyDetail extends Model
{
    protected $table = 'set_frequency_details';
    protected $fillable = ['set_frequency_id', 'product_id'];

    public function setFrequency()
    {
        return $this->belongsTo(SetFrequency::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
