@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Yeni Arıza Türü</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.types.index') }}">Arıza Türleri</a></li>
                <li class="breadcrumb-item active">Yeni Tür</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Arıza Türü Bilgileri</h5></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form action="{{ route('faults.types.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Arıza Türü Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Elektrik, Tesisat..." required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Hedef Tamamlanma Süresi (Saat) <span class="text-danger">*</span></label>
                            <input type="number" name="completion_hours" class="form-control @error('completion_hours') is-invalid @enderror"
                                   value="{{ old('completion_hours', 24) }}" min="1" max="720" required>
                            @error('completion_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şube</label>
                            <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                <option value="">Tüm Şubeler</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                                       @checked(old('is_active', true))>
                                <label class="form-check-label" for="isActive">Aktif</label>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Kaydet
                            </button>
                            <a href="{{ route('faults.types.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> İptal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
