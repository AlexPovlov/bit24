<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['name'];
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany('App\Product');
    }
}
