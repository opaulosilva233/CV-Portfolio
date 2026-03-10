<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TranslateModelJob implements ShouldQueue
{
    use Queueable;

    public $model;

    /**
     * Create a new job instance.
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\TranslationService $service): void
    {
        $service->translateModel($this->model);
    }
}
