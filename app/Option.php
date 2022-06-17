<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = ['name','product_options_json'];
    public $timestamps = false;
    protected $casts = [
        'product_options_json' => 'array',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    
}
