@extends('layouts.default')
@section('title', 'Egitim Siralamasi')

@push('styles')
<style>
    .education-leaderboard .summary-card {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #fff;
    }

    .education-leaderboard .summary-value {
        color: #111827;
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .education-leaderboard .podium-card {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #fff;
        overflow: hidden;
    }

    .education-leaderboard .podium-top {
        min-height: 92px;
        padding: 18px;
        color: #fff;
    }

    .education-leaderboard .podium-top.rank-1 { background: #8a6d1f; }
    .education-leaderboard .podium-top.rank-2 { background: #475569; }
    .education-leaderboard .podium-top.rank-3 { background: #9a4d1e; }

    .education-leaderboard .rank-badge {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        background: rgba(255, 255, 255, .18);
        border: 1px solid rgba(255, 255, 255, .28);
    }

    .education-leaderboard .rank-cell {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        background: #f1f5f9;
        color: #334155;
    }

    .education-leaderboard .rank-cell.rank-1 { background: #fef3c7; color: #92400e; }
    .education-leaderboard .rank-cell.rank-2 { background: #e2e8f0; color: #334155; }
    .education-leaderboard .rank-cell.rank-3 { background: #ffedd5; color: #9a3412; }

    .education-leaderboard .score-pill {
        display: inline-flex;
        min-width: 78px;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 10px;
        background: #102a43;
        color: #fff;
        font-weight: 800;
    }

    .education-leaderboard .mini-stat {
        border-radius: 8px;
        background: #f8fafc;
        padding: 10px;
        min-height: 66px;
    }

    .education-leaderboard .mini-stat strong {
        display: block;
        color: #111827;
        font-size: 1.05rem;
    }

    .education-leaderboard .progress {
        background: #e5e7eb;
    }
</style>
@endpush

@section('content')
<div class="container-fluid education-leaderboard">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Egitim Siralamasi</h4>
                <span>Tamamlanan egitim, quiz ve dogru cevap performansi</span>
            </div>
        </div>
    </div>

    @include('modules.education._tabs')

    <div class="card border-0 shadow-sm mb-3" style="border-radius:8px">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Dil</label>
                    <select name="language" class="form-select form-select-sm">
                        <option value="">Tum Diller</option>
                        @foreach($languages as $code => $label)
                            <option value="{{ $code }}" @selected($language === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Baslangic</label>
                    <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Bitis</label>
                    <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-primary w-100" type="submit"><i class="fas fa-filter me-1"></i>Filtrele</button>
                </div>
            </form>
        </div>
    </div>

    @if($myRank)
        <div class="alert border-0 shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3" style="background:#ecfdf5;color:#064e3b;border-radius:8px">
            <div>
                <strong>Senin siran: #{{ $myRank['rank'] }}</strong>
                <span class="ms-2">{{ number_format($myRank['score']) }} puan</span>
            </div>
            <div class="small">
                {{ $myRank['completed_count'] }} tamamlanan egitim &middot; {{ $myRank['correct_answers'] }} dogru cevap
            </div>
        </div>
    @endif

    <div class="row g-3 mb-3">
        @foreach([
            ['label' => 'Katilimci', 'value' => $summary['learner_count'], 'icon' => 'fa-users', 'color' => '#0f766e'],
            ['label' => 'Atama', 'value' => $summary['assignment_count'], 'icon' => 'fa-layer-group', 'color' => '#2a5298'],
            ['label' => 'Tamamlanan', 'value' => $summary['completed_count'], 'icon' => 'fa-check-circle', 'color' => '#2e7d52'],
            ['label' => 'Onaylanan', 'value' => $summary['approved_count'], 'icon' => 'fa-award', 'color' => '#8a6d1f'],
            ['label' => 'Dogru Cevap', 'value' => $summary['correct_answers'], 'icon' => 'fa-bullseye', 'color' => '#9a3412'],
            ['label' => 'Ort. Puan', 'value' => $summary['avg_score'], 'icon' => 'fa-chart-line', 'color' => '#1e2d3d'],
        ] as $card)
            <div class="col-sm-6 col-xl-2">
                <div class="summary-card h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="summary-value">{{ $card['value'] }}</div>
                            <div class="small text-muted">{{ $card['label'] }}</div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center text-white" style="width:38px;height:38px;border-radius:8px;background:{{ $card['color'] }}">
                            <i class="fas {{ $card['icon'] }}"></i>
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($topLeaders->isNotEmpty())
        <div class="row g-3 mb-3">
            @foreach($topLeaders as $leader)
                <div class="col-lg-4">
                    <div class="podium-card h-100 shadow-sm">
                        <div class="podium-top rank-{{ $leader['rank'] }}">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div>
                                    <div class="small opacity-75">#{{ $leader['rank'] }}</div>
                                    <div class="fs-5 fw-bold">{{ $leader['learner_name'] }}</div>
                                    <div class="small opacity-75">{{ $leader['branch_name'] }} &middot; {{ $leader['department_name'] }}</div>
                                </div>
                                <span class="rank-badge"><i class="fas fa-trophy"></i></span>
                            </div>
                        </div>
                        <div class="p-3">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-muted small">Puan</span>
                                <span class="score-pill">{{ number_format($leader['score']) }}</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="mini-stat">
                                        <strong>{{ $leader['completed_count'] }}</strong>
                                        <span class="small text-muted">Tamam</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mini-stat">
                                        <strong>{{ $leader['correct_answers'] }}</strong>
                                        <span class="small text-muted">Dogru</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mini-stat">
                                        <strong>{{ $leader['quiz_success_rate'] !== null ? '%'.number_format($leader['quiz_success_rate'], 1) : '-' }}</strong>
                                        <span class="small text-muted">Quiz</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius:8px">
        <div class="card-header bg-white border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="mb-0">Siralama Tablosu</h5>
            <span class="badge bg-light text-dark">Kapsam: {{ $scopeLabel }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Sira</th>
                            <th>Ogrenen</th>
                            <th class="text-center">Puan</th>
                            <th class="text-center">Tamamlanan</th>
                            <th class="text-center">Onaylanan</th>
                            <th class="text-center">Dogru</th>
                            <th class="text-center">Quiz Basari</th>
                            <th class="text-center">Ort. Ilerleme</th>
                            <th class="text-center">Son Aktivite</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaderboardRows as $row)
                            <tr>
                                <td class="text-center">
                                    <span class="rank-cell rank-{{ $row['rank'] <= 3 ? $row['rank'] : 'other' }}">#{{ $row['rank'] }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $row['learner_name'] }}</div>
                                    <small class="text-muted">{{ $row['branch_name'] }} &middot; {{ $row['department_name'] }}</small>
                                </td>
                                <td class="text-center"><span class="score-pill">{{ number_format($row['score']) }}</span></td>
                                <td class="text-center">
                                    <div class="fw-bold text-success">{{ $row['completed_count'] }}</div>
                                    <small class="text-muted">/{{ $row['assignment_count'] }}</small>
                                </td>
                                <td class="text-center fw-bold">{{ $row['approved_count'] }}</td>
                                <td class="text-center">
                                    <div class="fw-bold">{{ $row['correct_answers'] }}</div>
                                    <small class="text-muted">/{{ $row['total_questions'] }}</small>
                                </td>
                                <td class="text-center">
                                    {{ $row['quiz_success_rate'] !== null ? '%'.number_format($row['quiz_success_rate'], 1) : '-' }}
                                </td>
                                <td class="text-center" style="min-width:130px">
                                    <div class="progress" style="height:7px">
                                        <div class="progress-bar bg-success" style="width:{{ min(100, max(0, $row['avg_progress'])) }}%"></div>
                                    </div>
                                    <small class="text-muted">%{{ number_format($row['avg_progress'], 1) }}</small>
                                </td>
                                <td class="text-center">
                                    {{ $row['last_activity_at'] ? $row['last_activity_at']->format('d.m.Y H:i') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Siralama icin veri bulunamadi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
