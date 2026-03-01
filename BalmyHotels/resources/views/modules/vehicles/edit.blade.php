@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Araç Düzenle</h4>
                <span>{{ $vehicle->plate }} — {{ $vehicle->brand }} {{ $vehicle->model }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Araçlar</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vehicles.show', $vehicle) }}">{{ $vehicle->plate }}</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-edit me-2 text-warning"></i> Araç Bilgilerini Güncelle
                    </h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="row">
                            {{-- Şube --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Şube <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">— Seçiniz —</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}"
                                            @selected(old('branch_id', $vehicle->branch_id) == $b->id)>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Tip --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Araç Tipi <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    @foreach(['binek'=>'Binek','minibus'=>'Minibüs','kamyonet'=>'Kamyonet','kamyon'=>'Kamyon','diger'=>'Diğer'] as $k=>$label)
                                        <option value="{{ $k }}" @selected(old('type', $vehicle->type) == $k)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Plaka --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Plaka <span class="text-danger">*</span></label>
                                <input type="text" name="plate"
                                    class="form-control text-uppercase @error('plate') is-invalid @enderror"
                                    value="{{ old('plate', $vehicle->plate) }}" required>
                                @error('plate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Marka --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Marka <span class="text-danger">*</span></label>
                                <input type="text" name="brand"
                                    class="form-control @error('brand') is-invalid @enderror"
                                    value="{{ old('brand', $vehicle->brand) }}" required>
                                @error('brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Model --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text" name="model"
                                    class="form-control @error('model') is-invalid @enderror"
                                    value="{{ old('model', $vehicle->model) }}" required>
                                @error('model') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Yıl --}}
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Yıl <span class="text-danger">*</span></label>
                                <input type="number" name="year"
                                    class="form-control @error('year') is-invalid @enderror"
                                    value="{{ old('year', $vehicle->year) }}"
                                    min="1990" max="{{ date('Y') + 1 }}" required>
                                @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Renk --}}
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Renk</label>
                                <input type="text" name="color"
                                    class="form-control @error('color') is-invalid @enderror"
                                    value="{{ old('color', $vehicle->color) }}">
                                @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Güncel KM --}}
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Güncel KM <span class="text-danger">*</span></label>
                                <input type="number" name="current_km"
                                    class="form-control @error('current_km') is-invalid @enderror"
                                    value="{{ old('current_km', $vehicle->current_km) }}" min="0" required>
                                @error('current_km') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Muayene Tarihi --}}
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Muayene Tarihi</label>
                                <input type="date" name="license_expiry"
                                    class="form-control @error('license_expiry') is-invalid @enderror"
                                    value="{{ old('license_expiry', optional($vehicle->license_expiry)->format('Y-m-d')) }}">
                                @error('license_expiry') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Şasi No --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Şasi No</label>
                                <input type="text" name="chassis_no"
                                    class="form-control @error('chassis_no') is-invalid @enderror"
                                    value="{{ old('chassis_no', $vehicle->chassis_no) }}">
                                @error('chassis_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Motor No --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Motor No</label>
                                <input type="text" name="engine_no"
                                    class="form-control @error('engine_no') is-invalid @enderror"
                                    value="{{ old('engine_no', $vehicle->engine_no) }}">
                                @error('engine_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Notlar --}}
                            <div class="col-12 mb-3">
                                <label class="form-label">Notlar</label>
                                <textarea name="notes" rows="3"
                                    class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $vehicle->notes) }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Aktif / Pasif --}}
                            <div class="col-12 mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                        id="is_active" value="1" @checked(old('is_active', $vehicle->is_active))>
                                    <label class="form-check-label" for="is_active">Araç Aktif</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i> Güncelle
                            </button>
                            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Geri
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
