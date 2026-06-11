<div class="mr-card mr-filter mb-3">
    <form method="GET" action="{{ route($routeName) }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">Başlangıç</label>
            <input type="date" name="date_from" value="{{ $dateFrom->toDateString() }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Bitiş</label>
            <input type="date" name="date_to" value="{{ $dateTo->toDateString() }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Şube</label>
            <select name="branch_id" class="form-select form-select-sm">
                <option value="">Tüm görünür şubeler</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string)$branchId === (string)$branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                <i class="fas fa-filter me-1"></i> Filtrele
            </button>
            <a href="{{ route($routeName) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>
