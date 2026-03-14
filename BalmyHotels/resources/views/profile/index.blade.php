@extends('layouts.default')

@section('title', 'Profilim')

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h4 class="text-primary">Profilim</h4>
        </div>
        <div class="col-md-7 align-self-center text-end">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb justify-content-end">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Ana Sayfa</a></li>
                    <li class="breadcrumb-item active">Profilim</li>
                </ol>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Sol: Profil kartı -->
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-body text-center pt-4 pb-4">
                    <div class="profile-photo position-relative d-inline-block mb-3">
                        <img id="avatar-preview"
                             src="{{ $user->avatar ? asset($user->avatar) : asset('images/profile/12.png') }}"
                             class="rounded-circle"
                             style="width:110px;height:110px;object-fit:cover;border:3px solid var(--primary);"
                             alt="{{ $user->name }}">
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="mb-1 text-muted">{{ $user->email }}</p>
                    @if($user->title)
                        <span class="badge badge-primary">{{ $user->title }}</span>
                    @endif
                    @if($user->branch)
                        <p class="mt-2 mb-0 text-muted small"><i class="fa fa-building me-1"></i>{{ $user->branch->name ?? '' }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sağ: Form -->
        <div class="col-xl-8 col-lg-7">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- ===== Profil Bilgileri ===== --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-user me-2 text-primary"></i>Profil Bilgileri
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Profil Fotoğrafı --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Profil Fotoğrafı</label>
                            <div class="d-flex align-items-center gap-3">
                                <img id="avatar-preview-form"
                                     src="{{ $user->avatar ? asset($user->avatar) : asset('images/profile/12.png') }}"
                                     class="rounded-circle"
                                     style="width:70px;height:70px;object-fit:cover;border:2px solid #dee2e6;"
                                     alt="">
                                <div class="flex-grow-1">
                                    <input type="file" name="avatar" id="avatar-input"
                                           class="form-control @error('avatar') is-invalid @enderror"
                                           accept="image/jpg,image/jpeg,image/png,image/webp">
                                    <div class="form-text">JPG, PNG veya WEBP — en fazla 2 MB</div>
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Telefon --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Telefon Numarası</label>
                            <input type="text" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="+90 5xx xxx xx xx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ===== Şifre Değiştir ===== --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-lock me-2 text-warning"></i>Şifre Değiştir
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Şifrenizi değiştirmek istemiyorsanız bu alanları boş bırakın.</p>

                        <div class="mb-3 position-relative">
                            <label class="form-label fw-semibold">Mevcut Şifre</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   placeholder="Mevcut şifrenizi girin">
                            <span class="show-pass eye" data-target="current_password">
                                <i class="fa fa-eye-slash"></i>
                                <i class="fa fa-eye"></i>
                            </span>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="form-label fw-semibold">Yeni Şifre</label>
                            <input type="password" name="password" id="new_password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="En az 8 karakter">
                            <span class="show-pass eye" data-target="new_password">
                                <i class="fa fa-eye-slash"></i>
                                <i class="fa fa-eye"></i>
                            </span>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="form-label fw-semibold">Yeni Şifre (Tekrar)</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control"
                                   placeholder="Yeni şifreyi tekrar girin">
                            <span class="show-pass eye" data-target="password_confirmation">
                                <i class="fa fa-eye-slash"></i>
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end pb-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-save me-1"></i> Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Avatar önizleme
    document.getElementById('avatar-input').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        document.getElementById('avatar-preview').src      = url;
        document.getElementById('avatar-preview-form').src = url;
    });

    // Şifre göster/gizle: data-target ile input ID alınır
    document.querySelectorAll('.show-pass[data-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var inputId = btn.getAttribute('data-target');
            var input   = document.getElementById(inputId);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.classList.toggle('active');
        });
    });
</script>
@endpush
