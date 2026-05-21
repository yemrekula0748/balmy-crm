@extends('layouts.default')
@section('title', 'Egitim Icerikleri')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Egitim ve Gelisim</h4>
                <span>Video egitim icerikleri</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Egitim Icerikleri</li>
            </ol>
        </div>
    </div>

    @include('modules.education._tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius:8px">
        <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="language" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Tum Diller</option>
                    @foreach($languages as $code => $label)
                        <option value="{{ $code }}" @selected($language === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            @if(auth()->user()->hasPermission('education_courses','create'))
                <a href="{{ route('education.courses.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Yeni Egitim
                </a>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Baslik</th>
                            <th>Dil</th>
                            <th>Egitmen</th>
                            <th class="text-center">Atama</th>
                            <th class="text-center">Durum</th>
                            <th class="text-end">Islem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $course->title }}</div>
                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($course->description, 90) }}</small>
                                </td>
                                <td><span class="badge bg-secondary">{{ $course->language_label }}</span></td>
                                <td>{{ $course->trainer->name ?? '-' }}</td>
                                <td class="text-center">{{ $course->assignments_count }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $course->is_active ? 'bg-success' : 'bg-light text-dark' }}">
                                        {{ $course->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if(auth()->user()->hasPermission('education_courses','show'))
                                        <a href="{{ route('education.courses.show', $course) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    @endif
                                    @if(auth()->user()->hasPermission('education_courses','edit'))
                                        <a href="{{ route('education.courses.edit', $course) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                    @endif
                                    @if(auth()->user()->hasPermission('education_courses','delete'))
                                        <form method="POST" action="{{ route('education.courses.destroy', $course) }}" class="d-inline" onsubmit="return confirm('Bu egitimi silmek istiyor musunuz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Egitim icerigi bulunamadi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($courses->hasPages())
            <div class="card-footer bg-white">{{ $courses->links() }}</div>
        @endif
    </div>
</div>
@endsection
