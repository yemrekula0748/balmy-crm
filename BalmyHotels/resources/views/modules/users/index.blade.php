@extends('layouts.default')

@push('styles')
<style>
/* ── Hero ───────────────────────────────────────────────────── */
.page-hero {
    background: #fff;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,.07);
    border: 1px solid rgba(0,0,0,.05);
    overflow: hidden;
    display: flex;
    align-items: stretch;
}
.page-hero-stripe { width:6px;flex-shrink:0;background:linear-gradient(180deg,#4361ee,#3a0ca3);border-radius:16px 0 0 16px; }
.page-hero-icon   { width:68px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:linear-gradient(135deg,#4361ee,#3a0ca3);font-size:1.55rem;color:#fff; }
.page-hero-body   { flex:1;padding:18px 22px;min-width:0; }
.page-hero-body h3 { font-size:1.15rem;font-weight:700;color:#1f2937;margin:0 0 3px;letter-spacing:-.01em; }
.page-hero-body p  { margin:0;font-size:.82rem;color:#6b7280; }
.page-hero-actions { display:flex;align-items:center;gap:10px;padding:0 20px;flex-shrink:0;border-left:1px solid #f3f4f6; }
/* ── Stat chips ─────────────────────────────────────────────── */
.stat-chips { display:flex;gap:12px;flex-wrap:wrap;margin-bottom:22px; }
.stat-chip { background:#fff;border-radius:12px;padding:14px 20px;box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.05);display:flex;align-items:center;gap:12px;flex:1;min-width:140px; }
.stat-chip .sc-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0; }
.stat-chip .sc-num  { font-size:1.45rem;font-weight:700;line-height:1; }
.stat-chip .sc-lbl  { font-size:.78rem;color:#6b7280;margin-top:2px; }
/* ── Filter card ────────────────────────────────────────────── */
.filter-card { background:#fff;border-radius:14px;padding:16px 20px;box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.05);margin-bottom:20px; }
.filter-card .form-control,.filter-card .form-select { border:1.5px solid #e5e7eb;border-radius:9px;font-size:.83rem;color:#1f2937;padding:8px 12px;height:auto;transition:border-color .2s,box-shadow .2s;background:#fff; }
.filter-card .form-control:focus,.filter-card .form-select:focus { border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.15);outline:none; }
/* ── Users card ─────────────────────────────────────────────── */
.users-card { background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.07);border:1px solid rgba(0,0,0,.05);overflow:hidden; }
.users-card thead tr { background:#f8f9fb; }
.users-card thead th { font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;padding:13px 16px;border-bottom:1.5px solid #f3f4f6;white-space:nowrap; }
.users-card tbody td { padding:13px 16px;vertical-align:middle;border-bottom:1px solid #f9fafb;font-size:.86rem; }
.users-card tbody tr:last-child td { border-bottom:none; }
.users-card tbody tr:hover { background:#fafbff; }
/* ── Avatar ─────────────────────────────────────────────────── */
.u-avatar { width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.88rem;flex-shrink:0;color:#fff;overflow:hidden; }
.u-avatar img { width:38px;height:38px;border-radius:10px;object-fit:cover; }
/* ── Role pill ──────────────────────────────────────────────── */
.role-pill { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:6px;font-size:.72rem;font-weight:700;white-space:nowrap;margin:1px; }
/* ── Action buttons ─────────────────────────────────────────── */
.act-btn { display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:7px;border:none;font-size:.78rem;cursor:pointer;transition:all .15s;text-decoration:none; }
.act-btn-edit   { background:rgba(67,97,238,.1);color:#4361ee; }
.act-btn-edit:hover { background:#4361ee;color:#fff; }
.act-btn-delete { background:rgba(220,53,69,.1);color:#dc3545; }
.act-btn-delete:hover { background:#dc3545;color:#fff; }
.btn-add { background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;border:none;border-radius:9px;padding:9px 18px;font-size:.84rem;font-weight:600;display:inline-flex;align-items:center;gap:6px;box-shadow:0 3px 10px rgba(67,97,238,.35);transition:opacity .15s;text-decoration:none; }
.btn-add:hover { opacity:.88;color:#fff; }
.empty-state { padding:56px 24px;text-align:center;color:#9ca3af; }
.empty-state .es-icon { font-size:2.5rem;margin-bottom:12px;opacity:.25;display:block; }
.desktop-users-table { display:block; }
.mobile-user-list { display:none; }
.mobile-user-card {
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
}
.mobile-user-card:last-child {
    border-bottom: none;
}
.mobile-user-meta {
    font-size: .78rem;
    color: #6b7280;
    margin-top: 8px;
}
.mobile-user-actions {
    display: flex;
    gap: 8px;
    margin-top: 14px;
}
.mobile-user-actions .btn {
    flex: 1;
    justify-content: center;
}
@media (max-width: 767px) {
    .page-hero {
        flex-direction: column;
    }
    .page-hero-stripe {
        width: 100%;
        height: 6px;
        border-radius: 16px 16px 0 0;
    }
    .page-hero-icon {
        width: 100%;
        height: 72px;
    }
    .page-hero-actions {
        border-left: 0;
        border-top: 1px solid #f3f4f6;
        padding: 16px 18px 18px;
        justify-content: stretch;
    }
    .page-hero-actions .btn-add {
        width: 100%;
        justify-content: center;
    }
    .filter-card {
        padding: 16px;
    }
    .filter-card form .col-md-3 {
        width: 100%;
    }
    .filter-card form .col-md-3.d-flex {
        flex-wrap: wrap;
    }
    .filter-card form .col-md-3.d-flex .btn {
        flex: 1 1 calc(50% - 4px);
        justify-content: center;
    }
    .filter-card form .col-md-3.d-flex span {
        width: 100%;
        margin-left: 0 !important;
        margin-top: 4px;
    }
    .desktop-users-table {
        display: none;
    }
    .mobile-user-list {
        display: block;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb --}}
    <div class="row page-titles mx-0 mb-0">
        <div class="col-sm-6 p-md-0"><div class="welcome-text"><h4>Kullanıcı Yönetimi</h4></div></div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Kullanıcılar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Hero --}}
    <div class="page-hero">
        <div class="page-hero-stripe"></div>
        <div class="page-hero-icon"><i class="fas fa-users"></i></div>
        <div class="page-hero-body">
            <h3>Çalışanlar & Roller</h3>
            <p>Sistemdeki kullanıcıları, rollerini ve şube bağlantılarını yönetin</p>
        </div>
        <div class="page-hero-actions">
            <a href="{{ route('users.create') }}" class="btn-add">
                <i class="fas fa-plus"></i>Yeni Kullanıcı
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stat-chips">
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(67,97,238,.12);color:#4361ee"><i class="fas fa-users"></i></div>
            <div>
                <div class="sc-num">{{ \App\Models\User::count() }}</div>
                <div class="sc-lbl">Toplam</div>
            </div>
        </div>
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(16,185,129,.12);color:#059669"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="sc-num" style="color:#059669">{{ \App\Models\User::where('is_active',true)->count() }}</div>
                <div class="sc-lbl">Aktif</div>
            </div>
        </div>
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(156,163,175,.12);color:#9ca3af"><i class="fas fa-user-slash"></i></div>
            <div>
                <div class="sc-num" style="color:#9ca3af">{{ \App\Models\User::where('is_active',false)->count() }}</div>
                <div class="sc-lbl">Pasif</div>
            </div>
        </div>
        <div class="stat-chip">
            <div class="sc-icon" style="background:rgba(193,155,119,.12);color:#c19b77"><i class="fas fa-building"></i></div>
            <div>
                <div class="sc-num" style="color:#c19b77">{{ \App\Models\Branch::count() }}</div>
                <div class="sc-lbl">Şube</div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-building me-1"></i>Şube
                </label>
                <select name="branch_id" class="form-select">
                    <option value="">— Tüm Şubeler —</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-shield-alt me-1"></i>Rol
                </label>
                <select name="role" class="form-select">
                    <option value="">— Tüm Roller —</option>
                    @foreach($roles as $role_item)
                        <option value="{{ $role_item->name }}" @selected(request('role') == $role_item->name)>{{ $role_item->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#6b7280">
                    <i class="fas fa-search me-1"></i>Arama
                </label>
                <input type="text" name="search" class="form-control"
                    value="{{ request('search') }}" placeholder="İsim veya e-posta…">
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-sm"
                    style="background:#4361ee;color:#fff;border-radius:9px;padding:9px 18px;font-size:.83rem;font-weight:600;border:none">
                    <i class="fas fa-search me-1"></i>Ara
                </button>
                @if(request()->hasAny(['branch_id','role','search']))
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:9px;padding:9px 14px">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
                <span class="ms-auto" style="font-size:.78rem;color:#9ca3af;white-space:nowrap">
                    {{ $users->total() }} sonuç
                </span>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="users-card">
        <div class="table-responsive desktop-users-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Kullanıcı</th>
                    <th>Unvan / E-posta</th>
                    <th>Rol</th>
                    <th>Şube</th>
                    <th>Departman</th>
                    <th>Telefon</th>
                    <th>Durum</th>
                    <th class="text-end" style="padding-right:20px">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    @php
                        $initials = collect(explode(' ', $u->name))
                            ->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))
                            ->take(2)->implode('');
                        $avatarColors = ['#4361ee','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#c19b77'];
                        $avatarColor  = $avatarColors[abs(crc32($u->name)) % count($avatarColors)];
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="u-avatar" style="background:linear-gradient(135deg,{{ $avatarColor }},{{ $avatarColor }}bb)">
                                    @if($u->avatar)
                                        <img src="{{ asset('storage/'.$u->avatar) }}" alt="{{ $u->name }}">
                                    @else
                                        {{ $initials }}
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight:700;color:#1f2937;font-size:.88rem;line-height:1.2">{{ $u->name }}</div>
                                    @if($u->id === auth()->id())
                                        <span style="font-size:.67rem;background:rgba(67,97,238,.1);color:#4361ee;padding:1px 6px;border-radius:4px;font-weight:700">Sen</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($u->title)<div style="font-size:.82rem;font-weight:600;color:#374151">{{ $u->title }}</div>@endif
                            <div style="font-size:.78rem;color:#9ca3af">{{ $u->email }}</div>
                        </td>
                        <td>
                            @foreach($u->userRoles as $ur)
                                @php $roleObj = $roles->firstWhere('name', $ur->role_name); @endphp
                                <span class="role-pill"
                                    style="background:{{ ($roleObj->color ?? '#6c757d') }}1a;color:{{ $roleObj->color ?? '#6c757d' }};border:1px solid {{ ($roleObj->color ?? '#6c757d') }}33">
                                    {{ $roleObj->display_name ?? $ur->role_name }}
                                </span>
                            @endforeach
                        </td>
                        <td style="font-size:.83rem;color:#374151">{{ optional($u->branch)->name ?? '—' }}</td>
                        <td style="font-size:.83rem;color:#374151">{{ optional($u->department)->name ?? '—' }}</td>
                        <td style="font-size:.82rem;color:#6b7280">{{ $u->phone ?? '—' }}</td>
                        <td>
                            @if($u->is_active)
                                <span style="display:inline-flex;align-items:center;gap:4px;font-size:.77rem;font-weight:700;color:#059669;background:rgba(16,185,129,.1);padding:3px 9px;border-radius:6px">
                                    <i class="fas fa-check-circle"></i>Aktif
                                </span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:4px;font-size:.77rem;font-weight:700;color:#9ca3af;background:rgba(156,163,175,.1);padding:3px 9px;border-radius:6px">
                                    <i class="fas fa-times-circle"></i>Pasif
                                </span>
                            @endif
                        </td>
                        <td class="text-end" style="padding-right:16px">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('users.edit', $u) }}" class="act-btn act-btn-edit" title="Düzenle">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $u) }}" method="POST"
                                          class="d-inline" data-user-del="{{ $u->name }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="act-btn act-btn-delete" title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-users es-icon"></i>
                                <div style="font-weight:600;color:#374151;margin-bottom:4px">Kullanıcı bulunamadı</div>
                                <div style="font-size:.82rem">Filtreleri değiştirin veya yeni kullanıcı ekleyin.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="mobile-user-list">
            @forelse($users as $u)
                @php
                    $initials = collect(explode(' ', $u->name))
                        ->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))
                        ->take(2)->implode('');
                    $avatarColors = ['#4361ee','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#c19b77'];
                    $avatarColor  = $avatarColors[abs(crc32($u->name)) % count($avatarColors)];
                @endphp
                <div class="mobile-user-card">
                    <div class="d-flex align-items-start gap-3">
                        <div class="u-avatar" style="background:linear-gradient(135deg,{{ $avatarColor }},{{ $avatarColor }}bb)">
                            @if($u->avatar)
                                <img src="{{ asset('storage/'.$u->avatar) }}" alt="{{ $u->name }}">
                            @else
                                {{ $initials }}
                            @endif
                        </div>
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="d-flex justify-content-between gap-2 align-items-start">
                                <div>
                                    <div style="font-weight:700;color:#1f2937;font-size:.95rem;line-height:1.2">{{ $u->name }}</div>
                                    @if($u->title)
                                        <div class="mobile-user-meta" style="margin-top:2px">{{ $u->title }}</div>
                                    @endif
                                </div>
                                @if($u->is_active)
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:700;color:#059669;background:rgba(16,185,129,.1);padding:4px 8px;border-radius:999px">
                                        <i class="fas fa-check-circle"></i>Aktif
                                    </span>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:700;color:#9ca3af;background:rgba(156,163,175,.1);padding:4px 8px;border-radius:999px">
                                        <i class="fas fa-times-circle"></i>Pasif
                                    </span>
                                @endif
                            </div>
                            <div class="mobile-user-meta">{{ $u->email }}</div>
                            <div class="mobile-user-meta">
                                {{ optional($u->branch)->name ?? 'Şube yok' }} · {{ optional($u->department)->name ?? 'Departman yok' }}
                            </div>
                            <div class="mt-2">
                                @foreach($u->userRoles as $ur)
                                    @php $roleObj = $roles->firstWhere('name', $ur->role_name); @endphp
                                    <span class="role-pill"
                                        style="background:{{ ($roleObj->color ?? '#6c757d') }}1a;color:{{ $roleObj->color ?? '#6c757d' }};border:1px solid {{ ($roleObj->color ?? '#6c757d') }}33">
                                        {{ $roleObj->display_name ?? $ur->role_name }}
                                    </span>
                                @endforeach
                            </div>
                            <div class="mobile-user-actions">
                                <a href="{{ route('users.edit', $u) }}" class="btn btn-sm act-btn-edit">
                                    <i class="fas fa-pencil-alt me-1"></i>Düzenle
                                </a>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $u) }}" method="POST" class="flex-grow-1" data-user-del="{{ $u->name }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm act-btn-delete w-100">
                                            <i class="fas fa-trash me-1"></i>Sil
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-users es-icon"></i>
                    <div style="font-weight:600;color:#374151;margin-bottom:4px">KullanÄ±cÄ± bulunamadÄ±</div>
                    <div style="font-size:.82rem">Filtreleri deÄŸiÅŸtirin veya yeni kullanÄ±cÄ± ekleyin.</div>
                </div>
            @endforelse
        </div>

        @if($users->hasPages())
            <div style="padding:14px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:center">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script>
document.querySelectorAll('[data-user-del]').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Kullanıcıyı sil?',
            html: `<b>${this.dataset.userDel}</b> silinecek. Bu işlem geri alınamaz.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i>Evet, Sil',
            cancelButtonText: 'İptal',
        }).then(r => { if (r.isConfirmed) this.submit(); });
    });
});
</script>
@endpush
