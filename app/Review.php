<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['name','phone','full_name',
                            'region','area','place',
                            'comment','product_id','date'];
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


}
