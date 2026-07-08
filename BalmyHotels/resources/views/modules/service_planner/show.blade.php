@extends('layouts.default')
@section('title', $plan->name)

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-lg-7 p-md-0">
            <div class="welcome-text">
                <h4>{{ $plan->name }}</h4>
                <span>{{ $plan->start_location_name }} cikisli otomatik servis planlamasi</span>
            </div>
        </div>
        <div class="col-lg-5 p-md-0 d-flex justify-content-lg-end align-items-start mt-2 mt-lg-0">
            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                <a href="{{ route('service-planner.template') }}" class="btn btn-outline-secondary btn-sm">Excel Sablonu</a>
                <a href="{{ route('service-planner.edit', $plan) }}" class="btn btn-outline-secondary btn-sm">Duzenle</a>
                @if($stats['assigned_count'] > 0)
                    <a href="{{ route('service-planner.pdf', $plan) }}" class="btn btn-sm" style="background:#5d7fa3;border-color:#5d7fa3;color:#fff;">PDF Indir</a>
                @endif
                <form action="{{ route('service-planner.destroy', $plan) }}" method="POST" onsubmit="return confirm('Bu plan silinsin mi?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                </form>
            </div>
        </div>
    </div>

    @foreach (['success', 'error'] as $messageType)
        @if(session($messageType))
            <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show">
                {{ session($messageType) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-body">
                    <div class="small text-uppercase text-muted mb-2">Servis / Koltuk</div>
                    <div class="h4 mb-1">{{ $stats['vehicle_count'] }} servis</div>
                    <div class="text-muted">{{ $stats['seat_capacity'] }} koltuk kapasitesi</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-body">
                    <div class="small text-uppercase text-muted mb-2">Yuklenen Kisi</div>
                    <div class="h4 mb-1">{{ $stats['stop_count'] }}</div>
                    <div class="text-muted">{{ $stats['assigned_count'] }} kisi servislere yerlesti</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-body">
                    <div class="small text-uppercase text-muted mb-2">Geocode</div>
                    <div class="h4 mb-1">{{ $stats['geocoded_count'] }}</div>
                    <div class="text-muted">
                        {{ $stats['approximate_count'] }} yaklasik, {{ $stats['failed_geocode_count'] }} problemli adres
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-body">
                    <div class="small text-uppercase text-muted mb-2">Tahmini Mesafe</div>
                    <div class="h4 mb-1">{{ number_format($stats['total_distance_km'], 1, ',', '.') }} km</div>
                    <div class="text-muted">{{ $plan->status === 'planned' ? 'Rotalar hazir' : 'Hesaplama bekliyor' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h5 class="mb-3">Plan Ozeti</h5>
                    <div class="mb-2"><span class="text-muted">Sube:</span> {{ $plan->branch?->name ?? 'Genel / Ortak' }}</div>
                    <div class="mb-2"><span class="text-muted">Tarih:</span> {{ $plan->plan_date?->format('d.m.Y') ?? '-' }}</div>
                    <div class="mb-2"><span class="text-muted">Kalkis Noktasi:</span> {{ $plan->start_location_name }}</div>
                    <div class="mb-2"><span class="text-muted">Kalkis Adresi:</span> {{ $plan->start_address }}</div>
                    <div class="mb-2"><span class="text-muted">Durum:</span>
                        @if($plan->status === 'planned')
                            <span class="badge bg-success">Planlandi</span>
                        @else
                            <span class="badge bg-secondary">Taslak</span>
                        @endif
                    </div>
                    @if($plan->planning_notes)
                        <div class="rounded-3 p-3 mt-3" style="background:#fbf6ef;">
                            <div class="small text-uppercase text-muted mb-2">Plan Notu</div>
                            <div>{{ $plan->planning_notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <h5 class="mb-1">Sabit Servisler</h5>
                            <div class="text-muted small">Bu plan icin aktif servis tanimlari kullaniliyor.</div>
                        </div>
                        <a href="{{ route('service-planner.index') }}" class="btn btn-outline-secondary btn-sm">Servis Tanimlarini Ac</a>
                    </div>

                    <div class="row g-3 mb-4">
                        @foreach($plan->vehicles as $vehicle)
                            <div class="col-md-6">
                                <div class="rounded-3 p-3 h-100" style="background:#fafafa;border:1px solid #ececec;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="fw-semibold">{{ $vehicle->name }}</div>
                                        <span class="badge" style="background:{{ $vehicle->color ?: '#c19b77' }};">{{ $vehicle->seat_capacity }} koltuk</span>
                                    </div>
                                    <div class="small text-muted mt-2">Servis sirasi: {{ $vehicle->vehicle_order }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">Yeni Personel Ekle</h5>
                            <div class="text-muted small">Kayit eklenince sistem tum servisleri yeniden hesaplayip en uygun dagilimi kurar.</div>
                        </div>
                    </div>

                    <form action="{{ route('service-planner.stops.store', $plan) }}" method="POST" class="row g-3 mb-4">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" name="passenger_name" class="form-control" placeholder="Ornek: Ayse Yilmaz">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Departman</label>
                            <select name="department_id" class="form-select">
                                <option value="">Seciniz</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Acik Adres</label>
                            <input type="text" name="address" class="form-control" placeholder="Mahalle, sokak, bina no dahil girin">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="phone" class="form-control" placeholder="0555...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ilce / Semt</label>
                            <input type="text" name="district" class="form-control" placeholder="Kemer">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Not</label>
                            <input type="text" name="notes" class="form-control" placeholder="Vardiya veya operasyon notu">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn" style="background:#c19b77;border-color:#c19b77;color:#fff;">
                                Personeli Ekle ve Otomatik Ata
                            </button>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">Excel Yukle</h5>
                            <div class="text-muted small">Ad Soyad, Departman ve Adres kolonlariyla toplu personel ekleyebilirsiniz.</div>
                        </div>
                        <a href="{{ route('service-planner.template') }}" class="btn btn-outline-secondary btn-sm">Sablon Indir</a>
                    </div>

                    <form action="{{ route('service-planner.importStops', $plan) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-8">
                            <input type="file" name="excel_file" accept=".xlsx,.xls" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn w-100" style="background:#c19b77;border-color:#c19b77;color:#fff;">
                                Excel'i Yukle
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">Otomatik Hesaplama</div>
                            <div class="small text-muted">Kapasite, mevcut doluluk ve rota yakinligini dikkate alarak servisleri yeniden dengeler.</div>
                        </div>
                        <form action="{{ route('service-planner.calculate', $plan) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn" style="background:#5d7fa3;border-color:#5d7fa3;color:#fff;"
                                    @disabled($plan->stops->isEmpty())>
                                Guzergahi Hesapla
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Yuklenen Personel Listesi</h5>
                    <span class="badge bg-secondary">{{ $plan->stops->count() }} kisi</span>
                </div>
                <div class="card-body p-0">
                    @if($plan->stops->isEmpty())
                        <div class="text-center py-5 text-muted">Henuz Excel yuklenmedi.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Personel</th>
                                        <th>Departman</th>
                                        <th>Adres</th>
                                        <th>Ilce</th>
                                        <th>Durum</th>
                                        <th>Mesafe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plan->stops as $stop)
                                        <tr>
                                            <td>{{ $stop->row_number }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $stop->passenger_name }}</div>
                                                @if($stop->phone)<div class="small text-muted">{{ $stop->phone }}</div>@endif
                                            </td>
                                            <td>{{ $stop->department_name ?: ($stop->department?->name ?? '-') }}</td>
                                            <td>
                                                {{ $stop->address }}
                                                @if($stop->notes)
                                                    <div class="small text-muted">{{ $stop->notes }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $stop->district ?: '-' }}</td>
                                            <td>
                                                @if($stop->geocode_status === 'success')
                                                    <span class="badge bg-success">Hazir</span>
                                                @elseif($stop->geocode_status === 'approximate')
                                                    <span class="badge bg-warning text-dark">Yaklasik</span>
                                                @elseif($stop->geocode_status === 'failed')
                                                    <span class="badge bg-danger">Sorunlu</span>
                                                @else
                                                    <span class="badge bg-secondary">Bekliyor</span>
                                                @endif
                                                @if($stop->geocode_message)
                                                    <div class="small text-muted mt-1">{{ $stop->geocode_message }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $stop->distance_to_start_km ? number_format($stop->distance_to_start_km, 1, ',', '.') . ' km' : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @foreach($assignmentsByVehicle as $route)
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px;overflow:hidden;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">{{ $route['vehicle']->name }}</h5>
                            <div class="small text-muted">{{ $route['passenger_count'] }}/{{ $route['vehicle']->seat_capacity }} dolu</div>
                        </div>
                        <span class="badge" style="background:{{ $route['vehicle']->color ?: '#c19b77' }};">
                            {{ number_format($route['total_distance_km'], 1, ',', '.') }} km
                        </span>
                    </div>
                    <div class="card-body">
                        @if($route['assignments']->isEmpty())
                            <div class="text-muted">Bu servis icin henuz bir durak atanmadi.</div>
                        @else
                            <div class="small text-muted mb-3">Tahmini surus suresi: {{ $route['estimated_minutes'] }} dakika</div>
                            <div class="d-flex flex-column gap-3">
                                @foreach($route['assignments'] as $assignment)
                                    <div class="rounded-3 p-3" style="background:#fafafa;border:1px solid #ececec;">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="fw-semibold">{{ $assignment->stop_order }}. {{ $assignment->stop?->passenger_name }}</div>
                                                @if($assignment->stop?->department_name || $assignment->stop?->department?->name)
                                                    <div class="small text-muted">{{ $assignment->stop->department_name ?: $assignment->stop?->department?->name }}</div>
                                                @endif
                                                <div class="small text-muted">{{ $assignment->stop?->address }}</div>
                                                @if($assignment->stop?->district)
                                                    <div class="small text-muted">{{ $assignment->stop->district }}</div>
                                                @endif
                                            </div>
                                            <div class="text-end small text-muted">
                                                <div>{{ number_format((float) $assignment->leg_distance_km, 1, ',', '.') }} km</div>
                                                <div>{{ $assignment->travel_minutes }} dk</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
