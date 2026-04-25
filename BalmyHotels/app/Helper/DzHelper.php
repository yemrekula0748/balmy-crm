<?php
namespace App\Helper;
use Illuminate\Support\Facades\Route;
use Auth;

class DzHelper
{
	public static function action() {
		$chunks = explode("@",Route::currentRouteAction());
		return end($chunks);
    }
    
    public static function controller() {
	 	$chunks = explode("\\",Route::currentRouteAction());
		$controller = explode("@",end($chunks));
		return $controller[0]; 
    }

    /**
     * Ülke listesi: kod => Türkçe isim
     */
    public static function countries(): array
    {
        return [
            'TR' => 'Türkiye',
            'DE' => 'Almanya',
            'GB' => 'Birleşik Krallık (İngiltere)',
            'FR' => 'Fransa',
            'IT' => 'İtalya',
            'ES' => 'İspanya',
            'NL' => 'Hollanda',
            'BE' => 'Belçika',
            'CH' => 'İsviçre',
            'AT' => 'Avusturya',
            'SE' => 'İsveç',
            'NO' => 'Norveç',
            'DK' => 'Danimarka',
            'FI' => 'Finlandiya',
            'PL' => 'Polonya',
            'CZ' => 'Çekya',
            'HU' => 'Macaristan',
            'RO' => 'Romanya',
            'BG' => 'Bulgaristan',
            'GR' => 'Yunanistan',
            'RU' => 'Rusya',
            'UA' => 'Ukrayna',
            'US' => 'Amerika Birleşik Devletleri',
            'CA' => 'Kanada',
            'AU' => 'Avustralya',
            'JP' => 'Japonya',
            'CN' => 'Çin',
            'KR' => 'Güney Kore',
            'IN' => 'Hindistan',
            'SA' => 'Suudi Arabistan',
            'AE' => 'Birleşik Arap Emirlikleri',
            'KW' => 'Kuveyt',
            'QA' => 'Katar',
            'BH' => 'Bahreyn',
            'OM' => 'Umman',
            'JO' => 'Ürdün',
            'LB' => 'Lübnan',
            'EG' => 'Mısır',
            'IL' => 'İsrail',
            'IQ' => 'Irak',
            'IR' => 'İran',
            'SY' => 'Suriye',
            'LY' => 'Libya',
            'MA' => 'Fas',
            'TN' => 'Tunus',
            'DZ' => 'Cezayir',
            'AZ' => 'Azerbaycan',
            'KZ' => 'Kazakistan',
            'UZ' => 'Özbekistan',
            'TM' => 'Türkmenistan',
            'GE' => 'Gürcistan',
            'AM' => 'Ermenistan',
            'BY' => 'Belarus',
            'MD' => 'Moldova',
            'LT' => 'Litvanya',
            'LV' => 'Letonya',
            'EE' => 'Estonya',
            'SK' => 'Slovakya',
            'SI' => 'Slovenya',
            'HR' => 'Hırvatistan',
            'RS' => 'Sırbistan',
            'BA' => 'Bosna-Hersek',
            'MK' => 'Kuzey Makedonya',
            'AL' => 'Arnavutluk',
            'ME' => 'Karadağ',
            'PT' => 'Portekiz',
            'IE' => 'İrlanda',
            'IS' => 'İzlanda',
            'LU' => 'Lüksemburg',
            'MT' => 'Malta',
            'CY' => 'Kıbrıs',
            'MX' => 'Meksika',
            'BR' => 'Brezilya',
            'AR' => 'Arjantin',
            'CL' => 'Şili',
            'CO' => 'Kolombiya',
            'ZA' => 'Güney Afrika',
            'NG' => 'Nijerya',
            'KE' => 'Kenya',
            'PK' => 'Pakistan',
            'BD' => 'Bangladeş',
            'TH' => 'Tayland',
            'VN' => 'Vietnam',
            'ID' => 'Endonezya',
            'MY' => 'Malezya',
            'SG' => 'Singapur',
            'PH' => 'Filipinler',
            'NZ' => 'Yeni Zelanda',
        ];
    }

}