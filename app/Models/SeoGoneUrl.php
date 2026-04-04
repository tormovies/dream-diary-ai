<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoGoneUrl extends Model
{
    protected $table = 'seo_gone_urls';

    protected $fillable = [
        'path',
        'source',
        'note',
    ];

    /** Как у {@see Redirect::normalizePath()} */
    public static function normalizePath(string $path): string
    {
        return Redirect::normalizePath($path);
    }
}
