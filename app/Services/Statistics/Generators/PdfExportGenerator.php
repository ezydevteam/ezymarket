<?php

namespace App\Services\Statistics\Generators;

use App\Services\Statistics\DTOs\ExportConfig;
use App\Services\Statistics\DTOs\ExportData;
use App\Services\Statistics\Builders\QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class PdfExportGenerator
{
    public function __construct(
        protected QueryBuilder $queryBuilder
    ) {
    }

    /**
     * Generate PDF export from query results
     */
    public function generate(ExportConfig $config): ExportData
    {
        $filename = $this->ensureExtension($config->filename, 'pdf');
        $data = $this->getData($config);

        $html = $this->generateHtml($data, $config);

        return match ($config->format) {
            'download' => $this->download($html, $filename, $config),
            'store' => $this->store($html, $filename, $config),
            'stream' => $this->stream($html, $filename, $config),
            default => throw new \InvalidArgumentException("Invalid format: {$config->format}"),
        };
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
     * Generate HTML for PDF
     */
    protected function generateHtml(Collection $data, ExportConfig $config): string
    {
        $headers = $config->headers ?? $this->getHeaders($data->first(), $config->columns);

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statistics Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .meta {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #34495e;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2c3e50;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e9ecef;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
        }
        .summary-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Statistics Export Report</h1>
        <div class="meta">Generated on ' . now()->format('F d, Y H:i:s') . '</div>
    </div>

    <div class="summary">
        <div class="summary-title">Summary</div>
        <div>Total Records: ' . $data->count() . '</div>
    </div>

    <table>';

        // Add headers
        if ($config->includeHeaders && !empty($headers)) {
            $html .= '<thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead>';
        }

        // Add data rows
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            $rowData = $this->prepareRow($row, $config->columns);
            foreach ($rowData as $cell) {
                $html .= '<td>' . htmlspecialchars($this->formatCell($cell)) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>

    <div class="footer">
        Page {PAGENO} of {nb}
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Generate PDF download response
     */
    protected function download(string $html, string $filename, ExportConfig $config): ExportData
    {
        $pdf = PDF::loadHTML($html)
            ->setPaper($config->paperSize, $config->orientation)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $response = $pdf->download($filename);

        return new ExportData(
            filename: $filename,
            format: 'pdf',
            response: $response,
            success: true,
            message: 'PDF export ready for download',
        );
    }

    /**
     * Stream PDF
     */
    protected function stream(string $html, string $filename, ExportConfig $config): ExportData
    {
        $pdf = PDF::loadHTML($html)
            ->setPaper($config->paperSize, $config->orientation)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $response = $pdf->stream($filename);

        return new ExportData(
            filename: $filename,
            format: 'pdf',
            response: $response,
            success: true,
            message: 'PDF export ready for streaming',
        );
    }

    /**
     * Store PDF file to disk
     */
    protected function store(string $html, string $filename, ExportConfig $config): ExportData
    {
        $pdf = PDF::loadHTML($html)
            ->setPaper($config->paperSize, $config->orientation)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $path = "{$config->storagePath}/{$filename}";
        $fullPath = Storage::disk($config->disk)->path($path);

        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf->save($fullPath);

        return new ExportData(
            filename: $filename,
            format: 'pdf',
            path: $fullPath,
            url: asset("storage/{$path}"),
            success: true,
            message: 'PDF file stored successfully',
        );
    }

    /**
     * Get headers from first row
     */
    protected function getHeaders($firstRow, ?array $columns = null): array
    {
        if (!$firstRow) {
            return [];
        }

        $array = is_array($firstRow) ? $firstRow : (array) $firstRow;
        $headers = array_keys($array);

        // Filter headers if columns specified
        if ($columns) {
            $headers = array_intersect($headers, $columns);
        }

        // Convert to title case
        return array_map(function ($header) {
            return ucwords(str_replace('_', ' ', $header));
        }, $headers);
    }

    /**
     * Prepare row data
     */
    protected function prepareRow($row, ?array $columns = null): array
    {
        // Convert to array - handle Eloquent models properly
        if (is_object($row) && method_exists($row, 'toArray')) {
            $array = $row->toArray();
        } elseif (is_array($row)) {
            $array = $row;
        } else {
            $array = (array) $row;
        }

        // Filter columns if specified
        if ($columns) {
            $array = array_intersect_key($array, array_flip($columns));
        }

        return $array;
    }

    /**
     * Format cell value for display
     */
    protected function formatCell($value): string
    {
        if (is_null($value)) {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
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
