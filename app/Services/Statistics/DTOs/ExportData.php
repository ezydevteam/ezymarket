<?php

namespace App\Services\Statistics\DTOs;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Response;

class ExportData
{
    public function __construct(
        public string $filename,
        public string $format,
        public ?string $path = null,
        public ?string $url = null,
        public mixed $content = null,
        public BinaryFileResponse|StreamedResponse|Response|null $response = null,
        public bool $success = true,
        public ?string $message = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'format' => $this->format,
            'path' => $this->path,
            'url' => $this->url,
            'success' => $this->success,
            'message' => $this->message,
        ];
    }
}
