<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Добавление характеристик к товару в битрикс
 */

class AddCharacteristicToProductBitrixJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bx_service;
    
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
        $this->bx_service->add_characteristic_to_product();
        
    }
}
