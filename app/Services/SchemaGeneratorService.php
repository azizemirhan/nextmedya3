<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Arr;

class SchemaGeneratorService
{
    /**
     * Post için dinamik schema oluşturur
     *
     * @param Post $post
     * @param string $locale
     * @return array|null
     */
    public function generate(Post $post, string $locale = 'tr'): ?array
    {
        $schemaType = $post->schema_article_type ?? 'BlogPosting';

        return match($schemaType) {
            'Article', 'BlogPosting', 'NewsArticle' => $this->generateArticleSchema($post, $locale, $schemaType),
            'Product' => $this->generateProductSchema($post, $locale),
            'Service' => $this->generateServiceSchema($post, $locale),
            'FAQPage' => $this->generateFAQSchema($post, $locale),
            'HowTo' => $this->generateHowToSchema($post, $locale),
            'LocalBusiness' => $this->generateLocalBusinessSchema($post, $locale),
            'Person' => $this->generatePersonSchema($post, $locale),
            default => null
        };
    }

    /**
     * Article/BlogPosting/NewsArticle Schema
     */
    private function generateArticleSchema(Post $post, string $locale, string $type): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'headline' => $post->getTranslation('title', $locale),
            'description' => $post->getTranslation('excerpt', $locale) ?: $post->getTranslation('meta_description', $locale),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author->name ?? 'Unknown',
                'url' => 'https://www.linkedin.com/in/aziz-emirhan-%C3%B6zdemir-91b694265/'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png')
                ]
            ]
        ];

        // Öne çıkan görsel
        if ($post->featured_image) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => asset($post->featured_image),
                'width' => 1200,
                'height' => 630
            ];
        }

        // Makale içeriği (ilk 500 karakter)
        $content = strip_tags($post->getTranslation('content', $locale));
        $schema['articleBody'] = mb_substr($content, 0, 500) . '...';

        // Kategori
        if ($post->category) {
            $schema['articleSection'] = $post->category->getTranslation('name', $locale);
        }

        // Anahtar kelimeler
        $focusKeyword = $post->getTranslation('focus_keyword', $locale);
        if ($focusKeyword) {
            $schema['keywords'] = $focusKeyword;
        }

        // Kelime sayısı
        $schema['wordCount'] = str_word_count($content);

        return $schema;
    }

    /**
     * Product Schema
     */
    private function generateProductSchema(Post $post, string $locale): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $post->getTranslation('title', $locale),
            'description' => $post->getTranslation('excerpt', $locale),
        ];

        // Görsel
        if ($post->featured_image) {
            $schema['image'] = asset($post->featured_image);
        }

        // Fiyat bilgisi
        if ($post->schema_product_price) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => $post->schema_product_price,
                'priceCurrency' => $post->schema_product_currency ?? 'TRY',
                'availability' => 'https://schema.org/' . ($post->schema_product_availability ?? 'InStock'),
                'url' => route('blog.show', $post->slug)
            ];
        }

        // Değerlendirme
        if ($post->schema_product_rating) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $post->schema_product_rating,
                'reviewCount' => $post->schema_product_review_count ?? 0,
                'bestRating' => 5,
                'worstRating' => 1
            ];
        }

        return $schema;
    }

    /**
     * Service Schema
     */
    private function generateServiceSchema(Post $post, string $locale): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $post->getTranslation('title', $locale),
            'description' => $post->getTranslation('excerpt', $locale),
            'provider' => [
                '@type' => 'Organization',
                'name' => $post->getTranslation('schema_service_provider', $locale) ?? config('app.name')
            ]
        ];

        // Hizmet alanı
        $serviceArea = $post->getTranslation('schema_service_area', $locale);
        if ($serviceArea) {
            $schema['areaServed'] = [
                '@type' => 'Place',
                'name' => $serviceArea
            ];
        }

        // Görsel
        if ($post->featured_image) {
            $schema['image'] = asset($post->featured_image);
        }

        return $schema;
    }

    /**
     * FAQPage Schema
     */
    private function generateFAQSchema(Post $post, string $locale): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => []
        ];

        // FAQ items (JSON array olarak saklanıyor)
        $faqItems = $post->schema_faq_items;

        if (is_string($faqItems)) {
            $faqItems = json_decode($faqItems, true);
        }

        if (!empty($faqItems) && is_array($faqItems)) {
            foreach ($faqItems as $item) {
                if (!empty($item['question']) && !empty($item['answer'])) {
                    $schema['mainEntity'][] = [
                        '@type' => 'Question',
                        'name' => $item['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $item['answer']
                        ]
                    ];
                }
            }
        }

        return $schema;
    }

    /**
     * HowTo Schema
     */
    private function generateHowToSchema(Post $post, string $locale): array
    {
        $content = $post->getTranslation('content', $locale);

        // İçerikten adımları çıkarmaya çalış (numbered list)
        preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $content, $matches);

        $steps = [];
        foreach ($matches[1] ?? [] as $index => $step) {
            $steps[] = [
                '@type' => 'HowToStep',
                'position' => $index + 1,
                'name' => 'Adım ' . ($index + 1),
                'text' => strip_tags($step)
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $post->getTranslation('title', $locale),
            'description' => $post->getTranslation('excerpt', $locale),
            'step' => $steps,
            'totalTime' => 'PT10M' // Default 10 dakika (özelleştirilebilir)
        ];
    }

    /**
     * LocalBusiness Schema
     */
    private function generateLocalBusinessSchema(Post $post, string $locale): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $post->getTranslation('title', $locale),
            'description' => $post->getTranslation('excerpt', $locale),
            'image' => $post->featured_image ? asset($post->featured_image) : null,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $post->getTranslation('schema_service_area', $locale) ?? 'İstanbul',
                'addressCountry' => 'TR'
            ],
            'url' => route('blog.show', $post->slug)
        ];
    }

    /**
     * Person Schema
     */
    private function generatePersonSchema(Post $post, string $locale): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $post->getTranslation('title', $locale),
            'description' => $post->getTranslation('excerpt', $locale),
            'image' => $post->featured_image ? asset($post->featured_image) : null,
            'url' => route('blog.show', $post->slug)
        ];
    }

    /**
     * Schema JSON-LD'yi HTML <script> tag olarak döndürür
     */
    public function toScriptTag(array $schema): string
    {
        $json = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return '<script type="application/ld+json">' . PHP_EOL . $json . PHP_EOL . '</script>';
    }
}