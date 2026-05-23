@extends('layouts.default')

@section('title', 'Etkinlik Oluştur')

@push('styles')
<style>
.module-card { background:#fff;border:1px solid #e8eef5;border-radius:16px;box-shadow:0 5px 20px rgba(15,23,42,.05); }
.soft-hero { background:#fff;border:1px solid #e8eef5;border-left:5px solid #1e3a5f;border-radius:16px;padding:22px 24px;margin-bottom:18px;box-shadow:0 6px 22px rgba(15,23,42,.06); }
.soft-hero h4 { margin:0 0 5px;font-weight:850;color:#172033; }
.soft-hero p { margin:0;color:#64748b;line-height:1.6; }
.dynamic-row { display:flex;gap:10px;margin-bottom:9px; }
.dynamic-row .form-control { min-width:0; }
.date-chip { display:inline-flex;align-items:center;gap:8px;background:#f1f5f9;border:1px solid #e2e8f0;color:#334155;border-radius:999px;padding:7px 10px;font-size:.82rem;font-weight:700;margin:0 8px 8px 0; }
.date-chip button { border:0;background:transparent;color:#ef4444;font-weight:900;line-height:1;padding:0; }
.help-box { background:#f8fafc;border:1px dashed #cbd5e1;border-radius:13px;padding:13px 15px;color:#64748b;font-size:.82rem;line-height:1.6; }
</style>
@endpush

@section('content')
@php
    $oldParticipants = old('participants', ['']);
    $oldDates = old('dates', []);
@endphp

<div class="container-fluid pb-5">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="mb-0">Etkinlik Oluştur</h4>
                <span class="text-muted" style="font-size:.8rem">Katılımcı ve çoklu tarih planlama</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('animation.events.index') }}">Animasyon</a></li>
                <li class="breadcrumb-item active">Yeni Etkinlik</li>
            </ol>
        </div>
    </div>

    <div class="soft-hero">
        <h4>Show/Animasyon Etkinlik Planı</h4>
        <p>Etkinlik adını, katılımcı isimlerini ve etkinliğin yapılacağı tüm tarihleri seçin. Tarihler ardışık olmak zorunda değildir.</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        Lütfen formdaki eksik veya hatalı alanları kontrol edin.
    </div>
    @endif

    <form method="POST" action="{{ route('animation.events.store') }}" class="module-card p-4">
        @csrf

        <div class="row g-4">
            <div class="col-lg-5">
                <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                    <option value="">Şube seçin</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) old('branch_id', $autoBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-lg-7">
                <label class="form-label fw-semibold">Etkinlik Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Örn: Akşam Show Ekibi" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-lg-6">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label fw-semibold mb-0">Etkinlik Katılımcı İsimleri <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-participant">
                        <i class="fas fa-plus me-1"></i> Ekle
                    </button>
                </div>
                <div id="participant-list">
                    @foreach($oldParticipants as $participant)
                    <div class="dynamic-row">
                        <input type="text" name="participants[]" value="{{ $participant }}" class="form-control" placeholder="Katılımcı adı soyadı">
                        <button type="button" class="btn btn-outline-danger remove-row">Sil</button>
                    </div>
                    @endforeach
                </div>
                @error('participants')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                <div class="help-box mt-3">İstediğiniz kadar katılımcı ekleyebilirsiniz. Aynı isim tekrar yazılırsa kayıt sırasında tekilleştirilir.</div>
            </div>

            <div class="col-lg-6">
                <label class="form-label fw-semibold">Etkinlik Tarihleri <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="date" id="date-picker" class="form-control">
                    <button type="button" class="btn btn-outline-primary" id="add-date">
                        <i class="fas fa-calendar-plus me-1"></i> Tarih Ekle
                    </button>
                </div>
                <div id="selected-dates" class="mt-3"></div>
                @error('dates')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                <div class="help-box mt-3">Takvimden bir tarih seçip “Tarih Ekle”ye basın. Birden fazla ve ardışık olmayan tarih seçilebilir.</div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('animation.events.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Kaydet
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const participantList = document.getElementById('participant-list');
    const addParticipant = document.getElementById('add-participant');
    const datePicker = document.getElementById('date-picker');
    const addDate = document.getElementById('add-date');
    const selectedDatesWrap = document.getElementById('selected-dates');
    let selectedDates = @json(array_values($oldDates));

    function bindRemoveButtons() {
        participantList.querySelectorAll('.remove-row').forEach(button => {
            button.onclick = function () {
                if (participantList.querySelectorAll('.dynamic-row').length > 1) {
                    this.closest('.dynamic-row').remove();
                } else {
                    this.closest('.dynamic-row').querySelector('input').value = '';
                }
            };
        });
    }

    function renderDates() {
        selectedDates = [...new Set(selectedDates)].sort();
        selectedDatesWrap.innerHTML = '';
        selectedDates.forEach(date => {
            const chip = document.createElement('span');
            chip.className = 'date-chip';
            chip.innerHTML = `<span>${date.split('-').reverse().join('.')}</span><button type="button" aria-label="Sil">&times;</button><input type="hidden" name="dates[]" value="${date}">`;
            chip.querySelector('button').addEventListener('click', function () {
                selectedDates = selectedDates.filter(item => item !== date);
                renderDates();
            });
            selectedDatesWrap.appendChild(chip);
        });
    }

    addParticipant.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'dynamic-row';
        row.innerHTML = '<input type="text" name="participants[]" class="form-control" placeholder="Katılımcı adı soyadı"><button type="button" class="btn btn-outline-danger remove-row">Sil</button>';
        participantList.appendChild(row);
        bindRemoveButtons();
        row.querySelector('input').focus();
    });

    addDate.addEventListener('click', function () {
        if (!datePicker.value) return;
        selectedDates.push(datePicker.value);
        datePicker.value = '';
        renderDates();
    });

    bindRemoveButtons();
    renderDates();
});
</script>
@endpush
