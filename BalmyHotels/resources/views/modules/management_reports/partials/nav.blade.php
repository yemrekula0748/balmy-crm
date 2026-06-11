<div class="mr-tabs">
    <a href="{{ route('management-reports.manager-door-logs') }}"
       @class(['mr-tab', 'active' => request()->routeIs('management-reports.manager-door-logs')])>
        <i class="fas fa-door-open"></i> Müdür Giriş Çıkışları
    </a>
    <a href="{{ route('management-reports.technical-faults') }}"
       @class(['mr-tab', 'active' => request()->routeIs('management-reports.technical-faults')])>
        <i class="fas fa-tools"></i> Teknik Arıza Raporu
    </a>
    <a href="{{ route('management-reports.shuttle-services') }}"
       @class(['mr-tab', 'active' => request()->routeIs('management-reports.shuttle-services')])>
        <i class="fas fa-bus"></i> Servis Raporu
    </a>
</div>
