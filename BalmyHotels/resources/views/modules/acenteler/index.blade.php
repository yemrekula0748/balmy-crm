@extends('layouts.default')

@section('title', 'Acentelerим')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    #agenciesTable tbody tr { cursor: pointer; }
    #agenciesTable tbody tr:hover { background: rgba(193,155,119,.08) !important; }
    .badge-currency { font-size:.72rem; font-weight:600; }
    .dt-filter-row th { padding: 6px 8px !important; }
    .dt-filter-row input, .dt-filter-row select { font-size:.82rem; }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Acenteler</h4>
                <span>Acente Yönetimi — Tüm Acenteler</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Acenteler</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
        <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
        <h5 class="mb-0 fw-bold">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Acente Listesi
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $agencies->count() }}</span>
        </h5>
        <div class="d-flex gap-2">
            @if(auth()->user()->hasPermission('agencies', 'create'))
            <a href="{{ route('agencies.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Yeni Acente Tanımla
            </a>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="agenciesTable" class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Acenta Kodu</th>
                            <th>Acenta Adı</th>
                            <th>Para Birimi</th>
                            <th>Market</th>
                            <th>Ödeme Türü</th>
                            <th>Ödeme Tipi</th>
                            <th>Konaklama Tipi</th>
                            <th>Uyruklar</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agencies as $i => $agency)
                        <tr class="agency-row" data-id="{{ $agency->id }}">
                            <td class="text-muted" style="width:50px">{{ $i + 1 }}</td>
                            <td>
                                <span class="badge rounded-pill text-bg-dark fw-semibold">{{ $agency->agency_code }}</span>
                            </td>
                            <td class="fw-semibold">{{ $agency->name }}</td>
                            <td>
                                <span class="badge badge-currency
                                    @if($agency->currency === 'TL') bg-success-subtle text-success
                                    @elseif($agency->currency === 'USD') bg-primary-subtle text-primary
                                    @elseif($agency->currency === 'EUR') bg-warning-subtle text-warning
                                    @else bg-secondary-subtle text-secondary @endif">
                                    {{ \App\Models\Agency::CURRENCIES[$agency->currency] ?? $agency->currency }}
                                </span>
                            </td>
                            <td><span class="badge bg-info-subtle text-info">{{ \App\Models\Agency::MARKETS[$agency->market] ?? $agency->market }}</span></td>
                            <td><small>{{ \App\Models\Agency::PAYMENT_TYPES[$agency->payment_type] ?? $agency->payment_type }}</small></td>
                            <td><small>{{ \App\Models\Agency::PAYMENT_METHODS[$agency->payment_method] ?? $agency->payment_method }}</small></td>
                            <td>
                                <span class="badge
                                    @if($agency->accommodation_type === 'sold') bg-primary
                                    @elseif($agency->accommodation_type === 'comp') bg-warning
                                    @else bg-secondary @endif">
                                    {{ \App\Models\Agency::ACCOMMODATION_TYPES[$agency->accommodation_type] ?? $agency->accommodation_type }}
                                </span>
                            </td>
                            <td>
                                @if($agency->nationalities && count($agency->nationalities) > 0)
                                    <small class="text-muted">{{ implode(', ', array_slice($agency->nationalities, 0, 3)) }}
                                    @if(count($agency->nationalities) > 3)
                                        <span class="text-primary">+{{ count($agency->nationalities) - 3 }}</span>
                                    @endif
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if(auth()->user()->hasPermission('agencies', 'edit'))
                                <button type="button" class="btn btn-sm btn-outline-primary edit-agency-btn"
                                    data-id="{{ $agency->id }}"
                                    data-agency-code="{{ $agency->agency_code }}"
                                    data-name="{{ $agency->name }}"
                                    data-currency="{{ $agency->currency }}"
                                    data-billing-address="{{ $agency->billing_address }}"
                                    data-nationalities="{{ json_encode($agency->nationalities ?? []) }}"
                                    data-market="{{ $agency->market }}"
                                    data-payment-type="{{ $agency->payment_type }}"
                                    data-payment-method="{{ $agency->payment_method }}"
                                    data-accommodation-type="{{ $agency->accommodation_type }}"
                                    title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif
                                @if(auth()->user()->hasPermission('agencies', 'delete'))
                                <form action="{{ route('agencies.destroy', $agency) }}" method="POST" class="d-inline delete-agency-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center text-muted py-5">
                            <i class="fas fa-building fa-2x mb-2 d-block opacity-25"></i>
                            Henüz acente tanımlanmamış.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-muted mt-2 mb-0" style="font-size:.8rem"><i class="fas fa-info-circle me-1"></i> Bir kayda <strong>çift tıklayarak</strong> veya <i class="fas fa-edit"></i> ikonuna tıklayarak düzenleme yapabilirsiniz.</p>
</div>

{{-- Edit Modal --}}
@if(auth()->user()->hasPermission('agencies', 'edit'))
<div class="modal fade" id="editAgencyModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background:linear-gradient(135deg,#c19b77,#a07855);color:#fff">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-edit me-2"></i>Acente Düzenle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAgencyForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Acenta Kodu <span class="text-danger">*</span></label>
                            <input type="text" name="agency_code" id="edit_agency_code" class="form-control" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Acenta Tam İsmi <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Para Birimi <span class="text-danger">*</span></label>
                            <select name="currency" id="edit_currency" class="form-select" required>
                                <option value="TL">Türk Lirası (₺)</option>
                                <option value="USD">Dolar ($)</option>
                                <option value="EUR">Euro (€)</option>
                                <option value="GBP">Pound (£)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Market Seçimi <span class="text-danger">*</span></label>
                            <select name="market" id="edit_market" class="form-select" required>
                                <option value="domestic">İç Pazar</option>
                                <option value="europe">Avrupa Pazarı</option>
                                <option value="middle_east">Orta Doğu Pazarı</option>
                                <option value="russia">Rusya Pazarı</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Ödeme Türü <span class="text-danger">*</span></label>
                            <select name="payment_type" id="edit_payment_type" class="form-select" required>
                                <option value="agency_pay">Acente Ödeyecek</option>
                                <option value="guest_pay">Misafir Ödeyecek</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Ödeme Tipi <span class="text-danger">*</span></label>
                            <select name="payment_method" id="edit_payment_method" class="form-select" required>
                                <option value="city_ledger">Krediye Kaldır (City Ledger)</option>
                                <option value="cash">Nakit</option>
                                <option value="credit_card">Kredi Kartı</option>
                                <option value="bank_transfer">Havale</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Konaklama Tipi <span class="text-danger">*</span></label>
                            <select name="accommodation_type" id="edit_accommodation_type" class="form-select" required>
                                <option value="sold">Sold</option>
                                <option value="comp">Comp</option>
                                <option value="house_use">House Use</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Fatura Adresi <span class="text-danger">*</span></label>
                            <textarea name="billing_address" id="edit_billing_address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Varsayılan Uyruklar</label>
                            <select name="nationalities[]" id="edit_nationalities" class="form-select select2-multi" multiple>
                                @foreach(\App\Helper\DzHelper::countries() as $code => $label)
                                <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Acentenin misafir getirdiği ülkeler</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="fas fa-save me-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {

    // DataTable init with column-level filtering
    const table = $('#agenciesTable').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        orderCellsTop: true,
        fixedHeader: true,
        initComplete: function () {
            this.api().columns().every(function (colIdx) {
                if (colIdx === 0 || colIdx === 9) return; // skip # and action
                const column = this;
                const $th = $('<th>').appendTo($('#agenciesTable thead tr.dt-filter-row'));
                const $input = $('<input type="text" class="form-control form-control-sm" placeholder="Filtrele...">')
                    .appendTo($th)
                    .on('keyup change', function () {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
            });
        }
    });

    // Add filter row to thead
    $('#agenciesTable thead').prepend('<tr class="dt-filter-row"><th></th></tr>');
    // Re-init with footer filter row approach (simpler): use search inputs below each header

    // Double-click to edit
    $('#agenciesTable tbody').on('dblclick', 'tr.agency-row', function () {
        const btn = $(this).find('.edit-agency-btn');
        if (btn.length) btn.trigger('click');
    });

    // Fill edit modal
    $(document).on('click', '.edit-agency-btn', function () {
        const d = $(this).data();
        $('#editAgencyForm').attr('action', '/acenteler/' + d.id);
        $('#edit_agency_code').val(d.agencyCode);
        $('#edit_name').val(d.name);
        $('#edit_currency').val(d.currency);
        $('#edit_market').val(d.market);
        $('#edit_payment_type').val(d.paymentType);
        $('#edit_payment_method').val(d.paymentMethod);
        $('#edit_accommodation_type').val(d.accommodationType);
        $('#edit_billing_address').val(d.billingAddress);

        // Select2 nationalities
        const nats = d.nationalities || [];
        $('#edit_nationalities').val(Array.isArray(nats) ? nats : JSON.parse(nats)).trigger('change');

        const modal = new bootstrap.Modal(document.getElementById('editAgencyModal'));
        modal.show();
    });

    // Select2 init
    $('.select2-multi').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Ülke seçin...',
        allowClear: true,
        dropdownParent: $('#editAgencyModal')
    });

    // Delete confirm
    $(document).on('submit', '.delete-agency-form', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Acente silinsin mi?',
            text: 'Bu işlem geri alınamaz!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
