<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['name'];
    protected $table = 'models';
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany('App\Product','model_id');
    }
}
