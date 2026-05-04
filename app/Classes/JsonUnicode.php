<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Model;

abstract class JsonUnicode extends Model
{
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
