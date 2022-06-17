<?php

namespace App;

use App\Product;
use App\Manufacturer;
use App\Subcategory;
use App\Category;
use App\Form;
use App\Region;
use App\Review;

/**
 * Импорт товаров с Excel который генерирует OpenCart 
 */

class ImportOpencartService
{
    
    /**
     * Создание в базе данных товаров, производителей, моделей товаров, категорий 
     */

    public function create($rows)
    {
        for ($i=1; $i <= count($rows)-1; $i++) { 
            $manufacturer = Manufacturer::firstOrCreate([
                'name'=>$rows[$i][3],
                
            ]);

            $form = Form::firstOrCreate([
                'name'=>$rows[$i][2],
                
            ]);

            $region = Region::firstOrCreate([
                'name'=>$rows[$i][4],   
            ]);

            $category = Category::firstOrCreate([
                'name'=>$rows[$i][7],   
            ]);

            $subcategory = Subcategory::firstOrCreate([
                'name'=>$rows[$i][6],
            ],['category_id'=>$category->id,]);


            $product = Product::firstOrCreate([
                'name'=>$rows[$i][8],
                'price_purchases'=>$rows[$i][10],
                'price_selling'=>$rows[$i][9],
                'production_time'=>$rows[$i][5],
                'description_main'=>$rows[$i][17],
                'title'=>$rows[$i][14],
                'description'=>$rows[$i][15],
                'sources'=>$rows[$i][11],
                'video'=>$rows[$i][19],
                'code'=>$rows[$i][0],
                'subcategory_id'=>$subcategory->id,
                'manufacturer_id'=>$manufacturer->id,
                'region_id'=>$region->id,
                'model_id'=>$form->id,
                'characteristics_json'=>$this->create_characteristic_array($rows[$i][12]),
                'options_json'=>$this->create_option_array($rows[$i][13]),

            ]);

            
            
            $this->create_reviews($rows[$i][16],$product->id);
             
        }
    }

    /**
     * Подготавливаем опции в массив для добавления к товару в виде Json
     * 
     * @param string $options строка опций с Excel файла которую сгенерировал OpenCart
     * 
     * @return array $options_array подготовленный массив опций
     */

    public function create_option_array($options)
    {
        if (isset($options)) {
            $options = explode("---",$options);
            $options_array = [];
            foreach ($options as $option) {
                if (!empty($option)) {
                    $explode_option = explode("|",$option);
                    $options_array[$explode_option[1]." - ".$explode_option[2]] = [
                        
                        'price_selling'=>$explode_option[7],
                    ];

                    
                    if (isset($explode_option[9])) {
                        $options_array[$explode_option[1]." - ".$explode_option[2]] = array_merge($options_array[$explode_option[1]." - ".$explode_option[2]],
                        ['price_purchases'=>$explode_option[9]]);
                    }else {
                        $options_array[$explode_option[1]." - ".$explode_option[2]] = array_merge($options_array[$explode_option[1]." - ".$explode_option[2]],
                        ['price_purchases'=>$explode_option[8]]);
                    }
                    

                    if (isset($explode_option[10]) or !empty($explode_option[10])) {
                        $options_array[$explode_option[1]." - ".$explode_option[2]] = array_merge($options_array[$explode_option[1]." - ".$explode_option[2]]
                        ,['image'=>$explode_option[10]]);
                    }

                    
                }
                
            }

            return $options_array;
        }
    }

    /**
     * Подготавливаем и добавляем в базу комментарии к товарам
     * 
     * @param string $reviews строка комментриев с Excel файла которую сгенерировал OpenCart
     * @param int $product_id
     */

    public function create_reviews($reviews,$product_id)
    {
        if ($reviews != null) {
            $reviews = explode("---",$reviews);
            foreach ($reviews as $review) {
                if (!empty($review)) {
                    $explode_review = explode("|",$review);
                    $reviewobj = Review::firstOrCreate([
                        'name'=>$explode_review[0],
                        'phone'=>$explode_review[1],
                        'full_name'=>$explode_review[2],
                        'region'=>$explode_review[4],
                        'area'=>$explode_review[5],
                        'place'=>$explode_review[6],
                        'comment'=>$explode_review[7],
                        'product_id'=>$product_id,
                        'date'=>$explode_review[9],
                    ]);

                }
                
            }
            
        }
    }

    /**
     * Подготавливаем характеристики в массив для добавления к товару в виде Json
     * 
     * @param string $characteristics строка характеристик с Excel файла которую сгенерировал OpenCart
     * 
     * @return array $characteristics_array подготовленный массив характеристик
     */

    public function create_characteristic_array($characteristics)
    {
        if ($characteristics != null) {
            $characteristics = explode("|",$characteristics);

            $characteristics_array = [];

            foreach ($characteristics as $characteristic) {
                if (!empty($characteristic)) {
                    $explode_characteristic = explode("---",$characteristic);
                    
                    if (!empty($explode_characteristic[1])) {

                        preg_match("/\{\s*(?P<str>[^}]+?)\s*\}/", $explode_characteristic[1],$str);

                        if (!empty($str['str']) or isset($str['str'])) {
                            
                            $strc = explode("=",$str['str']);

                            $value = preg_replace("/\{.+/","", $explode_characteristic[1]);

                            $characteristics_array[$explode_characteristic[0]] = [
                                'value'=>$value,
                                'feature_title'=>$strc[0],
                                'feature'=>$strc[1],
                                'image'=>$strc[2],
                            ];

                            
                            
                        }else {
                            
                            $characteristics_array[$explode_characteristic[0]] = [
                                'value'=>$explode_characteristic[1],
                                
                            ];

                            

                        }

                    }
                }
            }

            return $characteristics_array;

        }
    }
}


// public function create_characteristic($characteristics,$product)
    // {
    //     if ($characteristics != null) {
    //         $characteristics = explode("|",$characteristics);

    //         $characteristics_array = [];

    //         foreach ($characteristics as $characteristic) {
    //             if (!empty($characteristic)) {
    //                 $explode_characteristic = explode("---",$characteristic);
                    
    //                 if (!empty($explode_characteristic[1])) {

    //                     preg_match("/\{\s*(?P<str>[^}]+?)\s*\}/", $explode_characteristic[1],$str);

    //                     if (!empty($str['str']) or isset($str['str'])) {
                            
    //                         $strc = explode("=",$str['str']);

    //                         $value = preg_replace("/\{.+/","", $explode_characteristic[1]);

    //                         $characteristics_array[$product->code] = [
    //                             'value'=>$value,
    //                             'feature_title'=>$strc[0],
    //                             'feature'=>$strc[1],
    //                             'image'=>$strc[2],
    //                         ];

    //                         $characteristics = Characteristic::firstOrCreate([
    //                             'name'=>$explode_characteristic[0],   
    //                         ]);

    //                         if ($characteristics
    //                         ->product_chracteristics_json != NULL or !empty($characteristics
    //                         ->product_chracteristics_json)) {
    //                             $characteristics
    //                             ->product_chracteristics_json = array_merge(
    //                                     $characteristics->product_chracteristics_json,
    //                                     $characteristics_array);
    //                         }else {
                                
    //                             $characteristics
    //                             ->product_chracteristics_json = $characteristics_array;
    //                         }
                            
    //                     }else {
                            
    //                         $characteristics_array[$product->code] = [
    //                             'value'=>$explode_characteristic[1],
                                
    //                         ];

    //                         $characteristics = Characteristic::firstOrCreate([
    //                             'name'=>$explode_characteristic[0]
    //                         ]);

    //                         if ($characteristics
    //                         ->product_chracteristics_json != NULL or !empty($characteristics
    //                         ->product_chracteristics_json)) {
    //                             $characteristics
    //                             ->product_chracteristics_json = array_merge(
    //                                     $characteristics->product_chracteristics_json,
    //                                     $characteristics_array);
    //                         }else {
                                
    //                             $characteristics
    //                             ->product_chracteristics_json = $characteristics_array;
    //                         }

    //                     }

                        
    //                     $characteristics->save();
    //                     $characteristics->products()->attach($product->id);
        
    //                 }
    //             }
    //         }

            

    //     }
    // }


    

    // public function create_option($options,$product)
    // {
    //     if (isset($options)) {
    //         $options = explode("---",$options);
    //         $options_array = [];
    //         foreach ($options as $option) {
    //             if (!empty($option)) {
    //                 $explode_option = explode("|",$option);
    //                 $options_array[$product->code] = [
    //                     'value'=>$explode_option[2],
    //                     'price_selling'=>$explode_option[7],
    //                 ];

                    
    //                 if (isset($explode_option[9])) {
    //                     $options_array[$product->code] = array_merge($options_array[$product->code],
    //                     ['price_purchases'=>$explode_option[9]]);
    //                 }else {
    //                     $options_array[$product->code] = array_merge($options_array[$product->code],
    //                     ['price_purchases'=>$explode_option[8]]);
    //                 }
                    

    //                 if (isset($explode_option[10]) or !empty($explode_option[10])) {
    //                     $options_array[$product->code] = array_merge($options_array[$product->code]
    //                     ,['image'=>$explode_option[10]]);
    //                 }

    //                 $options = Option::firstOrCreate([
    //                     'name'=>$explode_option[1],   
    //                 ]);

    //                 if ($options
    //                 ->product_options_json != null or !empty($options
    //                         ->product_options_json)) {
    //                     $options
    //                     ->product_options_json = array_merge(
    //                             $options->product_options_json,
    //                             $options_array);
    //                 }else {
    //                     $options
    //                     ->product_options_json = $options_array;
    //                 }

                    
    //                 $options->save();
    //                 $options->products()->attach($product->id);
    //             }
                
    //         }

            
    //     }
    // }