@extends('layouts.default')
@section('title', 'Servis Planlayici')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Servis Planlayici</h4>
                <span>Sabit servisler, personel kayitlari ve otomatik atama yonetimi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 d-flex justify-content-sm-end mt-2 mt-sm-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Servis Planlayici</li>
            </ol>
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

    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Ara</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                   placeholder="Plan adi veya kalkis noktasi">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sube</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Tum subeler</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Durum</label>
                            <select name="status" class="form-select">
                                <option value="">Tum durumlar</option>
                                <option value="draft" @selected(request('status') === 'draft')>Taslak</option>
                                <option value="planned" @selected(request('status') === 'planned')>Planlandi</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn w-100" style="background:#c19b77;border-color:#c19b77;color:#fff;">Filtrele</button>
                            <a href="{{ route('service-planner.index') }}" class="btn btn-outline-secondary w-100">Temizle</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Sabit Servis Tanimlari</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('service-planner.services.store') }}" class="row g-3 mb-4">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Sube</label>
                            <select name="service_branch_id" class="form-select">
                                <option value="">Genel / Ortak</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Servis Adi</label>
                            <input type="text" name="service_name" class="form-control" placeholder="Ornek: Servis 1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Koltuk</label>
                            <input type="number" name="service_capacity" min="1" max="100" class="form-control" placeholder="16">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sira</label>
                            <input type="number" name="service_sort_order" min="1" max="999" class="form-control" value="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Renk</label>
                            <input type="color" name="service_color" class="form-control form-control-color w-100" value="#c19b77">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn" style="background:#c19b77;border-color:#c19b77;color:#fff;">
                                Sabit Servis Ekle
                            </button>
                        </div>
                    </form>

                    <div class="d-flex flex-column gap-3">
                        @forelse($serviceDefinitions as $serviceDefinition)
                            <form method="POST" action="{{ route('service-planner.services.update', $serviceDefinition) }}" class="rounded-3 p-3" style="background:#fbf8f3;border:1px solid #eadcc9;">
                                @csrf
                                @method('PUT')
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label small">Servis</label>
                                        <input type="text" name="service_name" class="form-control form-control-sm" value="{{ $serviceDefinition->name }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Koltuk</label>
                                        <input type="number" name="service_capacity" class="form-control form-control-sm" value="{{ $serviceDefinition->seat_capacity }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Sira</label>
                                        <input type="number" name="service_sort_order" class="form-control form-control-sm" value="{{ $serviceDefinition->sort_order }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Renk</label>
                                        <input type="color" name="service_color" class="form-control form-control-color w-100" value="{{ $serviceDefinition->color ?: '#c19b77' }}">
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="service_is_active" value="1" @checked($serviceDefinition->is_active)>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="small text-muted">{{ $serviceDefinition->branch?->name ?? 'Genel / Ortak' }}</div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Kaydet</button>
                                        <button type="submit" form="delete-service-{{ $serviceDefinition->id }}" class="btn btn-outline-danger btn-sm">Sil</button>
                                    </div>
                                </div>
                            </form>
                            <form id="delete-service-{{ $serviceDefinition->id }}" method="POST" action="{{ route('service-planner.services.destroy', $serviceDefinition) }}" onsubmit="return confirm('Bu servis tanimi silinsin mi?')">
                                @csrf
                                @method('DELETE')
                            </form>
                        @empty
                            <div class="text-muted">Henuz sabit servis tanimi yok.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card border-0 shadow-sm" style="border-radius:16px;background:linear-gradient(135deg,#f8f2ea 0%,#f4f8fb 100%);">
                <div class="card-body p-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase small fw-bold" style="letter-spacing:.08em;color:#8f6d4f;">Yeni Akis</div>
                        <h4 class="mb-1">Plan olustur, personel ekle, sistem en uygun servisi otomatik secsin</h4>
                        <div class="text-muted">Excel import devam ediyor ama artik formdan tek tek personel ekleyip aninda otomatik atama da yapabilirsiniz.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('service-planner.template') }}" class="btn btn-outline-secondary">Excel Sablonu</a>
                        <a href="{{ route('service-planner.create') }}" class="btn" style="background:#c19b77;border-color:#c19b77;color:#fff;">
                            <i class="fas fa-plus me-1"></i> Yeni Plan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Plan Listesi <span class="badge bg-secondary ms-2">{{ $plans->total() }}</span></h5>
                </div>
                <div class="card-body p-0">
                    @if($plans->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-route fa-3x mb-3"></i>
                            <div>Henuz servis plani olusturulmamis.</div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Plan</th>
                                        <th>Tarih</th>
                                        <th>Kalkis</th>
                                        <th>Servis</th>
                                        <th>Kisi</th>
                                        <th>Durum</th>
                                        <th class="text-end">Islem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plans as $plan)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $plan->name }}</div>
                                                <div class="small text-muted">{{ $plan->branch?->name ?? 'Genel / Ortak' }}</div>
                                            </td>
                                            <td>{{ $plan->plan_date?->format('d.m.Y') ?? '-' }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $plan->start_location_name }}</div>
                                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($plan->start_address, 55) }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $plan->vehicles->count() }} servis</div>
                                                <div class="small text-muted">{{ $plan->vehicles->sum('seat_capacity') }} koltuk</div>
                                            </td>
                                            <td>{{ $plan->stops->count() }}</td>
                                            <td>
                                                @if($plan->status === 'planned')
                                                    <span class="badge bg-success">Planlandi</span>
                                                @else
                                                    <span class="badge bg-secondary">Taslak</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('service-planner.show', $plan) }}" class="btn btn-sm"
                                                   style="background:#fbf6ef;color:#8f6d4f;border:1px solid #eadcc9;">
                                                    Ac
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2">
                            {{ $plans->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
