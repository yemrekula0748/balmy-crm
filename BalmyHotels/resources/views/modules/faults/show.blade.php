@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Arıza Detayı</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">#{{ $fault->id }}</li>
            </ol>
        </div>
    </div>

    {{-- SESSION MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- HEADER CARD --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-start gap-3">
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-{{ \App\Models\Fault::PRIORITY_COLORS[$fault->priority] }} fs-6">
                            ▲ {{ \App\Models\Fault::PRIORITIES[$fault->priority] }}
                        </span>
                        <span class="badge bg-{{ \App\Models\Fault::STATUS_COLORS[$fault->status] }} fs-6">
                            {{ \App\Models\Fault::STATUSES[$fault->status] }}
                        </span>
                        @if($fault->location)
                            <span class="badge bg-light text-dark border fs-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                    <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                                </svg>
                                {{ $fault->location }}
                            </span>
                        @endif
                    </div>
                    <h2 class="mb-2">{{ $fault->title }}</h2>
                    <div class="d-flex flex-wrap gap-3 text-muted small">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4"/>
                            </svg>
                            Bildiren: <strong>{{ $fault->reporter->name ?? '-' }}</strong>
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5zm8 0A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5zm-8 8A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5zm8 0A1.5 1.5 0 0 1 10.5 9h3A1.5 1.5 0 0 1 15 10.5v3A1.5 1.5 0 0 1 13.5 15h-3A1.5 1.5 0 0 1 9 13.5z"/>
                            </svg>
                            Departman: <strong>{{ $fault->department->name ?? '-' }}</strong>
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                <path d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022"/>
                            </svg>
                            Şube: <strong>{{ $fault->branch->name ?? '-' }}</strong>
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                            </svg>
                            Kayıt: {{ $fault->created_at->format('d.m.Y H:i') }}
                        </span>
                        @if($fault->resolved_at)
                            <span class="text-success">
                                ✓ Çözüm: {{ \Carbon\Carbon::parse($fault->resolved_at)->format('d.m.Y H:i') }}
                                ({{ round($fault->resolutionTimeHours(), 1) }} sa.)
                            </span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('faults.index') }}" class="btn btn-sm btn-outline-secondary">← Listeye Dön</a>
                    @if(!in_array($fault->status, ['resolved','closed']))
                        <form action="{{ route('faults.destroy', $fault) }}" method="POST" id="deleteForm">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger" id="deleteBtn">Sil</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        {{-- LEFT: Detay + Atama --}}
        <div class="col-md-7">

            {{-- Açıklama --}}
            <div class="card mb-4">
                <div class="card-header"><h5 class="card-title mb-0">Arıza Açıklaması</h5></div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $fault->description }}</p>
                </div>
            </div>

            {{-- Arıza Fotoğrafı --}}
            @if($fault->image_path)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z"/>
                            </svg>
                            Arıza Fotoğrafı
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <a href="{{ asset('uploads/'.$fault->image_path) }}" target="_blank">
                            <img src="{{ asset('uploads/'.$fault->image_path) }}"
                                 class="img-fluid rounded"
                                 style="max-height:400px;object-fit:contain;"
                                 alt="Arıza fotoğrafı">
                        </a>
                        <div class="mt-2">
                            <a href="{{ asset('uploads/'.$fault->image_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                Tam boyutta aç
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Atama Paneli --}}
            <div class="card mb-4">
                <div class="card-header"><h5 class="card-title mb-0">Atama</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        @if($fault->assignedTo)
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                                     style="width:38px;height:38px;font-size:15px;">
                                    {{ strtoupper(substr($fault->assignedTo->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $fault->assignedTo->name }}</div>
                                    <div class="text-muted small">{{ $fault->assignedTo->department?->name ?? '' }}</div>
                                </div>
                            </div>
                        @else
                            <p class="text-muted mb-0">Henüz kimseye atanmadı.</p>
                        @endif
                    </div>
                    @if(!in_array($fault->status, ['resolved','closed']))
                        <form action="{{ route('faults.assign', $fault) }}" method="POST" class="d-flex gap-2">
                            @csrf
                            <select name="assigned_to" class="form-select form-select-sm">
                                <option value="">-- Atanacak kişi seçin --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected($fault->assigned_to == $user->id)>
                                        {{ $user->name }} ({{ $user->department?->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary" style="white-space:nowrap">Ata</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Zaman Çizelgesi --}}
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Güncelleme Geçmişi</h5></div>
                <div class="card-body p-0">
                    @forelse($fault->updates as $update)
                        <div class="d-flex gap-3 p-3 border-bottom">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                 style="width:36px;height:36px;background:{{ ($update->status_to && $update->status_to !== $update->status_from) ? '#c19b77' : '#6c757d' }};font-size:13px;">
                                {{ strtoupper(substr($update->user?->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <strong class="small">{{ $update->user?->name ?? 'Sistem' }}</strong>
                                    <span class="text-muted" style="font-size:11px;">{{ $update->created_at->format('d.m.Y H:i') }}</span>
                                </div>
                                @if($update->status_from && $update->status_to && $update->status_from !== $update->status_to)
                                    <div class="mb-1">
                                        <span class="badge bg-{{ \App\Models\Fault::STATUS_COLORS[$update->status_from] ?? 'secondary' }} me-1">
                                            {{ \App\Models\Fault::STATUSES[$update->status_from] ?? $update->status_from }}
                                        </span>
                                        → 
                                        <span class="badge bg-{{ \App\Models\Fault::STATUS_COLORS[$update->status_to] ?? 'secondary' }}">
                                            {{ \App\Models\Fault::STATUSES[$update->status_to] ?? $update->status_to }}
                                        </span>
                                    </div>
                                @endif
                                <p class="mb-0 text-muted small">{{ $update->note }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">Henüz güncelleme kaydı yok.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT: Durum Güncelle + Yorum --}}
        <div class="col-md-5">

            {{-- Durum Güncelle --}}
            @if(!in_array($fault->status, ['resolved','closed']))
                <div class="card mb-4">
                    <div class="card-header bg-warning bg-opacity-10 border-warning">
                        <h5 class="card-title mb-0 text-warning-emphasis">Durum Güncelle</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('faults.updateStatus', $fault) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Yeni Durum</label>
                                <div class="d-flex flex-column gap-2">
                                    @foreach(\App\Models\Fault::STATUSES as $val => $label)
                                        @if($val !== $fault->status && $val !== 'resolved')
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status"
                                                       id="status_{{ $val }}" value="{{ $val }}"
                                                       @checked(old('status') === $val)>
                                                <label class="form-check-label" for="status_{{ $val }}">
                                                    <span class="badge bg-{{ \App\Models\Fault::STATUS_COLORS[$val] }}">{{ $label }}</span>
                                                </label>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Açıklama / Not <span class="text-danger">*</span></label>
                                <textarea name="note" rows="3" class="form-control @error('note') is-invalid @enderror"
                                          placeholder="Durum değişikliği hakkında açıklama...">{{ old('note') }}</textarea>
                                @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-warning w-100">Durumu Güncelle</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card mb-4 border-success">
                    <div class="card-body text-center p-4">
                        <div class="display-4 mb-2">{{ $fault->status === 'resolved' ? '✓' : '⊠' }}</div>
                        <h5 class="text-success mb-1">{{ \App\Models\Fault::STATUSES[$fault->status] }}</h5>
                        @if($fault->resolved_at)
                            <p class="text-muted small mb-0">
                                Çözüm süresi: {{ round($fault->resolutionTimeHours(), 1) }} saat
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Yorum Ekle --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Yorum / Not Ekle</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('faults.addComment', $fault) }}" method="POST">
                        @csrf
                        <textarea name="note" rows="3" class="form-control mb-3"
                                  placeholder="Arıza hakkında notunuzu yazın..."></textarea>
                        <button type="submit" class="btn btn-outline-primary w-100">Yorum Ekle</button>
                    </form>
                </div>
            </div>

            {{-- Özet Bilgiler --}}
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Özet Bilgiler</h5></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width:40%">Arıza No</td>
                                <td><strong>#{{ $fault->id }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Öncelik</td>
                                <td><span class="badge bg-{{ \App\Models\Fault::PRIORITY_COLORS[$fault->priority] }}">{{ \App\Models\Fault::PRIORITIES[$fault->priority] }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Durum</td>
                                <td><span class="badge bg-{{ \App\Models\Fault::STATUS_COLORS[$fault->status] }}">{{ \App\Models\Fault::STATUSES[$fault->status] }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Şube</td>
                                <td>{{ $fault->branch->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Departman</td>
                                <td>{{ $fault->department->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Konum</td>
                                <td>{{ $fault->location ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Bildiren</td>
                                <td>{{ $fault->reporter->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Atanan</td>
                                <td>{{ $fault->assignedTo->name ?? 'Atanmadı' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kayıt Tarihi</td>
                                <td>{{ $fault->created_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            @if($fault->resolved_at)
                                <tr>
                                    <td class="text-muted">Çözüm Tarihi</td>
                                    <td>{{ \Carbon\Carbon::parse($fault->resolved_at)->format('d.m.Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Çözüm Süresi</td>
                                    <td class="text-success fw-semibold">{{ round($fault->resolutionTimeHours(), 1) }} saat</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Güncelleme</td>
                                <td>{{ $fault->updates->count() }} adet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.getElementById('deleteBtn')?.addEventListener('click', function () {
    Swal.fire({
        title: 'Arızayı sil?',
        text: 'Bu işlem geri alınamaz.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'İptal',
        confirmButtonText: 'Evet, sil!'
    }).then(result => {
        if (result.isConfirmed) document.getElementById('deleteForm').submit();
    });
});
</script>
@endpush
@endsection
