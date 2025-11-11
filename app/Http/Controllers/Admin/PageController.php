<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    use ImageUploadTrait;

    // Trait'i controller'a dahil ediyoruz

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
        // YENİ EKLENEN KISIM: Şablonları config dosyasından alıp view'e gönderiyoruz.
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
        // Gelen verileri doğrula
        $validatedData = $request->validate([
            'title' => 'required|array',
            'title.tr' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'banner_title' => 'nullable|array',      // YENİ EKLENDİ
            'banner_subtitle' => 'nullable|array',   // YENİ EKLENDİ
            'slug' => 'required|string|max:255|unique:pages,slug',
            'template' => 'nullable|string|in:' . implode(',', array_keys(config('page_templates', []))), // template'in geçerli olup olmadığını kontrol et
        ]);

        // Sayfayı oluştur
        $page = Page::create($request->only('title', 'slug', 'banner_title', 'banner_subtitle'));

        // YENİ EKLENEN KISIM: Eğer bir şablon seçildiyse, ilgili bölümleri oluştur.
        if ($request->filled('template')) {
            $templateKey = $request->input('template');
            $templateSections = config("page_templates.{$templateKey}.sections", []);

            foreach ($templateSections as $order => $sectionKey) {
                PageSection::create([
                    'page_id' => $page->id,
                    'section_key' => $sectionKey,
                    'order' => $order,
                    'is_active' => true,
                    'content' => [] // Başlangıçta içerik boş olacak
                ]);
            }
        }

        // Sayfa oluştuktan sonra düzenleme ekranına yönlendir.
        return redirect()->route('admin.pages.edit', $page)->with('success', 'Sayfa başarıyla oluşturuldu. Şimdi içeriğini düzenleyebilirsiniz.');
    }


    /**
     * Belirtilen sayfayı gösterir (Genellikle admin panelinde kullanılmaz).
     */
    public function show(Page $page)
    {
        // Ön yüze yönlendirmek daha mantıklı olabilir.
        return redirect()->route('frontend.page.show', $page->slug);
    }

    /**
     * Sayfayı ve section'larını düzenleme formunu gösterir.
     */
    // app/Http/Controllers/Admin/PageController.php

    /**
     * Sayfayı ve section'larını düzenleme formunu gösterir.
     */
    public function edit(Page $page)
    {
        // Config dosyasından tüm olası section'ları al
        $availableSections = config('sections');

        // Mevcut sayfanın section'larını yükle
        $page->load('sections');

        // ================================================================= //
        // DÜZELTİLMİŞ KISIM BAŞLANGICI
        // ================================================================= //

        // 1. `setting()` fonksiyonu zaten bize bir PHP dizisi veriyor.
        $activeLanguageCodes = setting('active_languages', ['tr', 'en']);

        // Güvenlik önlemi: Gelen değerin dizi olduğundan emin olalım.
        if (!is_array($activeLanguageCodes)) {
            $activeLanguageCodes = ['tr', 'en']; // Beklenmedik bir durumda varsayılanı kullan.
        }

        // 2. config/languages.php dosyasından tüm dilleri al
        $allLanguages = config('languages.supported', []);

        // 3. Sadece aktif olan dilleri filtrele ve sırala
        $activeLanguages = collect($allLanguages)
            ->filter(fn($lang, $code) => in_array($code, $activeLanguageCodes))
            ->sortBy(fn($lang, $code) => array_search($code, $activeLanguageCodes));

        // ================================================================= //
        // DÜZELTİLMİŞ KISIM BİTİŞİ
        // ================================================================= //

        return view('admin.pages.edit', compact('page', 'availableSections', 'activeLanguages'));
    }

    // app/Http/Controllers/Admin/PageController.php

    //    public function update(Request $request, Page $page)
//    {
//        // 1. ADIM: Sayfa ve SEO ayarlarını doğrula
//        $validatedPageData = $request->validate([
//            'title' => 'required|array',
//            'title.tr' => 'required|string|max:255',
//            'title.en' => 'nullable|string|max:255',
//            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
//            'status' => 'required|in:draft,published',
//            'seo_title' => 'nullable|array',
//            'meta_description' => 'nullable|array',
//            'keywords' => 'nullable|array',
//            'index_status' => 'required|in:index,noindex',
//            'follow_status' => 'required|in:follow,nofollow',
//            'canonical_url' => 'nullable|url',
//            'og_title' => 'nullable|array',
//            'og_description' => 'nullable|array',
//            'og_image' => 'nullable|url',
//        ]);
//
//        // 2. ADIM: Sayfanın temel bilgilerini (Başlık, slug vb.) güncelle
//        // Bu kısım artık doğru çalışacak çünkü JS buna müdahale etmeyecek.
//        $page->update($validatedPageData);
//
//        // 3. ADIM: Gelen section ID'lerini ve mevcut section ID'lerini alarak silinecekleri bul
//        $incomingSectionIds = collect($request->input('sections', []))->pluck('id')->filter()->toArray();
//        $sectionsToDelete = $page->sections()->whereNotIn('id', $incomingSectionIds)->get();
//
//        // Silinecek section'ları ve onlara ait resimleri temizle
//        foreach ($sectionsToDelete as $section) {
//            $sectionConfig = config('sections.' . $section->section_key, []);
//            if (isset($section->content) && !empty($sectionConfig['fields'])) {
//                foreach ($sectionConfig['fields'] as $field) {
//                    if ($field['type'] === 'file' && !empty($section->content[$field['name']])) {
//                        $this->deleteImage($section->content[$field['name']]);
//                    }
//                }
//            }
//            $section->delete();
//        }
//
//        // 4. ADIM: Gelen section'ları döngüye alarak güncelle veya oluştur
//        if ($request->has('sections')) {
//            foreach ($request->sections as $order => $sectionData) {
//                // Mevcut bölümü bul veya yeni bir tane oluştur
//                $section = PageSection::findOrNew($sectionData['id'] ?? null);
//                $content = $section->content ?? []; // Eski content'i koru
//
//                // Formdan gelen metin içeriklerini mevcut content ile birleştir
//                $formContent = $sectionData['content'] ?? [];
//                $content = array_merge($content, $formContent);
//
//                // Eğer yeni resimler yüklenmişse, onları işle
//                if ($request->hasFile("sections.{$order}.files")) {
//                    foreach ($request->file("sections.{$order}.files") as $fieldName => $uploadedFile) {
//                        // Eski resmi sil (varsa)
//                        if (!empty($content[$fieldName])) {
//                            $this->deleteImage($content[$fieldName]);
//                        }
//                        // Yeni resmi yükle ve yolunu content'e ekle
//                        $imagePath = $this->uploadImage($request, "sections.{$order}.files.{$fieldName}", 'uploads/sections');
//                        $content[$fieldName] = $imagePath;
//                    }
//                }
//
//                // Bölümü veritabanına kaydet
//                $section->fill([
//                    'page_id' => $page->id,
//                    'section_key' => $sectionData['section_key'],
//                    'order' => $order,
//                    'is_active' => $sectionData['is_active'] ?? false,
//                    'content' => $content,
//                ]);
//                $section->save();
//            }
//        }
//
//        return redirect()->back()->with('success', 'Sayfa başarıyla güncellendi.');
//    }

    // app/Http/Controllers/Admin/PageController.php

    // app/Http/Controllers/Admin/PageController.php

    // app/Http/Controllers/Admin/PageController.php

    public function update(Request $request, Page $page)
    {
        // 1. ADIM: Sayfa ve SEO ayarlarını doğrula (Bu kısım aynı kalıyor)
        $validatedPageData = $request->validate([
            'title' => 'required|array',
            'title.*' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
            'status' => 'required|in:draft,published',
            'banner_title' => 'nullable|array',
            'banner_subtitle' => 'nullable|array',
            'seo_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'keywords' => 'nullable|array',
            'index_status' => 'required|in:index,noindex',
            'follow_status' => 'required|in:follow,nofollow',
            'canonical_url' => 'nullable|url',
            'og_title' => 'nullable|array',
            'og_description' => 'nullable|array',
            'og_image' => 'nullable|url',
            'sections.*.files.*' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp,mp4|max:100048',
            'sections.*.content.*.files.*' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp,mp4|max:100048',
            'sections.*.content.*.*.files.*' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp,mp4|max:100048',
            'sections.*.content.*.*.*.files.*' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp,mp4|max:100048',
        ]);

        // 2. ADIM: Sayfanın temel bilgilerini güncelle
        $page->update($validatedPageData);

        // 3. ADIM: Silinecek section'ları yönet
        $incomingSectionIds = collect($request->input('sections', []))->pluck('id')->filter()->toArray();
        $sectionsToDelete = $page->sections()->whereNotIn('id', $incomingSectionIds)->get();

        foreach ($sectionsToDelete as $section) {
            $this->deleteSectionImages($section);
            $section->delete();
        }

        // 4. ADIM: Gelen section'ları işle (YENİ VE KARARLI YAPI)
        if ($request->has('sections')) {
            foreach ($request->sections as $order => $sectionData) {
                $section = PageSection::findOrNew($sectionData['id'] ?? null);
                $oldContent = $section->content ?? []; // Sadece eski resimleri silmek için lazım olacak

                // Gelen içeriği temel al, birleştirme yapma!
                $content = $sectionData['content'] ?? [];

                // Ana section için dosya yüklemelerini işle
                $mainFilePathPrefix = "sections.{$order}.files";
                if ($request->hasFile($mainFilePathPrefix)) {
                    foreach ($request->file($mainFilePathPrefix) as $fieldName => $uploadedFile) {
                        // Eski resmi sil
                        if (isset($oldContent[$fieldName])) {
                            $this->deleteImage($oldContent[$fieldName]);
                        }
                        // Yeni resmi yükle ve content'e ekle
                        $imagePath = $this->uploadImage($request, "{$mainFilePathPrefix}.{$fieldName}", 'uploads/sections');
                        $content[$fieldName] = $imagePath;
                    }
                } else {
                    // Eğer yeni dosya gelmediyse, eski dosya yolunu koru
                    $sectionConfig = config('sections.' . $sectionData['section_key'], []);
                    foreach ($sectionConfig['fields'] as $field) {
                        if ($field['type'] === 'file' && isset($oldContent[$field['name']])) {
                            $content[$field['name']] = $oldContent[$field['name']];
                        }
                    }
                }

                // Repeater alanlarındaki dosya yüklemelerini işle (RECURSIVE)
                $sectionConfig = config('sections.' . $sectionData['section_key'], []);
                if (!empty($sectionConfig['fields'])) {
                    // Debug: Gelen content'i logla
                    \Log::info('Processing section: ' . $sectionData['section_key'], [
                        'content' => $content,
                        'files' => $request->allFiles()
                    ]);
                    
                    // $content değişkenini, recursive dosya işleme fonksiyonundan gelen
                    // güncellenmiş haliyle değiştiriyoruz.
                    $content = $this->processRepeaterFields(
                        $request,
                        $order, // $order, section'ın index'idir
                        $content,
                        $oldContent,
                        $sectionConfig['fields'],
                        '' // Başlangıç path'i boş
                    );
                    
                    // Debug: İşlenmiş content'i logla
                    \Log::info('Processed content for section: ' . $sectionData['section_key'], [
                        'processed_content' => $content
                    ]);
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
     * Bir section ve içeriğindeki tüm resimleri siler.
     * @param PageSection $section
     */
    public function reorderSections(Request $request)
    {
        try {
            // Gelen veriyi doğrula
            $validated = $request->validate([
                'orders' => 'required|array',
                'orders.*' => 'integer|min:1'
            ]);

            \Log::info('Section sıralama isteği alındı:', $validated);

            // Her section için order güncelle
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
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Sayfa başarıyla silindi.');
    }

    /**
     * Repeater alanlarını recursive olarak işle (iç içe repeater desteği ile) - DÜZELTİLMİŞ VERSİYON
     */
    private function processRepeaterFields($request, $sectionOrder, $content, $oldContent, $fields, $parentPath = '')
    {
        foreach ($fields as $field) {
            if ($field['type'] === 'repeater' && isset($content[$field['name']])) {
                $repeaterName = $field['name'];

                foreach ($content[$repeaterName] as $itemIndex => &$item) {
                    // Dosya yükleme path'ini oluştur - DÜZELTİLMİŞ
                    $currentPath = $parentPath ? "{$parentPath}.{$repeaterName}.{$itemIndex}" : "{$repeaterName}.{$itemIndex}";
                    $repeaterFilePathPrefix = "sections.{$sectionOrder}.content.{$currentPath}.files";

                    // Bu repeater item için dosya yüklemelerini işle
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
                            
                            // Eski resmi sil
                            $oldValue = $this->getNestedValue($oldContent, "{$repeaterName}.{$itemIndex}.{$repeaterFieldName}");
                            if ($oldValue) {
                                $this->deleteImage($oldValue);
                            }

                            // Yeni resmi yükle
                            $imagePath = $this->uploadImage($request, "{$repeaterFilePathPrefix}.{$repeaterFieldName}", 'uploads/sections');
                            $item[$repeaterFieldName] = $imagePath;
                            
                            \Log::info("File uploaded successfully", [
                                'field_name' => $repeaterFieldName,
                                'image_path' => $imagePath
                            ]);
                        }
                    } else {
                        // Yeni resim gelmediyse, eski resim yolunu koru
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
                    // İç içe repeater için recursive çağrı
                    $this->deleteFieldImages($item, $field['fields']);
                }
            }
        }
    }
}