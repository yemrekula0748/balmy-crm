@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Sefer Düzenle</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shuttle.operations.index') }}">Operasyon</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        {{ $operation->trip_date->format('d.m.Y') }} — {{ $operation->shift }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('shuttle.operations.update', $operation) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Şube <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id', $operation->branch_id) == $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Araç <span class="text-danger">*</span></label>
                                <select name="shuttle_vehicle_id"
                                        class="form-select @error('shuttle_vehicle_id') is-invalid @enderror" required>
                                    @foreach($vehicles as $v)
                                        <option value="{{ $v->id }}" @selected(old('shuttle_vehicle_id', $operation->shuttle_vehicle_id) == $v->id)>
                                            {{ $v->name }}@if($v->plate) ({{ $v->plate }})@endif — Kap: {{ $v->capacity }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('shuttle_vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Güzergah</label>
                                <select name="route_id" class="form-select">
                                    <option value="">— Seçiniz (isteğe bağlı) —</option>
                                    @foreach($routes as $r)
                                        <option value="{{ $r->id }}" @selected(old('route_id', $operation->route_id) == $r->id)>{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vardiya <span class="text-danger">*</span></label>
                                <select name="shift" class="form-select @error('shift') is-invalid @enderror" required>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift }}" @selected(old('shift', $operation->shift) === $shift)>{{ $shift }}</option>
                                    @endforeach
                                </select>
                                @error('shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tarih <span class="text-danger">*</span></label>
                                <input type="date" name="trip_date"
                                       value="{{ old('trip_date', $operation->trip_date->format('Y-m-d')) }}"
                                       class="form-control @error('trip_date') is-invalid @enderror" required>
                                @error('trip_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6"></div>

                            {{-- Geliş --}}
                            <div class="col-12"><hr class="my-1"><h6 class="text-primary"><i class="fas fa-arrow-right me-1"></i>Geliş Bilgileri</h6></div>
                            <div class="col-md-4">
                                <label class="form-label">Geliş Saati</label>
                                <input type="time" name="arrival_time"
                                       value="{{ old('arrival_time', $operation->arrival_time ? substr($operation->arrival_time, 0, 5) : '') }}"
                                       class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gelen Kişi <span class="text-danger">*</span></label>
                                <input type="number" name="arrival_count"
                                       value="{{ old('arrival_count', $operation->arrival_count) }}"
                                       min="0" max="500" class="form-control" required>
                            </div>

                            {{-- Dönüş --}}
                            <div class="col-12"><hr class="my-1"><h6 class="text-success"><i class="fas fa-arrow-left me-1"></i>Dönüş Bilgileri</h6></div>
                            <div class="col-md-4">
                                <label class="form-label">Dönüş Saati</label>
                                <input type="time" name="departure_time"
                                       value="{{ old('departure_time', $operation->departure_time ? substr($operation->departure_time, 0, 5) : '') }}"
                                       class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Dönen Kişi <span class="text-danger">*</span></label>
                                <input type="number" name="departure_count"
                                       value="{{ old('departure_count', $operation->departure_count) }}"
                                       min="0" max="500" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Not</label>
                                <textarea name="notes" rows="2" class="form-control"
                                          maxlength="500">{{ old('notes', $operation->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Güncelle
                            </button>
                            <a href="{{ route('shuttle.operations.index', ['date' => $operation->trip_date->format('Y-m-d'), 'branch_id' => $operation->branch_id]) }}"
                               class="btn btn-outline-secondary">
                                İptal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
