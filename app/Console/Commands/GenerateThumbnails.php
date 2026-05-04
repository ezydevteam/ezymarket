<?php

namespace App\Console\Commands;

use App\Methods\ThumbnailGenerator;
use App\Models\Product\Product;
use Illuminate\Console\Command;

class GenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thumbnails:generate {--limit=100 : Number of products to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate thumbnails for all products with preview_image';

    protected ThumbnailGenerator $thumbnailGenerator;

    /**
     * Create a new command instance.
     */
    public function __construct(ThumbnailGenerator $thumbnailGenerator)
    {
        parent::__construct();
        $this->thumbnailGenerator = $thumbnailGenerator;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting thumbnail generation...');

        $limit = (int) $this->option('limit');
        $totalProducts = Product::whereNotNull('preview_image')->count();

        if ($totalProducts === 0) {
            $this->info('No products found with preview_image.');
            return 0;
        }

        $this->info("Found {$totalProducts} products with preview_image.");

        $bar = $this->output->createProgressBar($totalProducts);
        $bar->start();

        $generated = 0;
        $failed = 0;

        Product::whereNotNull('preview_image')
            ->chunk($limit, function ($products) use ($bar, &$generated, &$failed) {
                foreach ($products as $product) {
                    try {
                        $this->thumbnailGenerator->generate($product->preview_image);
                        $generated++;
                    } catch (\Exception $e) {
                        $failed++;
                        $this->newLine();
                        $this->error("Failed for product ID {$product->id}: {$e->getMessage()}");
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Thumbnail generation complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Generated', $generated],
                ['Failed', $failed],
                ['Total Processed', $generated + $failed],
            ]
        );

        return 0;
    }

    /**
     * Get thumbnail path (copied from ThumbnailGenerator)
     */
    protected function getThumbnailPath(string $imagePath, string $size): string
    {
        $info = pathinfo($imagePath);
        return $info['dirname'] . '/' . $info['filename'] . '_' . $size . '.' . $info['extension'];
    }
}
