@extends('layouts.default')

@section('title', 'Oda Tipleri')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
@endpush

@section('content')
<div class="container-fluid pb-4">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Oda Tipleri</h4>
                <span>Önbüro — Oda Tipi Yönetimi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Önbüro</span></li>
                <li class="breadcrumb-item active">Oda Tipleri</li>
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
            Oda Tipleri
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $roomTypes->count() }}</span>
        </h5>
        @if(auth()->user()->hasPermission('room_types', 'create'))
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomTypeModal">
            <i class="fas fa-plus me-1"></i> Yeni Oda Tipi
        </button>
        @endif
    </div>

    {{-- Oda Tipi Kartları --}}
    <div class="row g-3 mb-4">
        @forelse($roomTypes as $rt)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c19b77 !important">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <span class="badge bg-dark fw-semibold mb-1">{{ $rt->code }}</span>
                            <h6 class="fw-bold mb-0">{{ $rt->name }}</h6>
                        </div>
                        <div class="d-flex gap-1">
                            @if(auth()->user()->hasPermission('room_types', 'edit'))
                            <button class="btn btn-sm btn-outline-primary edit-rt-btn"
                                data-id="{{ $rt->id }}"
                                data-name="{{ $rt->name }}"
                                data-code="{{ $rt->code }}"
                                data-total-rooms="{{ $rt->total_rooms }}"
                                data-max-adults="{{ $rt->max_adults }}"
                                data-max-babies="{{ $rt->max_babies }}"
                                data-max-children="{{ $rt->max_children }}"
                                data-room-ids="{{ json_encode($rt->rooms->pluck('id')->toArray()) }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endif
                            @if(auth()->user()->hasPermission('room_types', 'delete'))
                            <form action="{{ route('frontdesk.room-types.destroy', $rt) }}" method="POST" class="d-inline delete-rt-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2 rounded" style="background:rgba(193,155,119,.1)">
                                <div class="fw-bold fs-5" style="color:#c19b77">{{ $rt->rooms_count }}</div>
                                <div class="text-muted" style="font-size:.7rem">ODA</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded bg-primary-subtle">
                                <div class="fw-bold fs-5 text-primary">{{ $rt->max_adults }}</div>
                                <div class="text-muted" style="font-size:.7rem">MAX YETİŞKİN</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded bg-warning-subtle">
                                <div class="fw-bold fs-5 text-warning">{{ $rt->max_children }}</div>
                                <div class="text-muted" style="font-size:.7rem">MAX ÇOCUK</div>
                            </div>
                        </div>
                    </div>

                    @if($rt->rooms->count() > 0)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted d-block mb-1">Bu tipteki odalar:</small>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($rt->rooms->take(6) as $room)
                            <span class="badge bg-light text-dark border">{{ $room->room_number }}</span>
                            @endforeach
                            @if($rt->rooms->count() > 6)
                            <span class="badge bg-secondary">+{{ $rt->rooms->count() - 6 }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-layer-group fa-3x text-muted opacity-25 mb-3"></i>
                    <p class="text-muted">Henüz oda tipi tanımlanmamış.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

</div>

{{-- Add Modal --}}
@if(auth()->user()->hasPermission('room_types', 'create'))
<div class="modal fade" id="addRoomTypeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background:linear-gradient(135deg,#c19b77,#a07855);color:#fff">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Yeni Oda Tipi Ekle</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('frontdesk.room-types.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Oda Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="ör: Standart Çift Kişilik" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Oda Kodu <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="ör: STD-DBL" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Oda Sayısı <span class="text-danger">*</span></label>
                            <input type="number" name="total_rooms" class="form-control" value="0" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Max Yetişkin <span class="text-danger">*</span></label>
                            <input type="number" name="max_adults" class="form-control" value="2" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Max Bebek <span class="text-danger">*</span></label>
                            <input type="number" name="max_babies" class="form-control" value="0" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Max Çocuk <span class="text-danger">*</span></label>
                            <input type="number" name="max_children" class="form-control" value="0" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Bu Tipe Ait Odalar</label>
                            <select name="room_ids[]" class="form-select select2-rooms-add" multiple>
                                @foreach($allRooms as $room)
                                <option value="{{ $room->id }}">
                                    {{ $room->room_number }}
                                    @if($room->roomType) ({{ $room->roomType->code }}) @endif
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Birden fazla oda seçebilirsiniz. Mevcut başka tipe atanmış odalar bu tipten çıkarılır.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn fw-semibold" style="background:#c19b77;color:#fff">
                        <i class="fas fa-save me-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Edit Modal --}}
@if(auth()->user()->hasPermission('room_types', 'edit'))
<div class="modal fade" id="editRoomTypeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background:linear-gradient(135deg,#c19b77,#a07855);color:#fff">
                <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Oda Tipi Düzenle</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editRoomTypeForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Oda Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_rt_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Oda Kodu <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="edit_rt_code" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Oda Sayısı <span class="text-danger">*</span></label>
                            <input type="number" name="total_rooms" id="edit_rt_total" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Max Yetişkin <span class="text-danger">*</span></label>
                            <input type="number" name="max_adults" id="edit_rt_adults" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Max Bebek <span class="text-danger">*</span></label>
                            <input type="number" name="max_babies" id="edit_rt_babies" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Max Çocuk <span class="text-danger">*</span></label>
                            <input type="number" name="max_children" id="edit_rt_children" class="form-control" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Bu Tipe Ait Odalar</label>
                            <select name="room_ids[]" id="edit_rt_rooms" class="form-select select2-rooms-edit" multiple>
                                @foreach($allRooms as $room)
                                <option value="{{ $room->id }}">
                                    {{ $room->room_number }}
                                    @if($room->roomType) ({{ $room->roomType->code }}) @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn fw-semibold" style="background:#c19b77;color:#fff">
                        <i class="fas fa-save me-1"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    $('.select2-rooms-add').select2({
        theme: 'bootstrap-5', width: '100%',
        placeholder: 'Oda seçin...', allowClear: true,
        dropdownParent: $('#addRoomTypeModal')
    });
    $('.select2-rooms-edit').select2({
        theme: 'bootstrap-5', width: '100%',
        placeholder: 'Oda seçin...', allowClear: true,
        dropdownParent: $('#editRoomTypeModal')
    });

    $(document).on('click', '.edit-rt-btn', function () {
        const d = $(this).data();
        $('#editRoomTypeForm').attr('action', '/onburo/oda-tipleri/' + d.id);
        $('#edit_rt_name').val(d.name);
        $('#edit_rt_code').val(d.code);
        $('#edit_rt_total').val(d.totalRooms);
        $('#edit_rt_adults').val(d.maxAdults);
        $('#edit_rt_babies').val(d.maxBabies);
        $('#edit_rt_children').val(d.maxChildren);
        const ids = d.roomIds || [];
        $('#edit_rt_rooms').val(Array.isArray(ids) ? ids : JSON.parse(ids)).trigger('change');
        new bootstrap.Modal(document.getElementById('editRoomTypeModal')).show();
    });

    $(document).on('submit', '.delete-rt-form', function (e) {
        e.preventDefault(); const form = this;
        Swal.fire({
            title: 'Oda tipi silinsin mi?', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sil', cancelButtonText: 'İptal'
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
@endsection
