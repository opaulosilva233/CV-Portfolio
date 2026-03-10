<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TranslateAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate all records to target languages';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\TranslationService $service)
    {
        $this->info('Starting translation of all content...');

        $models = [
            \App\Models\Project::class,
            \App\Models\Experience::class,
            \App\Models\ExperienceRole::class,
            \App\Models\Education::class,
            \App\Models\Skill::class,
            \App\Models\PageSection::class,
            \App\Models\SiteSetting::class,
        ];

        foreach ($models as $modelClass) {
            $this->info("Translating {$modelClass}...");
            $records = $modelClass::all();
            
            $bar = $this->output->createProgressBar(count($records));
            $bar->start();

            foreach ($records as $record) {
                $service->translateModel($record);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info('Translations completed successfully!');
    }
}
