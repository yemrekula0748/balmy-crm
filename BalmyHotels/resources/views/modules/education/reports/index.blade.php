@extends('layouts.default')
@section('title', 'Egitim Raporlari')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Egitim Raporlari</h4>
                <span>Izleme oranlari ve katilim cevaplari</span>
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

    <div class="row g-3 mb-3">
        @foreach([
            ['label' => 'Toplam Atama', 'value' => $stats['total_assignments'], 'color' => '#1e2d3d'],
            ['label' => 'Tamamlanan', 'value' => $stats['completed_assignments'], 'color' => '#2e7d52'],
            ['label' => 'Devam Eden', 'value' => $stats['in_progress_assignments'], 'color' => '#2a5298'],
            ['label' => 'Baslamayan', 'value' => $stats['not_started_assignments'], 'color' => '#8a6d3b'],
            ['label' => 'Ort. Ilerleme', 'value' => '%'.$stats['avg_progress'], 'color' => '#7a5c3d'],
            ['label' => 'Tamamlama', 'value' => '%'.$stats['completion_rate'], 'color' => '#3d7a5e'],
        ] as $card)
            <div class="col-sm-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100" style="background:{{ $card['color'] }};border-radius:8px">
                    <div class="card-body text-white">
                        <div class="fs-4 fw-bold">{{ $card['value'] }}</div>
                        <div class="small opacity-75">{{ $card['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:8px">
                <div class="card-header bg-white border-0"><h5 class="mb-0">Egitim Bazli Ozet</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Egitim</th>
                                    <th class="text-center">Atama</th>
                                    <th class="text-center">Tamamlanan</th>
                                    <th class="text-center">Ort.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courseStats as $row)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $row['course']->title }}</div>
                                            <small class="text-muted">{{ $row['course']->language_label }}</small>
                                        </td>
                                        <td class="text-center">{{ $row['assigned'] }}</td>
                                        <td class="text-center text-success fw-bold">{{ $row['completed'] }}</td>
                                        <td class="text-center">%{{ $row['avg_progress'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Veri yok.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius:8px">
                <div class="card-header bg-white border-0"><h5 class="mb-0">Yuz Yuze Katilim Ozeti</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Duyuru</th>
                                    <th>Tarih</th>
                                    <th class="text-center">Katilacak</th>
                                    <th class="text-center">Katilmayacak</th>
                                    <th class="text-center">Bekliyor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                    <tr>
                                        <td>
                                            <a href="{{ route('education.events.show', $event) }}" class="fw-semibold">{{ $event->title }}</a>
                                            <small class="d-block text-muted">{{ $event->language_label }}</small>
                                        </td>
                                        <td>{{ $event->starts_at->format('d.m.Y H:i') }}</td>
                                        <td class="text-center text-success fw-bold">{{ $event->responses->where('status', 'attending')->count() }}</td>
                                        <td class="text-center text-danger fw-bold">{{ $event->responses->where('status', 'declined')->count() }}</td>
                                        <td class="text-center text-muted fw-bold">{{ $event->responses->where('status', 'pending')->count() }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Yuz yuze egitim verisi yok.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius:8px">
                <div class="card-header bg-white border-0"><h5 class="mb-0">Ogrenen Ilerleme Detayi</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ogrenen</th>
                                    <th>Egitim</th>
                                    <th>Hafta</th>
                                    <th class="text-center">Ilerleme</th>
                                    <th class="text-center">Durum</th>
                                    <th class="text-center">Son Izleme</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $assignment->learner->name ?? '-' }}</div>
                                            <small class="text-muted">{{ $assignment->learner->branch->name ?? '' }}</small>
                                        </td>
                                        <td>{{ $assignment->course->title ?? '-' }}</td>
                                        <td>{{ $assignment->assigned_week_start->format('d.m.Y') }}</td>
                                        <td class="text-center">
                                            <div class="progress" style="height:7px">
                                                <div class="progress-bar" style="width:{{ $assignment->progress_percent }}%"></div>
                                            </div>
                                            <small class="text-muted">%{{ number_format($assignment->progress_percent, 1) }}</small>
                                        </td>
                                        <td class="text-center">{{ $assignment->status_label }}</td>
                                        <td class="text-center">{{ $assignment->last_watched_at ? $assignment->last_watched_at->format('d.m.Y H:i') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Detay bulunamadi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($assignments->hasPages())
                    <div class="card-footer bg-white">{{ $assignments->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
