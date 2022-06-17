<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Storage;

/**
 * Импорто даннхы с эксель файла в базу
 */

class ImportOpencartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importclass;
    protected $documetexcel;
    public $timeout = 5000;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($importclass)
    {
        $this->importclass = $importclass;
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function handle()
    {
        Excel::import(new UsersImport, Storage::disk('local')->path('artarus.xlsx'));
    }
}
