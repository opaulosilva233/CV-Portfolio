<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Models\ExperienceRole;
use App\Models\Interest;
use App\Models\PageSection;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TranslationController extends Controller
{
    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function index(Request $request)
    {
        $typeFilter = $request->input('type', 'all');
        $statusFilter = $request->input('status', 'all'); // all, incomplete, complete
        $search = $request->input('search', '');

        $targetLocales = $this->translationService->getTargetLocales();
        $sourceLocale = $this->translationService->getSourceLocale();

        // 1. Gather all translatable items from models
        $items = $this->collectAllTranslatableItems($targetLocales);

        // 2. Compute overall summary stats before filtering
        $totalItems = count($items);
        $completeItems = count(array_filter($items, fn($item) => $item['is_complete']));
        $incompleteItems = $totalItems - $completeItems;

        // 3. Apply search & filters in-memory
        if ($typeFilter && $typeFilter !== 'all') {
            $items = array_values(array_filter($items, fn($item) => $item['model_type'] === $typeFilter));
        }

        if ($statusFilter === 'complete') {
            $items = array_values(array_filter($items, fn($item) => $item['is_complete']));
        } elseif ($statusFilter === 'incomplete') {
            $items = array_values(array_filter($items, fn($item) => !$item['is_complete']));
        }

        if (!empty($search)) {
            $searchLower = mb_strtolower(trim($search));
            $items = array_values(array_filter($items, function ($item) use ($searchLower) {
                if (str_contains(mb_strtolower($item['title']), $searchLower)) return true;
                if (!empty($item['subtitle']) && str_contains(mb_strtolower($item['subtitle']), $searchLower)) return true;
                if (str_contains(mb_strtolower($item['type_label']), $searchLower)) return true;

                foreach ($item['fields'] as $f) {
                    if (str_contains(mb_strtolower($f['original_value'] ?? ''), $searchLower)) return true;
                    foreach ($f['translations'] as $tVal) {
                        if (str_contains(mb_strtolower($tVal ?? ''), $searchLower)) return true;
                    }
                }
                return false;
            }));
        }

        $modelTypes = [
            ['value' => 'all', 'label' => __('All Types')],
            ['value' => SiteSetting::class, 'label' => __('Site Settings')],
            ['value' => ExperienceRole::class, 'label' => __('Experiences & Roles')],
            ['value' => Education::class, 'label' => __('Education')],
            ['value' => Project::class, 'label' => __('Projects')],
            ['value' => Skill::class, 'label' => __('Skills')],
            ['value' => PageSection::class, 'label' => __('Sections')],
            ['value' => Interest::class, 'label' => __('Interests')],
        ];

        return Inertia::render('Admin/Translations/Index', [
            'items' => $items,
            'summary' => [
                'total_items' => $totalItems,
                'complete_items' => $completeItems,
                'incomplete_items' => $incompleteItems,
                'target_locales' => $targetLocales,
                'source_locale' => $sourceLocale,
            ],
            'model_types' => $modelTypes,
            'filters' => [
                'type' => $typeFilter,
                'status' => $statusFilter,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Batch translate all or only missing records.
     */
    public function translateAll(Request $request)
    {
        $force = $request->boolean('force', false);

        $result = $this->translationService->translateAll($force);

        $message = $force
            ? __("Full translation completed for :total items.", ['total' => $result['total_models']])
            : __("Missing translations auto-translated successfully.");

        return redirect()->back()->with('success', $message);
    }

    /**
     * Translate a specific item (optionally selected fields/locales).
     */
    public function translateItem(Request $request)
    {
        $validated = $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required',
            'fields' => 'nullable|array',
            'locales' => 'nullable|array',
            'force' => 'nullable|boolean',
        ]);

        $modelClass = $validated['model_type'];
        if (!class_exists($modelClass)) {
            return redirect()->back()->with('error', 'Invalid model class.');
        }

        $model = $modelClass::find($validated['model_id']);
        if (!$model) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        $force = $validated['force'] ?? true;
        $fields = $validated['fields'] ?? (method_exists($model, 'getTranslatableFields') ? $model->getTranslatableFields() : []);
        $locales = $validated['locales'] ?? $this->translationService->getTargetLocales();

        if (!empty($fields)) {
            $this->translationService->translateSpecificFields($model, $fields, $locales, $force);
        }

        // Handle Project Gallery metadata if applicable
        if ($model instanceof Project) {
            $this->translationService->translateProjectGallery($model, $force);
        }

        return redirect()->back()->with('success', __('Item translated successfully!'));
    }

    /**
     * Translate a text snippet on demand (AJAX / instant auto-fill in modal).
     */
    public function translateText(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'target_locale' => 'required|string|in:en,nl,pt',
            'source_locale' => 'nullable|string|in:en,nl,pt',
        ]);

        $sourceLocale = $validated['source_locale'] ?? $this->translationService->getSourceLocale();
        $targetLocale = $validated['target_locale'];

        $translated = $this->translationService->translateText($validated['text'], $sourceLocale, $targetLocale);

        return response()->json([
            'success' => $translated !== null,
            'translated_text' => $translated ?? $validated['text'],
        ]);
    }

    /**
     * Save manual edits made to translations of an item.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required',
            'translations' => 'required|array',
            'translations.*.field' => 'required|string',
            'translations.*.locale' => 'required|string',
            'translations.*.value' => 'nullable|string',
            'gallery_translations' => 'nullable|array',
        ]);

        $modelClass = $validated['model_type'];
        if (!class_exists($modelClass)) {
            return redirect()->back()->with('error', 'Invalid model type.');
        }

        $model = $modelClass::find($validated['model_id']);
        if (!$model) {
            return redirect()->back()->with('error', 'Item not found.');
        }

        // Update database translations
        foreach ($validated['translations'] as $t) {
            $val = trim($t['value'] ?? '');

            if ($val === '') {
                Translation::where('translatable_type', $modelClass)
                    ->where('translatable_id', $model->id)
                    ->where('field', $t['field'])
                    ->where('locale', $t['locale'])
                    ->delete();
            } else {
                Translation::updateOrCreate(
                    [
                        'translatable_type' => $modelClass,
                        'translatable_id' => $model->id,
                        'field' => $t['field'],
                        'locale' => $t['locale'],
                    ],
                    [
                        'value' => $val,
                    ]
                );
            }
        }

        // Update Project gallery metadata.json if provided
        if ($model instanceof Project && !empty($validated['gallery_translations'])) {
            $dir = storage_path('projects/' . $model->id);
            $metadataPath = $dir . '/metadata.json';

            if (file_exists($metadataPath)) {
                $fullMetadata = json_decode(file_get_contents($metadataPath), true) ?: [];
                foreach ($validated['gallery_translations'] as $loc => $imgMap) {
                    if (!isset($fullMetadata[$loc])) {
                        $fullMetadata[$loc] = [];
                    }
                    foreach ($imgMap as $imgKey => $desc) {
                        $fullMetadata[$loc][$imgKey] = $desc;
                    }
                }
                File::put($metadataPath, json_encode($fullMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        $this->translationService->refreshTranslatedContentCaches($model);

        return redirect()->back()->with('success', __('Translations saved successfully!'));
    }

    /**
     * Collect and structure all translatable items across the application.
     */
    protected function collectAllTranslatableItems(array $targetLocales): array
    {
        $items = [];

        // 1. Site Settings (only translatable keys)
        $settings = SiteSetting::with('translations')->get();
        foreach ($settings as $setting) {
            if (!$setting->isTranslatable()) {
                continue;
            }

            $fieldsData = $this->buildFieldsData($setting, ['value'], $targetLocales);
            $stats = $this->computeItemStats($fieldsData, $targetLocales);

            $humanKey = Str::headline(str_replace(['_', 'seo'], [' ', 'SEO'], $setting->key));

            $items[] = [
                'uid' => SiteSetting::class . ':' . $setting->id,
                'model_type' => SiteSetting::class,
                'model_id' => $setting->id,
                'type_label' => __('Site Setting'),
                'type_color' => 'amber',
                'title' => $humanKey,
                'subtitle' => 'Key: ' . $setting->key,
                'fields' => $fieldsData,
                'gallery_items' => [],
                'is_complete' => $stats['is_complete'],
                'stats' => $stats,
            ];
        }

        // 2. Experience Roles
        $roles = ExperienceRole::with(['translations', 'experience'])->get();
        foreach ($roles as $role) {
            $fieldsData = $this->buildFieldsData($role, ['role', 'description'], $targetLocales);
            $stats = $this->computeItemStats($fieldsData, $targetLocales);

            $company = $role->experience ? $role->experience->company : __('Unknown Company');

            $items[] = [
                'uid' => ExperienceRole::class . ':' . $role->id,
                'model_type' => ExperienceRole::class,
                'model_id' => $role->id,
                'type_label' => __('Experience Role'),
                'type_color' => 'blue',
                'title' => $role->role ?: __('Untitled Role'),
                'subtitle' => $company . ' (' . ($role->start_date ? $role->start_date->format('Y') : '') . ' - ' . ($role->is_current ? __('Present') : ($role->end_date ? $role->end_date->format('Y') : '')) . ')',
                'fields' => $fieldsData,
                'gallery_items' => [],
                'is_complete' => $stats['is_complete'],
                'stats' => $stats,
            ];
        }

        // 3. Education
        $educations = Education::with('translations')->get();
        foreach ($educations as $edu) {
            $fieldsData = $this->buildFieldsData($edu, ['degree', 'description'], $targetLocales);
            $stats = $this->computeItemStats($fieldsData, $targetLocales);

            $items[] = [
                'uid' => Education::class . ':' . $edu->id,
                'model_type' => Education::class,
                'model_id' => $edu->id,
                'type_label' => __('Education'),
                'type_color' => 'emerald',
                'title' => $edu->degree ?: __('Untitled Degree'),
                'subtitle' => $edu->institution . ' • ' . $edu->type,
                'fields' => $fieldsData,
                'gallery_items' => [],
                'is_complete' => $stats['is_complete'],
                'stats' => $stats,
            ];
        }

        // 4. Projects
        $projects = Project::with('translations')->get();
        foreach ($projects as $project) {
            $fieldsData = $this->buildFieldsData($project, ['description'], $targetLocales);
            $galleryData = $this->buildProjectGalleryData($project, $targetLocales);
            
            $stats = $this->computeItemStats($fieldsData, $targetLocales, $galleryData);

            $items[] = [
                'uid' => Project::class . ':' . $project->id,
                'model_type' => Project::class,
                'model_id' => $project->id,
                'type_label' => __('Project'),
                'type_color' => 'purple',
                'title' => $project->title,
                'subtitle' => $project->slug,
                'fields' => $fieldsData,
                'gallery_items' => $galleryData,
                'is_complete' => $stats['is_complete'],
                'stats' => $stats,
            ];
        }

        // 5. Skills
        $skills = Skill::with('translations')->get();
        foreach ($skills as $skill) {
            $fieldsData = $this->buildFieldsData($skill, ['category'], $targetLocales);
            $stats = $this->computeItemStats($fieldsData, $targetLocales);

            $items[] = [
                'uid' => Skill::class . ':' . $skill->id,
                'model_type' => Skill::class,
                'model_id' => $skill->id,
                'type_label' => __('Skill'),
                'type_color' => 'cyan',
                'title' => $skill->name,
                'subtitle' => 'Category: ' . $skill->category,
                'fields' => $fieldsData,
                'gallery_items' => [],
                'is_complete' => $stats['is_complete'],
                'stats' => $stats,
            ];
        }

        // 6. Page Sections
        $sections = PageSection::with('translations')->get();
        foreach ($sections as $sec) {
            $fieldsData = $this->buildFieldsData($sec, ['title'], $targetLocales);
            $stats = $this->computeItemStats($fieldsData, $targetLocales);

            $items[] = [
                'uid' => PageSection::class . ':' . $sec->id,
                'model_type' => PageSection::class,
                'model_id' => $sec->id,
                'type_label' => __('Section'),
                'type_color' => 'rose',
                'title' => $sec->title ?: $sec->name,
                'subtitle' => 'Section: ' . $sec->name,
                'fields' => $fieldsData,
                'gallery_items' => [],
                'is_complete' => $stats['is_complete'],
                'stats' => $stats,
            ];
        }

        // 7. Interests
        $interests = Interest::with('translations')->get();
        foreach ($interests as $interest) {
            $fieldsData = $this->buildFieldsData($interest, ['name', 'description', 'category'], $targetLocales);
            $stats = $this->computeItemStats($fieldsData, $targetLocales);

            $items[] = [
                'uid' => Interest::class . ':' . $interest->id,
                'model_type' => Interest::class,
                'model_id' => $interest->id,
                'type_label' => __('Interest'),
                'type_color' => 'teal',
                'title' => $interest->name,
                'subtitle' => 'Category: ' . $interest->category,
                'fields' => $fieldsData,
                'gallery_items' => [],
                'is_complete' => $stats['is_complete'],
                'stats' => $stats,
            ];
        }

        return $items;
    }

    /**
     * Build field representation for an item.
     */
    protected function buildFieldsData($model, array $fields, array $targetLocales): array
    {
        $fieldsData = [];
        $existingTranslations = $model->translations ?? collect();

        foreach ($fields as $field) {
            $originalVal = $model->$field ?? '';

            $localeMap = [];
            foreach ($targetLocales as $loc) {
                $t = $existingTranslations->where('field', $field)->where('locale', $loc)->first();
                $localeMap[$loc] = $t ? $t->value : '';
            }

            $fieldsData[] = [
                'field' => $field,
                'label' => Str::headline($field),
                'original_value' => $originalVal,
                'translations' => $localeMap,
            ];
        }

        return $fieldsData;
    }

    /**
     * Build project gallery metadata structure.
     */
    protected function buildProjectGalleryData(Project $project, array $targetLocales): array
    {
        $dir = storage_path('projects/' . $project->id);
        $metadataPath = $dir . '/metadata.json';

        if (!file_exists($metadataPath)) {
            return [];
        }

        $json = json_decode(file_get_contents($metadataPath), true) ?: [];
        $ptDescriptions = $json['pt'] ?? [];

        $gallery = [];
        foreach ($ptDescriptions as $imgKey => $origDesc) {
            if (empty($origDesc)) continue;

            $localeMap = [];
            foreach ($targetLocales as $loc) {
                $localeMap[$loc] = $json[$loc][$imgKey] ?? '';
            }

            $gallery[] = [
                'image_key' => $imgKey,
                'label' => 'Image ' . $imgKey . ' Description',
                'original_value' => $origDesc,
                'translations' => $localeMap,
            ];
        }

        return $gallery;
    }

    /**
     * Compute completion stats for an item.
     */
    protected function computeItemStats(array $fieldsData, array $targetLocales, array $galleryData = []): array
    {
        $allFields = array_merge($fieldsData, $galleryData);
        $totalSlots = count($allFields) * count($targetLocales);
        $translatedSlots = 0;

        $byLocale = [];
        foreach ($targetLocales as $loc) {
            $byLocale[$loc] = [
                'total' => count($allFields),
                'translated' => 0,
            ];
        }

        foreach ($allFields as $f) {
            foreach ($targetLocales as $loc) {
                $val = trim($f['translations'][$loc] ?? '');
                if ($val !== '') {
                    $translatedSlots++;
                    $byLocale[$loc]['translated']++;
                }
            }
        }

        $percentage = $totalSlots > 0 ? (int) round(($translatedSlots / $totalSlots) * 100) : 100;
        $isComplete = $totalSlots > 0 ? ($translatedSlots === $totalSlots) : true;

        return [
            'total_slots' => $totalSlots,
            'translated_slots' => $translatedSlots,
            'percentage' => $percentage,
            'is_complete' => $isComplete,
            'by_locale' => $byLocale,
        ];
    }
}
