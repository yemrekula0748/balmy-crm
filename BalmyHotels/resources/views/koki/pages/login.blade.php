@extends('layouts.fullwidth')

@section('content')
    @php($loginMode = old('login_type', old('email') ? 'email' : 'personnel'))

    <style>
        .login-methods { display:flex;gap:8px;padding:5px;background:#f4f6f8;border-radius:10px;margin-bottom:22px; }
        .login-method { flex:1;border:0;background:transparent;border-radius:8px;padding:10px 8px;color:#64748b;font-size:13px;font-weight:700;transition:.2s; }
        .login-method.active { background:#fff;color:#c19b77;box-shadow:0 2px 8px rgba(15,23,42,.09); }
        .login-help { color:#64748b;font-size:12px;line-height:1.5;margin-top:7px; }
    </style>

    <div class="col-md-6">
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="auth-form">
                        <div class="text-center mb-3">
                            <a href="{{ url('/') }}">
                                <img src="{{ asset('images/logo.svg') }}" alt="Balmy Hotels" style="height:55px;">
                            </a>
                        </div>
                        <h4 class="text-center mb-4">Hesabınıza Giriş Yapın</h4>

                        <div class="login-methods" role="tablist" aria-label="Giriş yöntemi">
                            <button type="button" class="login-method @if($loginMode === 'personnel') active @endif" data-login-mode="personnel">
                                <i class="fa fa-id-card mr-1"></i> Personel Girişi
                            </button>
                            <button type="button" class="login-method @if($loginMode === 'email') active @endif" data-login-mode="email">
                                <i class="fa fa-envelope mr-1"></i> Yönetici Girişi
                            </button>
                        </div>

                        <form action="{{ route('login') }}" method="POST" id="login-form">
                            @csrf
                            <input type="hidden" name="login_type" id="login-type" value="{{ $loginMode }}">

                            <div data-login-fields="personnel" @if($loginMode !== 'personnel') style="display:none" @endif>
                                <div class="form-group">
                                    <label class="mb-1"><strong>TC Kimlik Numarası</strong></label>
                                    <input type="text" name="identity_no" class="form-control"
                                        value="{{ old('identity_no') }}" inputmode="numeric" autocomplete="username"
                                        maxlength="11" @required($loginMode === 'personnel') @disabled($loginMode !== 'personnel')>
                                </div>
                                <div class="form-group">
                                    <label class="mb-1"><strong>Telefon Numarası</strong></label>
                                    <input type="tel" name="phone" class="form-control"
                                        value="{{ old('phone') }}" inputmode="tel" autocomplete="tel"
                                        maxlength="30" @required($loginMode === 'personnel') @disabled($loginMode !== 'personnel')>
                                    <div class="login-help">Telefonunuzu 05…, 5…, +9… veya +90… biçiminde yazabilirsiniz.</div>
                                </div>
                            </div>

                            <div data-login-fields="email" @if($loginMode !== 'email') style="display:none" @endif>
                                <div class="form-group">
                                    <label class="mb-1"><strong>E-posta</strong></label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email') }}" autocomplete="username"
                                        @required($loginMode === 'email') @disabled($loginMode !== 'email')>
                                </div>
                                <div class="form-group position-relative">
                                    <label class="mb-1"><strong>Şifre</strong></label>
                                    <input type="password" name="password" id="dz-password" class="form-control"
                                        autocomplete="current-password"
                                        @required($loginMode === 'email') @disabled($loginMode !== 'email')>
                                    <span class="show-pass eye">
                                        <i class="fa fa-eye-slash"></i>
                                        <i class="fa fa-eye"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="form-row d-flex justify-content-between mt-4 mb-2">
                                <div class="form-group">
                                    <div class="form-check custom-checkbox ms-1">
                                        <input type="checkbox" name="remember" class="form-check-input" id="remember_me" @checked(old('remember'))>
                                        <label class="custom-control-label" for="remember_me">Beni Hatırla</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
                            </div>
                            <div class="text-center mt-4">
                                <div style="font-size:14px;font-weight:700;color:#c19b77;">Core Resort Management</div>
                                <div style="font-size:13px;color:#64748b;margin-top:2px;">Temel Otel Operasyon Yönetimi</div>
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
document.addEventListener('DOMContentLoaded', function () {
    const modeInput = document.getElementById('login-type');
    const modeButtons = document.querySelectorAll('[data-login-mode]');
    const fieldGroups = document.querySelectorAll('[data-login-fields]');

    function activateMode(mode) {
        modeInput.value = mode;
        modeButtons.forEach(button => button.classList.toggle('active', button.dataset.loginMode === mode));
        fieldGroups.forEach(group => {
            const active = group.dataset.loginFields === mode;
            group.style.display = active ? '' : 'none';
            group.querySelectorAll('input').forEach(input => {
                input.disabled = !active;
                input.required = active;
            });
        });
    }

    modeButtons.forEach(button => {
        button.addEventListener('click', function () {
            activateMode(this.dataset.loginMode);
            const firstInput = document.querySelector(`[data-login-fields="${this.dataset.loginMode}"] input`);
            if (firstInput) firstInput.focus();
        });
    });

    activateMode(modeInput.value);
});
</script>

@if($errors->any() || session('error'))
<link rel="stylesheet" href="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.css') }}">
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Giriş Başarısız',
        text: @json($errors->first() ?: session('error')),
        confirmButtonText: 'Tamam',
        confirmButtonColor: '#c19b77'
    });
});
</script>
@endif
@endpush
