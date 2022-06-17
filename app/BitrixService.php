<?php

namespace App;

use App\Bitrix24\Bitrix24API;
use App\Bitrix24\Bitrix24APIException;
use App\Product;

/**
 * Класс работы с bx24 
 */

class BitrixService 
{
    /**
     * обьект класса обращений к битрикс24
     * 
     * @var App\Bitrix24\Bitrix24API
     */
    private $bx24;

    /**
     * Подключаемся
     */
        
    public function __construct()
    {
        $webhookURL = config('services.bitrix24.url').config('services.bitrix24.token');
        $this->bx24 = new Bitrix24API($webhookURL);
        
    }


    /**
     * Подготавливаем данные с битрикс24 для простого поиска по ним
     * @param Generator $generator данные получаемые с битрикс
     * @param string $search_field поисковое поле по которому после можно будет проверять наличие в битриксе24
     */

    public function prepare_items(\Generator $generator,$search_field)
    {
        $prepare_items_arr = [];
        foreach ($generator as $items) {
            foreach ($items as $items) {
                $prepare_items_arr[$items['ID']] = $items[$search_field];
            }
        }

        return $prepare_items_arr;
    }

    /**
     * добавляем характеристики к продукту
     */

    public function add_characteristic_to_product()
    {
        $products = Product::all();
        
        $property_fields = $this->prepare_items($this->bx24->getProductPropertyList(),'NAME');
        $products_bitrix = $this->prepare_items($this->bx24->getProductList(),'NAME');
        
        
        $product_arr = [];
        $count = count($products)-1;
        for($i = 0; $i < $count; $i++) {
            $chrac_arr = [];
                $product_id = array_search($products[$i]->name,$products_bitrix);
                if ($products[$i]->characteristics_json != null) {
                    foreach ($products[$i]->characteristics_json as $key => $characteristic) {
                        $property_id = array_search($key,$property_fields);
                        $chrac_arr = array_merge($chrac_arr,['PROPERTY_'.$property_id => $characteristic['value']]);
                        
                    }
                    
                $this->bx24->updateProduct($product_id,$chrac_arr);
                sleep(1);
                }
                
        }

    }

    /**
     * очистка мусора в битрикс24
     */

    public function delete_musor()
    {

        $products_bitrix = $this->bx24->getProductList();

        $arr = [];
        foreach ($products_bitrix as $key => $products) {
            foreach ($products as $key => $product) {
                if ($product['PRICE'] == null) {
                    
                    $arr[] = $product['ID'];
                }
            }
        }

        $this->bx24->deleteProducts($arr);
    }

    /**
     * добавляем поля характеристик
     */

    public function add_characteristics($characteristics)
    {
        try {

        $product_property_arr = $this->prepare_items($this->bx24->getProductPropertyList(),'NAME');
        $ids = [];
        foreach ($characteristics as $key => $characteristic) {
            if (!in_array($key,$product_property_arr)) {
                $ids[] = $this->bx24->addProductProperty([
                        "ACTIVE"=> "Y",
                        "IBLOCK_ID"=> 24,
                        "NAME"=> $key,
                        "SORT"=> 500,
                ]);
                sleep(1);
            }
            
        }

        return $ids;

        } catch (Bitrix24APIException $e) {
            logs()->warning('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
        }

    }

    /**
     * добавляем одно поле характеристики
     * @param string $name название поля
     */

    public function add_characteristic($name)
    {
        return $this->bx24->addProductProperty([
                        "ACTIVE"=> "Y",
                        "IBLOCK_ID"=> 24,
                        "NAME"=> $name,
                        "SORT"=> 500,
                ]);
                
            
    }

    /**
     * Добавляем товары
     */

    public function add_porduct()
    {
        $products = Product::with('form')->get();
        $products_bx = $this->prepare_items($this->bx24->getProductList(),'NAME');
        $sections_bx = $this->prepare_items($this->bx24->getProductSectionList(),'NAME');
        $property_fields = $this->prepare_items($this->bx24->getProductPropertyList(),'NAME');

        foreach ($products as $key => $product) {
            $sec_id = array_search($product->name,$sections_bx);
            $product_arr = [];
            if (is_int($sec_id)) {

                $this->options($product->options_json,$products_bx,$sec_id);

                $product_arr = $this->charac($product->characteristics_json,$property_fields);

                $prod = [
                    'NAME'=>$product->name,
                    'ACTIVE'=>'Y',
                    'SECTION_ID'=>$sec_id,
                    'DESCRIPTION'=>$product->description_main,
                    'PRICE'=>$product->price_selling,
                    'CURRENCY_ID'=>'RUB',
                    'PROPERTY_106'=>$product->form->name,
                    'PROPERTY_136'=>$product->code,
                    'PROPERTY_142'=>$product->production_time,
                    'PROPERTY_146'=>$product->price_purchases."|RUB",
                    'SORT' => 500
                ];

                $product_arr = array_merge($product_arr,$prod);

                $product_id = array_search($product->name,$products_bx);

                if (is_int($product_id)) {
                    $this->bx24->updateProduct($product_id,$product_arr);
            
                }else {
                    $this->bx24->addProduct($product_arr);
                }
    
            }
            sleep(1);
        }
    }



    protected function charac($characteristics,$property_fields)
    {
        if ($characteristics != null) {
            $product_arr = [];
            foreach ($characteristics as $key => $characteristic) {
                $property_id = array_search($key,$property_fields);
                if (is_int($property_id)) {
                    $product_arr = array_merge($product_arr,
                                    ['PROPERTY_'.$property_id => $characteristic['value']]);
                }else {
                    $product_arr = array_merge($product_arr,
                                    ['PROPERTY_'.$this->add_characteristic($key) => $characteristic['value']]);
                    
                }
                
            }

            return $product_arr;
        }
        
    }

    public function options($options,$products_bx,$sec_id)
    {
        if ($options != null) {
            foreach ($options as $key => $option) {

                $prod = [
                    'NAME'=>$key,
                    'ACTIVE'=>'Y',
                    'SECTION_ID'=>$sec_id,
                    'CURRENCY_ID'=>'RUB',
                    'PRICE'=>$option['price_selling'],
                    'PROPERTY_146' => $option['price_purchases'].'|RUB',
                    'SORT' => 500
                ];

                if (empty($option['price_purchases'])) {
                    $prod['PROPERTY_146'] = '0|RUB';
                }
    
                $option_id = array_search($key,$products_bx);
                    
                    if (is_int($option_id)) {
                        $this->bx24->updateProduct($option_id,$prod);
                
                    }else {
                        $this->bx24->addProduct($prod);
                    }
            }
        }
        
    }

    /**
     * @param string $search название раздела по которому будем искать
     * @param array $sections_bx массив разделов с битрикс24
     * @param string $parent_section родительский раздел
     * 
     * @return int $sec_id найденный или добавленный в битрикс24 раздел
     */

    protected function search_section_and_add($search,$sections_bx,$parent_section)
    {
        $sec_id = array_search($search,$sections_bx);

            if (!is_int($sec_id)) {
                $sec_id = $this->bx24->addProductSection([
                    'CATALOG_ID'=> 24,
                    'NAME'=> $search,
                    'SECTION_ID'=> $parent_section	
                ]);
            }

        return $sec_id;
    }

    /**
     * Добавляем разделы
     */

    public function add_section()
    {
        $manufacturers = Manufacturer::with('products')->get();
        $categories = Category::all();
        $sections_bx = $this->prepare_items($this->bx24->getProductSectionList(),'NAME');
        
        foreach ($manufacturers as $key => $manufacturer) {
           
            $sec_id = $this->search_section_and_add(
                            $manufacturer->name,
                            $sections_bx, null
                        );
                       
            foreach ($categories as $key => $category) {

                $sec_id_cat = $this->search_section_and_add(
                    $category->name,
                    $sections_bx, $sec_id
                );

               
                foreach ($manufacturer->products as $key => $product) {
                    if ($product->subcategory->category->id == $category->id) {
                        $this->search_section_and_add(
                            $product->name,
                            $sections_bx, $sec_id_cat
                        );
                        sleep(1);
                    }
                    

                }
            }
        }
    }



    

    

}
