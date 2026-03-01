@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Arıza Türleri</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.index') }}">Teknik Arıza</a></li>
                <li class="breadcrumb-item active">Arıza Türleri</li>
            </ol>
        </div>
    </div>

    <div class="row align-items-start g-4">
        {{-- Sol: Tablo --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tür Listesi</h5>
                    <a href="{{ route('faults.types.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Yeni Tür
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Arıza Türü</th>
                                    <th>Hedef Süre</th>
                                    <th>Şube</th>
                                    <th>Durum</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($types as $type)
                                <tr>
                                    <td class="fw-semibold">{{ $type->name }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            {{ $type->completion_hours }} saat
                                        </span>
                                    </td>
                                    <td>{{ $type->branch->name ?? '<span class="text-muted">Tüm Şubeler</span>' }}</td>
                                    <td>
                                        <span class="badge {{ $type->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $type->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('faults.types.edit', $type) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('faults.types.destroy', $type) }}" method="POST"
                                              class="d-inline" onsubmit="return confirm('Bu türü silmek istiyor musunuz?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Henüz arıza türü tanımlanmamış.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($types->hasPages())
                <div class="card-footer">{{ $types->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Sağ: Hızlı ekle --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0">Hızlı Tür Ekle</h6></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2 small">
                            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                        </div>
                    @endif
                    <form action="{{ route('faults.types.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Arıza Türü Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name') }}" placeholder="Örn: Elektrik Arızası" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Hedef Tamamlanma Süresi (Saat) <span class="text-danger">*</span></label>
                            <input type="number" name="completion_hours" class="form-control"
                                   value="{{ old('completion_hours', 24) }}" min="1" max="720" required>
                            <small class="text-muted">Bu süre istatistiklerde hedef olarak kullanılır.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şube (Opsiyonel)</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Tüm Şubeler</option>
                                @foreach(\App\Models\Branch::orderBy('name')->get() as $b)
                                    <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Boş bırakılırsa tüm şubelerde görünür.</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Ekle</button>
                    </form>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
