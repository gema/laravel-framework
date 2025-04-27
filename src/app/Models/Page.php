<?php

namespace GemaDigital\Models;

use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Backpack\PageManager\app\Models\Page as OriginalPage;
use Illuminate\Support\Facades\Cache;

class Page extends OriginalPage
{
    use HasTranslations;

    /**
     * @var list<string>
     */
    protected $translatable = [
        'title',
        'extras_translatable',
    ];

    /**
     * @var list<string>
     */
    protected $fakeColumns = [
        'extras',
        'extras_translatable',
    ];

    #[\Override]
    protected static function booted()
    {
        static::updated(fn () => Cache::forget('pages'));
    }
}
