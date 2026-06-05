@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="fas fa-check-circle"></i>{{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="fas fa-exclamation-circle"></i>{{ session('error') }}
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <strong>Kontrol gerekli:</strong> {{ $errors->first() }}
    </div>
@endif
