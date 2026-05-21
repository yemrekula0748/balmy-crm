@php($eduUser = auth()->user())
<div class="card border-0 shadow-sm mb-3" style="border-radius:8px">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2">
            @if($eduUser->hasPermission('education_learning','index'))
                <a href="{{ route('education.learning.index') }}" class="btn btn-sm {{ request()->is('egitim-ve-gelisim/egitimlerim*') || request()->is('egitim-ve-gelisim') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-play-circle me-1"></i>Egitimlerim
                </a>
            @endif
            @if($eduUser->hasPermission('education_courses','index'))
                <a href="{{ route('education.courses.index') }}" class="btn btn-sm {{ request()->is('egitim-ve-gelisim/icerikler*') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-video me-1"></i>Icerikler
                </a>
            @endif
            @if($eduUser->hasPermission('education_assignments','index'))
                <a href="{{ route('education.assignments.index') }}" class="btn btn-sm {{ request()->is('egitim-ve-gelisim/atamalar*') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-users me-1"></i>Toplu Atama
                </a>
            @endif
            @if($eduUser->hasPermission('education_events','index'))
                <a href="{{ route('education.events.index') }}" class="btn btn-sm {{ request()->is('egitim-ve-gelisim/yuz-yuze*') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-calendar-check me-1"></i>Yuz Yuze
                </a>
            @endif
            @if($eduUser->hasPermission('education_reports','index'))
                <a href="{{ route('education.reports.index') }}" class="btn btn-sm {{ request()->is('egitim-ve-gelisim/raporlar*') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-chart-line me-1"></i>Raporlar
                </a>
            @endif
        </div>
    </div>
</div>
