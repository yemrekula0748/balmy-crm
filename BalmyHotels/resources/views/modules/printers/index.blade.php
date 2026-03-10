@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-print me-2 text-primary"></i>Yazıcılar</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Yazıcılar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- YENİ YAZICI EKLE --}}
        @can('printers.store')
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-plus me-2 text-primary"></i>Yeni Yazıcı Ekle</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('printers.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">Şube seçin...</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Yazıcı Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name') }}" placeholder="ör. Mutfak Yazıcısı" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">IP Adresi <span class="text-danger">*</span></label>
                            <input type="text" name="ip_address" class="form-control"
                                   value="{{ old('ip_address') }}" placeholder="ör. 192.168.1.100" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i> Kaydet
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endcan

        {{-- YAZICI LİSTESİ --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2 text-primary"></i>Kayıtlı Yazıcılar
                        <span class="badge bg-primary ms-2">{{ $printers->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($printers->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-print fa-3x mb-3 opacity-25 d-block"></i>
                            <p class="mb-0">Henüz yazıcı eklenmemiş.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Yazıcı Adı</th>
                                        <th>IP Adresi</th>
                                        <th>Şube</th>
                                        <th>Durum</th>
                                        <th class="text-end pe-3">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($printers as $printer)
                                    <tr>
                                        <td class="ps-3 text-muted" style="font-size:13px;">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-semibold" style="font-size:14px;">{{ $printer->name }}</div>
                                        </td>
                                        <td>
                                            <code style="font-size:13px;">{{ $printer->ip_address }}</code>
                                        </td>
                                        <td style="font-size:13px;">{{ optional($printer->branch)->name ?? '-' }}</td>
                                        <td>
                                            @if($printer->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Pasif</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            @can('printers.update')
                                            <a href="{{ route('printers.edit', $printer) }}"
                                               class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('printers.destroy')
                                            <form action="{{ route('printers.destroy', $printer) }}" method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Bu yazıcıyı silmek istediğinize emin misiniz?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </td>
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
</div>
@endsection
