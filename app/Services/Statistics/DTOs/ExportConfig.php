<?php

namespace App\Services\Statistics\DTOs;

class ExportConfig
{
    public function __construct(
        public string $filename,
        public string $format = 'download', // 'download', 'store', 'stream'
        public ?array $columns = null,
        public ?array $headers = null,
        public ?string $storagePath = 'exports',
        public ?string $disk = 'public',
        public bool $includeHeaders = true,
        public ?string $delimiter = ',',
        public ?string $enclosure = '"',
        public ?string $paperSize = 'a4',
        public ?string $orientation = 'portrait', // 'portrait', 'landscape'
        public ?array $sheets = null, // For multi-sheet Excel exports
        public bool $includeCharts = false,
        public ?array $styles = null,
    ) {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            filename: $config['filename'] ?? 'export_' . now()->format('Y-m-d_His'),
            format: $config['format'] ?? 'download',
            columns: $config['columns'] ?? null,
            headers: $config['headers'] ?? null,
            storagePath: $config['storagePath'] ?? 'exports',
            disk: $config['disk'] ?? 'public',
            includeHeaders: $config['includeHeaders'] ?? true,
            delimiter: $config['delimiter'] ?? ',',
            enclosure: $config['enclosure'] ?? '"',
            paperSize: $config['paperSize'] ?? 'a4',
            orientation: $config['orientation'] ?? 'portrait',
            sheets: $config['sheets'] ?? null,
            includeCharts: $config['includeCharts'] ?? false,
            styles: $config['styles'] ?? null,
        );
    }

    public function getFullPath(): string
    {
        return storage_path("app/{$this->disk}/{$this->storagePath}/{$this->filename}");
    }

    public function getUrl(): string
    {
        return asset("storage/{$this->storagePath}/{$this->filename}");
    }
}
