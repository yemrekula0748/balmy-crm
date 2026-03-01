@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yeni Araç</h4>
                <span>Araç kayıt formu</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Araçlar</a></li>
                <li class="breadcrumb-item active">Yeni Araç</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-car me-2 text-primary"></i> Araç Bilgileri
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

                    <form action="{{ route('vehicles.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            {{-- Şube --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Şube <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">— Seçiniz —</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>
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
                                    <option value="">— Seçiniz —</option>
                                    @foreach(['binek'=>'Binek','minibus'=>'Minibüs','kamyonet'=>'Kamyonet','kamyon'=>'Kamyon','diger'=>'Diğer'] as $k=>$label)
                                        <option value="{{ $k }}" @selected(old('type') == $k)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Plaka --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Plaka <span class="text-danger">*</span></label>
                                <input type="text" name="plate"
                                    class="form-control text-uppercase @error('plate') is-invalid @enderror"
                                    value="{{ old('plate') }}" placeholder="34 ABC 123" required>
                                @error('plate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Marka --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Marka <span class="text-danger">*</span></label>
                                <input type="text" name="brand"
                                    class="form-control @error('brand') is-invalid @enderror"
                                    value="{{ old('brand') }}" placeholder="Toyota" required>
                                @error('brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Model --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text" name="model"
                                    class="form-control @error('model') is-invalid @enderror"
                                    value="{{ old('model') }}" placeholder="Corolla" required>
                                @error('model') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Yıl --}}
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Yıl <span class="text-danger">*</span></label>
                                <input type="number" name="year"
                                    class="form-control @error('year') is-invalid @enderror"
                                    value="{{ old('year', date('Y')) }}"
                                    min="1990" max="{{ date('Y') + 1 }}" required>
                                @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Renk --}}
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Renk</label>
                                <input type="text" name="color"
                                    class="form-control @error('color') is-invalid @enderror"
                                    value="{{ old('color') }}" placeholder="Beyaz">
                                @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Güncel KM --}}
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Güncel KM <span class="text-danger">*</span></label>
                                <input type="number" name="current_km"
                                    class="form-control @error('current_km') is-invalid @enderror"
                                    value="{{ old('current_km', 0) }}" min="0" required>
                                @error('current_km') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Muayene Tarihi --}}
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Muayene Tarihi</label>
                                <input type="date" name="license_expiry"
                                    class="form-control @error('license_expiry') is-invalid @enderror"
                                    value="{{ old('license_expiry') }}">
                                @error('license_expiry') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Şasi No --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Şasi No</label>
                                <input type="text" name="chassis_no"
                                    class="form-control @error('chassis_no') is-invalid @enderror"
                                    value="{{ old('chassis_no') }}">
                                @error('chassis_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Motor No --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Motor No</label>
                                <input type="text" name="engine_no"
                                    class="form-control @error('engine_no') is-invalid @enderror"
                                    value="{{ old('engine_no') }}">
                                @error('engine_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Notlar --}}
                            <div class="col-12 mb-4">
                                <label class="form-label">Notlar</label>
                                <textarea name="notes" rows="3"
                                    class="form-control @error('notes') is-invalid @enderror"
                                    placeholder="Ek bilgiler...">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Kaydet
                            </button>
                            <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">
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
