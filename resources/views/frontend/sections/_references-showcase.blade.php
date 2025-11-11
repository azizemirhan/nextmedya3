@php
    $sectionTitle = data_get($content, 'section_title.' . app()->getLocale(), 'Referanslarımız');
    $sectionSubtitle = data_get($content, 'section_subtitle.' . app()->getLocale(), 'Güvenilir İş Ortakları');
    $sectionDescription = data_get($content, 'section_description.' . app()->getLocale(), '');
    $layoutStyle = data_get($content, 'layout_style', 'grid');
    $showCategoryFilter = data_get($content, 'show_category_filter', true);
    $showStats = data_get($content, 'show_stats', true);
    $enableLightbox = data_get($content, 'enable_lightbox', true);
    $stats = data_get($content, 'stats', []);
    $references = data_get($content, 'references', []);
    
    // Kategorileri topla
    $categories = collect($references)->pluck('company_category.' . app()->getLocale())->unique()->filter()->values();
@endphp

<section class="nextmedya-references-section py-5">
    <!-- Background Decorations -->
    <div class="nextmedya-ref-bg">
        <div class="nextmedya-ref-shape shape-1"></div>
        <div class="nextmedya-ref-shape shape-2"></div>
    </div>

    <div class="container position-relative">
        <!-- Section Header -->
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5" data-aos="fade-up">
                @if($sectionSubtitle)
                    <span class="nextmedya-ref-badge">
                        <i class="fas fa-award me-2"></i>
                        {{ $sectionSubtitle }}
                    </span>
                @endif

                <h2 class="nextmedya-ref-title">{{ $sectionTitle }}</h2>

                @if($sectionDescription)
                    <p class="nextmedya-ref-description">{{ $sectionDescription }}</p>
                @endif
            </div>
        </div>

        <!-- Statistics -->
        @if($showStats && !empty($stats))
            <div class="row mb-5">
                <div class="col-lg-12">
                    <div class="nextmedya-ref-stats" data-aos="fade-up">
                        @foreach($stats as $stat)
                            @php
                                $statNumber = data_get($stat, 'stat_number', '0');
                                $statSuffix = data_get($stat, 'stat_suffix', '');
                                $statLabel = data_get($stat, 'stat_label.' . app()->getLocale(), '');
                                $statIcon = data_get($stat, 'stat_icon', 'fas fa-star');
                            @endphp
                            <div class="nextmedya-stat-item">
                                <div class="nextmedya-stat-icon">
                                    <i class="{{ $statIcon }}"></i>
                                </div>
                                <div class="nextmedya-stat-content">
                                    <h3 class="nextmedya-stat-number" data-count="{{ $statNumber }}">0</h3>
                                    <span class="nextmedya-stat-suffix">{{ $statSuffix }}</span>
                                    <p class="nextmedya-stat-label">{{ $statLabel }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Category Filter -->
        @if($showCategoryFilter && $categories->isNotEmpty())
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="nextmedya-ref-filters text-center" data-aos="fade-up">
                        <button class="nextmedya-filter-btn active" data-filter="*">
                            <i class="fas fa-th me-2"></i>
                            Tümü
                        </button>
                        @foreach($categories as $category)
                            <button class="nextmedya-filter-btn" data-filter=".category-{{ Str::slug($category) }}">
                                {{ $category }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        @include('frontend.sections.partials._references-grid', ['references' => $references, 'enableLightbox' => $enableLightbox])
    </div>
</section>

@push('styles')
    <style>
        .nextmedya-references-section {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
        }

        /* Background Shapes */
        .nextmedya-ref-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .nextmedya-ref-shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            animation: floatShape 20s ease-in-out infinite;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            top: -100px;
            left: -100px;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            bottom: -100px;
            right: -100px;
            animation-delay: 5s;
        }

        @keyframes floatShape {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(20px, -20px) rotate(90deg);
            }
            50% {
                transform: translate(-20px, 20px) rotate(180deg);
            }
            75% {
                transform: translate(20px, 20px) rotate(270deg);
            }
        }

        /* Section Header */
        .nextmedya-ref-badge {
            display: inline-flex;
            align-items: center;
            padding: 10px 25px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #667eea;
            margin-bottom: 20px;
        }

        .nextmedya-ref-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 900;
            color: #1e293b;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .nextmedya-ref-description {
            font-size: 1.125rem;
            color: #64748b;
            line-height: 1.7;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Statistics */
        .nextmedya-ref-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            padding: 40px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        .nextmedya-stat-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .nextmedya-stat-item:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            transform: translateY(-5px);
        }

        .nextmedya-stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .nextmedya-stat-icon i {
            font-size: 1.5rem;
            color: #ffffff;
        }

        .nextmedya-stat-content {
            display: flex;
            flex-direction: column;
        }

        .nextmedya-stat-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: #1e293b;
            line-height: 1;
            margin: 0;
            display: inline;
        }

        .nextmedya-stat-suffix {
            font-size: 1.5rem;
            font-weight: 900;
            color: #667eea;
            margin-left: 5px;
        }

        .nextmedya-stat-label {
            font-size: 0.875rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
            margin-bottom: 0;
        }

        /* Category Filters */
        .nextmedya-ref-filters {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            padding: 20px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .nextmedya-filter-btn {
            padding: 12px 30px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .nextmedya-filter-btn:hover {
            background: #667eea;
            border-color: #667eea;
            color: #ffffff;
            transform: translateY(-2px);
        }

        .nextmedya-filter-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nextmedya-ref-stats {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .nextmedya-ref-filters {
                flex-direction: column;
            }

            .nextmedya-filter-btn {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Counter Animation
            const counters = document.querySelectorAll('.nextmedya-stat-number');

            const animateCounter = (counter) => {
                const target = parseInt(counter.getAttribute('data-count'));
                const duration = 2000;
                const increment = target / (duration / 16);
                let current = 0;

                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 16);
            };

            // Intersection Observer for counter animation
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {threshold: 0.5});

            counters.forEach(counter => observer.observe(counter));

            // Category Filter
            const filterBtns = document.querySelectorAll('.nextmedya-filter-btn');
            const refItems = document.querySelectorAll('.nextmedya-ref-item');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const filter = this.getAttribute('data-filter');

                    // Update active button
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    // Filter items
                    refItems.forEach(item => {
                        if (filter === '*' || item.classList.contains(filter.substring(1))) {
                            item.style.display = 'block';
                            setTimeout(() => {
                                item.style.opacity = '1';
                                item.style.transform = 'scale(1)';
                            }, 10);
                        } else {
                            item.style.opacity = '0';
                            item.style.transform = 'scale(0.8)';
                            setTimeout(() => {
                                item.style.display = 'none';
                            }, 300);
                        }
                    });
                });
            });
        });
    </script>
@endpush