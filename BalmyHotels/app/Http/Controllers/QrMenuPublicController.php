<?php

namespace App\Http\Controllers;

use App\Models\MenuShowcase;
use App\Models\QrMenu;
use Illuminate\Http\Request;

class QrMenuPublicController extends Controller
{
    /**
     * Vitrin (showcase) — misafir menü seçim ekranı
     * GET /vitrin/{slug}
     */
    public function showcase(string $slug)
    {
        $showcase = MenuShowcase::where('slug', $slug)
            ->where('is_active', true)
            ->with(['items' => fn($q) => $q->orderBy('sort_order')->with('menu')])
            ->firstOrFail();

        return view('public.qrmenu.showcase', compact('showcase'));
    }

    /**
     * Dil seçim ekranı (splash)
     * GET /menu/{slug}
     */
    public function splash(string $slug)
    {
        $menu = QrMenu::where('name', $slug)
            ->with('languages')
            ->firstOrFail();

        if (!$menu->is_active) {
            return response(view('public.qrmenu.unavailable', compact('menu')), 503);
        }

        // Tek dil varsa direkt menüyo at
        if ($menu->languages->count() === 1) {
            return redirect()->route('qrmenu.view', [$slug, $menu->languages->first()->code]);
        }

        return view('public.qrmenu.splash', compact('menu'));
    }

    /**
     * Menü görüntüleme sayfası
     * GET /menu/{slug}/{lang}
     */
    public function view(string $slug, string $lang)
    {
        $menu = QrMenu::where('name', $slug)
            ->with(['languages', 'categories' => function ($q) {
                $q->where('is_active', true)
                  ->orderBy('sort_order')
                  ->with(['items' => function ($qi) {
                      $qi->where('is_active', true)
                         ->orderBy('sort_order')
                         ->with('foodProduct');
                  }]);
            }])
            ->firstOrFail();

        if (!$menu->is_active) {
            return response(view('public.qrmenu.unavailable', compact('menu')), 503);
        }

        // Dil geçerliliğini kontrol et
        $language = $menu->languages->firstWhere('code', $lang);
        if (!$language) {
            // Geçersiz dil → varsayılana yönlendir
            $default = $menu->defaultLanguage();
            return redirect()->route('qrmenu.view', [$slug, $default?->code ?? 'tr']);
        }

        // Öne çıkarılan ürünler (featured)
        $featured = collect();
        foreach ($menu->categories as $cat) {
            foreach ($cat->items as $item) {
                if ($item->is_featured) {
                    $featured->push($item->setRelation('category', $cat));
                }
            }
        }

        $categories = $menu->categories;

        $themeName = $menu->theme ?? 'default';
        $view = match($themeName) {
            'forest'   => 'public.qrmenu.menu_forest',
            'bohemian' => 'public.qrmenu.menu_bohemian',
            default    => 'public.qrmenu.menu',
        };

        return view($view, compact('menu', 'language', 'lang', 'featured', 'categories'));
    }
}
