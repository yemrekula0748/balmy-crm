@extends('layouts.default')

@section('title', 'Yeni Oltalama Testi')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-8 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-user-shield me-2 text-primary"></i>Yeni Oltalama Farkındalık Testi</h4>
                <span class="text-muted">Hedef çalışanları seçin; her biri için ayrı bağlantı üretilecek.</span>
            </div>
        </div>
        <div class="col-sm-4 p-md-0 d-flex justify-content-sm-end align-items-center mt-2 mt-sm-0">
            <a href="{{ route('it.phishing.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Geri
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-warning border-0 shadow-sm">
        <strong>Yetkili kullanım:</strong> Kampanyayı yalnızca şirketçe onaylanmış güvenlik farkındalık çalışmaları için kullanın.
        Sistem parola veya kullanıcı adı kaydetmez. Gerçek Microsoft giriş ekranı ya da dış Microsoft betikleri kullanılmaz.
    </div>

    <form method="POST" action="{{ route('it.phishing.store') }}" id="campaignForm">
        @csrf
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <label for="campaignName" class="form-label fw-semibold">Kampanya adı</label>
                <input id="campaignName" type="text" name="name" value="{{ old('name') }}"
                       class="form-control" maxlength="150" required
                       placeholder="Örn. Ağustos 2026 E-posta Güvenliği Testi">
                <div class="form-text">Kampanya oluşturulduğunda hemen aktif olur. Tamamlandıktan sonra rapor ekranından kapatabilirsiniz.</div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Hedef çalışanlar</h5>
                    <small class="text-muted">Yalnızca aktif ve gerçek e-posta adresi bulunan hesaplar gösterilir.</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-primary" id="selectedCount">0 seçili</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectVisible">Görünenleri seç</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelection">Temizle</button>
                </div>
            </div>
            <div class="card-body border-bottom py-3">
                <input type="search" class="form-control" id="userSearch"
                       placeholder="Ad, e-posta, şube veya departman ara..." autocomplete="off">
            </div>
            <div class="table-responsive" style="max-height:520px;overflow:auto">
                <table class="table table-hover align-middle mb-0" id="userTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-4" style="width:52px"></th>
                            <th>Çalışan</th>
                            <th>E-posta</th>
                            <th>Şube</th>
                            <th>Departman</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php $checked = in_array($user->id, old('user_ids', [])); @endphp
                            <tr class="user-row" data-search="{{ Illuminate\Support\Str::lower($user->name . ' ' . $user->email . ' ' . ($user->branch?->name ?? '') . ' ' . ($user->department?->name ?? '')) }}">
                                <td class="ps-4">
                                    <input class="form-check-input user-check" type="checkbox" name="user_ids[]"
                                           value="{{ $user->id }}" id="user-{{ $user->id }}" @checked($checked)>
                                </td>
                                <td><label class="fw-semibold mb-0" for="user-{{ $user->id }}">{{ $user->name }}</label></td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->branch?->name ?? '—' }}</td>
                                <td>{{ $user->department?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">Uygun kullanıcı bulunamadı.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
                <a href="{{ route('it.phishing.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-link me-1"></i>Kampanyayı ve bağlantıları oluştur
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('userSearch');
    const rows = Array.from(document.querySelectorAll('.user-row'));
    const checks = Array.from(document.querySelectorAll('.user-check'));
    const count = document.getElementById('selectedCount');

    function updateCount() {
        const selected = checks.filter(input => input.checked).length;
        count.textContent = selected + ' seçili';
    }

    search.addEventListener('input', function () {
        const term = this.value.toLocaleLowerCase('tr-TR').trim();
        rows.forEach(row => {
            row.style.display = !term || row.dataset.search.includes(term) ? '' : 'none';
        });
    });

    document.getElementById('selectVisible').addEventListener('click', function () {
        rows.filter(row => row.style.display !== 'none').forEach(row => {
            row.querySelector('.user-check').checked = true;
        });
        updateCount();
    });

    document.getElementById('clearSelection').addEventListener('click', function () {
        checks.forEach(input => input.checked = false);
        updateCount();
    });

    checks.forEach(input => input.addEventListener('change', updateCount));
    updateCount();
});
</script>
@endpush
