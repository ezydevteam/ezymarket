<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\{Sitemap, SitemapGenerator, SitemapIndex};

/**
 * GenerateSitemap
 *
 * Console command to generate XML sitemaps for SEO.
 * Automatically chunks large sitemaps and creates a sitemap index.
 */
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate
                            {--limit=40000 : Maximum URLs per sitemap file}';

    protected $description = 'Generate XML sitemap with automatic chunking for SEO';

    private const DEFAULT_LIMIT = 40000;
    private const EXCLUDED_SEGMENTS = ['currency'];
    private const EXCLUDED_PATTERNS = ['&page=', '?page='];

    private Sitemap $currentSitemap;
    private SitemapIndex $sitemapIndex;
    private int $chunkNumber = 1;
    private int $urlLimit;

    public function handle(): int
    {
        $this->urlLimit = (int) $this->option('limit') ?: self::DEFAULT_LIMIT;
        $url = $this->ensureTrailingSlash(config('app.url'));

        $this->info("Generating sitemap for: {$url}");
        $this->info("URL limit per file: {$this->urlLimit}");
        $this->newLine();

        $this->sitemapIndex = SitemapIndex::create();
        $this->currentSitemap = Sitemap::create();

        try {
            SitemapGenerator::create($url)
                ->hasCrawled(fn($url) => $this->processCrawledUrl($url))
                ->writeToFile(public_path("sitemap_{$this->chunkNumber}.xml"));

            // Write remaining URLs if any
            if (count($this->currentSitemap->getTags()) > 0) {
                $this->writeSitemapFile($this->chunkNumber);
            }

            // Write sitemap index
            $this->sitemapIndex->writeToFile(public_path('sitemap.xml'));

            $this->newLine();
            $this->info("✓ Sitemap generated successfully");
            $this->info("Total chunks created: {$this->chunkNumber}");

        } catch (\Exception $e) {
            $this->error("Failed to generate sitemap: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function processCrawledUrl($url): mixed
    {
        $actualUrl = $url->url;

        // Skip pagination URLs
        if ($this->isPaginationUrl($actualUrl)) {
            return null;
        }

        // Skip excluded segments
        if ($this->isExcludedSegment($url)) {
            return null;
        }

        // Add URL to current sitemap
        $this->currentSitemap->add($url);

        // Check if we need to create a new chunk
        if (count($this->currentSitemap->getTags()) >= $this->urlLimit) {
            $this->writeSitemapFile($this->chunkNumber);
            $this->chunkNumber++;
            $this->currentSitemap = Sitemap::create();

            $this->line("Created chunk {$this->chunkNumber}...");
        }

        return $url;
    }

    private function isPaginationUrl(string $url): bool
    {
        foreach (self::EXCLUDED_PATTERNS as $pattern) {
            if (str_contains($url, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function isExcludedSegment($url): bool
    {
        return in_array($url->segment(1), self::EXCLUDED_SEGMENTS, true);
    }

    private function ensureTrailingSlash(string $url): string
    {
        $parsedUrl = parse_url($url);

        if (isset($parsedUrl['path']) && !str_ends_with($parsedUrl['path'], '/')) {
            return $url . '/';
        }

        return $url;
    }

    private function writeSitemapFile(int $chunk): void
    {
        $filename = "sitemap_{$chunk}.xml";
        $this->currentSitemap->writeToFile(public_path($filename));
        $this->sitemapIndex->add(url($filename));
    }
}
