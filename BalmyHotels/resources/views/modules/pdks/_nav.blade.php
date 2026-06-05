@php $u = auth()->user(); @endphp
<div class="pdks-nav mb-3">
    <a href="{{ route('pdks.index') }}" @class(['active' => request()->routeIs('pdks.index')])>Panel</a>
    @if($u->hasPermission('pdks_employees','index'))<a href="{{ route('pdks.employees') }}" @class(['active' => request()->routeIs('pdks.employees')])>Personeller</a>@endif
    @if($u->hasPermission('pdks_attendance','index'))<a href="{{ route('pdks.attendance') }}" @class(['active' => request()->routeIs('pdks.attendance')])>Devam</a>@endif
    @if($u->hasPermission('pdks_shifts','index'))<a href="{{ route('pdks.shifts') }}" @class(['active' => request()->routeIs('pdks.shifts')])>Vardiya</a>@endif
    @if($u->hasPermission('pdks_leaves','index'))<a href="{{ route('pdks.leaves') }}" @class(['active' => request()->routeIs('pdks.leaves')])>İzin</a>@endif
    @if($u->hasPermission('pdks_overtime','index'))<a href="{{ route('pdks.overtime') }}" @class(['active' => request()->routeIs('pdks.overtime')])>Fazla Mesai</a>@endif
    @if($u->hasPermission('pdks_reports','index'))<a href="{{ route('pdks.reports') }}" @class(['active' => request()->routeIs('pdks.reports')])>Raporlar</a>@endif
    @if($u->hasPermission('pdks_breaks','index'))<a href="{{ route('pdks.breaks') }}" @class(['active' => request()->routeIs('pdks.breaks')])>Mola Tipleri</a>@endif
    @if($u->hasPermission('pdks_notifications','index'))<a href="{{ route('pdks.notifications') }}" @class(['active' => request()->routeIs('pdks.notifications')])>Bildirim</a>@endif
    @if($u->hasPermission('pdks_settings','index'))<a href="{{ route('pdks.settings') }}" @class(['active' => request()->routeIs('pdks.settings')])>Ayarlar</a>@endif
</div>
