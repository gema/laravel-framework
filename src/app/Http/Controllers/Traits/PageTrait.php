<?php

namespace GemaDigital\Framework\app\Http\Controllers\Traits;

use Cache;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Session;

trait PageTrait
{
    public function index(string $slug = 'home', string $sub = null): View | Factory
    {
        $locale = Session::get('locale', \Config::get('app.locale'));

        $this->data = Cache::rememberForever("page_{$slug}_{$locale}", function () use ($slug) {
            $page = class_exists(\App\Models\Page::class)
            ? \App\Models\Page::findBySlug($slug)
            : \Backpack\PageManager\app\Models\Page::findBySlug($slug);

            if (! $page) {
                abort(404);
            }

            return [
                'title' => $page->title,
                'page' => $page->withFakes(),
            ];
        });

        // Common data to all pages
        $this->data = array_merge($this->data, $this->common());

        // Sub page
        if ($sub) {
            $this->data['page']->template .= '_view';
        }

        // Page specific data
        if (method_exists($this, $this->data['page']->template)) {
            $this->data = array_merge($this->data, call_user_func([$this, $this->data['page']->template], $sub));
        }

        return view('pages.'.$this->data['page']->template, $this->data);
    }

    public function common(): array
    {
        return [];
    }
}
