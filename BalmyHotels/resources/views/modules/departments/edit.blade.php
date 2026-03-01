@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Departman Düzenle</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departmanlar</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $department->name }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('departments.update', $department) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                <option value="">Şube seçin...</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        @selected(old('branch_id', $department->branch_id) == $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Departman Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $department->name) }}"
                                   placeholder="Örn: Teknik, Resepsiyon, F&B...">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Badge Rengi</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" name="color" id="colorPicker"
                                       class="form-control form-control-color"
                                       value="{{ old('color', $department->color) }}"
                                       style="width:60px;height:38px;">
                                <span class="text-muted small">Listede departman adının arka plan rengi</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Arıza Yönetimi</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="fault_assignable"
                                       id="faultAssignable" value="1"
                                       @checked(old('fault_assignable', $department->fault_assignable))>
                                <label class="form-check-label" for="faultAssignable">
                                    Bu departmana arıza atanabilir
                                </label>
                            </div>
                            <small class="text-muted">İşaretlenirse arıza bildirimlerinde bu departman seçilebilir.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Durum</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="isActive" value="1"
                                       @checked(old('is_active', $department->is_active))>
                                <label class="form-check-label" for="isActive">Aktif</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Güncelle</button>
                            <a href="{{ route('departments.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
