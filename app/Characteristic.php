<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Characteristic extends Model
{
    protected $fillable = ['name','product_chracteristics_json'];
    public $timestamps = false;
    protected $casts = [
        'product_chracteristics_json' => 'array',
    ];

    

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

   
}
