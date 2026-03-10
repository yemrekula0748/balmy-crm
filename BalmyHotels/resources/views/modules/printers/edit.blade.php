@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-edit me-2 text-primary"></i>Yazıcı Düzenle</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('printers.index') }}">Yazıcılar</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">{{ $printer->name }}</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('printers.update', $printer) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">Şube seçin...</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id', $printer->branch_id) == $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Yazıcı Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $printer->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">IP Adresi <span class="text-danger">*</span></label>
                            <input type="text" name="ip_address" class="form-control"
                                   value="{{ old('ip_address', $printer->ip_address) }}" required>
                        </div>
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       id="isActiveCheck" @checked(old('is_active', $printer->is_active))>
                                <label class="form-check-label fw-semibold" for="isActiveCheck">Aktif</label>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Güncelle
                            </button>
                            <a href="{{ route('printers.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
