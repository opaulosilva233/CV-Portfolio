<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isMysql = Schema::getConnection()->getDriverName() === 'mysql';

        // 1. Experiences table
        Schema::table('experiences', function (Blueprint $table) use ($isMysql) {
            if (!$isMysql) {
                $table->binary('image_data')->nullable();
            }
            $table->string('image_mime_type', 100)->nullable();
        });
        if ($isMysql) {
            DB::statement('ALTER TABLE experiences ADD image_data LONGBLOB NULL');
        }

        // 2. Education table
        Schema::table('education', function (Blueprint $table) use ($isMysql) {
            if (!$isMysql) {
                $table->binary('image_data')->nullable();
            }
            $table->string('image_mime_type', 100)->nullable();
        });
        if ($isMysql) {
            DB::statement('ALTER TABLE education ADD image_data LONGBLOB NULL');
        }

        // 3. Site settings table
        Schema::table('site_settings', function (Blueprint $table) use ($isMysql) {
            if (!$isMysql) {
                $table->binary('image_data')->nullable();
            }
            $table->string('image_mime_type', 100)->nullable();
        });
        if ($isMysql) {
            DB::statement('ALTER TABLE site_settings ADD image_data LONGBLOB NULL');
        }

        // 4. Project images table
        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('filename')->nullable();
            $table->binary('image_data');
            $table->string('mime_type', 100)->default('image/png');
            $table->text('description')->nullable();
            $table->boolean('is_principal')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        if ($isMysql) {
            DB::statement('ALTER TABLE project_images MODIFY image_data LONGBLOB NOT NULL');
        }

        // 5. Data Migration from storage/
        $this->migrateExperiences();
        $this->migrateEducation();
        $this->migrateSettings();
        $this->migrateProjects();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_images');

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'image_mime_type']);
        });

        Schema::table('education', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'image_mime_type']);
        });

        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'image_mime_type']);
        });
    }

    private function migrateExperiences(): void
    {
        $dir = storage_path('experiences');
        if (!is_dir($dir)) return;

        $items = glob($dir . '/*');
        foreach ($items as $item) {
            if (!is_dir($item)) continue;
            $id = basename($item);
            $files = glob($item . '/logo.*');
            if (!empty($files)) {
                $file = $files[0];
                $data = file_get_contents($file);
                $mime = @mime_content_type($file) ?: 'image/png';
                DB::table('experiences')->where('id', $id)->update([
                    'image_data' => $data,
                    'image_mime_type' => $mime,
                ]);
            }
        }
    }

    private function migrateEducation(): void
    {
        $dir = storage_path('educations');
        if (!is_dir($dir)) return;

        $items = glob($dir . '/*');
        foreach ($items as $item) {
            if (!is_dir($item)) continue;
            $id = basename($item);
            $files = glob($item . '/logo.*');
            if (!empty($files)) {
                $file = $files[0];
                $data = file_get_contents($file);
                $mime = @mime_content_type($file) ?: 'image/png';
                DB::table('education')->where('id', $id)->update([
                    'image_data' => $data,
                    'image_mime_type' => $mime,
                ]);
            }
        }
    }

    private function migrateSettings(): void
    {
        $dir = storage_path('settings');
        if (!is_dir($dir)) return;

        $files = glob($dir . '/hero_image.*');
        if (!empty($files)) {
            $file = $files[0];
            $data = file_get_contents($file);
            $mime = @mime_content_type($file) ?: 'image/jpeg';
            DB::table('site_settings')->where('key', 'hero_image')->update([
                'image_data' => $data,
                'image_mime_type' => $mime,
            ]);
        }
    }

    private function migrateProjects(): void
    {
        $dir = storage_path('projects');
        if (!is_dir($dir)) return;

        $items = glob($dir . '/*');
        foreach ($items as $item) {
            if (!is_dir($item)) continue;
            $id = (int)basename($item);

            $projectExists = DB::table('projects')->where('id', $id)->exists();
            if (!$projectExists) continue;

            $metadataPath = $item . '/metadata.json';
            $metadata = [];
            if (file_exists($metadataPath)) {
                $metadata = json_decode(file_get_contents($metadataPath), true) ?: [];
            }

            $allFiles = glob($item . '/*.*');
            foreach ($allFiles as $filePath) {
                $filename = basename($filePath);
                if ($filename === 'metadata.json') continue;

                $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                $isPrincipal = ($nameWithoutExt === 'principal');
                $sortOrder = $isPrincipal ? 0 : (is_numeric($nameWithoutExt) ? (int)$nameWithoutExt : 999);

                $data = file_get_contents($filePath);
                $mime = @mime_content_type($filePath) ?: 'image/png';
                $ptDesc = $metadata['pt'][$nameWithoutExt] ?? ($metadata['en'][$nameWithoutExt] ?? '');

                $projectImageId = DB::table('project_images')->insertGetId([
                    'project_id' => $id,
                    'filename' => $filename,
                    'image_data' => $data,
                    'mime_type' => $mime,
                    'description' => $ptDesc,
                    'is_principal' => $isPrincipal,
                    'sort_order' => $sortOrder,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Migrate translations for other languages
                foreach ($metadata as $locale => $keys) {
                    if ($locale === 'pt') continue;
                    if (!empty($keys[$nameWithoutExt])) {
                        DB::table('translations')->insert([
                            'translatable_type' => \App\Models\ProjectImage::class,
                            'translatable_id' => $projectImageId,
                            'field' => 'description',
                            'locale' => $locale,
                            'value' => $keys[$nameWithoutExt],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
};
