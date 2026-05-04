<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TicketReplyAttachment extends Model
{
    use HasFactory;

    protected $table = 'ticket_reply_attachments';

    protected $fillable = [
        'name',
        'path',
        'ticket_reply_id',
    ];

    /**
     * Get the ticket reply this attachment belongs to.
     */
    public function reply(): BelongsTo
    {
        return $this->belongsTo(TicketReply::class, 'ticket_reply_id');
    }

    /**
     * Get the full file path.
     */
    protected function fullPath(): Attribute
    {
        return Attribute::make(
            get: function () {
                $driver = storageDriver();
                $disk = $driver ? $driver->alias : 'local';
                return Storage::disk($disk)->path($this->path);
            },
        );
    }

    /**
     * Get the file extension.
     */
    protected function extension(): Attribute
    {
        return Attribute::make(
            get: fn() => pathinfo($this->name, PATHINFO_EXTENSION),
        );
    }

    /**
     * Check if file is an image.
     */
    public function isImage(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        return in_array(strtolower($this->extension), $imageExtensions);
    }

    /**
     * Check if file exists.
     */
    public function exists(): bool
    {
        $driver = storageDriver();
        $disk = $driver ? $driver->alias : 'local';
        return Storage::disk($disk)->exists($this->path);
    }
}
