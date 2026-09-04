@extends('layouts.default')
@section('title', 'Eğitimlerim')

@php
    $localizedLanguageLabels = ['tr' => 'Türkçe', 'en' => 'İngilizce', 'ru' => 'Rusça'];
    $hasFilters = $status || $search !== '';
@endphp

@push('styles')
<style>
    .learning-page .learning-hero {
        background: linear-gradient(135deg, #f4f8ff 0%, #ffffff 62%, #f8fbff 100%);
        border: 1px solid #dfeaf8;
        border-radius: 14px;
        overflow: hidden;
        position: relative;
    }
    .learning-page .learning-hero::after {
        background: rgba(47, 116, 208, .07);
        border-radius: 50%;
        content: '';
        height: 190px;
        position: absolute;
        right: -55px;
        top: -85px;
        width: 190px;
    }
    .learning-page .hero-icon {
        align-items: center;
        background: #e4effd;
        border-radius: 12px;
        color: #2f74d0;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 1.25rem;
        height: 48px;
        justify-content: center;
        width: 48px;
    }
    .learning-page .average-progress {
        min-width: 170px;
        position: relative;
        z-index: 1;
    }
    .learning-page .summary-card {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(35, 48, 64, .06);
        height: 100%;
    }
    .learning-page .summary-icon {
        align-items: center;
        border-radius: 10px;
        display: inline-flex;
        height: 40px;
        justify-content: center;
        width: 40px;
    }
    .learning-page .filter-panel {
        background: #f8fafc;
        border: 1px solid #e8edf4;
        border-radius: 10px;
        padding: 12px;
    }
    .learning-page .course-card {
        background: #fff;
        border: 1px solid #e5ebf3;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(35, 48, 64, .05);
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
        padding: 18px;
        position: relative;
        transition: transform .16s ease, box-shadow .16s ease;
    }
    .learning-page .course-card::before {
        background: #98a2ad;
        content: '';
        height: 4px;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
    }
    .learning-page .course-card.status-in-progress::before {
        background: #2f74d0;
    }
    .learning-page .course-card.status-completed::before {
        background: #2c9b69;
    }
    .learning-page .course-card:hover {
        box-shadow: 0 10px 24px rgba(35, 48, 64, .1);
        transform: translateY(-2px);
    }
    .learning-page .course-description {
        color: #75808d;
        font-size: .84rem;
        line-height: 1.55;
        min-height: 42px;
    }
    .learning-page .course-meta {
        color: #6f7b88;
        font-size: .78rem;
    }
    .learning-page .course-meta i {
        color: #8da0b5;
        text-align: center;
        width: 17px;
    }
    .learning-page .quiz-state {
        border-radius: 8px;
        font-size: .78rem;
        padding: 8px 10px;
    }
    .learning-page .empty-learning-state {
        padding: 58px 20px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid learning-page">
    <div class="row page-titles mx-0 align-items-center">
        <div class="col-md-8 p-md-0">
            <div class="welcome-text">
                <h4>Eğitimlerim</h4>
                <span>Atanan eğitimlerinizi takip edin ve kaldığınız yerden devam edin.</span>
            </div>
        </div>
        <div class="col-md-4 p-md-0 mt-2 mt-md-0 text-md-end">
            <span class="badge bg-light text-dark border px-3 py-2">
                <i class="fas fa-layer-group me-1 text-primary"></i>{{ $assignmentSummary['total'] }} eğitim
            </span>
        </div>
    </div>

    <div class="learning-hero p-3 p-md-4 mb-3 shadow-sm">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 position-relative" style="z-index:1">
            <div class="d-flex align-items-start gap-3">
                <span class="hero-icon"><i class="fas fa-graduation-cap"></i></span>
                <div>
                    <h5 class="mb-1">Öğrenme yolculuğun</h5>
                    @if($weeklyNotificationCount > 0)
                        <div class="text-dark">Bu hafta tamamlaman gereken <strong>{{ $weeklyNotificationCount }}</strong> eğitim adımı var.</div>
                        <small class="text-muted">Öncelikli eğitimler listenin başında gösteriliyor.</small>
                    @elseif($assignmentSummary['total'] > 0)
                        <div class="text-dark">Bu haftaki eğitim adımların tamamlandı.</div>
                        <small class="text-muted">Devam eden diğer eğitimlerini aşağıdan sürdürebilirsin.</small>
                    @else
                        <div class="text-dark">Henüz atanmış aktif bir eğitimin bulunmuyor.</div>
                        <small class="text-muted">Yeni bir eğitim atandığında burada görebileceksin.</small>
                    @endif
                </div>
            </div>
            <div class="average-progress">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted">Genel ilerleme</span>
                    <strong>%{{ number_format($assignmentSummary['average_progress'], 1) }}</strong>
                </div>
                <div class="progress" style="height:9px">
                    <div class="progress-bar bg-primary" style="width:{{ min(100, $assignmentSummary['average_progress']) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="card summary-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="summary-icon bg-light text-secondary"><i class="fas fa-book-open"></i></span>
                    <div><div class="h4 mb-0">{{ $assignmentSummary['total'] }}</div><small class="text-muted">Toplam eğitim</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card summary-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="summary-icon bg-secondary-subtle text-secondary"><i class="fas fa-hourglass-start"></i></span>
                    <div><div class="h4 mb-0">{{ $assignmentSummary['not_started'] }}</div><small class="text-muted">Başlanmadı</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card summary-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="summary-icon bg-info-subtle text-info"><i class="fas fa-play"></i></span>
                    <div><div class="h4 mb-0">{{ $assignmentSummary['in_progress'] }}</div><small class="text-muted">Devam ediyor</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card summary-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="summary-icon bg-success-subtle text-success"><i class="fas fa-check"></i></span>
                    <div><div class="h4 mb-0">{{ $assignmentSummary['completed'] }}</div><small class="text-muted">Tamamlandı</small></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:12px">
        <div class="card-header bg-white border-0">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                <div>
                    <h5 class="mb-1">Atanan eğitimler</h5>
                    <small class="text-muted">{{ $assignments->total() }} sonuç gösteriliyor.</small>
                </div>
            </div>
            <form method="GET" class="filter-panel row g-2 align-items-end">
                <div class="col-md-7">
                    <label for="learningSearch" class="form-label small fw-semibold mb-1">Eğitim ara</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="search" name="search" id="learningSearch" class="form-control" value="{{ $search }}" placeholder="Eğitim adına göre ara..." autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="learningStatus" class="form-label small fw-semibold mb-1">Durum</label>
                    <select name="status" id="learningStatus" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tüm durumlar</option>
                        <option value="not_started" @selected($status === 'not_started')>Başlanmadı</option>
                        <option value="in_progress" @selected($status === 'in_progress')>Devam ediyor</option>
                        <option value="completed" @selected($status === 'completed')>Tamamlandı</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-sm btn-primary">Filtrele</button>
                </div>
                @if($hasFilters)
                    <div class="col-12 text-end">
                        <a href="{{ route('education.learning.index') }}" class="small text-decoration-none"><i class="fas fa-times me-1"></i>Filtreleri temizle</a>
                    </div>
                @endif
            </form>
        </div>
        <div class="card-body pt-0">
            <div class="row g-3">
                @forelse($assignments as $assignment)
                    @php
                        $statusClass = match($assignment->status) {
                            'completed' => 'success',
                            'in_progress' => 'info',
                            default => 'secondary',
                        };
                        $statusLabel = match($assignment->status) {
                            'completed' => 'Tamamlandı',
                            'in_progress' => 'Devam ediyor',
                            default => 'Başlanmadı',
                        };
                        $actionLabel = match($assignment->status) {
                            'completed' => 'Eğitimi tekrar aç',
                            'in_progress' => 'Kaldığın yerden devam et',
                            default => 'Eğitime başla',
                        };
                        $isQuizPending = $assignment->course && $assignment->course->has_quiz
                            && $assignment->video_completed
                            && ! $assignment->quiz_passed;
                        $isOverdue = $assignment->due_at
                            && $assignment->due_at->isPast()
                            && $assignment->status !== 'completed';
                    @endphp
                    <div class="col-md-6 col-xl-4">
                        <article class="course-card status-{{ str_replace('_', '-', $assignment->status) }}">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <span class="badge bg-light text-dark border">{{ $localizedLanguageLabels[$assignment->language] ?? strtoupper($assignment->language) }}</span>
                                <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>

                            <h6 class="fw-semibold mb-2">{{ $assignment->course->title ?? '-' }}</h6>
                            <p class="course-description mb-3">{{ \Illuminate\Support\Str::limit($assignment->course->description ?? 'Bu eğitim için açıklama eklenmemiş.', 105) }}</p>

                            <div class="course-meta mb-3">
                                @if($assignment->course?->trainer)
                                    <div class="mb-1"><i class="fas fa-chalkboard-teacher me-1"></i>{{ $assignment->course->trainer->name }}</div>
                                @endif
                                <div class="mb-1"><i class="fas fa-calendar-week me-1"></i>{{ $assignment->assigned_week_start->format('d.m.Y') }} haftası</div>
                                @if($assignment->due_at)
                                    <div class="{{ $isOverdue ? 'text-danger fw-semibold' : '' }}">
                                        <i class="fas fa-clock me-1"></i>Son tarih: {{ $assignment->due_at->format('d.m.Y H:i') }}{{ $isOverdue ? ' · Süresi geçti' : '' }}
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">İlerleme</small>
                                    <strong class="small">%{{ number_format($assignment->progress_percent, 1) }}</strong>
                                </div>
                                <div class="progress mb-3" style="height:8px">
                                    <div class="progress-bar {{ $assignment->status === 'completed' ? 'bg-success' : '' }}" style="width:{{ min(100, $assignment->progress_percent) }}%"></div>
                                </div>

                                @if($assignment->course && $assignment->course->has_quiz)
                                    @if($assignment->quiz_passed)
                                        <div class="quiz-state bg-success-subtle text-success mb-3"><i class="fas fa-check-circle me-1"></i>Quiz başarıyla tamamlandı.</div>
                                    @elseif($isQuizPending)
                                        <div class="quiz-state bg-warning-subtle text-warning-emphasis mb-3"><i class="fas fa-exclamation-circle me-1"></i>Video tamamlandı, quiz seni bekliyor.</div>
                                    @else
                                        <div class="quiz-state bg-light text-muted mb-3"><i class="fas fa-lock me-1"></i>Quiz, video tamamlandıktan sonra açılır.</div>
                                    @endif
                                @endif

                                @if($isQuizPending)
                                    <a href="{{ route('education.learning.quiz', $assignment) }}" class="btn btn-sm btn-primary w-100">
                                        <i class="fas fa-question-circle me-1"></i>Quize devam et
                                    </a>
                                    <a href="{{ route('education.learning.show', $assignment) }}" class="btn btn-sm btn-outline-secondary w-100 mt-2">
                                        <i class="fas fa-redo me-1"></i>Videoyu tekrar aç
                                    </a>
                                @else
                                    <a href="{{ route('education.learning.show', $assignment) }}" class="btn btn-sm btn-primary w-100">
                                        <i class="fas {{ $assignment->status === 'not_started' ? 'fa-play' : 'fa-arrow-right' }} me-1"></i>{{ $actionLabel }}
                                    </a>
                                @endif
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-learning-state text-muted">
                            <i class="fas {{ $hasFilters ? 'fa-search' : 'fa-graduation-cap' }} fa-3x mb-3 d-block"></i>
                            <h6 class="text-dark">{{ $hasFilters ? 'Aradığın eğitim bulunamadı' : 'Henüz atanmış eğitim yok' }}</h6>
                            <p class="mb-0">{{ $hasFilters ? 'Arama veya durum filtresini değiştirerek tekrar deneyebilirsin.' : 'Yeni bir eğitim atandığında burada görüntülenecek.' }}</p>
                            @if($hasFilters)
                                <a href="{{ route('education.learning.index') }}" class="btn btn-sm btn-outline-primary mt-3">Tüm eğitimleri göster</a>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
        @if($assignments->hasPages())
            <div class="card-footer bg-white">{{ $assignments->links() }}</div>
        @endif
    </div>
</div>
@endsection
