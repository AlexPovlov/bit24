<?php

namespace App\Http\Controllers;

use App\BitrixService;
use Illuminate\Http\Request;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Product;
use App\Subcategory;
use App\Category;
use App\Form;
use App\Characteristic;
use App\Manufacturer;
use App\Specification_characteristic;
use App\Jobs\ExportBitrixJob;
use App\Jobs\AddSectionBitrixJob;
use App\Jobs\AddProductBitrixJob;
use App\Jobs\AddCharacteristicToProductBitrixJob;
use App\Jobs\ImportOpencartJob;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class MainController extends Controller
{
    public function index()
    {
        
        //dump(Product::find(1)->characteristics_json['Марка стали рамы']['value']);
        
      
        //(new BitrixService)->add_porduct();
       
        
        //dump(Product::find(40));
        //$this->dispatch(new ImportOpencartJob(new Excel));
        $this->dispatch(new AddCharacteristicToProductBitrixJob(new BitrixService));
        //Excel::import(new UsersImport, Storage::disk('local')->path('artarus.xlsx'));
      

    }


    
}
