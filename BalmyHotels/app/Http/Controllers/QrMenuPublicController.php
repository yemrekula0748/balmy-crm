<?php

namespace App\Http\Controllers;

use App\Models\QrMenu;
use Illuminate\Http\Request;

class QrMenuPublicController extends Controller
{
    /**
     * Dil seçim ekranı (splash)
     * GET /menu/{slug}
     */
    public function splash(string $slug)
    {
        $menu = QrMenu::where('name', $slug)
            ->where('is_active', true)
            ->with('languages')
            ->firstOrFail();

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
            ->where('is_active', true)
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

        return view('public.qrmenu.menu', compact('menu', 'language', 'lang', 'featured', 'categories'));
    }
}
