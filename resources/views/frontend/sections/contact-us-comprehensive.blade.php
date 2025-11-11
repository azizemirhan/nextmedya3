{{-- resources/views/frontend/sections/contact-us-comprehensive.blade.php --}}
@php
    // Bölüm Başlıkları
    $lang = app()->getLocale();
    $subTitle = data_get($content, 'sub_title.' . $lang, 'İletişim');
    $mainTitle = data_get($content, 'main_title.' . $lang, 'Bize Ulaşın');
    $description = data_get($content, 'description.' . $lang, 'Dijital çözümleriniz için uzman ekibimizle hemen irtibata geçin.');
    
    // Form ve Harita Ayarları
    $formTitle = data_get($content, 'form_title.' . $lang, 'Teklif Talep Formu');
    $formAction = data_get($content, 'form_action', '/submit-contact-form');
    $showMap = (data_get($content, 'show_map', '1') === '1'); // Checkbox değeri
    
    // Repeater Verisi
    $infoBoxes = data_get($content, 'info_boxes', []);
    
    // Harita Ayarları
    $mapEmbedUrl = data_get($content, 'map_embed_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d153013.91890382346!2d32.721471342616235!3d39.90799636618474!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14d347c0500742f9%3A0xc3af7a52230a108!2sAnkara!5e0!3m2!1str!2str!4v1700000000000!5m2!1str!2str');
    $mapHeight = data_get($content, 'map_height', '500'); // px olarak kullanılacak
@endphp

<section class="section-contact-comprehensive">
    <div class="container">

        {{-- Başlık Bölümü (Grid) --}}
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                @if($subTitle)
                    <p class="section-subtitle text-uppercase fw-bold">{{ $subTitle }}</p>
                @endif
                @if($mainTitle)
                    <h2 class="section-title fw-bolder">{{ $mainTitle }}</h2>
                @endif
                @if($description)
                    <p class="lead text-muted mt-3">{{ $description }}</p>
                @endif
            </div>
        </div>

        <div class="row g-5">
            {{-- İletişim Bilgi Kutuları (Sol Kolon) --}}
            <div class="col-lg-4">
                <div class="contact-info-boxes">
                    @forelse($infoBoxes as $box)
                        <div class="info-box bg-white shadow-sm p-4 mb-4 rounded-3 border-start border-5 border-primary" data-aos="fade-up">
                            <i class="{{ data_get($box, 'icon', 'fas fa-info-circle') }} fa-2x text-primary mb-3"></i>
                            <h5 class="fw-bold">{{ data_get($box, 'title.' . $lang) }}</h5>
                            {{-- textarea içeriğini p etiketiyle ve nl2br ile gösterelim --}}
                            <p class="text-muted small mb-3">{!! data_get($box, 'content.' . $lang) !!}</p>

                            @if(!empty(data_get($box, 'link_url')))
                                <a href="{{ data_get($box, 'link_url') }}" class="text-decoration-none text-primary fw-semibold">
                                    {{ data_get($box, 'link_text.' . $lang, 'Detay Gör') }} <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="alert alert-info">İletişim bilgi kutusu eklenmemiş.</div>
                    @endforelse
                </div>
            </div>

            {{-- Modern İletişim Formu (Sağ Kolon) --}}
            <div class="col-lg-8">
                <div class="contact-form-wrapper bg-white shadow-lg p-4 p-md-5 rounded-3" data-aos="fade-left">
                    @if($formTitle)
                        <h3 class="mb-4 fw-bold text-center text-lg-start">{{ $formTitle }}</h3>
                    @endif

                    {{-- Form: Aksiyon URL'i ve method'u dinamik çekiliyor --}}
                    <form action="{{ $formAction }}" method="POST" class="row g-3">
                        @csrf

                        {{-- Örnek Form Alanları --}}
                        <div class="col-md-6">
                            <label for="name" class="form-label">Adınız Soyadınız <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">E-Posta Adresiniz <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-12">
                            <label for="subject" class="form-label">Konu</label>
                            <input type="text" class="form-control" id="subject" name="subject">
                        </div>
                        <div class="col-12">
                            <label for="message" class="form-label">Mesajınız <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="col-12 mt-4 text-center text-md-start">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Gönder <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Google Harita Bölümü --}}
@if($showMap && $mapEmbedUrl)
    <section class="p-0">
        <div class="ratio ratio-21x9" style="height: {{ $mapHeight }}px;">
            <iframe src="{{ $mapEmbedUrl }}"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </section>
@endif

@push('styles')
    <style>
        .section-contact-comprehensive {
            padding: 100px 0;
            background: #f8f9fa; /* Hafif arka plan */
        }

        .section-subtitle {
            color: #0d6efd;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .section-title {
            font-size: 2.5rem;
            color: #212529;
        }

        .info-box {
            transition: all 0.3s ease;
            cursor: pointer;
            border-color: #0d6efd !important;
        }

        .info-box:hover {
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.15) !important;
            transform: translateY(-5px);
        }

        .info-box i {
            color: #0d6efd;
        }

        .contact-form-wrapper {
            transition: box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .contact-form-wrapper:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1) !important;
        }

        /* Harita Yüksekliği dinamik olarak ayarlanır */
        .ratio-21x9 {
            height: auto;
        }

        .ratio-21x9 iframe {
            width: 100%;
            height: 100%;
        }

        @media (max-width: 992px) {
            .section-contact-comprehensive {
                padding: 60px 0;
            }
        }
    </style>
@endpush