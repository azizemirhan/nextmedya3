<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Services\SchemaGeneratorService;
use App\Services\SeoAnalysisService;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    use ImageUploadTrait;

    protected $seoService;
    protected $schemaService;

    public function __construct(SeoAnalysisService $seoService, SchemaGeneratorService $schemaService)
    {
        $this->seoService = $seoService;
        $this->schemaService = $schemaService;
    }

    /**
     * Tüm sayfaları listeler.
     */
    public function index()
    {
        $pages = Page::latest()->paginate(20);
        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Yeni sayfa oluşturma formunu gösterir.
     */
    public function create()
    {
        $page = new Page();
        $templates = config('page_templates', []);

        // Aktif dilleri al
        $activeLanguageCodes = setting('active_languages', ['tr', 'en']);
        if (!is_array($activeLanguageCodes)) {
            $activeLanguageCodes = ['tr', 'en'];
        }
        $allLanguages = config('languages.supported', []);
        $activeLanguages = collect($allLanguages)
            ->filter(fn($lang, $code) => in_array($code, $activeLanguageCodes))
            ->sortBy(fn($lang, $code) => array_search($code, $activeLanguageCodes));

        return view('admin.pages.create', compact('page', 'templates', 'activeLanguages'));
    }

    /**
     * Yeni bir sayfayı veritabanına kaydeder.
     */
    public function store(Request $request)
    {
        // Gelen verileri doğrula - SEO alanları eklendi
        $validatedData = $request->validate([
            'title' => 'required|array',
            'title.tr' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'banner_title' => 'nullable|array',
            'banner_subtitle' => 'nullable|array',
            'slug' => 'required|string|max:255|unique:pages,slug',
            'status' => 'nullable|in:draft,published',
            'template' => 'nullable|string|in:' . implode(',', array_keys(config('page_templates', []))),

            // SEO Temel Alanları
            'seo_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'keywords' => 'nullable|array',
            'focus_keyword' => 'nullable|array',
            'canonical_url' => 'nullable|url',
            'index_status' => 'nullable|in:index,noindex',
            'follow_status' => 'nullable|in:follow,nofollow',

            // Open Graph
            'og_title' => 'nullable|array',
            'og_description' => 'nullable|array',
            'og_image' => 'nullable|string',

            // Twitter Card
            'twitter_card_type' => 'nullable|in:summary,summary_large_image',
            'twitter_title' => 'nullable|array',
            'twitter_description' => 'nullable|array',
            'twitter_image' => 'nullable|string',

            // Meta Robots
            'meta_noindex' => 'nullable|boolean',
            'meta_nofollow' => 'nullable|boolean',
            'meta_noarchive' => 'nullable|boolean',
            'meta_nosnippet' => 'nullable|boolean',

            // Schema
            'schema_article_type' => 'nullable|string',
            'schema_faq_items' => 'nullable|array',
            'schema_product_price' => 'nullable|numeric',
            'schema_product_currency' => 'nullable|string|max:3',
            'schema_product_availability' => 'nullable|string',
            'schema_product_rating' => 'nullable|numeric|min:1|max:5',
            'schema_product_review_count' => 'nullable|integer',
            'schema_service_area' => 'nullable|array',
            'schema_service_provider' => 'nullable|array',

            // Redirect
            'redirect_enabled' => 'nullable|boolean',
            'redirect_url' => 'nullable|url',
            'redirect_type' => 'nullable|in:301,302',
        ]);

        // Boolean alanları düzelt
        $validatedData['status'] = $validatedData['status'] ?? 'draft';
        $validatedData['redirect_enabled'] = $request->has('redirect_enabled') ? 1 : 0;
        $validatedData['meta_noindex'] = $request->has('meta_noindex') ? 1 : 0;
        $validatedData['meta_nofollow'] = $request->has('meta_nofollow') ? 1 : 0;
        $validatedData['meta_noarchive'] = $request->has('meta_noarchive') ? 1 : 0;
        $validatedData['meta_nosnippet'] = $request->has('meta_nosnippet') ? 1 : 0;

        // Varsayılan değerler
        $validatedData['index_status'] = $validatedData['index_status'] ?? 'index';
        $validatedData['follow_status'] = $validatedData['follow_status'] ?? 'follow';
        $validatedData['schema_article_type'] = $validatedData['schema_article_type'] ?? 'WebPage';

        // Sayfayı oluştur
        $page = Page::create($validatedData);

        // SEO Analizi Yap
        $primaryLocale = config('app.locale', 'tr');
        if (!empty($validatedData['focus_keyword'][$primaryLocale])) {
            try {
                $analysis = $this->seoService->analyze($page, $primaryLocale);
                $page->seo_score = $analysis['score'] ?? 0;
                $page->seo_analysis_results = $analysis;
                $page->seo_last_analyzed_at = now();
                $page->save();
            } catch (\Exception $e) {
                \Log::error('SEO Analysis Error: ' . $e->getMessage());
            }
        }

        // Schema JSON Oluştur
        try {
            $schema = $this->schemaService->generate($page, $primaryLocale);
            if ($schema) {
                $page->generated_schema_json = $schema;
                $page->save();
            }
        } catch (\Exception $e) {
            \Log::error('Schema Generation Error: ' . $e->getMessage());
        }

        // Eğer bir şablon seçildiyse, ilgili bölümleri oluştur
        if ($request->filled('template')) {
            $templateKey = $request->input('template');
            $templateSections = config("page_templates.{$templateKey}.sections", []);

            foreach ($templateSections as $order => $sectionKey) {
                PageSection::create([
                    'page_id' => $page->id,
                    'section_key' => $sectionKey,
                    'order' => $order,
                    'is_active' => true,
                    'content' => []
                ]);
            }
        }

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Sayfa başarıyla oluşturuldu. Şimdi içeriğini düzenleyebilirsiniz.');
    }

    /**
     * Belirtilen sayfayı gösterir (Genellikle admin panelinde kullanılmaz).
     */
    public function show(Page $page)
    {
        return redirect()->route('frontend.page.show', $page->slug);
    }

    /**
     * Sayfayı ve section'larını düzenleme formunu gösterir.
     */
    public function edit(Page $page)
    {
        $availableSections = config('sections');
        $page->load('sections');

        $activeLanguageCodes = setting('active_languages', ['tr', 'en']);
        if (!is_array($activeLanguageCodes)) {
            $activeLanguageCodes = ['tr', 'en'];
        }

        $allLanguages = config('languages.supported', []);
        $activeLanguages = collect($allLanguages)
            ->filter(fn($lang, $code) => in_array($code, $activeLanguageCodes))
            ->sortBy(fn($lang, $code) => array_search($code, $activeLanguageCodes));

        return view('admin.pages.edit', compact('page', 'availableSections', 'activeLanguages'));
    }

    /**
     * Sayfayı günceller - SEO alanları eklendi
     */
    public function update(Request $request, Page $page)
    {
        // 1. ADIM: Sayfa ve SEO ayarlarını doğrula
        $validatedPageData = $request->validate([
            'title' => 'required|array',
            'title.tr' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'banner_title' => 'nullable|array',
            'banner_subtitle' => 'nullable|array',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
            'status' => 'required|in:draft,published',

            // SEO Temel Alanları
            'seo_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'keywords' => 'nullable|array',
            'focus_keyword' => 'nullable|array',
            'canonical_url' => 'nullable|url',
            'index_status' => 'required|in:index,noindex',
            'follow_status' => 'required|in:follow,nofollow',

            // Open Graph
            'og_title' => 'nullable|array',
            'og_description' => 'nullable|array',
            'og_image' => 'nullable|string',

            // Twitter Card
            'twitter_card_type' => 'nullable|in:summary,summary_large_image',
            'twitter_title' => 'nullable|array',
            'twitter_description' => 'nullable|array',
            'twitter_image' => 'nullable|string',

            // Meta Robots
            'meta_noindex' => 'nullable|boolean',
            'meta_nofollow' => 'nullable|boolean',
            'meta_noarchive' => 'nullable|boolean',
            'meta_nosnippet' => 'nullable|boolean',

            // Schema
            'schema_article_type' => 'nullable|string',
            'schema_faq_items' => 'nullable|array',
            'schema_product_price' => 'nullable|numeric',
            'schema_product_currency' => 'nullable|string|max:3',
            'schema_product_availability' => 'nullable|string',
            'schema_product_rating' => 'nullable|numeric|min:1|max:5',
            'schema_product_review_count' => 'nullable|integer',
            'schema_service_area' => 'nullable|array',
            'schema_service_provider' => 'nullable|array',

            // Redirect
            'redirect_enabled' => 'nullable|boolean',
            'redirect_url' => 'nullable|url',
            'redirect_type' => 'nullable|in:301,302',
        ]);

        // Boolean alanları düzelt
        $validatedPageData['redirect_enabled'] = $request->has('redirect_enabled') ? 1 : 0;
        $validatedPageData['meta_noindex'] = $request->has('meta_noindex') ? 1 : 0;
        $validatedPageData['meta_nofollow'] = $request->has('meta_nofollow') ? 1 : 0;
        $validatedPageData['meta_noarchive'] = $request->has('meta_noarchive') ? 1 : 0;
        $validatedPageData['meta_nosnippet'] = $request->has('meta_nosnippet') ? 1 : 0;

        // SEO Analizi Yap
        $primaryLocale = config('app.locale', 'tr');
        if (!empty($validatedPageData['focus_keyword'][$primaryLocale])) {
            try {
                $analysis = $this->seoService->analyze($page, $primaryLocale);
                $validatedPageData['seo_score'] = $analysis['score'] ?? 0;
                $validatedPageData['seo_analysis_results'] = $analysis;
                $validatedPageData['seo_last_analyzed_at'] = now();
            } catch (\Exception $e) {
                \Log::error('SEO Analysis Error: ' . $e->getMessage());
            }
        }

        // Schema JSON Oluştur
        try {
            $schema = $this->schemaService->generate($page, $primaryLocale);
            if ($schema) {
                $validatedPageData['generated_schema_json'] = $schema;
            }
        } catch (\Exception $e) {
            \Log::error('Schema Generation Error: ' . $e->getMessage());
        }

        // 2. ADIM: Sayfanın temel bilgilerini güncelle
        $page->update($validatedPageData);

        // 3. ADIM: Section işlemleri (mevcut kodunuz aynen korundu)
        $incomingSectionIds = collect($request->input('sections', []))->pluck('id')->filter()->toArray();
        $sectionsToDelete = $page->sections()->whereNotIn('id', $incomingSectionIds)->get();

        // Silinecek section'ları ve onlara ait resimleri temizle
        foreach ($sectionsToDelete as $section) {
            $this->deleteSectionImages($section);
            $section->delete();
        }

        // 4. ADIM: Gelen section'ları döngüye alarak güncelle veya oluştur
        if ($request->has('sections')) {
            foreach ($request->sections as $order => $sectionData) {
                $section = PageSection::findOrNew($sectionData['id'] ?? null);
                $content = $section->content ?? [];

                // Formdan gelen metin içeriklerini mevcut content ile birleştir
                $formContent = $sectionData['content'] ?? [];
                $content = array_merge($content, $formContent);

                // Section config'ini al
                $sectionConfig = config('sections.' . $sectionData['section_key'], []);

                // Dosya yükleme işlemleri
                if (!empty($sectionConfig['fields'])) {
                    foreach ($sectionConfig['fields'] as $field) {
                        if ($field['type'] === 'file') {
                            $fileKey = "sections.{$order}.content.files.{$field['name']}";

                            if ($request->hasFile($fileKey)) {
                                if (!empty($content[$field['name']])) {
                                    $this->deleteImage($content[$field['name']]);
                                }
                                $imagePath = $this->uploadImage($request, $fileKey, 'uploads/sections');
                                $content[$field['name']] = $imagePath;
                            } else {
                                $oldValue = $section->content[$field['name']] ?? null;
                                if ($oldValue && !isset($content[$field['name']])) {
                                    $content[$field['name']] = $oldValue;
                                }
                            }
                        }
                    }

                    // Repeater alanlarını işle
                    $content = $this->processRepeaterFields(
                        $request,
                        $order,
                        $content,
                        $section->content ?? [],
                        $sectionConfig['fields']
                    );
                }

                $section->fill([
                    'page_id' => $page->id,
                    'section_key' => $sectionData['section_key'],
                    'order' => $order,
                    'is_active' => $sectionData['is_active'] ?? false,
                    'content' => $content,
                ]);
                $section->save();
            }
        }

        return redirect()->back()->with('success', 'Sayfa başarıyla güncellendi.');
    }

    /**
     * Section sıralamasını güncelle
     */
    public function reorderSections(Request $request)
    {
        try {
            $validated = $request->validate([
                'orders' => 'required|array',
                'orders.*' => 'integer|min:1'
            ]);

            \Log::info('Section sıralama isteği alındı:', $validated);

            $updatedCount = 0;
            foreach ($validated['orders'] as $sectionId => $newOrder) {
                $updated = \App\Models\PageSection::where('id', $sectionId)
                    ->update(['order' => $newOrder]);

                if ($updated) {
                    $updatedCount++;
                }
            }

            \Log::info("$updatedCount section güncellendi");

            return response()->json([
                'ok' => true,
                'message' => "$updatedCount section sıralaması güncellendi",
                'updated_count' => $updatedCount
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation hatası:', ['errors' => $e->errors()]);

            return response()->json([
                'ok' => false,
                'message' => 'Geçersiz veri',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Section sıralama hatası:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Section aktif/pasif durumunu değiştir
     */
    public function toggleStatus(PageSection $section)
    {
        $section->is_active = !$section->is_active;
        $section->save();

        return response()->json(['ok' => true, 'is_active' => $section->is_active]);
    }

    /**
     * Belirtilen sayfayı siler.
     */
    public function destroy(Page $page)
    {
        // Tüm section'ların resimlerini sil
        foreach ($page->sections as $section) {
            $this->deleteSectionImages($section);
        }

        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Sayfa başarıyla silindi.');
    }

    /**
     * Repeater alanlarını recursive olarak işle (iç içe repeater desteği ile)
     */
    private function processRepeaterFields($request, $sectionOrder, $content, $oldContent, $fields, $parentPath = '')
    {
        foreach ($fields as $field) {
            if ($field['type'] === 'repeater' && isset($content[$field['name']])) {
                $repeaterName = $field['name'];

                foreach ($content[$repeaterName] as $itemIndex => &$item) {
                    $currentPath = $parentPath ? "{$parentPath}.{$repeaterName}.{$itemIndex}" : "{$repeaterName}.{$itemIndex}";
                    $repeaterFilePathPrefix = "sections.{$sectionOrder}.content.{$currentPath}.files";

                    \Log::info("Checking files for path: {$repeaterFilePathPrefix}", [
                        'has_file' => $request->hasFile($repeaterFilePathPrefix),
                        'all_files' => $request->allFiles()
                    ]);

                    if ($request->hasFile($repeaterFilePathPrefix)) {
                        foreach ($request->file($repeaterFilePathPrefix) as $repeaterFieldName => $uploadedFile) {
                            \Log::info("Processing file: {$repeaterFieldName}", [
                                'file_name' => $uploadedFile->getClientOriginalName(),
                                'file_size' => $uploadedFile->getSize()
                            ]);

                            $oldValue = $this->getNestedValue($oldContent, "{$repeaterName}.{$itemIndex}.{$repeaterFieldName}");
                            if ($oldValue) {
                                $this->deleteImage($oldValue);
                            }

                            $imagePath = $this->uploadImage($request, "{$repeaterFilePathPrefix}.{$repeaterFieldName}", 'uploads/sections');
                            $item[$repeaterFieldName] = $imagePath;

                            \Log::info("File uploaded successfully", [
                                'field_name' => $repeaterFieldName,
                                'image_path' => $imagePath
                            ]);
                        }
                    } else {
                        foreach ($field['fields'] as $repeaterField) {
                            if ($repeaterField['type'] === 'file') {
                                $oldValue = $this->getNestedValue($oldContent, "{$repeaterName}.{$itemIndex}.{$repeaterField['name']}");
                                if ($oldValue && !isset($item[$repeaterField['name']])) {
                                    $item[$repeaterField['name']] = $oldValue;
                                }
                            }
                        }
                    }

                    // İç içe repeater varsa, onları da işle (RECURSIVE)
                    $item = $this->processRepeaterFields(
                        $request,
                        $sectionOrder,
                        $item,
                        $this->getNestedValue($oldContent, "{$repeaterName}.{$itemIndex}", []),
                        $field['fields'],
                        $currentPath
                    );
                }
            }
        }

        return $content;
    }

    /**
     * Nested array'den değer al (helper metod)
     */
    private function getNestedValue($array, $path, $default = null)
    {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Bir section ve içeriğindeki tüm resimleri siler (iç içe repeater desteği ile)
     */
    private function deleteSectionImages(PageSection $section)
    {
        $sectionConfig = config('sections.' . $section->section_key, []);
        if (!empty($section->content) && !empty($sectionConfig['fields'])) {
            $this->deleteFieldImages($section->content, $sectionConfig['fields']);
        }
    }

    /**
     * Field'lardaki resimleri recursive olarak sil
     */
    private function deleteFieldImages($content, $fields)
    {
        foreach ($fields as $field) {
            if ($field['type'] === 'file' && !empty($content[$field['name']])) {
                $this->deleteImage($content[$field['name']]);
            }

            if ($field['type'] === 'repeater' && !empty($content[$field['name']])) {
                foreach ($content[$field['name']] as $item) {
                    $this->deleteFieldImages($item, $field['fields']);
                }
            }
        }
    }
}