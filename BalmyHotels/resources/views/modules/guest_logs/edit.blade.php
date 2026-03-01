@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-edit me-2 text-primary"></i>Ziyaretçi Kaydı Düzenle</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('guest-logs.index') }}">Ziyaretçi Kayıtları</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $guestLog->visitor_name }}</h5></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form action="{{ route('guest-logs.update', $guestLog) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select" required>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id', $guestLog->branch_id) == $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Departman</label>
                                <select name="department_id" class="form-select">
                                    <option value="">Seçin...</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->id }}" @selected(old('department_id', $guestLog->department_id) == $d->id)>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kime Geliyor</label>
                            <select name="host_user_id" class="form-select">
                                <option value="">Kişi seçin...</option>
                                @foreach($hosts as $h)
                                    <option value="{{ $h->id }}" @selected(old('host_user_id', $guestLog->host_user_id) == $h->id)>
                                        {{ $h->name }}{{ $h->title ? ' — '.$h->title : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <hr>
                        <h6 class="text-muted fw-semibold mb-3">Ziyaretçi Bilgileri</h6>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="visitor_name" class="form-control"
                                       value="{{ old('visitor_name', $guestLog->visitor_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Telefon</label>
                                <input type="text" name="visitor_phone" class="form-control"
                                       value="{{ old('visitor_phone', $guestLog->visitor_phone) }}">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">TC / Pasaport No</label>
                                <input type="text" name="visitor_id_no" class="form-control"
                                       value="{{ old('visitor_id_no', $guestLog->visitor_id_no) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kurum / Şirket</label>
                                <input type="text" name="visitor_company" class="form-control"
                                       value="{{ old('visitor_company', $guestLog->visitor_company) }}">
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-muted fw-semibold mb-3">Ziyaret Amacı & Zaman</h6>

                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Amaç <span class="text-danger">*</span></label>
                                <select name="purpose" class="form-select" required>
                                    @foreach(\App\Models\GuestLog::PURPOSES as $val => $lbl)
                                        <option value="{{ $val }}" @selected(old('purpose', $guestLog->purpose) == $val)>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Açıklama</label>
                                <input type="text" name="purpose_note" class="form-control"
                                       value="{{ old('purpose_note', $guestLog->purpose_note) }}">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Giriş <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="check_in_at" class="form-control"
                                       value="{{ old('check_in_at', $guestLog->check_in_at->format('Y-m-d\TH:i')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Çıkış <small class="text-muted">(boş = içeride)</small></label>
                                <input type="datetime-local" name="check_out_at" class="form-control"
                                       value="{{ old('check_out_at', $guestLog->check_out_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notlar</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $guestLog->notes) }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">Güncelle</button>
                            <a href="{{ route('guest-logs.show', $guestLog) }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
