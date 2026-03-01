@extends('layouts.default')
@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $menu->getTitle() }}</h4>
                <p class="mb-0 text-muted">/menu/{{ $menu->name }}</p>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.index') }}">QR Menüler</a></li>
                <li class="breadcrumb-item active">{{ $menu->getTitle() }}</li>
            </ol>
        </div>
    </div>

    {{-- Flash mesaj --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-4 align-items-start">

        {{-- SOL KOLON: Kategoriler & Ürünler --}}
        <div class="col-md-8">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Kategoriler &amp; Ürünler</h6>
                <a href="{{ route('qrmenus.category.create', $menu) }}"
                   class="btn btn-sm text-white" style="background:#c19b77">
                    <i class="fa fa-plus me-1"></i> Kategori Ekle
                </a>
            </div>

            @forelse($menu->categories as $category)
            <div class="card mb-3">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        @if($category->icon)
                            <span>{{ $category->icon }}</span>
                        @endif
                        <strong>{{ $category->getTitle() }}</strong>
                        @if(!$category->is_active)
                            <span class="badge bg-secondary small">Pasif</span>
                        @endif
                        <span class="badge bg-light text-dark border small">{{ $category->items->count() }} ürün</span>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('qrmenus.item.create', [$menu, $category]) }}"
                           class="btn btn-outline-success btn-sm py-0 px-2" title="Ürün ekle">
                            <i class="fa fa-plus"></i> Ürün
                        </a>
                        <a href="{{ route('qrmenus.category.edit', [$menu, $category]) }}"
                           class="btn btn-outline-warning btn-sm py-0 px-2">
                            <i class="fa fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('qrmenus.category.destroy', [$menu, $category]) }}"
                              onsubmit="return confirm('Kategori ve tüm ürünleri silinecek. Emin misiniz?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                @if($category->items->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($category->items->sortBy('sort_order') as $item)
                    <div class="list-group-item py-2">
                        <div class="d-flex align-items-center gap-2">
                            @if($item->image)
                                <img src="{{ asset('storage/'.$item->image) }}" alt=""
                                     class="rounded" style="width:44px;height:44px;object-fit:cover">
                            @else
                                <div class="rounded d-flex align-items-center justify-content-center"
                                     style="width:44px;height:44px;background:#f0e6d9;color:#8b6a4f">
                                    <i class="fa fa-image"></i>
                                </div>
                            @endif

                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="fw-semibold">{{ $item->getTitle() }}</span>
                                    @if($item->is_featured)
                                        <span class="badge" style="background:#c19b77;font-size:.65rem">⭐ Öne Çıkan</span>
                                    @endif
                                    @if($item->badges)
                                        @foreach(array_slice($item->badges, 0, 3) as $badge)
                                            <span class="badge bg-light text-dark border" style="font-size:.65rem">{{ $badge }}</span>
                                        @endforeach
                                    @endif
                                    @if(!$item->is_active)
                                        <span class="badge bg-secondary" style="font-size:.65rem">Pasif</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-end">
                                @if($item->price)
                                    <div class="fw-bold" style="color:#c19b77">
                                        {{ $item->formattedPrice($menu->currency_symbol) }}
                                    </div>
                                @endif
                                <div class="d-flex gap-1 justify-content-end mt-1">
                                    <a href="{{ route('qrmenus.item.edit', [$menu, $category, $item]) }}"
                                       class="btn btn-outline-warning btn-sm py-0 px-2">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('qrmenus.item.destroy', [$menu, $category, $item]) }}"
                                          onsubmit="return confirm('Ürün silinecek?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="card-body py-2 text-center text-muted small">
                    Henüz ürün yok.
                    <a href="{{ route('qrmenus.item.create', [$menu, $category]) }}" style="color:#c19b77">
                        İlk ürünü ekle
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-3">Henüz kategori eklenmemiş.</p>
                    <a href="{{ route('qrmenus.category.create', $menu) }}"
                       class="btn text-white" style="background:#c19b77">
                        İlk Kategoriyi Ekle
                    </a>
                </div>
            </div>
            @endforelse

        </div>

        {{-- SAĞ KOLON: Menü Bilgisi + QR + İstatistik --}}
        <div class="col-md-4">

            {{-- Menü Bilgi Kartı --}}
            <div class="card mb-4">
                <div class="card-body text-center">
                    @if($menu->logo)
                        <img src="{{ asset('storage/'.$menu->logo) }}" alt="logo"
                             class="rounded-circle mb-3" style="width:72px;height:72px;object-fit:cover">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center
                                    fw-bold fs-3 mx-auto mb-3 text-white"
                             style="width:72px;height:72px;background:#c19b77">
                            {{ strtoupper(substr($menu->name, 0, 1)) }}
                        </div>
                    @endif

                    <h6 class="mb-1">{{ $menu->getTitle() }}</h6>
                    <div class="small text-muted mb-2">
                        @if($menu->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Pasif</span>
                        @endif
                        <span class="ms-1">{{ $menu->currency }}</span>
                    </div>

                    <div class="d-flex gap-1 justify-content-center flex-wrap mb-3">
                        @foreach($menu->languages as $lang)
                            <span class="badge" style="background:#f0e6d9;color:#8b6a4f">
                                {{ $lang->flag }} {{ $lang->name }}
                                @if($lang->is_default) <small>(varsayılan)</small> @endif
                            </span>
                        @endforeach
                    </div>

                    <a href="{{ route('qrmenus.edit', $menu) }}"
                       class="btn btn-sm btn-outline-secondary w-100 mb-2">
                        <i class="fa fa-edit me-1"></i> Ayarları Düzenle
                    </a>
                    <a href="{{ route('qrmenu.show', $menu->name) }}" target="_blank"
                       class="btn btn-sm w-100 text-white" style="background:#c19b77">
                        <i class="fa fa-external-link me-1"></i> Müşteri Görünümü
                    </a>
                </div>
            </div>

            {{-- QR Kod Kartı --}}
            <div class="card mb-4">
                <div class="card-header py-3"><h6 class="mb-0">QR Kod</h6></div>
                <div class="card-body text-center">
                    <div id="qrcode" class="mx-auto mb-3" style="width:160px;height:160px"></div>
                    <div class="small text-muted text-break mb-3">{{ url('/menu/'.$menu->name) }}</div>
                    <button onclick="downloadQr()" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fa fa-download me-1"></i> QR İndir
                    </button>
                </div>
            </div>

            {{-- İstatistik Kartı --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted small">Kategori</span>
                        <strong>{{ $menu->categories->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted small">Toplam Ürün</span>
                        <strong>{{ $menu->items()->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted small">Öne Çıkan</span>
                        <strong>{{ $menu->items()->where('is_featured', true)->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Dil Sayısı</span>
                        <strong>{{ $menu->languages->count() }}</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
    var qrUrl = '{{ url('/menu/'.$menu->name) }}';
    var qr = new QRCode(document.getElementById('qrcode'), {
        text: qrUrl,
        width: 160,
        height: 160,
        colorDark: '{{ $menu->theme_color ?? "#1a1a2e" }}',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });

    window.downloadQr = function () {
        var img = document.querySelector('#qrcode img');
        if (!img) return;
        var a = document.createElement('a');
        a.href = img.src;
        a.download = '{{ $menu->name }}-qr.png';
        a.click();
    };
})();
</script>
@endpush
@endsection
