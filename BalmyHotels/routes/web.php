<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KokiAdminController;
use App\Http\Controllers\Modules\VehicleController;
use App\Http\Controllers\Modules\VehicleOperationController;
use App\Http\Controllers\Modules\VehicleMaintenanceController;
use App\Http\Controllers\Modules\VehicleInsuranceController;
use App\Http\Controllers\Modules\VehicleTripController;
use App\Http\Controllers\Modules\UserController;
use App\Http\Controllers\Modules\DepartmentController;
use App\Http\Controllers\Modules\DoorLogController;
use App\Http\Controllers\Modules\DoorLogReportController;
use App\Http\Controllers\Modules\HrReportController;
use App\Http\Controllers\Modules\GuestLogController;
use App\Http\Controllers\Modules\FaultController;
use App\Http\Controllers\Modules\FaultLocationController;
use App\Http\Controllers\Modules\FaultTypeController;
use App\Http\Controllers\Modules\AssetCategoryController;
use App\Http\Controllers\Modules\AssetController;
use App\Http\Controllers\Modules\AssetExitController;
use App\Http\Controllers\Modules\QrMenuController;
use App\Http\Controllers\Modules\QrMenuCategoryController;
use App\Http\Controllers\Modules\MenuShowcaseController;
use App\Http\Controllers\Modules\FoodLibraryController;
use App\Http\Controllers\QrMenuPublicController;
use App\Http\Controllers\Modules\SurveyController;
use App\Http\Controllers\SurveyPublicController;
use App\Http\Controllers\Modules\FoodLabelController;
use App\Http\Controllers\Modules\PrinterController;
use App\Http\Controllers\FoodLabelPublicController;
use App\Http\Controllers\StaffSurveyPublicController;
use App\Http\Controllers\Modules\StaffSurveyController;
use App\Http\Controllers\Modules\ContractComparisonController;
use App\Http\Controllers\Modules\PdfConverterController;
use App\Http\Controllers\Modules\PdfMergerController;
use App\Http\Controllers\Modules\RoleController;
use App\Http\Controllers\Modules\CarbonFootprintController;
use App\Http\Controllers\Modules\ShuttleRouteController;
use App\Http\Controllers\Modules\ShuttleVehicleController;
use App\Http\Controllers\Modules\ShuttleOperationController;
use App\Http\Controllers\Modules\ShuttleReportController;
use App\Http\Controllers\Modules\ServicePlannerController;
use App\Http\Controllers\Modules\ManagementReportController;
use App\Http\Controllers\Modules\RestaurantController;
use App\Http\Controllers\Modules\OrderController;
use App\Http\Controllers\Modules\OrderReportController;
use App\Http\Controllers\Modules\OrderAnalyticsController;
use App\Http\Controllers\Modules\OrderAiAnalysisController;
use App\Http\Controllers\Modules\OrderGuestAnalysisController;
use App\Http\Controllers\Modules\OcrController;
use App\Http\Controllers\Modules\AuditTypeController;
use App\Http\Controllers\Modules\AuditController;
use App\Http\Controllers\Modules\AuditNonconformityController;
use App\Http\Controllers\Modules\AuditAnalyticsController;
use App\Http\Controllers\Modules\ItComputerController;
use App\Http\Controllers\Modules\ItBackupController;
use App\Http\Controllers\Modules\MikroTikController;
use App\Http\Controllers\Modules\LoginLogController;
use App\Http\Controllers\Modules\MyTaskController;
use App\Http\Controllers\Modules\AgencyController;
use App\Http\Controllers\Modules\AgencyContractController;
use App\Http\Controllers\Modules\FrontDeskBedTypeController;
use App\Http\Controllers\Modules\GuestControlController;
use App\Http\Controllers\Modules\FrontDeskRoomTypeController;
use App\Http\Controllers\Modules\FrontDeskRoomController;
use App\Http\Controllers\Modules\FrontDeskReservationController;
use App\Http\Controllers\Modules\EducationAssignmentController;
use App\Http\Controllers\Modules\EducationCourseController;
use App\Http\Controllers\Modules\EducationEventController;
use App\Http\Controllers\Modules\EducationLearningController;
use App\Http\Controllers\Modules\EducationQuizController;
use App\Http\Controllers\Modules\EducationReportController;
use App\Http\Controllers\Modules\AnimationEventController;
use App\Http\Controllers\Modules\EventTrackingController;
use App\Http\Controllers\Modules\EventShowReportController;
use App\Http\Controllers\Modules\PdksController;
use App\Http\Controllers\AssetPublicController;

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

// Profil
use App\Http\Controllers\ProfileController;
Route::middleware('auth')->group(function () {
    Route::get('/profil',  [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profil', [ProfileController::class, 'update'])->name('profile.update');

    // Sunucudaki mevcut storage dosyalarının izinlerini düz (0644) yap — bir kez çalıştırılır
    Route::get('/storage-fix-permissions', function () {
        $dir = storage_path('app/public');
        $fixed = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                @chmod($file->getPathname(), 0644);
                $fixed++;
            }
        }
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item) {
            if ($item->isDir()) {
                @chmod($item->getPathname(), 0755);
            }
        }
        return response("Tamam: {$fixed} dosya 0644, klasörler 0755 yapıldı.");
    })->middleware('auth');
});

/*
|--------------------------------------------------------------------------
| Demirbaş QR — Public (auth gerekmez)
|--------------------------------------------------------------------------
*/
Route::get('/demirbaslar/qr/{token}', [AssetPublicController::class, 'show'])->name('assets.public.qr');

/*
|--------------------------------------------------------------------------
| QR Menü — Public (auth gerekmez)
|--------------------------------------------------------------------------
*/
Route::get('/menu/{slug}', [QrMenuPublicController::class, 'splash'])->name('qrmenu.show');
Route::get('/menu/{slug}/{lang}', [QrMenuPublicController::class, 'view'])->name('qrmenu.view');
Route::get('/vitrin/{slug}', [QrMenuPublicController::class, 'showcase'])->name('showcase.show');

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
    | Araç Görev Takip
    |--------------------------------------------------------------------------
    */
    Route::prefix('arac-gorevler')->name('vehicle-trips.')->group(function () {
        Route::get('/',                                  [VehicleTripController::class, 'index'])->name('index');
        Route::get('/yeni',                              [VehicleTripController::class, 'create'])->name('create');
        Route::post('/',                                 [VehicleTripController::class, 'store'])->name('store');
        Route::get('/aktif-gorevim',                     [VehicleTripController::class, 'myTrip'])->name('my');
        Route::get('/kontrol',                           [VehicleTripController::class, 'control'])->name('control');
        Route::get('/{vehicleTrip}',                     [VehicleTripController::class, 'show'])->name('show');
        Route::get('/{vehicleTrip}/yazdir',              [VehicleTripController::class, 'printTrip'])->name('print');
        Route::get('/{vehicleTrip}/bitir',               [VehicleTripController::class, 'complete'])->name('complete');
        Route::put('/{vehicleTrip}',                     [VehicleTripController::class, 'update'])->name('update');
        Route::delete('/{vehicleTrip}',                  [VehicleTripController::class, 'destroy'])->name('destroy');
        // API: konum kaydet
        Route::post('/{vehicleTrip}/konum',              [VehicleTripController::class, 'storeLocation'])->name('location');
        // API: kontrol sayfası konum polling
        Route::get('/{vehicleTrip}/konumlar',            [VehicleTripController::class, 'controlLocations'])->name('control-locations');
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
    | Personel PDKS Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('pdks')->name('pdks.')->group(function () {
        Route::get('/',                         [PdksController::class, 'index'])->name('index');
        Route::post('/giris',                   [PdksController::class, 'checkIn'])->name('check-in');
        Route::post('/cikis',                   [PdksController::class, 'checkOut'])->name('check-out');
        Route::post('/mola-baslat',             [PdksController::class, 'startBreak'])->name('break-start');
        Route::post('/mola-bitir',              [PdksController::class, 'endBreak'])->name('break-end');
        Route::get('/personeller',              [PdksController::class, 'employees'])->name('employees');
        Route::post('/personeller/elektra-sync',[PdksController::class, 'syncForesta'])->name('employees.sync-foresta');
        Route::get('/devam-kayitlari',          [PdksController::class, 'attendance'])->name('attendance');
        Route::get('/mola-tipleri',             [PdksController::class, 'breakTypes'])->name('breaks');
        Route::post('/mola-tipleri',            [PdksController::class, 'storeBreakType'])->name('breaks.store');
        Route::get('/vardiyalar',               [PdksController::class, 'shifts'])->name('shifts');
        Route::post('/vardiyalar/tip',          [PdksController::class, 'storeShiftType'])->name('shifts.types.store');
        Route::post('/vardiyalar/ata',          [PdksController::class, 'storeShiftAssignment'])->name('shifts.assign');
        Route::post('/vardiya-degisim',         [PdksController::class, 'storeShiftChangeRequest'])->name('shift-change.store');
        Route::post('/vardiya-degisim/{shiftChangeRequest}/durum', [PdksController::class, 'updateShiftChangeStatus'])->name('shift-change.status');
        Route::get('/izinler',                  [PdksController::class, 'leaves'])->name('leaves');
        Route::post('/izinler/tip',             [PdksController::class, 'storeLeaveType'])->name('leaves.types.store');
        Route::post('/izinler',                 [PdksController::class, 'storeLeaveRequest'])->name('leaves.store');
        Route::post('/izinler/{leaveRequest}/durum', [PdksController::class, 'updateLeaveStatus'])->name('leaves.status');
        Route::get('/fazla-mesai',              [PdksController::class, 'overtime'])->name('overtime');
        Route::post('/fazla-mesai',             [PdksController::class, 'storeOvertimeRequest'])->name('overtime.store');
        Route::post('/fazla-mesai/{overtimeRequest}/durum', [PdksController::class, 'updateOvertimeStatus'])->name('overtime.status');
        Route::get('/raporlar',                 [PdksController::class, 'reports'])->name('reports');
        Route::get('/bildirimler',              [PdksController::class, 'notifications'])->name('notifications');
        Route::post('/bildirimler/{notification}/okundu', [PdksController::class, 'markNotificationRead'])->name('notifications.read');
        Route::post('/bildirimler/toplu-okundu',[PdksController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
        Route::get('/ayarlar',                  [PdksController::class, 'settings'])->name('settings');
        Route::post('/ayarlar/politika',        [PdksController::class, 'storePolicy'])->name('settings.policy.store');
    });

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
        Route::get('/etkinlik-takip',        [EventTrackingController::class, 'index'])->name('event-tracking.index');
        Route::get('/etkinlik-takip/{eventDate}', [EventTrackingController::class, 'show'])->name('event-tracking.show');
        Route::post('/etkinlik-takip/{eventDate}', [EventTrackingController::class, 'store'])->name('event-tracking.store');
        Route::get('/ik-rapor',              [HrReportController::class, 'index'])->name('hr-report');
        Route::get('/ik-rapor/pdf',          [HrReportController::class, 'pdf'])->name('hr-report-pdf');
        Route::get('/ik-rapor/personeller',  [HrReportController::class, 'staffByBranch'])->name('hr-report-staff');
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
    | Animasyon Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('animasyon')->name('animation.')->group(function () {
        Route::get('/etkinlikler',             [AnimationEventController::class, 'index'])->name('events.index');
        Route::get('/etkinlikler/yeni',        [AnimationEventController::class, 'create'])->name('events.create');
        Route::post('/etkinlikler',            [AnimationEventController::class, 'store'])->name('events.store');
        Route::get('/etkinlikler/{event}',     [AnimationEventController::class, 'show'])->name('events.show');
        Route::delete('/etkinlikler/{event}',  [AnimationEventController::class, 'destroy'])->name('events.destroy');
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
        Route::get('/istatistikler',              [FaultController::class, 'stats'])->name('stats');
        Route::get('/oda-raporu',                [FaultController::class, 'roomReport'])->name('room-report');
        Route::get('/oda-raporu/excel',          [FaultController::class, 'roomReportExcel'])->name('room-report.excel');
        Route::get('/ariza-raporu',              [FaultController::class, 'typeReport'])->name('type-report');
        Route::get('/ariza-raporu/excel',        [FaultController::class, 'typeReportExcel'])->name('type-report.excel');
        Route::get('/analiz',                    [FaultController::class, 'analysis'])->name('analysis');
        Route::post('/analiz/gonder',            [FaultController::class, 'sendAnalysisReport'])->name('analysis.send');

        // AJAX cascading dropdown
        Route::get('/ajax/departmanlar',         [FaultController::class, 'ajaxDepartments'])->name('ajax.departments');
        Route::get('/ajax/konumlar',             [FaultController::class, 'ajaxLocations'])->name('ajax.locations');
        Route::get('/ajax/alanlar',              [FaultController::class, 'ajaxAreas'])->name('ajax.areas');
        Route::get('/ajax/ariza-turleri',        [FaultController::class, 'ajaxFaultTypes'])->name('ajax.fault-types');
        Route::get('/ajax/gelen-yeni',           [FaultController::class, 'ajaxNewIncoming'])->name('ajax.new-incoming');

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
        Route::get('/',                                          [AssetCategoryController::class, 'index'])->name('index');
        Route::get('/ekle',                                      [AssetCategoryController::class, 'create'])->name('create');
        Route::post('/ekle',                                     [AssetCategoryController::class, 'store'])->name('store');
        Route::get('/{assetCategory}/alt-kategoriler',           [AssetCategoryController::class, 'subcategories'])->name('subcategories');
        Route::get('/{assetCategory}/duzenle',                   [AssetCategoryController::class, 'edit'])->name('edit');
        Route::put('/{assetCategory}',                           [AssetCategoryController::class, 'update'])->name('update');
        Route::delete('/{assetCategory}',                        [AssetCategoryController::class, 'destroy'])->name('destroy');
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
        Route::get('/{asset}/qr-yazdir',       [AssetController::class, 'qrPrint'])->name('qrPrint');
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
        Route::post('/json-import',         [FoodLabelController::class, 'importJson'])->name('json-import');
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
        // Vitrinler (showcase)
        Route::prefix('vitrinler')->name('showcases.')->group(function () {
            Route::get('/',                    [MenuShowcaseController::class, 'index'])->name('index');
            Route::get('/ekle',                [MenuShowcaseController::class, 'create'])->name('create');
            Route::post('/ekle',               [MenuShowcaseController::class, 'store'])->name('store');
            Route::get('/{showcase}/duzenle',  [MenuShowcaseController::class, 'edit'])->name('edit');
            Route::put('/{showcase}',          [MenuShowcaseController::class, 'update'])->name('update');
            Route::delete('/{showcase}',       [MenuShowcaseController::class, 'destroy'])->name('destroy');
        });
        Route::get('/ekle', [QrMenuController::class, 'create'])->name('create');
        Route::post('/ekle', [QrMenuController::class, 'store'])->name('store');
        Route::get('/{qrmenu}', [QrMenuController::class, 'show'])->name('show');
        Route::get('/{qrmenu}/duzenle', [QrMenuController::class, 'edit'])->name('edit');
        Route::put('/{qrmenu}', [QrMenuController::class, 'update'])->name('update');
        Route::delete('/{qrmenu}', [QrMenuController::class, 'destroy'])->name('destroy');
        Route::post('/{qrmenu}/toggle', [QrMenuController::class, 'toggle'])->name('toggle');
        Route::post('/{qrmenu}/klonla', [QrMenuController::class, 'clone'])->name('clone');
        // Kategori
        Route::get('/{qrmenu}/kategori/ekle', [QrMenuCategoryController::class, 'createCategory'])->name('category.create');
        Route::post('/{qrmenu}/kategori/ekle', [QrMenuCategoryController::class, 'storeCategory'])->name('category.store');
        Route::get('/{qrmenu}/kategori/{category}/duzenle', [QrMenuCategoryController::class, 'editCategory'])->name('category.edit');
        Route::put('/{qrmenu}/kategori/{category}', [QrMenuCategoryController::class, 'updateCategory'])->name('category.update');
        Route::delete('/{qrmenu}/kategori/{category}', [QrMenuCategoryController::class, 'destroyCategory'])->name('category.destroy');
        Route::post('/{qrmenu}/kategori/{category}/kutuphane-guncelle', [QrMenuCategoryController::class, 'syncCategoryFromLibrary'])->name('category.syncLibrary');
        // Ürün
        Route::get('/{qrmenu}/kategori/{category}/urun/ekle', [QrMenuCategoryController::class, 'createItem'])->name('item.create');
        Route::post('/{qrmenu}/kategori/{category}/urun/ekle', [QrMenuCategoryController::class, 'storeItem'])->name('item.store');
        Route::get('/{qrmenu}/kategori/{category}/urun/{item}/duzenle', [QrMenuCategoryController::class, 'editItem'])->name('item.edit');
        Route::put('/{qrmenu}/kategori/{category}/urun/{item}', [QrMenuCategoryController::class, 'updateItem'])->name('item.update');
        Route::delete('/{qrmenu}/kategori/{category}/urun/{item}', [QrMenuCategoryController::class, 'destroyItem'])->name('item.destroy');
        // Kütüphaneden Ürün Ekle
        Route::post('/{qrmenu}/kategoriler/{category}/kutuphane', [QrMenuCategoryController::class, 'addFromLibrary'])->name('category.addFromLibrary');
    });

    // Yemek Kütüphanesi
    Route::prefix('yemek-kutuphane')->name('food-library.')->group(function () {
        Route::get('/', [FoodLibraryController::class, 'index'])->name('index');
        Route::get('/kategoriler/ekle', [FoodLibraryController::class, 'createCategory'])->name('categories.create');
        Route::post('/kategoriler', [FoodLibraryController::class, 'storeCategory'])->name('categories.store');
        Route::post('/kategoriler/json-aktar', [FoodLibraryController::class, 'importCategoriesJson'])->name('categories.json-import');
        Route::get('/kategoriler/{category}/duzenle', [FoodLibraryController::class, 'editCategory'])->name('categories.edit');
        Route::put('/kategoriler/{category}', [FoodLibraryController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/kategoriler/{category}', [FoodLibraryController::class, 'destroyCategory'])->name('categories.destroy');
        Route::delete('/kategoriler/{category}/urunlerle-sil', [FoodLibraryController::class, 'destroyCategoryWithProducts'])->name('categories.destroy-with-products');
        Route::get('/urunler', [FoodLibraryController::class, 'products'])->name('products');
        Route::get('/urunler/ekle', [FoodLibraryController::class, 'createProduct'])->name('product.create');
        Route::post('/urunler', [FoodLibraryController::class, 'storeProduct'])->name('product.store');
        Route::post('/urunler/json-aktar', [FoodLibraryController::class, 'importProductsJson'])->name('product.json-import');
        Route::get('/urunler/{product}/duzenle', [FoodLibraryController::class, 'editProduct'])->name('product.edit');
        Route::put('/urunler/{product}', [FoodLibraryController::class, 'updateProduct'])->name('product.update');
        Route::delete('/urunler/{product}', [FoodLibraryController::class, 'destroyProduct'])->name('product.destroy');
        Route::get('/api/urunler', [FoodLibraryController::class, 'apiProducts'])->name('api.products');
        Route::get('/api/urunler/{product}', [FoodLibraryController::class, 'apiProduct'])->name('api.product');
    });

    // Yazıcılar
    Route::prefix('yazicilar')->name('printers.')->group(function () {
        Route::get('/',                   [PrinterController::class, 'index'])->name('index');
        Route::post('/',                  [PrinterController::class, 'store'])->name('store');
        Route::get('/{printer}/duzenle',  [PrinterController::class, 'edit'])->name('edit');
        Route::put('/{printer}',          [PrinterController::class, 'update'])->name('update');
        Route::delete('/{printer}',       [PrinterController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Sözleşme Karşılaştırma
    |--------------------------------------------------------------------------
    */
    Route::prefix('sozlesme-karsilastirma')->name('contracts.')->group(function () {
        Route::get('/',            [ContractComparisonController::class, 'index'])->name('index');
        Route::get('/yeni',        [ContractComparisonController::class, 'create'])->name('create');
        Route::post('/karsilastir',[ContractComparisonController::class, 'compare'])->name('compare');
        Route::get('/{contract}',  [ContractComparisonController::class, 'show'])->name('show');
        Route::delete('/{contract}',[ContractComparisonController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Karbon Ayak İzi Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('karbon-ayak-izi')->name('carbon.')->group(function () {
        Route::get('/',                        [CarbonFootprintController::class, 'index'])->name('index');
        Route::get('/yeni',                    [CarbonFootprintController::class, 'create'])->name('create');
        Route::post('/yeni',                   [CarbonFootprintController::class, 'store'])->name('store');
        Route::get('/emisyon-faktoru',         [CarbonFootprintController::class, 'emissionFactor'])->name('emission-factor');
        Route::get('/{carbon}',                [CarbonFootprintController::class, 'show'])->name('show');
        Route::get('/{carbon}/duzenle',        [CarbonFootprintController::class, 'edit'])->name('edit');
        Route::put('/{carbon}',                [CarbonFootprintController::class, 'update'])->name('update');
        Route::post('/{carbon}/finalize',      [CarbonFootprintController::class, 'finalize'])->name('finalize');
        Route::get('/{carbon}/pdf',            [CarbonFootprintController::class, 'pdf'])->name('pdf');
        Route::delete('/{carbon}',             [CarbonFootprintController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | PDF Word Çevirici
    |--------------------------------------------------------------------------
    */
    Route::prefix('pdf-donusturme')->name('pdf-converter.')->group(function () {
        Route::get('/',        [PdfConverterController::class, 'index'])->name('index');
        Route::post('/cevir',  [PdfConverterController::class, 'convert'])->name('convert');
    });

    /*
    |--------------------------------------------------------------------------
    | PDF Birleştirici
    |--------------------------------------------------------------------------
    */
    Route::prefix('pdf-birlestirme')->name('pdf-merger.')->group(function () {
        Route::get('/',       [PdfMergerController::class, 'index'])->name('index');
        Route::post('/birlestir', [PdfMergerController::class, 'merge'])->name('merge');
    });

    /*
    |--------------------------------------------------------------------------
    | OCR — Yazıya Çevir
    |--------------------------------------------------------------------------
    */
    Route::prefix('yaziya-cevir')->name('ocr.')->group(function () {
        Route::get('/',      [OcrController::class, 'index'])->name('index');
        Route::post('/cevir',[OcrController::class, 'extract'])->name('extract');
    });

    /*
    |--------------------------------------------------------------------------
    | Raporlar — TripAdvisor
    |--------------------------------------------------------------------------
    */
    Route::prefix('raporlar')->name('reports.')->group(function () {
        Route::get('/tripadvisor', [\App\Http\Controllers\Modules\TripAdvisorReportController::class, 'index'])->name('tripadvisor');
        Route::post('/tripadvisor/snapshot', [\App\Http\Controllers\Modules\TripAdvisorReportController::class, 'snapshot'])->name('tripadvisor.snapshot');
        Route::get('/google', [\App\Http\Controllers\Modules\GoogleReportController::class, 'index'])->name('google');
        Route::post('/google/snapshot', [\App\Http\Controllers\Modules\GoogleReportController::class, 'snapshot'])->name('google.snapshot');
        Route::get('/etkinlik-show', [EventShowReportController::class, 'index'])->name('event-shows');
        Route::get('/etkinlik-show/pdf', [EventShowReportController::class, 'pdf'])->name('event-shows.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Üst Yönetim Rapor Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('ust-yonetim-rapor')->name('management-reports.')->group(function () {
        Route::get('/',                         [ManagementReportController::class, 'index'])->name('index');
        Route::get('/mudur-giris-cikislari',    [ManagementReportController::class, 'managerDoorLogs'])->name('manager-door-logs');
        Route::get('/teknik-ariza-raporu',      [ManagementReportController::class, 'technicalFaults'])->name('technical-faults');
        Route::get('/servis-raporu',            [ManagementReportController::class, 'shuttleServices'])->name('shuttle-services');
        Route::get('/siparis-tuketim-raporu',   [ManagementReportController::class, 'focusedOrderConsumption'])->name('order-consumption');
    });

    /*
    |--------------------------------------------------------------------------
    | Servis Takip Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('servis-takip')->name('shuttle.')->group(function () {

        // Güzergah tanımları
        Route::prefix('guzergahlar')->name('routes.')->group(function () {
            Route::get('/',             [ShuttleRouteController::class, 'index'])->name('index');
            Route::get('/ekle',         [ShuttleRouteController::class, 'create'])->name('create');
            Route::post('/',            [ShuttleRouteController::class, 'store'])->name('store');
            Route::get('/{route}/duzenle', [ShuttleRouteController::class, 'edit'])->name('edit');
            Route::put('/{route}',      [ShuttleRouteController::class, 'update'])->name('update');
            Route::delete('/{route}',   [ShuttleRouteController::class, 'destroy'])->name('destroy');
        });

        // Araçlar
        Route::prefix('araclar')->name('vehicles.')->group(function () {
            Route::get('/',              [ShuttleVehicleController::class, 'index'])->name('index');
            Route::get('/ekle',          [ShuttleVehicleController::class, 'create'])->name('create');
            Route::post('/',             [ShuttleVehicleController::class, 'store'])->name('store');
            Route::get('/{vehicle}/duzenle', [ShuttleVehicleController::class, 'edit'])->name('edit');
            Route::put('/{vehicle}',     [ShuttleVehicleController::class, 'update'])->name('update');
            Route::delete('/{vehicle}',  [ShuttleVehicleController::class, 'destroy'])->name('destroy');
        });

        // Operasyon
        Route::prefix('operasyon')->name('operations.')->group(function () {
            Route::get('/',                     [ShuttleOperationController::class, 'index'])->name('index');
            Route::post('/',                    [ShuttleOperationController::class, 'store'])->name('store');
            Route::get('/{operation}/duzenle',      [ShuttleOperationController::class, 'edit'])->name('edit');
            Route::put('/{operation}',               [ShuttleOperationController::class, 'update'])->name('update');
            Route::patch('/{operation}/donus',       [ShuttleOperationController::class, 'departure'])->name('departure');
            Route::delete('/{operation}',            [ShuttleOperationController::class, 'destroy'])->name('destroy');
        });

        // Raporlar
        Route::prefix('raporlar')->name('reports.')->group(function () {
            Route::get('/',    [ShuttleReportController::class, 'index'])->name('index');
            Route::get('/pdf', [ShuttleReportController::class, 'pdf'])->name('pdf');
            Route::get('/excel', [ShuttleReportController::class, 'excel'])->name('excel');
        });

    });

    Route::prefix('servis-planlayici')->name('service-planner.')->group(function () {
        Route::get('/', [ServicePlannerController::class, 'index'])->name('index');
        Route::get('/yeni', [ServicePlannerController::class, 'create'])->name('create');
        Route::post('/', [ServicePlannerController::class, 'store'])->name('store');
        Route::get('/sablon/excel', [ServicePlannerController::class, 'template'])->name('template');
        Route::get('/{plan}', [ServicePlannerController::class, 'show'])->name('show');
        Route::get('/{plan}/duzenle', [ServicePlannerController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [ServicePlannerController::class, 'update'])->name('update');
        Route::delete('/{plan}', [ServicePlannerController::class, 'destroy'])->name('destroy');
        Route::post('/{plan}/excel-yukle', [ServicePlannerController::class, 'importStops'])->name('importStops');
        Route::post('/{plan}/hesapla', [ServicePlannerController::class, 'calculate'])->name('calculate');
        Route::get('/{plan}/pdf', [ServicePlannerController::class, 'pdf'])->name('pdf');
    });

    Route::prefix('servis-takip/planlayici')->group(function () {
        Route::get('/', fn () => redirect()->route('service-planner.index'));
        Route::get('/sablon/excel', fn () => redirect()->route('service-planner.template'));
        Route::get('/yeni', fn () => redirect()->route('service-planner.create'));
        Route::get('/{plan}', fn (\App\Models\ServicePlannerPlan $plan) => redirect()->route('service-planner.show', $plan));
        Route::get('/{plan}/duzenle', fn (\App\Models\ServicePlannerPlan $plan) => redirect()->route('service-planner.edit', $plan));
        Route::get('/{plan}/pdf', fn (\App\Models\ServicePlannerPlan $plan) => redirect()->route('service-planner.pdf', $plan));
        Route::post('/{plan}/excel-yukle', [ServicePlannerController::class, 'importStops']);
        Route::post('/{plan}/hesapla', [ServicePlannerController::class, 'calculate']);
    });

    /*
    |--------------------------------------------------------------------------
    | Sipariş Modülü (Garson)
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | Egitim ve Gelisim
    |--------------------------------------------------------------------------
    */
    Route::prefix('egitim-ve-gelisim')->name('education.')->group(function () {
        Route::get('/', [EducationLearningController::class, 'index'])->name('index');
        Route::get('/egitimlerim', [EducationLearningController::class, 'index'])->name('learning.index');
        Route::get('/egitimlerim/{assignment}/video', [EducationLearningController::class, 'video'])->name('learning.video');
        Route::get('/egitimlerim/{assignment}/quiz', [EducationQuizController::class, 'take'])->name('learning.quiz');
        Route::post('/egitimlerim/{assignment}/quiz', [EducationQuizController::class, 'submit'])->name('learning.quiz.submit');
        Route::get('/egitimlerim/{assignment}', [EducationLearningController::class, 'show'])->name('learning.show');
        Route::post('/egitimlerim/{assignment}/ilerleme', [EducationLearningController::class, 'progress'])->name('learning.progress');

        Route::prefix('icerikler')->name('courses.')->group(function () {
            Route::get('/', [EducationCourseController::class, 'index'])->name('index');
            Route::get('/yeni', [EducationCourseController::class, 'create'])->name('create');
            Route::post('/', [EducationCourseController::class, 'store'])->name('store');
            Route::get('/{course}/video', [EducationCourseController::class, 'video'])->name('video');
            Route::get('/{course}/quiz', [EducationQuizController::class, 'edit'])->name('quiz.edit');
            Route::put('/{course}/quiz', [EducationQuizController::class, 'update'])->name('quiz.update');
            Route::get('/{course}', [EducationCourseController::class, 'show'])->name('show');
            Route::get('/{course}/duzenle', [EducationCourseController::class, 'edit'])->name('edit');
            Route::put('/{course}', [EducationCourseController::class, 'update'])->name('update');
            Route::delete('/{course}', [EducationCourseController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('atamalar')->name('assignments.')->group(function () {
            Route::get('/', [EducationAssignmentController::class, 'index'])->name('index');
            Route::post('/', [EducationAssignmentController::class, 'store'])->name('store');
            Route::delete('/{assignment}', [EducationAssignmentController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('yuz-yuze')->name('events.')->group(function () {
            Route::get('/', [EducationEventController::class, 'index'])->name('index');
            Route::get('/yeni', [EducationEventController::class, 'create'])->name('create');
            Route::post('/', [EducationEventController::class, 'store'])->name('store');
            Route::get('/{event}', [EducationEventController::class, 'show'])->name('show');
            Route::get('/{event}/duzenle', [EducationEventController::class, 'edit'])->name('edit');
            Route::put('/{event}', [EducationEventController::class, 'update'])->name('update');
            Route::post('/{event}/cevap', [EducationEventController::class, 'respond'])->name('respond');
            Route::delete('/{event}', [EducationEventController::class, 'destroy'])->name('destroy');
        });

        Route::get('/raporlar', [EducationReportController::class, 'index'])->name('reports.index');
    });

    Route::prefix('siparisler')->name('orders.')->group(function () {

        // Ana sayfa: restoran + masa seçimi
        Route::get('/',                              [OrderController::class, 'take'])->name('take');

        // Masayı aç
        Route::post('/masa-ac/{table}',              [OrderController::class, 'openTable'])->name('open-table');

        // Aktif seans sayfası
        Route::get('/seans/{session}',               [OrderController::class, 'session'])->name('session');

        // Sipariş kaydet
        Route::post('/seans/{session}/siparis',      [OrderController::class, 'storeOrder'])->name('store-order');

        // Sipariş fişi
        Route::get('/seans/{session}/siparis/{order}/fis', [OrderController::class, 'receipt'])->name('receipt');

        // Sipariş kalemi sil
        Route::delete('/seans/{session}/kalem/{item}', [OrderController::class, 'destroyOrderItem'])->name('destroy-item');

        // Masayı kapat
        Route::post('/seans/{session}/kapat',        [OrderController::class, 'closeTable'])->name('close-table');

        // Raporlar
        Route::get('/raporlar',                      [OrderReportController::class, 'index'])->name('report');

        // Analiz
        Route::get('/analiz',                        [OrderAnalyticsController::class, 'index'])->name('analytics');
        Route::get('/analiz/pdf',                    [OrderAnalyticsController::class, 'pdf'])->name('analytics.pdf');

        // AI Analiz
        Route::get('/ai-analiz',                     [OrderAiAnalysisController::class, 'index'])->name('ai-analysis');

        // Misafir Tüketim Analizi (hasılatsız)
        Route::get('/misafir-analiz',                [OrderGuestAnalysisController::class, 'index'])->name('guest-analysis');

        // Restoran tanımları
        Route::prefix('restoranlar')->name('restaurants.')->group(function () {
            Route::get('/',                                               [RestaurantController::class, 'index'])->name('index');
            Route::get('/ekle',                                           [RestaurantController::class, 'create'])->name('create');
            Route::post('/ekle',                                          [RestaurantController::class, 'store'])->name('store');
            Route::get('/{restaurant}',                                   [RestaurantController::class, 'show'])->name('show');
            Route::get('/{restaurant}/duzenle',                           [RestaurantController::class, 'edit'])->name('edit');
            Route::put('/{restaurant}',                                   [RestaurantController::class, 'update'])->name('update');
            Route::delete('/{restaurant}',                                [RestaurantController::class, 'destroy'])->name('destroy');
            Route::post('/{restaurant}/masalar',                          [RestaurantController::class, 'storeTable'])->name('tables.store');
            Route::post('/{restaurant}/masalar-toplu',                    [RestaurantController::class, 'storeBulkTables'])->name('tables.store-bulk');
            Route::delete('/{restaurant}/masalar/{table}',                [RestaurantController::class, 'destroyTable'])->name('tables.destroy');
            Route::post('/{restaurant}/yazicilar',                        [RestaurantController::class, 'savePrinters'])->name('printers.save');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | İç Denetim Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('ic-denetim')->name('audit.')->group(function () {

        // AJAX
        Route::get('ajax/departmanlar', [AuditController::class, 'ajaxDepartments'])->name('ajax.departments');

        // Denetim Tipleri Yönetimi
        Route::get('tipler',                    [AuditTypeController::class, 'index'])->name('types.index');
        Route::post('tipler',                   [AuditTypeController::class, 'store'])->name('types.store');
        Route::put('tipler/{auditType}',        [AuditTypeController::class, 'update'])->name('types.update');
        Route::delete('tipler/{auditType}',     [AuditTypeController::class, 'destroy'])->name('types.destroy');

        // Uygunsuzluklarım
        Route::get('uygunsuzluklarim',          [AuditNonconformityController::class, 'index'])->name('nonconformities.index');
        Route::get('uygunsuzluk/{nonconformity}', [AuditNonconformityController::class, 'show'])->name('nonconformities.show');
        Route::patch('uygunsuzluk/{nonconformity}/coz', [AuditNonconformityController::class, 'resolve'])->name('nonconformities.resolve');

        // Analiz & PDF (static routes before {audit} wildcard)
        Route::get('analiz',                    [AuditAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('analiz/pdf',                [AuditAnalyticsController::class, 'pdf'])->name('analytics.pdf');

        // Denetimler (parameterized routes LAST)
        Route::get('olustur',                   [AuditController::class, 'create'])->name('create');
        Route::get('',                          [AuditController::class, 'index'])->name('index');
        Route::post('',                         [AuditController::class, 'store'])->name('store');
        Route::get('{audit}',                   [AuditController::class, 'show'])->name('show');
        Route::delete('{audit}',                [AuditController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Rol & Yetki Yönetimi (sadece super_admin)
    |--------------------------------------------------------------------------
    */
    Route::prefix('roller')->name('roles.')->group(function () {        Route::get('/',                          [RoleController::class, 'index'])->name('index');
        Route::post('/',                         [RoleController::class, 'store'])->name('store');
        Route::put('/{role}',                    [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}',                 [RoleController::class, 'destroy'])->name('destroy');
        Route::get('/{role}/izinler',            [RoleController::class, 'permissions'])->name('permissions');
        Route::post('/{role}/izinler',           [RoleController::class, 'updatePermissions'])->name('updatePermissions');
    });

    /*
    |--------------------------------------------------------------------------
    | Bilgi İşlem Modülü
    |--------------------------------------------------------------------------
    */
    Route::prefix('bilgi-islem')->name('it.')->group(function () {
        // Bilgisayarlar (manuel envanter)
        Route::get('bilgisayarlar',               [ItComputerController::class, 'index'])->name('computers.index');
        Route::get('bilgisayarlar/{computer}',    [ItComputerController::class, 'show'])->name('computers.show');
        Route::post('bilgisayarlar',              [ItComputerController::class, 'store'])->name('computers.store');
        Route::put('bilgisayarlar/{computer}',    [ItComputerController::class, 'update'])->name('computers.update');
        Route::delete('bilgisayarlar/{computer}', [ItComputerController::class, 'destroy'])->name('computers.destroy');

        // Ajan Envanter (Windows Agent otomatik toplama)
        Route::get('ajan-envanter',                        [\App\Http\Controllers\Modules\AgentInventoryController::class, 'index'])->name('agent.index');
        Route::get('ajan-envanter/istatistikler',          [\App\Http\Controllers\Modules\AgentInventoryController::class, 'stats'])->name('agent.stats');
        Route::post('ajan-envanter/snapshot-temizle',      [\App\Http\Controllers\Modules\AgentInventoryController::class, 'clearSnapshotFields'])->name('agent.snapshot-temizle');
        Route::get('ajan-envanter/{agentComputer}',        [\App\Http\Controllers\Modules\AgentInventoryController::class, 'show'])->name('agent.show');
        Route::get('ajan-envanter/{agentComputer}/programlar',    [\App\Http\Controllers\Modules\AgentInventoryController::class, 'programs'])->name('agent.programs');
        Route::get('ajan-envanter/{agentComputer}/dosya-olaylari',    [\App\Http\Controllers\Modules\AgentInventoryController::class, 'fileEvents'])->name('agent.file-events');
        Route::get('ajan-envanter/{agentComputer}/program-degisiklikleri', [\App\Http\Controllers\Modules\AgentInventoryController::class, 'programEvents'])->name('agent.program-events');
        Route::get('ajan-envanter/{agentComputer}/silinen-dosyalar',   [\App\Http\Controllers\Modules\AgentInventoryController::class, 'deletions'])->name('agent.deletions');
        Route::get('ajan-envanter/{agentComputer}/tarayici-gecmisi',   [\App\Http\Controllers\Modules\AgentInventoryController::class, 'browserHistory'])->name('agent.browser-history');
        Route::post('ajan-envanter/{agentComputer}/komut',             [\App\Http\Controllers\Modules\AgentInventoryController::class, 'sendCommand'])->name('agent.send-command');
        Route::post('ajan-envanter/{agentComputer}/screenshot',        [\App\Http\Controllers\Modules\AgentInventoryController::class, 'requestScreenshot'])->name('agent.request-screenshot');
        Route::delete('ajan-envanter/{agentComputer}/screenshot',       [\App\Http\Controllers\Modules\AgentInventoryController::class, 'clearScreenshots'])->name('agent.clear-screenshots');
        Route::get('ajan-envanter/{agentComputer}/son-ekran',          [\App\Http\Controllers\Modules\AgentInventoryController::class, 'latestScreenshot'])->name('agent.latest-screenshot');
        Route::get('ajan-envanter/{agentComputer}/vnc',                [\App\Http\Controllers\Modules\VncBrowserController::class, 'show'])->name('agent.vnc');
        Route::post('ajan-envanter/{agentComputer}/vnc/connect',       [\App\Http\Controllers\Modules\VncBrowserController::class, 'connect'])->name('agent.vnc.connect');
        Route::post('ajan-envanter/{agentComputer}/wol',               [\App\Http\Controllers\Modules\AgentInventoryController::class, 'wakeOnLan'])->name('agent.wol');
        Route::delete('ajan-envanter/{agentComputer}',                 [\App\Http\Controllers\Modules\AgentInventoryController::class, 'destroy'])->name('agent.destroy');

        // Yedekleme
        Route::get('yedekleme',            [ItBackupController::class, 'index'])->name('backup.index');
        Route::post('yedekleme',           [ItBackupController::class, 'run'])->name('backup.run');
        Route::get('yedekleme/indir/{filename}',   [ItBackupController::class, 'download'])->name('backup.download')
            ->where('filename', '[^/]+');
        Route::delete('yedekleme/{filename}',      [ItBackupController::class, 'deleteFile'])->name('backup.delete')
            ->where('filename', '[^/]+');

        // Giriş Logları
        Route::get('giris-loglari', [LoginLogController::class, 'index'])->name('login-logs.index');

        // MikroTik Dashboard
        Route::get('mikrotik',               [MikroTikController::class, 'index'])->name('mikrotik.index');
        Route::get('mikrotik/hotspot-aktif', [MikroTikController::class, 'hotspotActive'])->name('mikrotik.hotspot-aktif');
        Route::get('mikrotik/dhcp',          [MikroTikController::class, 'dhcpLeases'])->name('mikrotik.dhcp');
        Route::get('mikrotik/kaynaklar',     [MikroTikController::class, 'resources'])->name('mikrotik.kaynaklar');
        Route::get('mikrotik/kullanim',          [MikroTikController::class, 'usageStats'])->name('mikrotik.kullanim');
        Route::get('mikrotik/client-kullanim',   [MikroTikController::class, 'clientUsage'])->name('mikrotik.client-kullanim');
    });

    /*
    |--------------------------------------------------------------------------
    | İşlerim (Kişisel Görev Listesi)
    |--------------------------------------------------------------------------
    */
    Route::prefix('islerim')->name('islerim.')->group(function () {
        Route::get('/',              [MyTaskController::class, 'index'])->name('index');
        Route::post('/',             [MyTaskController::class, 'store'])->name('store');
        Route::put('/{userTask}',    [MyTaskController::class, 'update'])->name('update');
        Route::patch('/{userTask}/tamamla', [MyTaskController::class, 'complete'])->name('complete');
        Route::delete('/{userTask}', [MyTaskController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Acenteler
    |--------------------------------------------------------------------------
    */
    Route::prefix('acenteler')->name('agencies.')->group(function () {
        Route::get('/',              [AgencyController::class, 'index'])->name('index');
        Route::get('/yeni',          [AgencyController::class, 'create'])->name('create');
        Route::post('/',             [AgencyController::class, 'store'])->name('store');
        Route::put('/{agency}',      [AgencyController::class, 'update'])->name('update');
        Route::delete('/{agency}',   [AgencyController::class, 'destroy'])->name('destroy');

        Route::prefix('kontratlar')->name('contracts.')->group(function () {
            Route::get('/',              [AgencyContractController::class, 'index'])->name('index');
            Route::get('/yeni',          [AgencyContractController::class, 'create'])->name('create');
            Route::post('/',             [AgencyContractController::class, 'store'])->name('store');
            Route::put('/{contract}',    [AgencyContractController::class, 'update'])->name('update');
            Route::delete('/{contract}', [AgencyContractController::class, 'destroy'])->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Önbüro (Front Desk)
    |--------------------------------------------------------------------------
    */
    Route::prefix('onburo')->name('frontdesk.')->group(function () {
        Route::get('yatak-tipleri',             [FrontDeskBedTypeController::class, 'index'])->name('bed-types.index');
        Route::post('yatak-tipleri',            [FrontDeskBedTypeController::class, 'store'])->name('bed-types.store');
        Route::put('yatak-tipleri/{bedType}',   [FrontDeskBedTypeController::class, 'update'])->name('bed-types.update');
        Route::delete('yatak-tipleri/{bedType}',[FrontDeskBedTypeController::class, 'destroy'])->name('bed-types.destroy');

        Route::get('oda-tipleri',               [FrontDeskRoomTypeController::class, 'index'])->name('room-types.index');
        Route::post('oda-tipleri',              [FrontDeskRoomTypeController::class, 'store'])->name('room-types.store');
        Route::put('oda-tipleri/{roomType}',    [FrontDeskRoomTypeController::class, 'update'])->name('room-types.update');
        Route::delete('oda-tipleri/{roomType}', [FrontDeskRoomTypeController::class, 'destroy'])->name('room-types.destroy');

        Route::get('odalar',                    [FrontDeskRoomController::class, 'index'])->name('rooms.index');
        Route::post('odalar',                   [FrontDeskRoomController::class, 'store'])->name('rooms.store');
        Route::put('odalar/{room}',             [FrontDeskRoomController::class, 'update'])->name('rooms.update');
        Route::delete('odalar/{room}',          [FrontDeskRoomController::class, 'destroy'])->name('rooms.destroy');

        Route::get('misafir-kontrol',                   [GuestControlController::class, 'index'])->name('guest-control.index');
        Route::post('misafir-kontrol/sorgula',          [GuestControlController::class, 'lookup'])->name('guest-control.lookup');
        Route::post('misafir-kontrol/islem',            [GuestControlController::class, 'store'])->name('guest-control.store');
        Route::get('misafir-kontrol/kayitlar',          [GuestControlController::class, 'history'])->name('guest-control.history');

        Route::get('rezervasyonlar',            [FrontDeskReservationController::class, 'index'])->name('reservations.index');
        Route::get('rezervasyonlar/yeni',       [FrontDeskReservationController::class, 'create'])->name('reservations.create');
        Route::post('rezervasyonlar',           [FrontDeskReservationController::class, 'store'])->name('reservations.store');
        Route::get('rezervasyonlar/{reservation}', [FrontDeskReservationController::class, 'show'])->name('reservations.show');
        Route::delete('rezervasyonlar/{reservation}', [FrontDeskReservationController::class, 'destroy'])->name('reservations.destroy');
    });

}); // auth middleware group
