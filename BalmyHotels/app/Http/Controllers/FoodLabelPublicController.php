<?php

namespace App\Http\Controllers;

use App\Models\FoodLabel;
use Illuminate\Http\Request;

class FoodLabelPublicController extends Controller
{
    /**
     * QR okutunca gelen public yemek detay sayfası.
     * URL: /yemek/{token}?lang=tr
     */
    public function show(Request $request, string $token)
    {
        $label = FoodLabel::where('qr_token', $token)
                          ->where('is_active', true)
                          ->firstOrFail();

        $availableLangs = array_keys(array_filter([
            'tr' => $label->getName('tr'),
            'en' => $label->getName('en'),
            'de' => $label->getName('de'),
            'ru' => $label->getName('ru'),
            'ar' => $label->getName('ar'),
            'fr' => $label->getName('fr'),
        ]));

        // Dil seçimi: query string → tarayıcı dili → tr
        $lang = $request->query('lang', '');
        if (!$lang || !in_array($lang, $availableLangs, true)) {
            // Tarayıcı diline bak
            $browserLang = substr($request->header('Accept-Language', 'tr'), 0, 2);
            $lang = in_array($browserLang, $availableLangs, true) ? $browserLang : ($availableLangs[0] ?? 'tr');
        }

        return view('public.food_label', compact('label', 'lang', 'availableLangs'));
    }
}
