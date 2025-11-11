@php
    // Section Başlıkları
    $sectionTitle = data_get($content, 'section_title.' . app()->getLocale(), 'Web Sitesi Paketleri & Paket Fiyatları');
    $brandsTitle = data_get($content, 'brands_title.' . app()->getLocale(), 'Türkiye\'nin En Gelişmiş Web Sitesi Altyapısı');

    // Promo Banner (Çok Dilli Alanlar)
    $promoTitle = data_get($content, 'promo_title.' . app()->getLocale(), 'Kasım Ayına Özel %40 İndirim Fırsatı!');
    $promoSubtitle = data_get($content, 'promo_subtitle.' . app()->getLocale(), 'Sınırsız Revizyon Hakkı + Ücretsiz Hosting Hediye!');
    $promoDescription = data_get($content, 'promo_description.' . app()->getLocale(), 'İlk 50 Müşteriye Özel SSL Sertifikası Hediye!');

    // Geri Sayım Bitiş Tarihi
    $countdownEndDate = data_get($content, 'countdown_end_date', '2025-11-30 23:59:59');

    // Repeater Alanları
    $brands = $content['brands'] ?? [];
    $packages = $content['packages'] ?? [];
@endphp

<section class="next-pricing-section" id="nextPricingSection">

    <div class="next-promo-banner" id="nextPromoBanner">
        <div class="next-promo-content">
            <h2 class="next-promo-title">{{ $promoTitle }}</h2>
            <p class="next-promo-subtitle">{{ $promoSubtitle }}</p>
            <p class="next-promo-description">{{ $promoDescription }}</p>
        </div>

        {{-- Countdown JavaScript ile dinamik olarak doldurulacağı varsayılıyor. Sadece yapıyı tutuyoruz. --}}
        <div class="next-countdown-wrapper" id="nextCountdown">
            <div class="next-countdown-item">
                <span class="next-countdown-number">0</span>
                <span class="next-countdown-label">Gün</span>
            </div>
            <div class="next-countdown-item">
                <span class="next-countdown-number">0</span>
                <span class="next-countdown-label">Saat</span>
            </div>
            <div class="next-countdown-item">
                <span class="next-countdown-number">0</span>
                <span class="next-countdown-label">Dakika</span>
            </div>
            <div class="next-countdown-item">
                <span class="next-countdown-number">0</span>
                <span class="next-countdown-label">Saniye</span>
            </div>
        </div>
    </div>
    @if (!empty($packages))
        <div class="next-packages-container" id="nextPackagesContainer">

            @foreach($packages as $package)
                @php
                    // Paket detayları
                    $packageName = data_get($package, 'package_name.' . app()->getLocale());
                    $packagePrice = data_get($package, 'package_price', '0');
                    $priceCurrency = data_get($package, 'price_currency', '₺');
                    $pricePeriod = data_get($package, 'price_period.' . app()->getLocale(), '/aylık');
                    $priceOld = data_get($package, 'price_old.' . app()->getLocale());
                    $priceNew = data_get($package, 'price_new.' . app()->getLocale());
                    $packageExtra = data_get($package, 'package_extra.' . app()->getLocale());
                    $isFeatured = data_get($package, 'is_featured', false);
                    $featuredBadgeText = data_get($package, 'featured_badge_text.' . app()->getLocale(), 'En Popüler');
                    $callButtonText = data_get($package, 'call_button_text.' . app()->getLocale(), 'Sizi Arayalım');

                    // İç içe repeater'lar
                    $features = $package['features'] ?? [];
                    $techLogos = $package['tech_logos'] ?? [];
                    $techTitle = data_get($package, 'tech_title.' . app()->getLocale(), 'Altyapı Teknolojileri');

                    // CSS Sınıfları
                    $cardClass = 'next-package-card';
                    if ($isFeatured) {
                        $cardClass .= ' next-package-featured';
                    }
                @endphp

                <div class="{{ $cardClass }}">
                    @if($isFeatured)
                        <div class="next-featured-badge">{{ $featuredBadgeText }}</div>
                    @endif

                    <div class="next-package-header">
                        <h3 class="next-package-name">{{ $packageName }}</h3>
                        {{-- Sizi Arayalım butonu (Link action'ı yönetilebilir) --}}
                        <button class="next-call-button" onclick="alert('{{ $callButtonText }} başlatılıyor...')">
                            <svg class="next-call-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                            </svg>
                            {{ $callButtonText }}
                        </button>
                    </div>

                    <div class="next-price-wrapper">
                        <div class="next-price-main">
                            <span class="next-price-currency">{{ $priceCurrency }}</span>
                            <span class="next-price-amount">{{ $packagePrice }}</span>
                            <span class="next-price-period">{{ $pricePeriod }}</span>
                        </div>
                        @if($priceOld)
                            <p class="next-price-installment">
                                <span class="next-price-old">{{ $priceOld }}</span>
                            </p>
                        @endif
                        @if($priceNew)
                            <p class="next-price-installment">
                                <span class="next-price-new">{{ $priceNew }}</span>
                            </p>
                        @endif
                    </div>

                    @if($packageExtra)
                        <div class="next-package-extra">
                            {!! $packageExtra !!}
                        </div>
                    @endif

                    <ul class="next-features-list">
                        @foreach($features as $feature)
                            @php
                                $featureText = data_get($feature, 'feature_text.' . app()->getLocale());
                                $isNew = data_get($feature, 'is_new', false);
                                $isPro = data_get($feature, 'is_pro', false);
                                $badgeText = '';
                                if ($isNew) $badgeText = 'YENİ';
                                if ($isPro) $badgeText = 'PRO';
                            @endphp

                            <li class="next-feature-item">
                                <span class="next-feature-icon">✓</span>
                                <span>{{ $featureText }}
                                    @if($isNew || $isPro)
                                        <span class="next-feature-badge">{{ $badgeText }}</span>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    @if (!empty($techLogos))
                        <div class="next-tech-logos">
                            <div class="next-tech-title">{{ $techTitle }}</div>
                            <div class="next-logo-grid">
                                @foreach($techLogos as $techLogo)
                                    @php
                                        // ✅ Düzeltme 1: $techLogo değişkeni kullanılmalı.
                                        $logoFile = data_get($techLogo, 'tech_logo_text'); // Dosya yolunu al
                                        // ✅ Düzeltme 2: Doğru dosya yolunu asset() ile kullan.
                                        $imageUrl = $logoFile ? asset($logoFile) : 'https://via.placeholder.com/60x30/cccccc/333333?text=LOGO';

                                        $techLogoAltText = data_get($techLogo, 'tech_alt_text.' . app()->getLocale());
                                    @endphp
                                    <div class="next-logo-placeholder">
                                        <img src="{{ $imageUrl }}" alt="{{ $techLogoAltText }}" width="50px">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- CSS Styles --}}
<style>
    /* Next Prefix Styles */
    .next-pricing-section {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .next-promo-banner {
        background: linear-gradient(135deg, #00d2ff 0%, #3a47d5 100%);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .next-promo-content {
        flex: 1;
        min-width: 300px;
    }

    .next-promo-title {
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 10px;
        line-height: 1.3;
    }

    .next-promo-subtitle {
        color: rgba(255, 255, 255, 0.95);
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .next-promo-description {
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
        font-weight: 500;
    }

    .next-countdown-wrapper {
        display: flex;
        gap: 20px;
    }

    .next-countdown-item {
        text-align: center;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 15px 20px;
        border-radius: 12px;
        min-width: 80px;
    }

    .next-countdown-number {
        display: block;
        font-size: 32px;
        font-weight: 800;
        color: #fff;
        line-height: 1;
        margin-bottom: 5px;
    }

    .next-countdown-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
        text-transform: uppercase;
    }

    .next-section-title {
        text-align: center;
        color: #2d3748;
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 50px;
    }

    .next-brands-section {
        text-align: center;
        margin-bottom: 60px;
    }

    .next-brands-title {
        font-size: 18px;
        font-weight: 700;
        color: #4a5568;
        margin-bottom: 30px;
    }

    .next-brands-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 30px;
        align-items: center;
        max-width: 900px;
        margin: 0 auto;
    }

    .next-brand-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .next-brand-logo:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .next-brand-logo img {
        max-width: 100%;
        height: auto;
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }

    .next-brand-logo:hover img {
        opacity: 1;
    }

    .next-packages-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        margin-bottom: 60px;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
        align-items: stretch; /* ✅ Kartları eşit yükseklikte yapar */
    }

    .next-package-card {
        background: #fff;
        border-radius: 20px;
        padding: 35px 25px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        display: flex; /* ✅ Flexbox ile içerik dağılımı */
        flex-direction: column; /* ✅ Dikey hizalama */
        height: 100%; /* ✅ Tüm yüksekliği kullan */
    }

    .next-package-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }

    .next-package-featured {
        border: 3px solid #3b82f6;
    }

    .next-featured-badge {
        position: absolute;
        top: 20px;
        right: -35px;
        background: #3b82f6;
        color: #fff;
        padding: 5px 40px;
        font-size: 12px;
        font-weight: 700;
        transform: rotate(45deg);
    }

    .next-package-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .next-package-name {
        font-size: 28px;
        font-weight: 800;
        color: #2d3748;
    }

    .next-call-button {
        background: #48bb78;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .next-call-button:hover {
        background: #38a169;
        transform: scale(1.05);
    }

    .next-call-icon {
        width: 16px;
        height: 16px;
    }

    .next-price-wrapper {
        margin-bottom: 25px;
    }

    .next-price-main {
        display: flex;
        align-items: baseline;
        gap: 5px;
        margin-bottom: 10px;
    }

    .next-price-currency {
        font-size: 24px;
        font-weight: 700;
        color: #2d3748;
    }

    .next-price-amount {
        font-size: 48px;
        font-weight: 900;
        color: #2d3748;
        line-height: 1;
    }

    .next-price-period {
        font-size: 14px;
        color: #718096;
        font-weight: 500;
    }

    .next-price-installment {
        font-size: 13px;
        color: #4a5568;
        margin-bottom: 5px;
    }

    .next-price-old {
        text-decoration: line-through;
        opacity: 0.6;
    }

    .next-price-new {
        color: #48bb78;
        font-weight: 600;
    }

    .next-package-extra {
        background: #f7fafc;
        border-left: 4px solid #4299e1;
        padding: 12px 15px;
        margin-bottom: 25px;
        font-size: 13px;
        color: #2d3748;
        border-radius: 6px;
    }

    .next-features-list {
        list-style: none;
        padding: 0;
        margin: 0 0 30px 0;
        flex-grow: 1; /* ✅ Boş alanı doldur */
    }

    .next-feature-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        color: #4a5568;
        border-bottom: 1px solid #f7fafc;
    }

    .next-feature-item:last-child {
        border-bottom: none;
    }

    .next-feature-icon {
        color: #48bb78;
        font-weight: 900;
        font-size: 16px;
        flex-shrink: 0;
    }

    .next-feature-badge {
        display: inline-block;
        background: var(--gradient-primary);
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
        margin-left: 6px;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .next-tech-logos {
        border-top: 1px solid #e2e8f0;
        padding-top: 20px;
        margin-top: auto; /* ✅ Her zaman en altta kalmasını sağlar */
    }

    .next-tech-title {
        font-size: 13px;
        font-weight: 700;
        color: #4a5568;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .next-logo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
        gap: 15px;
    }

    .next-logo-placeholder {
        background: #f7fafc;
        padding: 10px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #718096;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s ease;
    }

    .next-logo-placeholder:hover {
        background: #edf2f7;
        transform: scale(1.05);
    }

    .next-logo-placeholder img {
        max-width: 100%;
        height: auto;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .next-packages-container {
            grid-template-columns: repeat(2, 1fr);
        }

        .next-promo-banner {
            flex-direction: column;
            text-align: center;
        }

        .next-countdown-wrapper {
            justify-content: center;
        }
    }

    @media (max-width: 640px) {
        .next-packages-container {
            grid-template-columns: 1fr;
        }

        .next-package-name {
            font-size: 22px;
        }

        .next-price-amount {
            font-size: 36px;
        }

        .next-promo-title {
            font-size: 20px;
        }

        .next-section-title {
            font-size: 24px;
        }

        .next-countdown-wrapper {
            gap: 10px;
        }

        .next-countdown-item {
            min-width: 60px;
            padding: 10px 15px;
        }

        .next-countdown-number {
            font-size: 24px;
        }
    }
</style>

{{-- JavaScript for Countdown --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Countdown Timer - Dinamik Tarih
        function updateCountdown() {
            // Blade'den gelen tarihi JavaScript Date formatına çevir
            const countdownDate = '{{ $countdownEndDate }}';
            const targetDate = new Date(countdownDate.replace(' ', 'T')).getTime();
            const now = new Date().getTime();
            const distance = targetDate - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const countdownItems = document.querySelectorAll('.next-countdown-number');
            if (countdownItems.length >= 4) {
                countdownItems[0].textContent = days;
                countdownItems[1].textContent = hours;
                countdownItems[2].textContent = minutes;
                countdownItems[3].textContent = seconds;
            }

            if (distance < 0) {
                clearInterval(countdownInterval);
                const promoBanner = document.querySelector('.next-promo-banner');
                if (promoBanner) {
                    promoBanner.style.display = 'none';
                }
            }
        }

        const countdownInterval = setInterval(updateCountdown, 1000);
        updateCountdown();

        // Smooth scroll animation on load
        const cards = document.querySelectorAll('.next-package-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'all 0.6s ease';

                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }, index * 150);
        });
    });
</script>