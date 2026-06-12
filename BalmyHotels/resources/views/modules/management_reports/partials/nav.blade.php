<div class="mr-tabs">
    @php($reportUser = auth()->user())
    @if($reportUser->hasPermission('yonetim_kurulu_rapor', 'index') || $reportUser->hasPermission('yonetim_mudur_giris_cikis_raporu', 'index'))
    <a href="{{ route('management-reports.manager-door-logs') }}"
       @class(['mr-tab', 'active' => request()->routeIs('management-reports.manager-door-logs')])>
        <i class="fas fa-door-open"></i> Müdür Giriş Çıkışları
    </a>
    @endif
    @if($reportUser->hasPermission('yonetim_kurulu_rapor', 'index') || $reportUser->hasPermission('yonetim_teknik_ariza_raporu', 'index'))
    <a href="{{ route('management-reports.technical-faults') }}"
       @class(['mr-tab', 'active' => request()->routeIs('management-reports.technical-faults')])>
        <i class="fas fa-tools"></i> Teknik Arıza Raporu
    </a>
    @endif
    @if($reportUser->hasPermission('yonetim_kurulu_rapor', 'index') || $reportUser->hasPermission('yonetim_servis_raporu', 'index'))
    <a href="{{ route('management-reports.shuttle-services') }}"
       @class(['mr-tab', 'active' => request()->routeIs('management-reports.shuttle-services')])>
        <i class="fas fa-bus"></i> Servis Raporu
    </a>
    @endif
    @if($reportUser->hasPermission('yonetim_siparis_raporu', 'index'))
    <a href="{{ route('management-reports.order-consumption') }}"
       @class(['mr-tab', 'active' => request()->routeIs('management-reports.order-consumption')])>
        <i class="fas fa-utensils"></i> Sipariş Tüketim Raporu
    </a>
    @endif
</div>
