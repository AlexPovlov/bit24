<?php

namespace App\Imports;


use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\ImportOpencartService;


class UsersImport implements ToCollection
{
    
    public function collection(Collection $rows)
    {
        //dump(explode("|",$rows[42][12]));
        (new ImportOpencartService)->create($rows); 
    }

    
}
