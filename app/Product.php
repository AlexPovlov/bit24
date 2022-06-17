<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
                'name',
                'price_purchases',
                'price_selling',
                'production_time',
                'description_main',
                'title',
                'description',
                'subcategory_id',
                'model_id',
                'manufacturer_id',
                'region_id',
                'sources',
                'video',
                'code',
                'characteristics_json',
                'options_json',
            ];
            
    protected $casts = [
        'characteristics_json' => 'array',
        'options_json' => 'array',
    ];
    

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class,'model_id');
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function specification_option()
    {
        return $this->hasOne(Specification_option::class);
    }

    public function options()
    {
        return $this->belongsToMany(Option::class);
    }

    public function specification_characteristics()
    {
        return $this->hasMany(Specification_characteristic::class);
    }

    public function characteristics()
    {
        return $this->belongsToMany(Characteristic::class);
    }

    public function ehe()
    {

        return $this->characteristics;
    }
}
