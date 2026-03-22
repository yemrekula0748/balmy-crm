@extends('layouts.default')

@section('title', 'Odalar')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    .modern-dt { border-collapse: separate !important; border-spacing: 0 5px !important; }
    .modern-dt thead th { border: none !important; font-size: 11.5px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; white-space: nowrap; background: transparent; padding: 6px 12px 10px; }
    .modern-dt tbody tr { background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.06); transition: box-shadow .15s, transform .1s; }
    .modern-dt tbody tr:hover { background: #fff !important; box-shadow: 0 3px 12px rgba(0,0,0,.12) !important; transform: translateY(-1px); }
    .modern-dt tbody tr td { border: none !important; vertical-align: middle; padding: 10px 12px; }
    .modern-dt tbody tr td:first-child { border-radius: 10px 0 0 10px; }
    .modern-dt tbody tr td:last-child  { border-radius: 0 10px 10px 0; }
    .room-thumb { width:44px;height:34px;object-fit:cover;border-radius:5px;border:1px solid #dee2e6;cursor:pointer;transition:transform .15s; }
    .room-thumb:hover { transform:scale(1.1); }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Odalar</h4>
                <span>Önbüro — Oda Yönetimi</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Önbüro</span></li>
                <li class="breadcrumb-item active">Odalar</li>
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

    <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
        <h5 class="mb-0 fw-bold">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Oda Listesi
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $rooms->count() }}</span>
        </h5>
        @if(auth()->user()->hasPermission('rooms', 'create'))
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="fas fa-plus me-1"></i> Yeni Oda Ekle
        </button>
        @endif
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="px-3 py-2 border-bottom bg-white d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size:.8rem">Göster</span>
                <select id="roomsTable-len" class="form-select form-select-sm" style="width:72px">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-muted" style="font-size:.8rem">kayıt</span>
            </div>
            <input type="text" id="roomsTable-search" class="form-control form-control-sm" placeholder="Ara..." style="max-width:220px">
        </div>
        <div class="table-responsive px-2 pt-1">
            <table id="roomsTable" class="table modern-dt align-middle mb-0 w-100">
                <thead>
                        <tr>
                            <th>Oda No</th>
                            <th>Oda Tipi</th>
                            <th>Kat</th>
                            <th>Blok</th>
                            <th>Yatak Tipleri</th>
                            <th>Ek Özellikler</th>
                            <th>Resimler</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                        <tr>
                            <td>
                                <span class="badge bg-dark fw-bold fs-6">{{ $room->room_number }}</span>
                            </td>
                            <td>
                                @if($room->roomType)
                                <span class="badge bg-primary-subtle text-primary">[{{ $room->roomType->code }}] {{ $room->roomType->name }}</span>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ $room->floor ?? '—' }}</span></td>
                            <td>{{ $room->block ?? '—' }}</td>
                            <td>
                                @foreach($room->bedTypes as $bt)
                                <span class="badge bg-info-subtle text-info me-1">{{ $bt->abbreviation }}</span>
                                @endforeach
                            </td>
                            <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($room->extra_features, 40) }}</small></td>
                            <td>
                            @if($room->images && count($room->images) > 0)
                            <div class="d-flex gap-1 flex-wrap">
                                @foreach(array_slice($room->images, 0, 3) as $img)
                                <img src="{{ asset('uploads/' . $img) }}" alt="" class="room-thumb"
                                     data-bs-toggle="tooltip" title="Büyütmek için tıklayın">
                                @endforeach
                                @if(count($room->images) > 3)
                                <span class="badge bg-secondary align-self-center">+{{ count($room->images) - 3 }}</span>
                                @endif
                            </div>
                            @else
                            <span class="text-muted" style="font-size:.78rem"><i class="fas fa-image me-1 opacity-25"></i>Yok</span>
                            @endif
                        </td>
                            <td>
                                @if(auth()->user()->hasPermission('rooms', 'edit'))
                                <button class="btn btn-sm btn-outline-primary edit-room-btn"
                                    data-id="{{ $room->id }}"
                                    data-room-number="{{ $room->room_number }}"
                                    data-room-type-id="{{ $room->room_type_id }}"
                                    data-floor="{{ $room->floor }}"
                                    data-block="{{ $room->block }}"
                                    data-extra-features="{{ $room->extra_features }}"
                                    data-description="{{ $room->description }}"
                                    data-bed-type-ids="{{ json_encode($room->bedTypes->pluck('id')->toArray()) }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif
                                @if(auth()->user()->hasPermission('rooms', 'delete'))
                                <form action="{{ route('frontdesk.rooms.destroy', $room) }}" method="POST" class="d-inline delete-room-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-door-open fa-2x mb-2 d-block opacity-25"></i>
                            Henüz oda eklenmemiş.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
        </div>
        <div class="px-3 py-2 border-top bg-white d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <small class="text-muted" id="roomsTable-info"></small>
            <nav><ul class="pagination pagination-sm mb-0" id="roomsTable-pagin"></ul></nav>
        </div>
    </div>
</div>

{{-- Add Room Modal --}}
@if(auth()->user()->hasPermission('rooms', 'create'))
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background:linear-gradient(135deg,#c19b77,#a07855);color:#fff">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Yeni Oda Ekle</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('frontdesk.rooms.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Oda No <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" placeholder="ör: 101" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Oda Tipi</label>
                            <select name="room_type_id" class="form-select">
                                <option value="">— Seçiniz —</option>
                                @foreach($roomTypes as $rt)
                                <option value="{{ $rt->id }}">[{{ $rt->code }}] {{ $rt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Kaçıncı Kat</label>
                            <input type="text" name="floor" class="form-control" placeholder="ör: 1, Zemin, -1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Blok</label>
                            <input type="text" name="block" class="form-control" placeholder="ör: A Blok">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Yatak Tipleri</label>
                            <select name="bed_type_ids[]" class="form-select select2-bed-add" multiple>
                                @foreach($bedTypes as $bt)
                                <option value="{{ $bt->id }}">{{ $bt->name }} ({{ $bt->abbreviation }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ek Özellikler</label>
                            <textarea name="extra_features" class="form-control" rows="2" placeholder="Balkon, deniz manzarası, jakuzi..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Oda Açıklaması</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Oda hakkında detaylı açıklama..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Oda Resimleri</label>
                            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted">Birden fazla resim seçebilirsiniz. Max 5MB/resim.</small>
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

{{-- Edit Room Modal --}}
@if(auth()->user()->hasPermission('rooms', 'edit'))
<div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background:linear-gradient(135deg,#c19b77,#a07855);color:#fff">
                <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Oda Düzenle</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editRoomForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Oda No <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" id="edit_room_number" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Oda Tipi</label>
                            <select name="room_type_id" id="edit_room_type" class="form-select">
                                <option value="">— Seçiniz —</option>
                                @foreach($roomTypes as $rt)
                                <option value="{{ $rt->id }}">[{{ $rt->code }}] {{ $rt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Kaçıncı Kat</label>
                            <input type="text" name="floor" id="edit_room_floor" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Blok</label>
                            <input type="text" name="block" id="edit_room_block" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Yatak Tipleri</label>
                            <select name="bed_type_ids[]" id="edit_room_beds" class="form-select select2-bed-edit" multiple>
                                @foreach($bedTypes as $bt)
                                <option value="{{ $bt->id }}">{{ $bt->name }} ({{ $bt->abbreviation }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ek Özellikler</label>
                            <textarea name="extra_features" id="edit_room_extra" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Oda Açıklaması</label>
                            <textarea name="description" id="edit_room_desc" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Yeni Resim Ekle</label>
                            <input type="file" name="new_images[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted">Mevcut resimlere ek olarak yeni resim eklenecektir.</small>
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
<script src="{{ asset('js/modern-table.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    modernTable('roomsTable', { pageLength: 25 });

    // Tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    $('.select2-bed-add').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Yatak tipi...', allowClear: true, dropdownParent: $('#addRoomModal') });
    $('.select2-bed-edit').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Yatak tipi...', allowClear: true, dropdownParent: $('#editRoomModal') });

    $(document).on('click', '.edit-room-btn', function () {
        const d = $(this).data();
        $('#editRoomForm').attr('action', '/onburo/odalar/' + d.id);
        $('#edit_room_number').val(d.roomNumber);
        $('#edit_room_type').val(d.roomTypeId);
        $('#edit_room_floor').val(d.floor);
        $('#edit_room_block').val(d.block);
        $('#edit_room_extra').val(d.extraFeatures);
        $('#edit_room_desc').val(d.description);
        const ids = d.bedTypeIds || [];
        $('#edit_room_beds').val(Array.isArray(ids) ? ids : JSON.parse(ids)).trigger('change');
        new bootstrap.Modal(document.getElementById('editRoomModal')).show();
    });

    $(document).on('submit', '.delete-room-form', function (e) {
        e.preventDefault(); const form = this;
        Swal.fire({
            title: 'Oda silinsin mi?', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sil', cancelButtonText: 'İptal'
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
@endsection
