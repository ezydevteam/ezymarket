<?php

namespace App\Services\Statistics\Generators;

use App\Services\Statistics\DTOs\ExportConfig;
use App\Services\Statistics\DTOs\ExportData;
use App\Services\Statistics\Builders\QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelExportGenerator
{
    public function __construct(
        protected QueryBuilder $queryBuilder
    ) {
    }

    /**
     * Generate Excel export from query results
     */
    public function generate(ExportConfig $config): ExportData
    {
        $filename = $this->ensureExtension($config->filename, 'xlsx');

        // Handle multi-sheet exports
        if ($config->sheets) {
            return $this->generateMultiSheet($filename, $config);
        }

        // Single sheet export
        return $this->generateSingleSheet($filename, $config);
    }

    /**
     * Generate single sheet Excel export
     */
    protected function generateSingleSheet(string $filename, ExportConfig $config): ExportData
    {
        $data = $this->getData($config);

        $export = new class($data, $config) implements FromCollection, WithHeadings, WithStyles, WithTitle {
            public function __construct(
                protected Collection $data,
                protected ExportConfig $config
            ) {
            }

            public function collection(): Collection
            {
                return $this->data->map(function ($row) {
                    // Convert to array - handle Eloquent models properly
                    if (is_object($row) && method_exists($row, 'toArray')) {
                        $array = $row->toArray();
                    } elseif (is_array($row)) {
                        $array = $row;
                    } else {
                        $array = (array) $row;
                    }

                    // Filter columns if specified
                    if ($this->config->columns) {
                        $array = array_intersect_key($array, array_flip($this->config->columns));
                    }

                    return $array;
                });
            }

            public function headings(): array
            {
                if (!$this->config->includeHeaders) {
                    return [];
                }

                if ($this->config->headers) {
                    return $this->config->headers;
                }

                $firstRow = $this->data->first();
                if (!$firstRow) {
                    return [];
                }

                $array = is_array($firstRow) ? $firstRow : (array) $firstRow;
                $headers = array_keys($array);

                // Filter headers if columns specified
                if ($this->config->columns) {
                    $headers = array_intersect($headers, $this->config->columns);
                }

                return array_map('ucfirst', $headers);
            }

            public function styles(Worksheet $sheet)
            {
                if ($this->config->styles) {
                    foreach ($this->config->styles as $range => $style) {
                        $sheet->getStyle($range)->applyFromArray($style);
                    }
                }

                // Default header styling
                return [
                    1 => [
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E9ECEF']
                        ]
                    ],
                ];
            }

            public function title(): string
            {
                return 'Statistics Export';
            }
        };

        return $this->performExport($export, $filename, $config);
    }

    /**
     * Generate multi-sheet Excel export
     */
    protected function generateMultiSheet(string $filename, ExportConfig $config): ExportData
    {
        $sheets = [];

        foreach ($config->sheets as $sheetName => $sheetConfig) {
            // Create a new query builder for each sheet
            $sheetQueryBuilder = clone $this->queryBuilder;

            $sheetData = $sheetQueryBuilder->getQuery()->get();

            $sheets[] = new class($sheetData, $sheetName, $sheetConfig, $config) implements FromCollection, WithHeadings, WithStyles, WithTitle {
                public function __construct(
                    protected Collection $data,
                    protected string $sheetName,
                    protected array $sheetConfig,
                    protected ExportConfig $config
                ) {
                }

                public function collection(): Collection
                {
                    return $this->data->map(function ($row) {
                        // Convert to array - handle Eloquent models properly
                        if (is_object($row) && method_exists($row, 'toArray')) {
                            $array = $row->toArray();
                        } elseif (is_array($row)) {
                            $array = $row;
                        } else {
                            $array = (array) $row;
                        }

                        $columns = $this->sheetConfig['columns'] ?? $this->config->columns;
                        if ($columns) {
                            $array = array_intersect_key($array, array_flip($columns));
                        }

                        return $array;
                    });
                }

                public function headings(): array
                {
                    $headers = $this->sheetConfig['headers'] ?? $this->config->headers;

                    if ($headers) {
                        return $headers;
                    }

                    $firstRow = $this->data->first();
                    if (!$firstRow) {
                        return [];
                    }

                    $array = is_array($firstRow) ? $firstRow : (array) $firstRow;
                    return array_map('ucfirst', array_keys($array));
                }

                public function styles(Worksheet $sheet)
                {
                    return [
                        1 => [
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'E9ECEF']
                            ]
                        ],
                    ];
                }

                public function title(): string
                {
                    return $this->sheetName;
                }
            };
        }

        $multiSheetExport = new class($sheets) implements WithMultipleSheets {
            public function __construct(protected array $sheets)
            {
            }

            public function sheets(): array
            {
                return $this->sheets;
            }
        };

        return $this->performExport($multiSheetExport, $filename, $config);
    }

    /**
     * Perform the actual export
     */
    protected function performExport($export, string $filename, ExportConfig $config): ExportData
    {
        return match ($config->format) {
            'download' => $this->download($export, $filename),
            'store' => $this->store($export, $filename, $config),
            'stream' => $this->download($export, $filename),
            default => throw new \InvalidArgumentException("Invalid format: {$config->format}"),
        };
    }

    /**
     * Generate Excel download response
     */
    protected function download($export, string $filename): ExportData
    {
        $response = Excel::download($export, $filename);

        return new ExportData(
            filename: $filename,
            format: 'excel',
            response: $response,
            success: true,
            message: 'Excel export ready for download',
        );
    }

    /**
     * Store Excel file to disk
     */
    protected function store($export, string $filename, ExportConfig $config): ExportData
    {
        $path = "{$config->storagePath}/{$filename}";

        Excel::store($export, $path, $config->disk);

        $fullPath = Storage::disk($config->disk)->path($path);

        return new ExportData(
            filename: $filename,
            format: 'excel',
            path: $fullPath,
            url: asset("storage/{$path}"),
            success: true,
            message: 'Excel file stored successfully',
        );
    }

    /**
     * Get data from query builder
     */
    protected function getData(ExportConfig $config): Collection
    {
        $query = $this->queryBuilder->getQuery()->clone();

        // Apply column selection if specified
        if ($config->columns) {
            $query->select($config->columns);
        }

        return $query->get();
    }

    /**
     * Ensure filename has correct extension
     */
    protected function ensureExtension(string $filename, string $extension): string
    {
        if (!str_ends_with($filename, ".{$extension}")) {
            return "{$filename}.{$extension}";
        }

        return $filename;
    }
}
