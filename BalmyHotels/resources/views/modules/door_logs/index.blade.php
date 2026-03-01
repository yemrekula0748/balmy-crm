@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Kapı Giriş/Çıkış</h4>
                <span>Personel giriş çıkış kayıtları</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Kapı Giriş/Çıkış</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- İSTATİSTİK KARTLARI --}}
    <div class="row mb-4">
        <div class="col-xl-4 col-sm-6">
            <div class="card" style="background:linear-gradient(to right, #037bb3, #045058);">
                <div class="card-body p-4">
                    <div class="media align-items-center">
                        <div class="media-body me-3">
                            <p class="fs-14 text-white mb-1 opacity-75">Toplam Giriş</p>
                            <h2 class="text-white mb-0">{{ $totalGiris }}</h2>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none"
                             stroke="rgba(255,255,255,0.7)" stroke-width="1.5"
                             viewBox="0 0 24 24">
                            <path d="M15 3H19a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H15"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card" style="background:linear-gradient(to right, #f7971e, #ffd200);">
                <div class="card-body p-4">
                    <div class="media align-items-center">
                        <div class="media-body me-3">
                            <p class="fs-14 text-white mb-1 opacity-75">Toplam Çıkış</p>
                            <h2 class="text-white mb-0">{{ $totalCikis }}</h2>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none"
                             stroke="rgba(255,255,255,0.7)" stroke-width="1.5"
                             viewBox="0 0 24 24">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2H9"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card" style="background:linear-gradient(to right, #c19b77, #8b6e52);">
                <div class="card-body p-4">
                    <div class="media align-items-center">
                        <div class="media-body me-3">
                            <p class="fs-14 text-white mb-1 opacity-75">Tekil Personel</p>
                            <h2 class="text-white mb-0">{{ $uniquePersonel }}</h2>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none"
                             stroke="rgba(255,255,255,0.7)" stroke-width="1.5"
                             viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- HIZ KAYIT PANELİ --}}
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                     stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Hızlı Kayıt — Anlık
            </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('door-logs.quick') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Personel</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Personel seçin...</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}">
                                    {{ $manager->name }}
                                    @if($manager->department)— {{ $manager->department->name }}@endif
                                    ({{ $manager->branch->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">İşlem</label>
                        <select name="type" class="form-select" required>
                            <option value="giris">🟢 Giriş</option>
                            <option value="cikis">🔴 Çıkış</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            Şimdi Kaydet
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- KAYIT LİSTESİ --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Kayıtlar</h4>
            <a href="{{ route('door-logs.create') }}" class="btn btn-secondary btn-sm">
                + Manuel Kayıt
            </a>
        </div>
        <div class="card-body">

            {{-- FİLTRELER --}}
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-2">
                    <label class="form-label small">Başlangıç</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Bitiş</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Personel</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Tüm Personel</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(request('user_id') == $manager->id)>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Şube</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tip</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        <option value="giris" @selected(request('type') === 'giris')>Giriş</option>
                        <option value="cikis" @selected(request('type') === 'cikis')>Çıkış</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrele</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Personel</th>
                            <th>Departman</th>
                            <th>Şube</th>
                            <th>Tip</th>
                            <th>Tarih & Saat</th>
                            <th>Not</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-muted small">{{ $log->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $log->user->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $log->user->title ?? '' }}</small>
                                </td>
                                <td>
                                    @if($log->user?->department)
                                        <span class="badge" style="background-color: {{ $log->user->department->color }};">
                                            {{ $log->user->department->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $log->branch->name ?? '-' }}</td>
                                <td>
                                    @if($log->type === 'giris')
                                        <span class="badge badge-success light">
                                            &#x2B24; Giriş
                                        </span>
                                    @else
                                        <span class="badge badge-danger light">
                                            &#x2B24; Çıkış
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $log->logged_at->format('d.m.Y') }}</div>
                                    <small class="text-muted fw-bold">{{ $log->logged_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->notes ?? '-' }}</small>
                                </td>
                                <td class="text-end">
                                    <button type="button"
                                            class="btn btn-danger btn-xs btn-sil"
                                            data-id="{{ $log->id }}"
                                            data-name="{{ $log->user->name ?? '' }}">
                                        Sil
                                    </button>
                                    <form id="form-sil-{{ $log->id }}"
                                          action="{{ route('door-logs.destroy', $log) }}"
                                          method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    Bu tarih aralığında kayıt bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-sil').forEach(btn => {
    btn.addEventListener('click', function () {
        const id   = this.dataset.id;
        const name = this.dataset.name;
        Swal.fire({
            title: 'Emin misiniz?',
            text: `${name} adlı kişinin bu kaydı silinecek!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('form-sil-' + id).submit();
            }
        });
    });
});
</script>
@endpush
