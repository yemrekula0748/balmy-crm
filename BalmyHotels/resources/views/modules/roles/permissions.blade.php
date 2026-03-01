@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>
                    <span class="badge bg-{{ $role->color }}">{{ $role->display_name }}</span>
                    — Modül İzinleri
                </h4>
                <span>Hangi sayfalara erişebilir, hangi işlemleri yapabilir?</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Yetki Yönetimi</a></li>
                <li class="breadcrumb-item active">{{ $role->display_name }}</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <form method="POST" action="{{ route('roles.updatePermissions', $role) }}">
        @csrf

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">İzin Matrisi</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnAllOn">Tümünü Aç</button>
                    <button type="button" class="btn btn-sm btn-outline-danger"  id="btnAllOff">Tümünü Kapat</button>
                    <a href="{{ route('roles.index') }}" class="btn btn-sm btn-secondary">← Geri</a>
                    <button type="submit" class="btn btn-sm btn-primary">💾 Kaydet</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 align-middle" id="permMatrix">
                        <thead class="table-dark">
                            <tr>
                                <th style="min-width:200px">Modül</th>
                                <th class="text-center" style="min-width:80px">
                                    <span title="Listeleme sayfasını açabilir">Listele</span>
                                </th>
                                <th class="text-center" style="min-width:80px">
                                    <span title="Detay/görüntüleme sayfasını açabilir">Görüntüle</span>
                                </th>
                                <th class="text-center" style="min-width:80px">
                                    <span title="Yeni kayıt ekleyebilir">Ekle</span>
                                </th>
                                <th class="text-center" style="min-width:80px">
                                    <span title="Mevcut kaydı düzenleyebilir">Düzenle</span>
                                </th>
                                <th class="text-center" style="min-width:80px">
                                    <span title="Kaydı silebilir">Sil</span>
                                </th>
                                <th class="text-center" style="min-width:80px">Tümü</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($modules as $groupName => $moduleList)
                            {{-- Grup başlığı --}}
                            <tr class="table-secondary">
                                <td colspan="7" class="fw-bold text-uppercase small ps-3">
                                    {{ $groupName }}
                                </td>
                            </tr>

                            @foreach($moduleList as $moduleKey => $moduleLabel)
                            @php
                                $p = $permissions[$moduleKey] ?? null;
                            @endphp
                            <tr>
                                <td class="ps-4">{{ $moduleLabel }}</td>

                                @foreach(['index' => 'Listele', 'show' => 'Görüntüle', 'create' => 'Ekle', 'edit' => 'Düzenle', 'delete' => 'Sil'] as $action => $label)
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input perm-cb"
                                               type="checkbox"
                                               name="perms[{{ $moduleKey }}][{{ $action }}]"
                                               value="1"
                                               data-module="{{ $moduleKey }}"
                                               @checked($p && $p->{"can_{$action}"})>
                                    </div>
                                </td>
                                @endforeach

                                {{-- Satır seç/kaldır --}}
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-outline-primary row-toggle"
                                            data-module="{{ $moduleKey }}"
                                            title="Satırı aç/kapat">
                                        ↔
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary px-5">💾 İzinleri Kaydet</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Tümünü aç / kapat
document.getElementById('btnAllOn').addEventListener('click', function () {
    document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = true);
});
document.getElementById('btnAllOff').addEventListener('click', function () {
    document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = false);
});

// Satır toggle (bir modülün tüm izinleri)
document.querySelectorAll('.row-toggle').forEach(btn => {
    btn.addEventListener('click', function () {
        const module = this.dataset.module;
        const cbs = document.querySelectorAll(`.perm-cb[data-module="${module}"]`);
        const allChecked = [...cbs].every(cb => cb.checked);
        cbs.forEach(cb => cb.checked = !allChecked);
    });
});
</script>
@endpush
@endsection
