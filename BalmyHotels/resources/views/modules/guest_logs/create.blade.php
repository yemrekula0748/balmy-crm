@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-user-plus me-2 text-primary"></i>Ziyaretçi Kaydı Ekle</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('guest-logs.index') }}">Ziyaretçi Kayıtları</a></li>
                <li class="breadcrumb-item active">Yeni Kayıt</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Ziyaretçi Bilgileri</h5></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form action="{{ route('guest-logs.store') }}" method="POST">
                        @csrf

                        {{-- ŞUBE --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                                @if($autoBranchId)
                                    <input type="hidden" name="branch_id" value="{{ $autoBranchId }}">
                                    <input type="text" class="form-control" value="{{ $branches->firstWhere('id', $autoBranchId)?->name }}" disabled>
                                @else
                                    <select name="branch_id" id="branchSelect" class="form-select" required>
                                        <option value="">Şube seçin...</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Departman</label>
                                <select name="department_id" id="deptSelect" class="form-select">
                                    <option value="">Seçin...</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- KİME GELİYOR --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kime Geliyor</label>
                            <select name="host_user_id" id="hostSelect" class="form-select">
                                <option value="">Kişi seçin...</option>
                                @foreach($hosts as $h)
                                    @php $activeLog = \App\Models\GuestLog::where('host_user_id',$h->id)->whereNull('check_out_at')->exists(); @endphp
                                    <option value="{{ $h->id }}"
                                            data-dept="{{ $h->department_id }}"
                                            @selected(old('host_user_id', request('host_user_id')) == $h->id)
                                            @if($activeLog) disabled class="text-muted" @endif>
                                        {{ $h->name }}{{ $h->title ? ' — '.$h->title : '' }}
                                        @if($activeLog) (İçeride ziyaretçi var) @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted"><i class="fas fa-info-circle me-1"></i>Gri seçenekler: yanında zaten aktif ziyaretçi olan müdürler</div>
                            @error('host_user_id')
                                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        {{-- ZİYARETÇİ BİLGİLERİ --}}
                        <h6 class="text-muted fw-semibold mb-3">Ziyaretçi Bilgileri</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="visitor_name" class="form-control" value="{{ old('visitor_name') }}"
                                       placeholder="Ad Soyad" required autofocus>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Telefon</label>
                                <input type="text" name="visitor_phone" class="form-control" value="{{ old('visitor_phone') }}"
                                       placeholder="0 5xx xxx xx xx">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">TC / Pasaport No</label>
                                <input type="text" name="visitor_id_no" class="form-control" value="{{ old('visitor_id_no') }}"
                                       placeholder="Kimlik numarası">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kurum / Şirket</label>
                                <input type="text" name="visitor_company" class="form-control" value="{{ old('visitor_company') }}"
                                       placeholder="Çalıştığı kurum / şirket">
                            </div>
                        </div>

                        <hr>

                        {{-- ZİYARET AMACI --}}
                        <h6 class="text-muted fw-semibold mb-3">Ziyaret Amacı</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Amaç <span class="text-danger">*</span></label>
                                <select name="purpose" class="form-select" required>
                                    @foreach(\App\Models\GuestLog::PURPOSES as $val => $lbl)
                                        <option value="{{ $val }}" @selected(old('purpose', 'meeting') == $val)>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Açıklama</label>
                                <input type="text" name="purpose_note" class="form-control" value="{{ old('purpose_note') }}"
                                       placeholder="Kısa açıklama...">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Giriş Tarihi / Saati <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="check_in_at" class="form-control"
                                       value="{{ old('check_in_at', now()->format('Y-m-d\TH:i')) }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notlar</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Ek notlar...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">Kaydet</button>
                            <a href="{{ route('guest-logs.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Departman seçince host listesini filtrele
document.getElementById('deptSelect')?.addEventListener('change', function () {
    const deptId = this.value;
    const hostSel = document.getElementById('hostSelect');
    [...hostSel.options].forEach(opt => {
        if (!opt.value) return;
        opt.hidden = deptId && opt.dataset.dept !== deptId;
    });
    if (deptId) hostSel.value = '';
});
</script>
@endpush
