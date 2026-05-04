<?php

namespace App\Models;

use App\Models\Product\ProductCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'mime_type',
        'extension',
        'size',
        'path',
        'expiry_at',
        'width',
        'height',
    ];

    protected function casts(): array
    {
        return [
            'expiry_at' => 'datetime',
        ];
    }

    public function scopeExpired($query)
    {
        $query->where('expiry_at', '<', Carbon::now());
    }

    public function scopeNotExpired($query)
    {
        $query->where('expiry_at', '>', Carbon::now());
    }

    public function isImage()
    {
        if ($this->mime_type && str_starts_with($this->mime_type, 'image/')) {
            return true;
        }
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'];
        return in_array(strtolower($this->extension ?? ''), $imageExtensions);
    }

    public function isAudio()
    {
        // Check by MIME type
        if ($this->mime_type && str_starts_with($this->mime_type, 'audio/')) {
            return true;
        }

        // Check by extension
        $audioExtensions = ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a', 'wma'];
        return in_array(strtolower($this->extension ?? ''), $audioExtensions);
    }

    public function isVideo()
    {
        // Check by MIME type
        if ($this->mime_type && str_starts_with($this->mime_type, 'video/')) {
            return true;
        }

        // Check by extension
        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', '3gp'];
        return in_array(strtolower($this->extension ?? ''), $videoExtensions);
    }

    public function isArchive()
    {
        // Check by MIME type
        $archiveMimeTypes = [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-tar',
            'application/gzip',
            'application/x-7z-compressed'
        ];

        if ($this->mime_type && in_array($this->mime_type, $archiveMimeTypes)) {
            return true;
        }

        // Check by extension
        $archiveExtensions = ['zip', 'rar', 'tar', '7z', 'gz', 'bz2'];
        return in_array(strtolower($this->extension ?? ''), $archiveExtensions);
    }

    public function isDocument()
    {
        // Check by MIME type
        if ($this->mime_type && (
            str_starts_with($this->mime_type, 'application/pdf') ||
            str_starts_with($this->mime_type, 'application/msword') ||
            str_starts_with($this->mime_type, 'text/')
        )) {
            return true;
        }

        // Check by extension
        $documentExtensions = ['pdf', 'doc', 'docx', 'txt', 'rtf', 'xls', 'xlsx', 'ppt', 'pptx'];
        return in_array(strtolower($this->extension ?? ''), $documentExtensions);
    }

    // General method to get file type
    public function getFileType()
    {
        if ($this->isImage()) return 'image';
        if ($this->isAudio()) return 'audio';
        if ($this->isVideo()) return 'video';
        if ($this->isArchive()) return 'archive';
        if ($this->isDocument()) return 'document';

        return 'other';
    }

    public function getShortName()
    {
        $name = $this->name;
        if (strlen($name) > 40) {
            return substr($name, 0, 20) . ".." . substr($name, -4);
        }
        return $name;
    }

    public function getSize()
    {
        return formatFileSize($this->size);
    }

    public function getDimensions()
    {
        return [
            'width' => $this->width ?? 0,
            'height' => $this->height ?? 0,
        ];
    }

    public function getDimensionString()
    {
        return $this->width && $this->height ? $this->width . 'x' . $this->height : null;
    }

    public function getFileLink()
    {
        return storageUrl($this->path);
    }

    public function deleteFile()
    {
        $driver = storageDriver();
        $handler = new $driver->handler;
        $handler->delete($this->path);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function toSelectOption()
    {
        $fileType = $this->getFileType();

        return [
            'text' => $this->getShortName(),
            'name' => $this->name,
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'file_type' => $fileType,
            'data_width' => $this->width ?? 0,
            'data_height' => $this->height ?? 0,
            'data_content' => $this->generateDataContent($fileType),
            'image_url' => $this->getFileLink(),
            'file_size' => $this->size ?? 0,
        ];
    }

    public function generateDataContent($fileType = null)
    {
        $fileType = $fileType ?? $this->getFileType();
        $shortName = $this->getShortName();
        $fileLink = $this->getFileLink();
        $placeholder = 'data:image/svg+xml;charset=UTF-8,' . urlencode('
            <svg width="25" height="25" xmlns="http://www.w3.org/2000/svg">
                <rect width="25" height="25" fill="#f1f3f4" stroke="#dadce0" rx="4"/>
                <path d="M9 9h12v12H9z" fill="#e8eaed"/>
                <circle cx="13" cy="13" r="1.5" fill="#9aa0a6"/>
                <path d="M9 19h12l-3-3-2.5-2.5L9 19z" fill="#9aa0a6"/>
            </svg>
        ');

        switch ($fileType) {
            case 'image':
                return "<img src='{$fileLink}'
                    alt='{$this->name}'
                    width='24'
                    height='24'
                    style='object-fit: cover; margin-right: 8px; vertical-align: middle; border-radius: 3px;'
                    onerror='this.src=\"{$placeholder}\"; this.style.opacity=\"0.7\";'
                    loading='eager'/>
                <span>{$shortName}</span>";

            case 'audio':
                return "<i class='bi bi-file-earmark-music fs-6 text-primary ms-1 me-2'></i> <span>{$shortName}</span>";

            case 'video':
                return "<i class='bi bi-file-earmark-play fs-6 text-primary ms-1 me-2'></i> <span>{$shortName}</span>";

            case 'archive':
                return "<i class='bi bi-file-earmark-zip fs-6 text-primary ms-1 me-2'></i> <span>{$shortName}</span>";

            case 'document':
                return "<i class='bi bi-file-earmark-pdf fs-6 text-primary ms-1 me-2'></i> <span>{$shortName}</span>";

            default:
                return "<i class='bi bi-file-earmark fs-6 text-primary ms-1 me-2'></i> <span>{$shortName}</span>";
        }
    }

}

















