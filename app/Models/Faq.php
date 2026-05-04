<?php

namespace App\Models;

use App\Scopes\SortableScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $table = 'faqs';

    protected static function booted()
    {
        static::addGlobalScope(new SortableScope);
    }

    protected $fillable = [
        'title',
        'content',
        'sort_id',
    ];
}


















