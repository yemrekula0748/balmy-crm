@extends('layouts.default')
@section('content')

<style>
.lang-chip {
    display: inline-block;
    padding: 6px 16px;
    border: 2px solid #dee2e6;
    border-radius: 50px;
    cursor: pointer;
    font-size: .85rem;
    user-select: none;
    transition: .2s;
}
.lang-chip.active {
    border-color: #c19b77;
    background: #fdf6ef;
    color: #8b6a4f;
    font-weight: 600;
}
</style>

<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Menü Düzenle: {{ $menu->getTitle() }}</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.index') }}">QR Menüler</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.show', $menu) }}">{{ $menu->getTitle() }}</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    {{-- Hatalar --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('qrmenus.update', $menu) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row justify-content-center align-items-start">
            <div class="col-lg-10">

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
                                       value="{{ old('name', $menu->name) }}"
                                       pattern="[a-z0-9\-]+" title="Sadece küçük harf, rakam ve tire">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">QR kod URL'de kullanılır: /menu/<strong>slug</strong></small>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Para Birimi</label>
                                <select name="currency" class="form-select">
                                    <option value="TRY" @selected(old('currency', $menu->currency) == 'TRY')>TRY — Türk Lirası (₺)</option>
                                    <option value="USD" @selected(old('currency', $menu->currency) == 'USD')>USD — Dolar ($)</option>
                                    <option value="EUR" @selected(old('currency', $menu->currency) == 'EUR')>EUR — Euro (€)</option>
                                    <option value="GBP" @selected(old('currency', $menu->currency) == 'GBP')>GBP — Sterlin (£)</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Şube</label>
                                <select name="branch_id" class="form-select">
                                    <option value="">-- Şube Seçin --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            @selected(old('branch_id', $menu->branch_id) == $branch->id)>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Dil bazlı başlık alanları --}}
                            @foreach($menu->languages as $lang)
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    {{ $lang->flag }} {{ $lang->name }} — Menü Başlığı
                                </label>
                                <input type="text" name="title_{{ $lang->code }}" class="form-control"
                                       value="{{ old('title_'.$lang->code, $menu->getTitle($lang->code)) }}"
                                       placeholder="{{ $lang->name }} dilinde menü başlığı">
                            </div>
                            @endforeach

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Logo</label>
                                @if($menu->logo)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/'.$menu->logo) }}" alt="logo"
                                             class="rounded" style="width:56px;height:56px;object-fit:cover">
                                    </div>
                                @endif
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <small class="text-muted">Önerilen: 200×200px</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kapak Görseli</label>
                                @if($menu->cover_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/'.$menu->cover_image) }}" alt="kapak"
                                             class="rounded w-100" style="height:72px;object-fit:cover">
                                    </div>
                                @endif
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                                <small class="text-muted">Önerilen: 1200×400px</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Tema Rengi</label>
                                <div class="d-flex gap-2 flex-wrap align-items-center">
                                    @foreach(['#c19b77','#1a1a2e','#2c2c2c','#7c3aed','#059669','#dc2626','#2563eb','#0f172a'] as $color)
                                        <label style="cursor:pointer">
                                            <input type="radio" name="theme_color" value="{{ $color }}"
                                                   @checked(old('theme_color', $menu->theme_color) == $color)
                                                   class="d-none theme-radio">
                                            <span class="rounded-circle border border-2 d-inline-block"
                                                  style="width:30px;height:30px;background:{{ $color }};transition:.15s;
                                                  {{ old('theme_color', $menu->theme_color) == $color
                                                     ? 'box-shadow:0 0 0 3px '.$color.'66;transform:scale(1.15)' : '' }}">
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                           id="is_active" @checked(old('is_active', $menu->is_active))>
                                    <label class="form-check-label" for="is_active">Menü Aktif</label>
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
                        @php $existingCodes = $menu->languages->pluck('code')->toArray(); @endphp
                        <div class="d-flex flex-wrap gap-2 mb-3" id="lang-chips">
                            @foreach($langPresets as $code => $info)
                                <span class="lang-chip {{ in_array($code, $existingCodes) ? 'active' : '' }}"
                                      data-code="{{ $code }}" style="cursor:pointer">
                                    {{ $info['flag'] }} {{ $info['name'] }}
                                    <input type="checkbox" name="languages[]" value="{{ $code }}"
                                           class="d-none" @checked(in_array($code, $existingCodes))>
                                </span>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Varsayılan Dil</label>
                                <select name="default_lang" id="default_lang" class="form-select">
                                    @foreach($menu->languages as $lang)
                                        <option value="{{ $lang->code }}" @selected($lang->is_default)>
                                            {{ $lang->flag }} {{ $lang->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 mb-4">
                    <button type="submit" class="btn text-white" style="background:#c19b77">
                        <i class="fa fa-save me-1"></i> Değişiklikleri Kaydet
                    </button>
                    <a href="{{ route('qrmenus.show', $menu) }}" class="btn btn-secondary">İptal</a>
                </div>

            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
(function () {
    var presets = @json($langPresets);
    var selectedLangs = @json($menu->languages->pluck('code')->toArray());

    document.getElementById('lang-chips').addEventListener('click', function (e) {
        var chip = e.target.closest('.lang-chip');
        if (!chip) return;

        var code = chip.dataset.code;
        var cb = chip.querySelector('input[type=checkbox]');
        var isActive = chip.classList.contains('active');

        if (isActive) {
            if (selectedLangs.length <= 1) {
                alert('En az 1 dil seçili olmalıdır.');
                return;
            }
            chip.classList.remove('active');
            cb.checked = false;
            selectedLangs.splice(selectedLangs.indexOf(code), 1);
        } else {
            chip.classList.add('active');
            cb.checked = true;
            selectedLangs.push(code);
        }
        renderDefaults();
    });

    function renderDefaults() {
        var sel = document.getElementById('default_lang');
        var prev = sel.value;
        sel.innerHTML = '';
        selectedLangs.forEach(function (code) {
            var o = document.createElement('option');
            o.value = code;
            o.textContent = presets[code].flag + ' ' + presets[code].name;
            sel.appendChild(o);
        });
        if (selectedLangs.indexOf(prev) !== -1) sel.value = prev;
    }

    document.querySelectorAll('.theme-radio').forEach(function (r) {
        r.addEventListener('change', function () {
            document.querySelectorAll('.theme-radio').forEach(function (r2) {
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
