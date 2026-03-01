@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yeni QR Menü Oluştur</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.index') }}">QR Menüler</a></li>
                <li class="breadcrumb-item active">Yeni Menü</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center align-items-start">
        <div class="col-lg-10">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('qrmenus.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Menü Bilgileri --}}
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Menü Bilgileri</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Menü Adı (Slug) <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="örn: balmy-restorant"
                                       pattern="[a-z0-9\-]+" title="Sadece küçük harf, rakam ve tire kullanın">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">QR kod URL'de kullanılır: /menu/<strong>slug</strong></small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Para Birimi</label>
                                <select name="currency" class="form-select">
                                    <option value="TRY" @selected(old('currency','TRY')=='TRY')>TRY — Türk Lirası (₺)</option>
                                    <option value="USD" @selected(old('currency')=='USD')>USD — Dolar ($)</option>
                                    <option value="EUR" @selected(old('currency')=='EUR')>EUR — Euro (€)</option>
                                    <option value="GBP" @selected(old('currency')=='GBP')>GBP — Sterlin (£)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Şube</label>
                                <select name="branch_id" class="form-select">
                                    <option value="">-- Şube Seçin --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected(old('branch_id')==$branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Dil bazlı başlık alanları (JS ile dolar) --}}
                            <div class="col-12" id="lang-title-fields"></div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <small class="text-muted">Önerilen: 200×200px</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kapak Görseli</label>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                                <small class="text-muted">Önerilen: 1200×400px</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Tema Rengi</label>
                                <div class="d-flex gap-2 flex-wrap align-items-center">
                                    @foreach(['#c19b77','#1a1a2e','#2c2c2c','#7c3aed','#059669','#dc2626','#2563eb','#0f172a'] as $color)
                                        <label style="cursor:pointer">
                                            <input type="radio" name="theme_color" value="{{ $color }}"
                                                   @checked(old('theme_color','#c19b77')==$color) class="d-none theme-radio">
                                            <span class="rounded-circle border border-2 d-inline-block"
                                                  style="width:30px;height:30px;background:{{ $color }};transition:.15s;
                                                  {{ old('theme_color','#c19b77')==$color ? 'box-shadow:0 0 0 3px '.$color.'66;transform:scale(1.15)' : '' }}"></span>
                                        </label>
                                    @endforeach
                                    <input type="color" name="theme_color_custom" class="form-control form-control-color"
                                           value="#c19b77" title="Özel renk" style="width:40px;height:30px;padding:2px">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dil Yönetimi --}}
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Menü Dilleri</h4>
                        <small class="text-muted">En az 1 dil seçin</small>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 mb-3" id="lang-chips">
                            @foreach($langPresets as $code => $info)
                                <span class="lang-chip {{ $code=='tr' ? 'active' : '' }}" data-code="{{ $code }}" style="cursor:pointer">
                                    {{ $info['flag'] }} {{ $info['name'] }}
                                    <input type="checkbox" name="languages[]" value="{{ $code }}" class="d-none" @checked($code=='tr')>
                                </span>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Varsayılan Dil</label>
                                <select name="default_lang" id="default_lang" class="form-select">
                                    <option value="tr">🇹🇷 Türkçe</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 mb-4">
                    <button type="submit" class="btn text-white" style="background:#c19b77">
                        <i class="fa fa-save me-1"></i> Menü Oluştur
                    </button>
                    <a href="{{ route('qrmenus.index') }}" class="btn btn-secondary">İptal</a>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    var presets = @json($langPresets);
    var selectedLangs = ['tr'];

    document.getElementById('lang-chips').addEventListener('click', function(e) {
        var chip = e.target.closest('.lang-chip');
        if (!chip) return;

        var code = chip.dataset.code;
        var cb = chip.querySelector('input[type=checkbox]');
        var isActive = chip.classList.contains('active');

        if (isActive) {
            if (selectedLangs.length <= 1) { alert('En az 1 dil seçili olmalıdır.'); return; }
            chip.classList.remove('active');
            cb.checked = false;
            selectedLangs.splice(selectedLangs.indexOf(code), 1);
        } else {
            chip.classList.add('active');
            cb.checked = true;
            selectedLangs.push(code);
        }
        renderDefaults();
        renderTitleFields();
    });

    function renderDefaults() {
        var sel = document.getElementById('default_lang');
        var prev = sel.value;
        sel.innerHTML = '';
        selectedLangs.forEach(function(code) {
            var o = document.createElement('option');
            o.value = code;
            o.textContent = presets[code].flag + ' ' + presets[code].name;
            sel.appendChild(o);
        });
        if (selectedLangs.indexOf(prev) !== -1) sel.value = prev;
    }

    function renderTitleFields() {
        var box = document.getElementById('lang-title-fields');
        box.innerHTML = '';
        selectedLangs.forEach(function(code) {
            var info = presets[code];
            var div = document.createElement('div');
            div.className = 'mb-2';
            div.innerHTML =
                '<label class="form-label fw-semibold">' + info.flag + ' ' + info.name + ' — Menü Başlığı</label>' +
                '<input type="text" name="title_' + code + '" class="form-control" placeholder="Menü adı (' + info.name + ')">';
            box.appendChild(div);
        });
    }

    renderDefaults();
    renderTitleFields();

    document.querySelectorAll('.theme-radio').forEach(function(r) {
        r.addEventListener('change', function() {
            document.querySelectorAll('.theme-radio').forEach(function(r2) {
                r2.nextElementSibling.style.boxShadow = '';
                r2.nextElementSibling.style.transform = '';
            });
            r.nextElementSibling.style.boxShadow = '0 0 0 3px ' + r.value + '66';
            r.nextElementSibling.style.transform = 'scale(1.15)';
        });
    });
})();
</script>
@endpush
@endsection
