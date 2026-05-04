<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class RichTextImage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rich_text_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'filename',
        'path',
    ];

    /**
     * Get the full URL of the image.
     *
     * @return string
     */
    protected function viewLink(): Attribute
    {
        return Attribute::make(
            get: fn(): string => storageUrl($this->path),
        );
    }

    /**
     * Delete the rich text image.
     */
    public function deleteImage(): void
    {
        $driver = storageDriver();
        if ($driver && !$driver->isLocal()) {
            removeFileFromStorage($this->path, $driver->alias);
        }
    }
}












