@extends('layouts.default')

@push('styles')
<style>
/* Manuel Kayit */
.mc-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 8px 24px rgba(79,70,229,.08);
    overflow: hidden;
}
.mc-header {
    background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 60%, #6366f1 100%);
    padding: 1.75rem 2rem;
    display: flex;
    align-items: center;
    gap: .875rem;
}
.mc-header-icon {
    width: 44px; height: 44px;
    background: rgba(255,255,255,.15);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mc-header-text h5 { margin: 0; font-size: 1.1rem; font-weight: 700; color: #fff; }
.mc-header-text p  { margin: 0; font-size: .8rem; color: rgba(255,255,255,.65); }
.mc-body { padding: 2rem; }
.mc-field { margin-bottom: 1.25rem; }
.mc-label {
    display: flex; align-items: center; gap: .375rem;
    font-size: .78rem; font-weight: 600;
    color: #64748b; text-transform: uppercase; letter-spacing: .04em;
    margin-bottom: .5rem;
}
.mc-label .dot { width: 5px; height: 5px; border-radius: 50%; background: #ef4444; flex-shrink: 0; }
.mc-input {
    width: 100%;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: .65rem 1rem;
    font-size: .925rem;
    color: #0f172a;
    transition: border-color .15s, box-shadow .15s, background .15s;
    outline: none;
    appearance: none;
}
.mc-input:focus {
    border-color: #6366f1;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.mc-input.is-invalid { border-color: #ef4444; background: #fff5f5; }
.mc-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.12); }
.mc-error { margin-top: .375rem; font-size: .8rem; color: #ef4444; display: flex; align-items: center; gap: .25rem; }
.mc-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
.mc-type-card {
    position: relative; border-radius: 14px;
    border: 2px solid #e2e8f0; background: #f8fafc;
    padding: 1rem; cursor: pointer;
    transition: all .18s;
    user-select: none;
}
.mc-type-card input[type=radio] { position: absolute; opacity: 0; width: 0; height: 0; }
.mc-type-card .mc-type-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: .5rem; transition: background .18s;
}
.mc-type-card .mc-type-label { font-size: .95rem; font-weight: 600; color: #334155; }
.mc-type-card .mc-type-sub   { font-size: .75rem; color: #94a3b8; margin-top: 2px; }
.mc-type-card:hover { border-color: #a5b4fc; background: #fafafa; }
.mc-type-card.is-giris-active {
    border-color: #22c55e; background: #f0fdf4;
    box-shadow: 0 0 0 3px rgba(34,197,94,.1);
}
.mc-type-card.is-giris-active .mc-type-icon { background: #dcfce7; }
.mc-type-card.is-giris-active .mc-type-label { color: #15803d; }
.mc-type-card.is-cikis-active {
    border-color: #ef4444; background: #fff5f5;
    box-shadow: 0 0 0 3px rgba(239,68,68,.1);
}
.mc-type-card.is-cikis-active .mc-type-icon { background: #fee2e2; }
.mc-type-card.is-cikis-active .mc-type-label { color: #dc2626; }
.mc-divider { height: 1px; background: #f1f5f9; margin: 1.5rem 0; }
.mc-actions { display: flex; gap: .75rem; }
.mc-btn-primary {
    flex: 1; padding: .75rem 1.5rem;
    background: linear-gradient(135deg, #4338ca, #6366f1);
    color: #fff; border: none; border-radius: 12px;
    font-size: .95rem; font-weight: 600; cursor: pointer;
    transition: opacity .15s, transform .1s;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
}
.mc-btn-primary:hover { opacity: .9; }
.mc-btn-primary:active { transform: scale(.98); }
.mc-btn-cancel {
    padding: .75rem 1.25rem;
    background: #f1f5f9; color: #64748b;
    border: none; border-radius: 12px;
    font-size: .95rem; font-weight: 500; cursor: pointer;
    text-decoration: none;
    display: flex; align-items: center; justify-content: center; gap: .375rem;
    transition: background .15s;
}
.mc-btn-cancel:hover { background: #e2e8f0; color: #475569; }
.mc-alert {
    background: #fff5f5; border: 1.5px solid #fecaca;
    border-radius: 12px; padding: .875rem 1rem;
    margin-bottom: 1.25rem;
    font-size: .85rem; color: #dc2626;
}
.mc-alert ul { margin: 0; padding-left: 1.25rem; }
.mc-page-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .5rem;
    margin-bottom: 1.5rem;
}
.mc-page-header h4 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0; }
.mc-breadcrumb { display: flex; align-items: center; gap: .375rem; list-style: none; padding: 0; margin: 0; }
.mc-breadcrumb li { font-size: .8rem; color: #94a3b8; display: flex; align-items: center; gap: .375rem; }
.mc-breadcrumb li a { color: #6366f1; text-decoration: none; }
.mc-breadcrumb li a:hover { text-decoration: underline; }
.mc-breadcrumb li:not(:last-child)::after { content: '/'; color: #cbd5e1; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">

    <div class="mc-page-header">
        <h4>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                 stroke="#6366f1" stroke-width="2.2" viewBox="0 0 24 24"
                 style="vertical-align:-3px;margin-right:8px;">
                <path d="M15 3H19a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H15"/>
                <polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            Manuel Giriş/Çıkış Kaydı
        </h4>
        <ol class="mc-breadcrumb">
            <li><a href="{{ url('/') }}">Anasayfa</a></li>
            <li><a href="{{ route('door-logs.index') }}">Kapı Giriş/Çıkış</a></li>
            <li>Manuel Kayıt</li>
        </ol>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10 col-12">
            <div class="mc-card">

                <div class="mc-header">
                    <div class="mc-header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                             stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M15 3H19a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H15"/>
                            <polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                    </div>
                    <div class="mc-header-text">
                        <h5>Kayıt Bilgileri</h5>
                        <p>Aşağıdaki alanları doldurun ve kaydedin</p>
                    </div>
                </div>

                <div class="mc-body">

                    @if($errors->any())
                        <div class="mc-alert">
                            <ul>
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('door-logs.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">

                            @if($branches->count() > 1)
                            <div class="col-md-6">
                                <div class="mc-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <path d="M3 9h18M9 21V9"/>
                                    </svg>
                                    Şube Filtrele
                                </div>
                                <select id="branchFilter" class="mc-input">
                                    <option value="">— Tüm şubeler —</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="{{ $branches->count() > 1 ? 'col-md-6' : 'col-12' }}">
                                <div class="mc-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                                    </svg>
                                    Personel <span class="dot"></span>
                                </div>
                                <select id="userSelect" name="user_id"
                                        class="mc-input {{ $errors->has('user_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">Personel seçin...</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}"
                                                data-branch="{{ $manager->branch_id }}"
                                                @selected(old('user_id') == $manager->id)>
                                            {{ $manager->name }}
                                            @if($manager->department) &mdash; {{ $manager->department->name }}@endif
                                            ({{ $manager->branch->name ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="mc-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                                             stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        </div>

                        <div class="mc-field mt-3">
                            <div class="mc-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                                     stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="17 1 21 5 17 9"/>
                                    <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                                    <polyline points="7 23 3 19 7 15"/>
                                    <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                                </svg>
                                İşlem Tipi <span class="dot"></span>
                            </div>
                            <div class="mc-type-grid">

                                <label class="mc-type-card {{ old('type', 'giris') === 'giris' ? 'is-giris-active' : '' }}" id="card-giris">
                                    <input type="radio" name="type" value="giris"
                                           @checked(old('type', 'giris') === 'giris')>
                                    <div class="mc-type-icon"
                                         style="{{ old('type', 'giris') === 'giris' ? 'background:#dcfce7' : 'background:#f1f5f9' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                                             stroke="{{ old('type', 'giris') === 'giris' ? '#16a34a' : '#94a3b8' }}"
                                             stroke-width="2" viewBox="0 0 24 24" id="icon-giris">
                                            <path d="M15 3H19a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H15"/>
                                            <polyline points="10 17 15 12 10 7"/>
                                            <line x1="15" y1="12" x2="3" y2="12"/>
                                        </svg>
                                    </div>
                                    <div class="mc-type-label">Giriş</div>
                                    <div class="mc-type-sub">Binaya giriş kaydı</div>
                                </label>

                                <label class="mc-type-card {{ old('type') === 'cikis' ? 'is-cikis-active' : '' }}" id="card-cikis">
                                    <input type="radio" name="type" value="cikis"
                                           @checked(old('type') === 'cikis')>
                                    <div class="mc-type-icon"
                                         style="{{ old('type') === 'cikis' ? 'background:#fee2e2' : 'background:#f1f5f9' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                                             stroke="{{ old('type') === 'cikis' ? '#dc2626' : '#94a3b8' }}"
                                             stroke-width="2" viewBox="0 0 24 24" id="icon-cikis">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                            <polyline points="16 17 21 12 16 7"/>
                                            <line x1="21" y1="12" x2="9" y2="12"/>
                                        </svg>
                                    </div>
                                    <div class="mc-type-label">Çıkış</div>
                                    <div class="mc-type-sub">Binadan çıkış kaydı</div>
                                </label>

                            </div>
                            @error('type')
                                <div class="mc-error mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mc-divider"></div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <div class="mc-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    Tarih &amp; Saat <span class="dot"></span>
                                </div>
                                <input type="datetime-local" name="logged_at"
                                       class="mc-input {{ $errors->has('logged_at') ? 'is-invalid' : '' }}"
                                       value="{{ old('logged_at', now()->format('Y-m-d\TH:i')) }}"
                                       required>
                                @error('logged_at')
                                    <div class="mc-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="mc-label" style="justify-content:space-between;">
                                    <span style="display:flex;align-items:center;gap:.375rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                                             stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5l3 3L12 15l-4 1 1-4z"/>
                                        </svg>
                                        Not
                                    </span>
                                    <span style="font-size:.72rem;color:#94a3b8;font-weight:400;text-transform:none;letter-spacing:0;">opsiyonel</span>
                                </div>
                                <input type="text" name="notes"
                                       class="mc-input {{ $errors->has('notes') ? 'is-invalid' : '' }}"
                                       value="{{ old('notes') }}"
                                       placeholder="Kısa açıklama...">
                                @error('notes')
                                    <div class="mc-error">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="mc-actions mt-4">
                            <button type="submit" class="mc-btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                     stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Kaydı Oluştur
                            </button>
                            <a href="{{ route('door-logs.index') }}" class="mc-btn-cancel">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none"
                                     stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
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

@push('scripts')
<script>
(function () {
    const branchFilter = document.getElementById('branchFilter');
    const userSelect   = document.getElementById('userSelect');

    if (branchFilter && userSelect) {
        const allOptions = Array.from(userSelect.options).filter(o => o.value !== '');
        branchFilter.addEventListener('change', function () {
            const sel = this.value;
            Array.from(userSelect.options).forEach(o => { if (o.value !== '') o.remove(); });
            allOptions.forEach(o => {
                if (!sel || o.dataset.branch === sel) userSelect.appendChild(o);
            });
            if (userSelect.value && ![...userSelect.options].some(o => o.value === userSelect.value)) {
                userSelect.value = '';
            }
        });
    }

    const cardGiris = document.getElementById('card-giris');
    const cardCikis = document.getElementById('card-cikis');
    const iconGiris = document.getElementById('icon-giris');
    const iconCikis = document.getElementById('icon-cikis');

    if (cardGiris && cardCikis) {
        cardGiris.addEventListener('click', function () {
            cardGiris.classList.add('is-giris-active');
            cardGiris.classList.remove('is-cikis-active');
            cardCikis.classList.remove('is-cikis-active', 'is-giris-active');
            cardGiris.querySelector('.mc-type-icon').style.background = '#dcfce7';
            cardCikis.querySelector('.mc-type-icon').style.background = '#f1f5f9';
            iconGiris.setAttribute('stroke', '#16a34a');
            iconCikis.setAttribute('stroke', '#94a3b8');
        });

        cardCikis.addEventListener('click', function () {
            cardCikis.classList.add('is-cikis-active');
            cardCikis.classList.remove('is-giris-active');
            cardGiris.classList.remove('is-giris-active', 'is-cikis-active');
            cardCikis.querySelector('.mc-type-icon').style.background = '#fee2e2';
            cardGiris.querySelector('.mc-type-icon').style.background = '#f1f5f9';
            iconCikis.setAttribute('stroke', '#dc2626');
            iconGiris.setAttribute('stroke', '#94a3b8');
        });
    }
})();
</script>
@endpush