<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body p-4">
                <h5 class="mb-3">Plan Bilgileri</h5>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Plan Adi</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $plan->name ?? '') }}" placeholder="Ornek: Foresta Sabah Personel Servis Plani">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Plan Tarihi</label>
                        <input type="date" name="plan_date" class="form-control @error('plan_date') is-invalid @enderror"
                               value="{{ old('plan_date', isset($plan->plan_date) && $plan->plan_date ? $plan->plan_date->format('Y-m-d') : '') }}">
                        @error('plan_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sube</label>
                        <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                            <option value="">Genel / Ortak</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $plan->branch_id ?? '') === (string) $branch->id)>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kalkis Noktasi Adi</label>
                        <input type="text" name="start_location_name" class="form-control @error('start_location_name') is-invalid @enderror"
                               value="{{ old('start_location_name', $plan->start_location_name ?? '') }}" placeholder="Ornek: Balmy Foresta Personel Girisi">
                        @error('start_location_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Kalkis Adresi</label>
                        <textarea name="start_address" rows="3" class="form-control @error('start_address') is-invalid @enderror"
                                  placeholder="Acik adres. Sistem bunu bulamazsa personel adreslerinin merkeziyle devam eder.">{{ old('start_address', $plan->start_address ?? '') }}</textarea>
                        @error('start_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Plan Notu</label>
                        <textarea name="planning_notes" rows="4" class="form-control @error('planning_notes') is-invalid @enderror"
                                  placeholder="Vardiya, surucu notu veya operasyonel aciklama girebilirsiniz.">{{ old('planning_notes', $plan->planning_notes ?? '') }}</textarea>
                        @error('planning_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body p-4">
                <h5 class="mb-2">Sabit Servis Tanimlari</h5>
                <div class="text-muted small mb-3">
                    Bu plan icin servisler, bu modulde tanimladiginiz aktif sabit servislerden otomatik cekilir.
                </div>

                @if($serviceDefinitions->isEmpty())
                    <div class="alert alert-warning mb-0">
                        Once ana modul ekraninda sabit servis tanimi yapmalisiniz. Ornek: Servis 1 - 16 koltuk, Servis 2 - 27 koltuk.
                    </div>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($serviceDefinitions as $serviceDefinition)
                            <div class="rounded-3 p-3" style="background:#fbf8f3;border:1px solid #eadcc9;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ $serviceDefinition->name }}</div>
                                        <div class="small text-muted">
                                            {{ $serviceDefinition->branch?->name ?? 'Genel / Ortak' }}
                                        </div>
                                    </div>
                                    <span class="badge" style="background:{{ $serviceDefinition->color ?: '#c19b77' }};">
                                        {{ $serviceDefinition->seat_capacity }} koltuk
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-3 rounded-3 p-3" style="background:#f6fbf9;border:1px solid #d6eee5;">
                    <div class="fw-semibold mb-1">Yeni Is Akisi</div>
                    <div class="small text-muted">
                        1. Sabit servisleri bir kez tanimlayin.
                        2. Plani olusturun.
                        3. Excel yukleyin veya formdan personel ekleyin.
                        4. Sistem en uygun servisi otomatik yeniden hesaplasin.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
