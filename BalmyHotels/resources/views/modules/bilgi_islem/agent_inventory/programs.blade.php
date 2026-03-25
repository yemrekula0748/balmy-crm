@extends('layouts.default')

@section('title', $agentComputer->hostname . ' — Kurulu Programlar')

@section('content')
<div class="container-fluid pb-4">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>{{ $agentComputer->hostname }}</h4>
                <span>Kurulu Programlar</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.index') }}">Ajan Envanter</a></li>
                <li class="breadcrumb-item"><a href="{{ route('it.agent.show', $agentComputer) }}">{{ $agentComputer->hostname }}</a></li>
                <li class="breadcrumb-item active">Programlar</li>
            </ol>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-list-ul me-2 text-primary"></i>
            Kurulu Programlar
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $programs->total() }}</span>
        </h5>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex">
                <div class="input-group input-group-sm" style="width:260px">
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="Program veya yayıncı ara...">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <a href="{{ route('it.agent.show', $agentComputer) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Detay
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-sm mb-0">
                    <thead style="background:linear-gradient(135deg,#f8f9ff,#eef0ff);">
                        <tr>
                            <th class="ps-4 py-3 small text-muted">#</th>
                            <th class="py-3 small text-muted">PROGRAM ADI</th>
                            <th class="py-3 small text-muted">VERSİYON</th>
                            <th class="py-3 small text-muted">YAYINEVİ</th>
                            <th class="py-3 small text-muted">KURULUM TARİHİ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($programs as $prog)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $programs->firstItem() + $loop->index }}</td>
                            <td class="fw-semibold small">{{ $prog->name }}</td>
                            <td class="small text-muted">{{ $prog->version ?? '—' }}</td>
                            <td class="small text-muted">{{ $prog->publisher ?? '—' }}</td>
                            <td class="small text-muted">{{ $prog->install_date ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted small">Kayıt bulunamadı.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($programs->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $programs->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
