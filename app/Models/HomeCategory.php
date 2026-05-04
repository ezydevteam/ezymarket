<?php

namespace App\Models;

use App\Scopes\SortableScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeCategory extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new SortableScope);
    }

    protected $fillable = [
        'name',
        'icon',
        'link',
    ];

    public function getIcon()
    {
        return asset($this->icon);
    }
}


















