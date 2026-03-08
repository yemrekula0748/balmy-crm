@extends('layouts.default')
@section('content')
<div class="container-fluid">

    {{-- Başlık --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $session->table->name }}</h4>
                <span class="text-muted small">{{ $session->table->restaurant->name }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.take', ['restaurant_id' => $session->table->restaurant_id]) }}">Sipariş</a></li>
                <li class="breadcrumb-item active">{{ $session->table->name }}</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif

    {{-- Seans bilgi bandı --}}
    <div class="card border-0 mb-4" style="background:linear-gradient(135deg,#1e3d2b 0%,#2c4e3a 100%);border-radius:12px">
        <div class="card-body px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:46px;height:46px;background:rgba(255,255,255,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                         fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2"/>
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                        <line x1="12" y1="12" x2="12" y2="16"/>
                        <line x1="10" y1="14" x2="14" y2="14"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white fw-semibold">{{ $session->table->name }} — <span class="text-success">Açık</span></div>
                    <div style="color:rgba(255,255,255,.55);font-size:.82rem">
                        Açan: {{ $session->opener?->name ?? '—' }}
                        &nbsp;·&nbsp; {{ $session->opened_at->format('d.m.Y H:i') }}
                        &nbsp;·&nbsp; <span id="duration-badge" class="text-success fw-semibold">{{ $session->durationFormatted() }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="text-white fw-semibold">Toplam:
                    <span id="total-display">{{ number_format($session->totalAmount(), 2) }}</span> {{ $currency }}
                </span>
                @if(auth()->user()->hasPermission('orders','edit'))
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#closeModal">
                    <i class="fa fa-times me-1"></i> Masayı Kapat
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 align-items-start">
        {{-- Sol: Menü --}}
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">Menü</h6>
                    <span class="badge bg-secondary">{{ $categories->count() }} kategori</span>
                </div>
                <div class="card-body p-0">
                    @if($categories->isEmpty())
                        <div class="text-muted text-center py-5">
                            <i class="fa fa-exclamation-triangle fa-2x mb-2 d-block opacity-25"></i>
                            Bu restorana menü atanmamış veya menü içeriği boş.
                        </div>
                    @else
                    <div class="accordion accordion-flush" id="menuAccordion">
                        @foreach($categories as $catIdx => $category)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $catIdx > 0 ? 'collapsed' : '' }}"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#cat-{{ $category->id }}">
                                    {{ $category->getTitle('tr') }}
                                    <span class="badge bg-secondary ms-2">{{ $category->items->count() }}</span>
                                </button>
                            </h2>
                            <div id="cat-{{ $category->id }}" class="accordion-collapse collapse {{ $catIdx === 0 ? 'show' : '' }}">
                                <div class="accordion-body p-0">
                                    @foreach($category->items as $item)
                                    <div class="d-flex align-items-center gap-3 p-3 border-bottom menu-item-row"
                                         data-id="{{ $item->id }}"
                                         data-name="{{ $item->getTitle('tr') }}"
                                         data-price="{{ $item->effectivePrice() ?? 0 }}">
                                        {{-- Görsel --}}
                                        @if($item->image)
                                        <img src="{{ asset('storage/'.$item->image) }}" alt=""
                                             class="rounded" style="width:56px;height:56px;object-fit:cover;flex-shrink:0">
                                        @else
                                        <div class="rounded d-flex align-items-center justify-content-center text-white"
                                             style="width:56px;height:56px;background:#e8d5b7;flex-shrink:0;font-size:1.5rem">
                                            🍽
                                        </div>
                                        @endif
                                        {{-- Bilgi --}}
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $item->getTitle('tr') }}</div>
                                            @if($item->effectivePrice())
                                            <div class="text-muted small">{{ $currency }} {{ number_format($item->effectivePrice(), 2) }}</div>
                                            @else
                                            <div class="text-muted small">Fiyat yok</div>
                                            @endif
                                        </div>
                                        {{-- Miktar + Not + Ekle --}}
                                        <div class="d-flex flex-column gap-1 align-items-end" style="min-width:130px">
                                            <div class="input-group input-group-sm" style="width:100px">
                                                <button type="button" class="btn btn-outline-secondary qty-minus">−</button>
                                                <input type="number" class="form-control text-center qty-input"
                                                       value="1" min="1" max="99" style="width:40px">
                                                <button type="button" class="btn btn-outline-secondary qty-plus">+</button>
                                            </div>
                                            <input type="text" class="form-control form-control-sm note-input"
                                                   placeholder="Not (opsiyonel)" style="font-size:.75rem">
                                            <button type="button" class="btn btn-sm text-white w-100 add-to-cart-btn"
                                                    style="background:#c19b77;font-size:.78rem">
                                                <i class="fa fa-plus me-1"></i>Sepete Ekle
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sağ: Sepet + Geçmiş --}}
        <div class="col-lg-5">
            {{-- Sepet --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:#fff8f0">
                    <h6 class="mb-0"><i class="fa fa-shopping-cart me-2" style="color:#c19b77"></i>Sepet</h6>
                    <span id="cart-count" class="badge" style="background:#c19b77">0 ürün</span>
                </div>
                <div class="card-body p-0">
                    <ul id="cart-list" class="list-group list-group-flush">
                        <li id="cart-empty" class="list-group-item text-muted text-center py-4 small">Sepet boş</li>
                    </ul>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center" style="background:#fff8f0">
                    <div class="fw-bold">
                        Sepet Toplamı: <span id="cart-total">0.00</span> {{ $currency }}
                    </div>
                    <button type="button" id="submit-order-btn" class="btn btn-sm text-white px-3" style="background:#c19b77" disabled>
                        <i class="fa fa-check me-1"></i> Siparişi Kaydet
                    </button>
                </div>
            </div>

            {{-- Gönder formu (hidden) --}}
            <form id="order-form" method="POST" action="{{ route('orders.store-order', $session) }}" style="display:none">
                @csrf
                <div id="form-items-container"></div>
                <input type="hidden" name="note" id="order-note">
            </form>

            {{-- Geçmiş siparişler --}}
            @if($orders->isNotEmpty())
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">Masanın Siparişleri
                        <span class="badge bg-secondary ms-1">{{ $orders->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @foreach($orders as $order)
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                <i class="fa fa-user me-1"></i>{{ $order->creator?->name ?? '—' }}
                                &nbsp;·&nbsp; {{ $order->created_at->format('H:i') }}
                            </small>
                            <span class="badge bg-light text-dark border">
                                {{ $order->items->count() }} kalem
                            </span>
                        </div>
                        @foreach($order->items as $oi)
                        <div class="d-flex justify-content-between align-items-start small py-1">
                            <div>
                                <span class="fw-semibold">{{ $oi->item_name }}</span>
                                <span class="text-muted ms-1">×{{ $oi->quantity }}</span>
                                @if($oi->note)
                                    <div class="text-muted fst-italic" style="font-size:.75rem">📝 {{ $oi->note }}</div>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($oi->unit_price)
                                <span class="text-muted">{{ $currency }} {{ number_format($oi->lineTotal(), 2) }}</span>
                                @endif
                                @if(auth()->user()->hasPermission('orders','delete'))
                                <form method="POST" action="{{ route('orders.destroy-item', [$session, $oi]) }}"
                                      onsubmit="return confirm('Bu kalemi silmek istediğinize emin misiniz?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0" title="Sil">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Toast container --}}
<div id="cart-toast-container"
     class="toast-container position-fixed bottom-0 end-0 p-3"
     style="z-index:9999"></div>

{{-- Masa Kapat Modal --}}
<div class="modal fade" id="closeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Masayı Kapat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>{{ $session->table->name }}</strong> masasını kapatmak istediğinize emin misiniz?</p>
                <div class="alert alert-warning small mb-0">
                    Masayı kapattıktan sonra yeni sipariş eklenemez. Mevcut siparişler raporlarda görüntülenmeye devam eder.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form method="POST" action="{{ route('orders.close-table', $session) }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Evet, Masayı Kapat</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const cart = [];
    const currency = '{{ $currency }}';

    // Sepet render
    function renderCart(){
        const list  = document.getElementById('cart-list');
        const empty = document.getElementById('cart-empty');
        const count = document.getElementById('cart-count');
        const total = document.getElementById('cart-total');
        const btn   = document.getElementById('submit-order-btn');

        list.querySelectorAll('.cart-item-row').forEach(e => e.remove());

        if(cart.length === 0){
            empty.style.display = '';
            count.textContent   = '0 ürün';
            total.textContent   = '0.00';
            btn.disabled        = true;
            return;
        }
        empty.style.display = 'none';
        let sum = 0;
        cart.forEach((item, idx) => {
            const liTotal = item.price * item.qty;
            sum += liTotal;
            const li = document.createElement('li');
            li.className = 'list-group-item cart-item-row py-2';
            li.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <span class="fw-semibold small">${item.name}</span>
                        <span class="text-muted small ms-1">×${item.qty}</span>
                        ${item.note ? `<div class="text-muted" style="font-size:.72rem">📝 ${item.note}</div>` : ''}
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        ${item.price > 0 ? `<span class="small text-muted">${currency} ${liTotal.toFixed(2)}</span>` : ''}
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-cart-btn" data-idx="${idx}">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>`;
            list.appendChild(li);
        });

        count.textContent = cart.length + ' ürün';
        total.textContent = sum.toFixed(2);
        btn.disabled = false;
    }

    // Toast helper
    function showToast(message){
        const container = document.getElementById('cart-toast-container');
        const id = 'toast-' + Date.now();
        const el = document.createElement('div');
        el.id = id;
        el.className = 'toast align-items-center border-0 text-white';
        el.style.background = '#71d8b5';
        el.setAttribute('role', 'alert');
        el.setAttribute('aria-live', 'assertive');
        el.setAttribute('aria-atomic', 'true');
        el.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fa fa-check-circle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>`;
        container.appendChild(el);
        const bsToast = new bootstrap.Toast(el, {delay: 2500});
        bsToast.show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    // Sepete ekle
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            const row  = this.closest('.menu-item-row');
            const id   = parseInt(row.dataset.id);
            const name = row.dataset.name;
            const price= parseFloat(row.dataset.price) || 0;
            const qty  = parseInt(row.querySelector('.qty-input').value) || 1;
            const note = row.querySelector('.note-input').value.trim();

            // Aynı ürün+aynı not varsa Qt artır
            const existing = cart.find(i => i.id === id && i.note === note);
            if(existing){
                existing.qty += qty;
            } else {
                cart.push({id, name, price, qty, note});
            }
            row.querySelector('.qty-input').value = 1;
            row.querySelector('.note-input').value = '';
            renderCart();
            showToast(`<strong>${name}</strong> (×${qty}) sepete eklendi`);
        });
    });

    // Sepetten kaldır
    document.getElementById('cart-list').addEventListener('click', function(e){
        const btn = e.target.closest('.remove-cart-btn');
        if(!btn) return;
        cart.splice(parseInt(btn.dataset.idx), 1);
        renderCart();
    });

    // Qty +/-  butonları
    document.querySelectorAll('.qty-minus').forEach(btn => {
        btn.addEventListener('click', function(){
            const inp = this.nextElementSibling;
            if(parseInt(inp.value) > 1) inp.value = parseInt(inp.value) - 1;
        });
    });
    document.querySelectorAll('.qty-plus').forEach(btn => {
        btn.addEventListener('click', function(){
            const inp = this.previousElementSibling;
            if(parseInt(inp.value) < 99) inp.value = parseInt(inp.value) + 1;
        });
    });

    // Sipariş gönder
    document.getElementById('submit-order-btn').addEventListener('click', function(){
        if(cart.length === 0) return;
        const container = document.getElementById('form-items-container');
        container.innerHTML = '';
        cart.forEach((item, idx) => {
            container.innerHTML += `
                <input type="hidden" name="items[${idx}][qr_menu_item_id]" value="${item.id}">
                <input type="hidden" name="items[${idx}][quantity]"       value="${item.qty}">
                <input type="hidden" name="items[${idx}][note]"           value="${item.note}">`;
        });
        document.getElementById('order-form').submit();
    });

    // Süre sayacı
    let openedAt = new Date('{{ $session->opened_at->toIso8601String() }}');
    function updateDuration(){
        const now  = new Date();
        const mins = Math.floor((now - openedAt) / 60000);
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        const badge = document.getElementById('duration-badge');
        if(badge) badge.textContent = h > 0 ? `${h}s ${m}dk` : `${m}dk`;
    }
    updateDuration();
    setInterval(updateDuration, 30000);
})();
</script>
@endpush
@endsection
