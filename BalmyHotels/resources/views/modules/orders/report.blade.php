@extends('layouts.default')
@section('content')
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Sipariş Raporları</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.take') }}">Sipariş</a></li>
                <li class="breadcrumb-item active">Raporlar</li>
            </ol>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('orders.report') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Restoran</label>
                    <select name="restaurant_id" class="form-select form-select-sm">
                        <option value="">— Tümü —</option>
                        @foreach($restaurants as $r)
                            <option value="{{ $r->id }}" @selected(request('restaurant_id') == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Durum</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">— Tümü —</option>
                        <option value="open"   @selected(request('status') === 'open')>Açık Masalar</option>
                        <option value="closed" @selected(request('status') === 'closed')>Kapalı Masalar</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Başlangıç</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Bitiş</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm text-white px-4" style="background:#c19b77">Filtrele</button>
                    <a href="{{ route('orders.report') }}" class="btn btn-sm btn-outline-secondary">Temizle</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tablo --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Masa Seansları</h6>
            <span class="badge bg-secondary">{{ $sessions->total() }} kayıt</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Restoran / Masa</th>
                            <th>Açan</th>
                            <th>Açılış</th>
                            <th>Kapanış</th>
                            <th>Süre</th>
                            <th>Ürünler</th>
                            <th class="text-end">Toplam</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $s)
                        @php
                            $items = $s->allItems()->get();
                            $grouped = $items->groupBy('item_name');
                            $total   = $items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity);
                            $currency = $s->table->restaurant->qrMenu?->currency_symbol ?? '₺';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $s->table->restaurant->name }}</div>
                                <div class="text-muted small">{{ $s->table->name }}</div>
                            </td>
                            <td>
                                <div class="small">{{ $s->opener?->name ?? '—' }}</div>
                            </td>
                            <td class="small">{{ $s->opened_at->format('d.m.Y H:i') }}</td>
                            <td class="small">{{ $s->closed_at?->format('d.m.Y H:i') ?? '—' }}</td>
                            <td class="small fw-semibold">{{ $s->durationFormatted() }}</td>
                            <td>
                                @if($items->isEmpty())
                                    <span class="text-muted small">—</span>
                                @else
                                <div style="max-width:200px">
                                    @foreach($grouped as $name => $group)
                                    <span class="badge bg-light text-dark border me-1 mb-1" style="font-size:.72rem">
                                        {{ $name }} ×{{ $group->sum('quantity') }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
                            </td>
                            <td class="text-end fw-semibold">
                                @if($total > 0)
                                    {{ $currency }} {{ number_format($total, 2) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($s->is_open)
                                    <span class="badge bg-success">Açık</span>
                                @else
                                    <span class="badge bg-secondary">Kapalı</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                Kayıt bulunamadı.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sessions->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
