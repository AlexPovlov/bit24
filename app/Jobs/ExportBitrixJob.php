<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Characteristic;
use App\BitrixService;
use App\Bitrix24\Bitrix24APIException;

class ExportBitrixJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bx_service;
    public $timeout = 5000;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bx)
    {
        $this->bx_service = $bx;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->chain([
            new AddSectionBitrixJob($this->bx_service),
        ]);
        
    }
}
