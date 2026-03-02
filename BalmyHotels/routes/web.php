<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KokiAdminController;
use App\Http\Controllers\Modules\VehicleController;
use App\Http\Controllers\Modules\VehicleOperationController;
use App\Http\Controllers\Modules\VehicleMaintenanceController;
use App\Http\Controllers\Modules\VehicleInsuranceController;
use App\Http\Controllers\Modules\UserController;
use App\Http\Controllers\Modules\DepartmentController;
use App\Http\Controllers\Modules\DoorLogController;
use App\Http\Controllers\Modules\DoorLogReportController;
use App\Http\Controllers\Modules\GuestLogController;
use App\Http\Controllers\Modules\FaultController;
use App\Http\Controllers\Modules\FaultLocationController;
use App\Http\Controllers\Modules\FaultTypeController;
use App\Http\Controllers\Modules\AssetCategoryController;
use App\Http\Controllers\Modules\AssetController;
use App\Http\Controllers\Modules\AssetExitController;
use App\Http\Controllers\Modules\QrMenuController;
use App\Http\Controllers\Modules\QrMenuCategoryController;
use App\Http\Controllers\QrMenuPublicController;
use App\Http\Controllers\Modules\SurveyController;
use App\Http\Controllers\SurveyPublicController;
use App\Http\Controllers\Modules\FoodLabelController;
use App\Http\Controllers\FoodLabelPublicController;
use App\Http\Controllers\StaffSurveyPublicController;
use App\Http\Controllers\Modules\StaffSurveyController;
use App\Http\Controllers\Modules\RoleController;

/*
|--------------------------------------------------------------------------
| Auth Route'ları (giriş yapmadan erişilebilir)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| QR Menü — Public (auth gerekmez)
|--------------------------------------------------------------------------
*/
Route::get('/menu/{slug}', [QrMenuPublicController::class, 'splash'])->name('qrmenu.show');
Route::get('/menu/{slug}/{lang}', [QrMenuPublicController::class, 'view'])->name('qrmenu.view');

/*
|--------------------------------------------------------------------------
| Misafir Anket — Public (auth gerekmez)
|--------------------------------------------------------------------------
*/
Route::get('/anket/{slug}',                        [SurveyPublicController::class, 'splash'])->name('surveys.public.splash');
Route::get('/anket/{slug}/{lang}',                 [SurveyPublicController::class, 'form'])->name('surveys.public.form');
Route::post('/anket/{slug}/{lang}',                [SurveyPublicController::class, 'submit'])->name('surveys.public.submit');
Route::get('/anket/{slug}/{lang}/tesekkurler',     [SurveyPublicController::class, 'thankyou'])->name('surveys.public.thankyou');

/*
|--------------------------------------------------------------------------
| Personel Anket — Public (auth gerekmez)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Yemek İsimlik — Public (auth gerekmez)
|--------------------------------------------------------------------------
*/
Route::get('/yemek/{token}', [FoodLabelPublicController::class, 'show'])->name('food-labels.public');

Route::get('/personel-anketi/{slug}',            [StaffSurveyPublicController::class, 'form'])->name('staff-surveys.public.form');
Route::post('/personel-anketi/{slug}',           [StaffSurveyPublicController::class, 'submit'])->name('staff-surveys.public.submit');
Route::get('/personel-anketi/{slug}/tesekkurler',[StaffSurveyPublicController::class, 'thankyou'])->name('staff-surveys.public.thankyou');

/*
|--------------------------------------------------------------------------
| Korumalı Route'lar (auth zorunlu)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/index', [DashboardController::class, 'index']);

    Route::controller(KokiAdminController::class)->group(function () {
    Route::get('/analytics','analytics');
    Route::get('/review','review');
    Route::get('/order','order');
    Route::get('/order-list','order_list');
    Route::get('/customer-list','customer_list');
    Route::get('/app-profile','app_profile');
    Route::match(['get','post'],'/post-details','post_details');
    Route::get('/app-calender','app_calender');
    Route::get('/email-compose','email_compose');
    Route::get('/email-inbox','email_inbox');
    Route::get('/email-read','email_read');
    Route::get('/ecom-product-grid','ecom_product_grid');
    Route::get('/ecom-product-list','ecom_product_list');
    Route::get('/ecom-product-detail','ecom_product_detail');
    Route::get('/ecom-product-order','ecom_product_order');
    Route::get('/ecom-checkout','ecom_checkout');
    Route::get('/ecom-invoice','ecom_invoice');
    Route::get('/ecom-customers','ecom_customers');
    Route::get('/chart-flot','chart_flot');
    Route::get('/chart-morris','chart_morris');
    Route::get('/chart-chartjs','chart_chartjs');
    Route::get('/chart-chartist','chart_chartist');
    Route::get('/chart-sparkline','chart_sparkline');
    Route::get('/chart-peity','chart_peity');
    Route::get('/ui-accordion','ui_accordion');
    Route::get('/ui-alert','ui_alert');
    Route::get('/ui-badge','ui_badge');
    Route::get('/ui-button','ui_button');
    Route::get('/ui-modal','ui_modal');
    Route::get('/ui-button-group','ui_button_group');
    Route::get('/ui-list-group','ui_list_group');
    Route::get('/ui-media-object','ui_media_object');
    Route::get('/ui-card','ui_card');
    Route::get('/ui-carousel','ui_carousel');
    Route::get('/ui-dropdown','ui_dropdown');
    Route::get('/ui-popover','ui_popover');
    Route::get('/ui-progressbar','ui_progressbar');
    Route::get('/ui-tab','ui_tab');
    Route::get('/ui-typography','ui_typography');
    Route::get('/ui-pagination','ui_pagination');
    Route::get('/ui-grid','ui_grid');
    Route::get('/uc-select2','uc_select2');
    Route::get('/uc-nestable','uc_nestable');
    Route::get('/uc-noui-slider','uc_noui_slider');
    Route::get('/uc-sweetalert','uc_sweetalert');
    Route::get('/uc-toastr','uc_toastr');
    Route::get('/map-jqvmap','map_jqvmap');
    Route::get('/uc-lightgallery','uc_lightgallery');
    Route::get('/widget-basic','widget_basic');
    Route::get('/flat-icons','flat_icons');
    Route::get('/svg-icons','svg_icons');
    Route::get('/form-element','form_element');
    Route::get('/form-wizard','form_wizard');
    Route::get('/form-ckeditor','form_ckeditor');
    Route::get('/form-pickers','form_pickers');
    Route::get('/form-validation-jquery','form_validation_jquery');
    Route::get('/table-bootstrap-basic','table_bootstrap_basic');
    Route::get('/table-datatable-basic','table_datatable_basic');
    Route::get('/page-register','page_register');
    Route::get('/page-login','page_login');
    Route::get('/page-error-400','page_error_400');
    Route::get('/page-error-403','page_error_403');
    Route::get('/page-error-404','page_error_404');
    Route::get('/page-error-500','page_error_500');
    Route::get('/page-error-503','page_error_503');
    Route::get('/page-lock-screen','page_lock_screen');
    Route::get('/page-forgot-password','page_forgot_password');


    Route::post('/best-menus','best_menus');
    Route::post('/loyal-customers','loyal_customers');
    }); // KokiAdminController group

    /*
    |--------------------------------------------------------------------------
    | Araç Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('araclar')->name('vehicles.')->group(function () {
        Route::resource('/', VehicleController::class)->parameters(['' => 'vehicle']);

        Route::resource('{vehicle}/operasyonlar', VehicleOperationController::class)
            ->parameters(['operasyonlar' => 'operation'])
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

        Route::resource('{vehicle}/bakimlar', VehicleMaintenanceController::class)
            ->parameters(['bakimlar' => 'maintenance'])
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

        Route::resource('{vehicle}/sigortalar', VehicleInsuranceController::class)
            ->parameters(['sigortalar' => 'insurance'])
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Kullanıcı / Çalışan Modülü
    |--------------------------------------------------------------------------
    */
    Route::resource('kullanicilar', UserController::class)
        ->names('users')
        ->parameters(['kullanicilar' => 'user']);

    /*
    |--------------------------------------------------------------------------
    | Departman Modülü
    |--------------------------------------------------------------------------
    */
    Route::resource('departmanlar', DepartmentController::class)
        ->names('departments')
        ->parameters(['departmanlar' => 'department'])
        ->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Kapı Giriş/Çıkış Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('kapi-giris')->name('door-logs.')->group(function () {
        Route::get('/',               [DoorLogController::class, 'index'])->name('index');
        Route::get('/manuel',         [DoorLogController::class, 'create'])->name('create');
        Route::post('/manuel',        [DoorLogController::class, 'store'])->name('store');
        Route::post('/hizli',         [DoorLogController::class, 'quick'])->name('quick');
        Route::delete('/{doorLog}',   [DoorLogController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Kapı Giriş/Çıkış Raporları
    |--------------------------------------------------------------------------
    */
    Route::prefix('kapi-rapor')->name('door-reports.')->group(function () {
        Route::get('/',         [DoorLogReportController::class, 'index'])->name('index');
        Route::get('/pdf',      [DoorLogReportController::class, 'pdf'])->name('pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Misafir Giriş/Çıkış Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('misafir-giris')->name('guest-logs.')->group(function () {
        Route::get('/',                   [GuestLogController::class, 'index'])->name('index');
        Route::get('/ekle',               [GuestLogController::class, 'create'])->name('create');
        Route::post('/ekle',              [GuestLogController::class, 'store'])->name('store');
        Route::get('/{guestLog}',         [GuestLogController::class, 'show'])->name('show');
        Route::get('/{guestLog}/duzenle', [GuestLogController::class, 'edit'])->name('edit');
        Route::put('/{guestLog}',         [GuestLogController::class, 'update'])->name('update');
        Route::post('/{guestLog}/cikis',  [GuestLogController::class, 'checkOut'])->name('checkout');
        Route::delete('/{guestLog}',      [GuestLogController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Teknik Arıza Takip Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('arizalar')->name('faults.')->group(function () {
        Route::get('/',                          [FaultController::class, 'index'])->name('index');
        Route::get('/ekle',                      [FaultController::class, 'create'])->name('create');
        Route::post('/ekle',                     [FaultController::class, 'store'])->name('store');

        // Yeni alt sayfalar
        Route::get('/gelen',                     [FaultController::class, 'incoming'])->name('incoming');
        Route::get('/bildirdiklerim',            [FaultController::class, 'myReports'])->name('my-reports');
        Route::get('/departmanim',               [FaultController::class, 'myDepartment'])->name('my-department');

        // AJAX cascading dropdown
        Route::get('/ajax/departmanlar',         [FaultController::class, 'ajaxDepartments'])->name('ajax.departments');
        Route::get('/ajax/konumlar',             [FaultController::class, 'ajaxLocations'])->name('ajax.locations');
        Route::get('/ajax/alanlar',              [FaultController::class, 'ajaxAreas'])->name('ajax.areas');

        // Konum + Alan yönetimi
        Route::prefix('konumlar')->name('locations.')->group(function () {
            Route::get('/',                          [FaultLocationController::class, 'index'])->name('index');
            Route::get('/yeni',                      [FaultLocationController::class, 'create'])->name('create');
            Route::post('/',                         [FaultLocationController::class, 'store'])->name('store');
            Route::get('/{location}/duzenle',        [FaultLocationController::class, 'edit'])->name('edit');
            Route::put('/{location}',                [FaultLocationController::class, 'update'])->name('update');
            Route::delete('/{location}',             [FaultLocationController::class, 'destroy'])->name('destroy');
            Route::post('/{location}/alanlar',       [FaultLocationController::class, 'storeArea'])->name('areas.store');
            Route::delete('/alanlar/{area}',         [FaultLocationController::class, 'destroyArea'])->name('areas.destroy');
        });

        // Arıza Türleri yönetimi
        Route::prefix('turler')->name('types.')->group(function () {
            Route::get('/',                      [FaultTypeController::class, 'index'])->name('index');
            Route::get('/yeni',                  [FaultTypeController::class, 'create'])->name('create');
            Route::post('/',                     [FaultTypeController::class, 'store'])->name('store');
            Route::get('/{type}/duzenle',        [FaultTypeController::class, 'edit'])->name('edit');
            Route::put('/{type}',                [FaultTypeController::class, 'update'])->name('update');
            Route::delete('/{type}',             [FaultTypeController::class, 'destroy'])->name('destroy');
        });

        // Model-bound route'lar (statik route'lardan sonra gelmeli)
        Route::get('/{fault}',                   [FaultController::class, 'show'])->name('show');
        Route::post('/{fault}/durum',            [FaultController::class, 'updateStatus'])->name('updateStatus');
        Route::post('/{fault}/yorum',            [FaultController::class, 'addComment'])->name('addComment');
        Route::post('/{fault}/ata',              [FaultController::class, 'assign'])->name('assign');
        Route::delete('/{fault}',                [FaultController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Demirbaş + Eşya Çıkış Modülü
    |--------------------------------------------------------------------------
    */
    // Kategori yönetimi
    Route::prefix('demirbaslar/kategoriler')->name('asset-categories.')->group(function () {
        Route::get('/',                              [AssetCategoryController::class, 'index'])->name('index');
        Route::get('/ekle',                          [AssetCategoryController::class, 'create'])->name('create');
        Route::post('/ekle',                         [AssetCategoryController::class, 'store'])->name('store');
        Route::get('/{assetCategory}/duzenle',       [AssetCategoryController::class, 'edit'])->name('edit');
        Route::put('/{assetCategory}',               [AssetCategoryController::class, 'update'])->name('update');
        Route::delete('/{assetCategory}',            [AssetCategoryController::class, 'destroy'])->name('destroy');
    });

    // Eşya çıkış formları  (assets'ten önce tanımlanmalı — prefix çakışmasını önlemek için)
    Route::prefix('demirbaslar/cikislar')->name('asset-exits.')->group(function () {
        Route::get('/',                        [AssetExitController::class, 'index'])->name('index');
        Route::get('/ekle',                    [AssetExitController::class, 'create'])->name('create');
        Route::post('/ekle',                   [AssetExitController::class, 'store'])->name('store');
        Route::get('/{assetExit}',             [AssetExitController::class, 'show'])->name('show');
        Route::post('/{assetExit}/onayla',     [AssetExitController::class, 'approve'])->name('approve');
        Route::post('/{assetExit}/reddet',     [AssetExitController::class, 'reject'])->name('reject');
        Route::post('/{assetExit}/iade',       [AssetExitController::class, 'returnItem'])->name('return');
        Route::delete('/{assetExit}',          [AssetExitController::class, 'destroy'])->name('destroy');
    });

    // Demirbaş envanter
    Route::prefix('demirbaslar')->name('assets.')->group(function () {
        Route::get('/',                        [AssetController::class, 'index'])->name('index');
        Route::get('/ekle',                    [AssetController::class, 'create'])->name('create');
        Route::post('/ekle',                   [AssetController::class, 'store'])->name('store');
        Route::get('/kategori/{assetCategory}/alanlar', [AssetController::class, 'categoryFields'])->name('categoryFields');
        Route::get('/{asset}',                 [AssetController::class, 'show'])->name('show');
        Route::get('/{asset}/duzenle',         [AssetController::class, 'edit'])->name('edit');
        Route::put('/{asset}',                 [AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}',              [AssetController::class, 'destroy'])->name('destroy');
    });
    /*
    |--------------------------------------------------------------------------
    | Misafir Anket Yönetimi
    |--------------------------------------------------------------------------
    */
    Route::prefix('anketler')->name('surveys.')->group(function () {
        Route::get('/',                 [SurveyController::class, 'index'])->name('index');
        Route::get('/yeni',             [SurveyController::class, 'create'])->name('create');
        Route::post('/yeni',            [SurveyController::class, 'store'])->name('store');
        Route::get('/{survey}',         [SurveyController::class, 'show'])->name('show');
        Route::get('/{survey}/duzenle', [SurveyController::class, 'edit'])->name('edit');
        Route::put('/{survey}',         [SurveyController::class, 'update'])->name('update');
        Route::post('/{survey}/toggle', [SurveyController::class, 'toggle'])->name('toggle');
        Route::delete('/{survey}',      [SurveyController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Yemek İsimlik
    |--------------------------------------------------------------------------
    */
    Route::prefix('yemek-isimlikler')->name('food-labels.')->group(function () {
        Route::get('/',                     [FoodLabelController::class, 'index'])->name('index');
        Route::get('/export',               [FoodLabelController::class, 'export'])->name('export');
        Route::get('/ekle',                 [FoodLabelController::class, 'create'])->name('create');
        Route::post('/ekle',                [FoodLabelController::class, 'store'])->name('store');
        Route::post('/yazdir',              [FoodLabelController::class, 'printBulk'])->name('print-bulk');
        Route::get('/{foodLabel}/duzenle',  [FoodLabelController::class, 'edit'])->name('edit');
        Route::put('/{foodLabel}',          [FoodLabelController::class, 'update'])->name('update');
        Route::delete('/{foodLabel}',       [FoodLabelController::class, 'destroy'])->name('destroy');
        Route::get('/{foodLabel}/yazdir',   [FoodLabelController::class, 'printSingle'])->name('print-single');
    });

    /*
    |--------------------------------------------------------------------------
    | Personel Anket Yönetimi
    |--------------------------------------------------------------------------
    */
    Route::prefix('personel-anketleri')->name('staff-surveys.')->group(function () {
        Route::get('/',                        [StaffSurveyController::class, 'index'])->name('index');
        Route::get('/yeni',                    [StaffSurveyController::class, 'create'])->name('create');
        Route::post('/yeni',                   [StaffSurveyController::class, 'store'])->name('store');
        Route::get('/{staffSurvey}',           [StaffSurveyController::class, 'show'])->name('show');
        Route::get('/{staffSurvey}/duzenle',   [StaffSurveyController::class, 'edit'])->name('edit');
        Route::put('/{staffSurvey}',           [StaffSurveyController::class, 'update'])->name('update');
        Route::post('/{staffSurvey}/toggle',   [StaffSurveyController::class, 'toggle'])->name('toggle');
        Route::delete('/{staffSurvey}',        [StaffSurveyController::class, 'destroy'])->name('destroy');
    });

    // QR Menü Yönetimi
    Route::prefix('qr-menuler')->name('qrmenus.')->group(function () {
        Route::get('/', [QrMenuController::class, 'index'])->name('index');
        Route::get('/ekle', [QrMenuController::class, 'create'])->name('create');
        Route::post('/ekle', [QrMenuController::class, 'store'])->name('store');
        Route::get('/{qrmenu}', [QrMenuController::class, 'show'])->name('show');
        Route::get('/{qrmenu}/duzenle', [QrMenuController::class, 'edit'])->name('edit');
        Route::put('/{qrmenu}', [QrMenuController::class, 'update'])->name('update');
        Route::delete('/{qrmenu}', [QrMenuController::class, 'destroy'])->name('destroy');
        Route::post('/{qrmenu}/toggle', [QrMenuController::class, 'toggle'])->name('toggle');
        // Kategori
        Route::get('/{qrmenu}/kategori/ekle', [QrMenuCategoryController::class, 'createCategory'])->name('category.create');
        Route::post('/{qrmenu}/kategori/ekle', [QrMenuCategoryController::class, 'storeCategory'])->name('category.store');
        Route::get('/{qrmenu}/kategori/{category}/duzenle', [QrMenuCategoryController::class, 'editCategory'])->name('category.edit');
        Route::put('/{qrmenu}/kategori/{category}', [QrMenuCategoryController::class, 'updateCategory'])->name('category.update');
        Route::delete('/{qrmenu}/kategori/{category}', [QrMenuCategoryController::class, 'destroyCategory'])->name('category.destroy');
        // Ürün
        Route::get('/{qrmenu}/kategori/{category}/urun/ekle', [QrMenuCategoryController::class, 'createItem'])->name('item.create');
        Route::post('/{qrmenu}/kategori/{category}/urun/ekle', [QrMenuCategoryController::class, 'storeItem'])->name('item.store');
        Route::get('/{qrmenu}/kategori/{category}/urun/{item}/duzenle', [QrMenuCategoryController::class, 'editItem'])->name('item.edit');
        Route::put('/{qrmenu}/kategori/{category}/urun/{item}', [QrMenuCategoryController::class, 'updateItem'])->name('item.update');
        Route::delete('/{qrmenu}/kategori/{category}/urun/{item}', [QrMenuCategoryController::class, 'destroyItem'])->name('item.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Rol & Yetki Yönetimi (sadece super_admin)
    |--------------------------------------------------------------------------
    */
    Route::prefix('roller')->name('roles.')->group(function () {
        Route::get('/',                          [RoleController::class, 'index'])->name('index');
        Route::post('/',                         [RoleController::class, 'store'])->name('store');
        Route::put('/{role}',                    [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}',                 [RoleController::class, 'destroy'])->name('destroy');
        Route::get('/{role}/izinler',            [RoleController::class, 'permissions'])->name('permissions');
        Route::post('/{role}/izinler',           [RoleController::class, 'updatePermissions'])->name('updatePermissions');
    });

}); // auth middleware group