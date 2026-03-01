@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Manuel Giriş/Çıkış Kaydı</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('door-logs.index') }}">Kapı Giriş/Çıkış</a></li>
                <li class="breadcrumb-item active">Manuel Kayıt</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Kayıt Bilgileri</h4>
                </div>
                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('door-logs.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Personel <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Personel seçin...</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" @selected(old('user_id') == $manager->id)>
                                        {{ $manager->name }}
                                        @if($manager->department)— {{ $manager->department->name }}@endif
                                        ({{ $manager->branch->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">İşlem Tipi <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type"
                                           id="typeGiris" value="giris"
                                           @checked(old('type', 'giris') === 'giris')>
                                    <label class="form-check-label text-success fw-semibold" for="typeGiris">
                                        &#x2B24; Giriş
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type"
                                           id="typeCikis" value="cikis"
                                           @checked(old('type') === 'cikis')>
                                    <label class="form-check-label text-danger fw-semibold" for="typeCikis">
                                        &#x2B24; Çıkış
                                    </label>
                                </div>
                            </div>
                            @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tarih & Saat <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="logged_at"
                                   class="form-control @error('logged_at') is-invalid @enderror"
                                   value="{{ old('logged_at', now()->format('Y-m-d\TH:i')) }}"
                                   required>
                            @error('logged_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Not <span class="text-muted small">(opsiyonel)</span></label>
                            <input type="text" name="notes"
                                   class="form-control @error('notes') is-invalid @enderror"
                                   value="{{ old('notes') }}"
                                   placeholder="Açıklama...">
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Kaydet</button>
                            <a href="{{ route('door-logs.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
