@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Arıza Türü Düzenle</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('faults.types.index') }}">Arıza Türleri</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $type->name }}</h5></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form action="{{ route('faults.types.update', $type) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Arıza Türü Adı</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $type->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Hedef Tamamlanma Süresi (Saat)</label>
                            <input type="number" name="completion_hours" class="form-control"
                                   value="{{ old('completion_hours', $type->completion_hours) }}" min="1" max="720" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şube</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Tüm Şubeler</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected($type->branch_id == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DEPARTMAN KİLITLENMEE --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Hangi Departmanlar Kullanabilir?</label>
                            <div class="small text-muted mb-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Hiçbir departman seçilmezse <strong>tüm departmanlar</strong> bu arıza türünü girebilir.
                            </div>
                            @if($departments->isEmpty())
                                <div class="alert alert-warning py-2 small mb-0">
                                    Henüz hiç departman tanımlanmamış.
                                </div>
                            @else
                                <div class="border rounded p-3" style="max-height:220px;overflow-y:auto;background:#f8f9fa">
                                    @foreach($departments as $dept)
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox"
                                                   name="department_ids[]" value="{{ $dept->id }}"
                                                   id="dept_{{ $dept->id }}"
                                                   @checked(in_array($dept->id, old('department_ids', $selectedDepts)))>
                                            <label class="form-check-label" for="dept_{{ $dept->id }}">
                                                {{ $dept->name }}
                                                @if($dept->branch)
                                                    <span class="text-muted small">({{ $dept->branch->name }})</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                                       @checked(old('is_active', $type->is_active))>
                                <label class="form-check-label" for="isActive">Aktif</label>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Güncelle</button>
                            <a href="{{ route('faults.types.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const branchSel   = document.getElementById('branchSelect');
    const deptItems   = document.querySelectorAll('.dept-item');
    const deptList    = document.getElementById('deptList');
    const deptNoMatch = document.getElementById('deptNoMatch');
    if (!branchSel || !deptItems.length) return;

    function filterDepts() {
        const bid = branchSel.value;
        let visible = 0;
        deptItems.forEach(item => {
            const match = !bid || item.dataset.branch === bid;
            item.style.display = match ? '' : 'none';
            if (!match) item.querySelector('input').checked = false;
            if (match) visible++;
        });
        if (deptList) deptList.style.display = visible ? '' : 'none';
        if (deptNoMatch) deptNoMatch.classList.toggle('d-none', visible > 0 || !bid);
    }

    branchSel.addEventListener('change', filterDepts);
    filterDepts();
})();
</script>
@endpush
