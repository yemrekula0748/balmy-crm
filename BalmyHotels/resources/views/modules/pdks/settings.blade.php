@extends('layouts.default')

@section('content')
@include('modules.pdks._styles')
<div class="container-fluid">
    <div class="pdks-hero mb-3"><div class="pdks-hero-bar"></div><div class="p-4"><h4 class="mb-1">PDKS Ayarları</h4><div class="pdks-muted">GPS, WiFi SSID/BSSID ve sahte konum kontrol politikaları</div></div></div>
    @include('modules.pdks._nav')
    @include('modules.pdks._flash')
    <div class="row g-3">
        @if(auth()->user()->hasPermission('pdks_settings','edit'))
        <div class="col-lg-4">
            <div class="pdks-card p-3">
                <h6 class="fw-bold">Doğrulama Politikası</h6>
                <form method="POST" action="{{ route('pdks.settings.policy.store') }}" class="row g-2">
                    @csrf
                    <div class="col-12"><input name="name" class="form-control form-control-sm" placeholder="Balmy Foresta Ana Politika" required></div>
                    <div class="col-12"><select name="branch_id" class="form-select form-select-sm"><option value="">Genel</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
                    <div class="col-6"><input name="allowed_latitude" class="form-control form-control-sm" placeholder="Latitude"></div>
                    <div class="col-6"><input name="allowed_longitude" class="form-control form-control-sm" placeholder="Longitude"></div>
                    <div class="col-6"><input type="number" name="allowed_radius_meters" class="form-control form-control-sm" value="200" required></div>
                    <div class="col-6"><input type="number" name="max_accuracy_meters" class="form-control form-control-sm" placeholder="Max hassasiyet"></div>
                    <div class="col-12"><textarea name="allowed_wifi_ssids" rows="2" class="form-control form-control-sm" placeholder="SSID listesi, satır satır"></textarea></div>
                    <div class="col-12"><textarea name="allowed_wifi_bssids" rows="2" class="form-control form-control-sm" placeholder="BSSID listesi, satır satır"></textarea></div>
                    <div class="col-12 small"><label class="me-3"><input type="checkbox" name="require_gps" value="1" checked> GPS zorunlu</label><label class="me-3"><input type="checkbox" name="require_wifi" value="1"> WiFi doğrula</label><label><input type="checkbox" name="block_mock_location" value="1" checked> Mock GPS blokla</label></div>
                    <div class="col-12"><button class="btn btn-sm pdks-btn-main w-100">Kaydet</button></div>
                </form>
            </div>
        </div>
        @endif
        <div class="{{ auth()->user()->hasPermission('pdks_settings','edit') ? 'col-lg-8' : 'col-12' }}">
            <div class="pdks-card">
                <table class="table pdks-table align-middle mb-0"><thead><tr><th>Ad</th><th>Şube</th><th>GPS</th><th>WiFi</th><th>Yarıçap</th><th>Mock</th><th>Durum</th></tr></thead><tbody>
                @foreach($policies as $policy)
                    <tr><td>{{ $policy->name }}</td><td>{{ $policy->branch->name ?? 'Genel' }}</td><td>{{ $policy->require_gps ? 'Zorunlu' : 'Opsiyonel' }}</td><td>{{ $policy->require_wifi ? 'Zorunlu' : 'Opsiyonel' }}</td><td>{{ $policy->allowed_radius_meters }} m</td><td>{{ $policy->block_mock_location ? 'Blokla' : 'Uyar' }}</td><td>{{ $policy->is_active ? 'Aktif' : 'Pasif' }}</td></tr>
                @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
</div>
@endsection
