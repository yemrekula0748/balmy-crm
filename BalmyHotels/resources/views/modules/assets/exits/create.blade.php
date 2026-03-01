@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Çıkış Formu Oluştur</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('asset-exits.index') }}">Çıkış Formları</a></li>
                <li class="breadcrumb-item active">Oluştur</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Eşya Çıkış Formu</h4></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form action="{{ route('asset-exits.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Demirbaş <span class="text-danger">*</span></label>
                                <select name="asset_id" class="form-select @error('asset_id') is-invalid @enderror" required>
                                    <option value="">Demirbaş seçin...</option>
                                    @foreach($assets as $a)
                                        <option value="{{ $a->id }}"
                                            @selected(old('asset_id', $selectedAsset?->id) == $a->id)>
                                            {{ $a->asset_code }} — {{ $a->name }}
                                            ({{ \App\Models\Asset::STATUSES[$a->status] }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('asset_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">Şube...</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- ALAN TİPİ SEÇIMI --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Kim Alıyor? <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="taker_type" id="typeStaff"
                                               value="staff" @checked(old('taker_type', 'staff') === 'staff')>
                                        <label class="form-check-label" for="typeStaff">Personel</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="taker_type" id="typeGuest"
                                               value="guest" @checked(old('taker_type') === 'guest')>
                                        <label class="form-check-label" for="typeGuest">Misafir</label>
                                    </div>
                                </div>
                                @error('taker_type')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            {{-- PERSONEL ALANI --}}
                            <div class="col-12" id="staffSection">
                                <label class="form-label fw-semibold">Personel Seçin <span class="text-danger">*</span></label>
                                <select name="staff_id" class="form-select @error('staff_id') is-invalid @enderror">
                                    <option value="">Personel seçin...</option>
                                    @foreach($staff as $u)
                                        <option value="{{ $u->id }}" @selected(old('staff_id') == $u->id)>
                                            {{ $u->name }} @if($u->department)({{ $u->department->name }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('staff_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- MİSAFİR ALANI --}}
                            <div class="col-12" id="guestSection" style="display:none">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Misafir Adı <span class="text-danger">*</span></label>
                                        <input type="text" name="guest_name" class="form-control @error('guest_name') is-invalid @enderror"
                                               value="{{ old('guest_name') }}" placeholder="Ad Soyad">
                                        @error('guest_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Oda No</label>
                                        <input type="text" name="guest_room" class="form-control" value="{{ old('guest_room') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Telefon</label>
                                        <input type="text" name="guest_phone" class="form-control" value="{{ old('guest_phone') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">TC/Pasaport No</label>
                                        <input type="text" name="guest_id_no" class="form-control" value="{{ old('guest_id_no') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Çıkış Sebebi <span class="text-danger">*</span></label>
                                <textarea name="purpose" rows="2" class="form-control @error('purpose') is-invalid @enderror"
                                          placeholder="Eşyanın neden alındığını açıklayın...">{{ old('purpose') }}</textarea>
                                @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Çıkış Tarihi <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="taken_at" class="form-control @error('taken_at') is-invalid @enderror"
                                       value="{{ old('taken_at', now()->format('Y-m-d\TH:i')) }}">
                                @error('taken_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Beklenen İade Tarihi</label>
                                <input type="datetime-local" name="expected_return_at" class="form-control"
                                       value="{{ old('expected_return_at') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notlar</label>
                                <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn" style="background:#c19b77;color:#fff">Form Oluştur</button>
                            <a href="{{ route('asset-exits.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleTakerSection() {
    const isGuest = document.getElementById('typeGuest').checked;
    document.getElementById('staffSection').style.display = isGuest ? 'none' : 'block';
    document.getElementById('guestSection').style.display = isGuest ? 'block' : 'none';
}
document.getElementById('typeStaff').addEventListener('change', toggleTakerSection);
document.getElementById('typeGuest').addEventListener('change', toggleTakerSection);
toggleTakerSection(); // initial
</script>
@endpush
@endsection
