@extends('layouts.default')

@section('content')
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-shield-alt me-2 text-primary"></i>Giriş Logları</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="#">Bilgi İşlem</a></li>
                <li class="breadcrumb-item active">Giriş Logları</li>
            </ol>
        </div>
    </div>

    {{-- İstatistik Kartları --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:46px;height:46px;background:#e0f2fe">
                        <i class="fas fa-list text-info fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Toplam Deneme</div>
                        <div class="fw-bold fs-5">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:46px;height:46px;background:#dcfce7">
                        <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Başarılı Giriş</div>
                        <div class="fw-bold fs-5 text-success">{{ number_format($stats['success']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:46px;height:46px;background:#fee2e2">
                        <i class="fas fa-times-circle text-danger fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Başarısız Giriş</div>
                        <div class="fw-bold fs-5 text-danger">{{ number_format($stats['failed']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:46px;height:46px;background:#ede9fe">
                        <i class="fas fa-network-wired" style="color:#7c3aed;font-size:1.15rem"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tekil IP</div>
                        <div class="fw-bold fs-5" style="color:#7c3aed">{{ number_format($stats['unique_ips']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-sm-3">
                    <label class="form-label small text-muted mb-1">E-posta</label>
                    <input type="text" name="email" class="form-control form-control-sm"
                           placeholder="Kullanıcı ara..." value="{{ request('email') }}">
                </div>
                <div class="col-sm-2">
                    <label class="form-label small text-muted mb-1">IP Adresi</label>
                    <input type="text" name="ip" class="form-control form-control-sm"
                           placeholder="192.168..." value="{{ request('ip') }}">
                </div>
                <div class="col-sm-2">
                    <label class="form-label small text-muted mb-1">Durum</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        <option value="success" @selected(request('status') === 'success')>✅ Başarılı</option>
                        <option value="failed"  @selected(request('status') === 'failed')>❌ Başarısız</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label class="form-label small text-muted mb-1">Tarih (dan)</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-sm-2">
                    <label class="form-label small text-muted mb-1">Tarih (a)</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-sm-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1" title="Filtrele">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('it.login-logs.index') }}" class="btn btn-sm btn-outline-secondary" title="Temizle">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tablo --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($logs->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-shield-alt fa-3x text-muted opacity-25 d-block mb-3"></i>
                    <p class="text-muted">Kayıt bulunamadı.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" style="font-size:13px">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:50px">#</th>
                            <th>Durum</th>
                            <th>E-posta</th>
                            <th>Kullanıcı</th>
                            <th>IP Adresi</th>
                            <th>Tarayıcı / OS</th>
                            <th>Sebep</th>
                            <th class="pe-3">Tarih & Saat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr @if(!$log->isSuccess()) style="background:#fff8f8" @endif>
                            <td class="ps-3 text-muted small">{{ $log->id }}</td>
                            <td>
                                @if($log->isSuccess())
                                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-30 px-2 py-1">
                                        <i class="fas fa-check me-1"></i>Başarılı
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-30 px-2 py-1">
                                        <i class="fas fa-times me-1"></i>Başarısız
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $log->email }}</span>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width:28px;height:28px;font-size:11px;font-weight:700;color:#4361ee">
                                            {{ strtoupper(substr($log->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <span>{{ $log->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">—</span>
                                @endif
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded small">{{ $log->ip_address ?? '—' }}</code>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-0">
                                    <span class="small"><i class="fas fa-globe me-1 text-muted" style="font-size:10px"></i>{{ $log->browser }}</span>
                                    <span class="small text-muted"><i class="fas fa-desktop me-1" style="font-size:10px"></i>{{ $log->os }}</span>
                                </div>
                            </td>
                            <td>
                                @if($log->failure_reason)
                                    <span class="text-danger small">{{ $log->failure_reason }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="pe-3">
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold small">{{ $log->created_at->format('d.m.Y') }}</span>
                                    <span class="text-muted small">{{ $log->created_at->format('H:i:s') }}</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Sayfalama --}}
            @if($logs->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                <div class="text-muted small">
                    {{ $logs->firstItem() }}–{{ $logs->lastItem() }} / {{ $logs->total() }} kayıt
                </div>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
            @endif
            @endif
        </div>
    </div>

</div>
@endsection
