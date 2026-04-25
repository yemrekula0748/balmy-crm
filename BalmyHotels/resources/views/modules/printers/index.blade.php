@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-print me-2 text-primary"></i>Yazıcılar</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item active">Yazıcılar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- YENİ YAZICI EKLE --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-plus me-2 text-primary"></i>Yeni Yazıcı Ekle</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('printers.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">Şube seçin...</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Yazıcı Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name') }}" placeholder="ör. Mutfak Yazıcısı" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">IP Adresi <span class="text-danger">*</span></label>
                            <input type="text" name="ip_address" class="form-control"
                                   value="{{ old('ip_address') }}" placeholder="ör. 192.168.1.100" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Karakter Seti (Codepage) <span class="text-danger">*</span></label>
                            <select name="codepage" class="form-select" required>
                                <option value="0"  @selected(old('codepage',32)==0)>0 — PC437 (USA / Standart)</option>
                                <option value="1"  @selected(old('codepage',32)==1)>1 — Katakana</option>
                                <option value="2"  @selected(old('codepage',32)==2)>2 — PC850 (Multilingual)</option>
                                <option value="3"  @selected(old('codepage',32)==3)>3 — PC860 (Portekizce)</option>
                                <option value="4"  @selected(old('codepage',32)==4)>4 — PC863 (Kanada Fransızcası)</option>
                                <option value="5"  @selected(old('codepage',32)==5)>5 — PC865 (Nordik)</option>
                                <option value="6"  @selected(old('codepage',32)==6)>6 — PC851</option>
                                <option value="7"  @selected(old('codepage',32)==7)>7 — PC852 (Latin 2)</option>
                                <option value="8"  @selected(old('codepage',32)==8)>8 — PC858 (Multilingual+Euro)</option>
                                <option value="9"  @selected(old('codepage',32)==9)>9 — PC866 (Kiril)</option>
                                <option value="10" @selected(old('codepage',32)==10)>10 — PC928 (Yunan)</option>
                                <option value="11" @selected(old('codepage',32)==11)>11 — PC770</option>
                                <option value="12" @selected(old('codepage',32)==12)>12 — PC857 (DOS Turkish)</option>
                                <option value="13" @selected(old('codepage',32)==13)>13 — PC737 (Yunan)</option>
                                <option value="14" @selected(old('codepage',32)==14)>14 — ISO8859-7 (Yunan)</option>
                                <option value="15" @selected(old('codepage',32)==15)>15 — WPC1252 (Batı Avrupa)</option>
                                <option value="16" @selected(old('codepage',32)==16)>16 — PC866 (Kiril)</option>
                                <option value="17" @selected(old('codepage',32)==17)>17 — PC852 (Latin 2)</option>
                                <option value="18" @selected(old('codepage',32)==18)>18 — PC858</option>
                                <option value="19" @selected(old('codepage',32)==19)>19 — Thai42</option>
                                <option value="20" @selected(old('codepage',32)==20)>20 — Thai11</option>
                                <option value="21" @selected(old('codepage',32)==21)>21 — Thai13</option>
                                <option value="22" @selected(old('codepage',32)==22)>22 — Thai14</option>
                                <option value="23" @selected(old('codepage',32)==23)>23 — Thai16</option>
                                <option value="24" @selected(old('codepage',32)==24)>24 — Thai17</option>
                                <option value="25" @selected(old('codepage',32)==25)>25 — Thai18</option>
                                <option value="26" @selected(old('codepage',32)==26)>26 — TCVN-3 (Vietnamca)</option>
                                <option value="27" @selected(old('codepage',32)==27)>27 — PC720 (Arapça)</option>
                                <option value="28" @selected(old('codepage',32)==28)>28 — WPC775 (Baltik)</option>
                                <option value="29" @selected(old('codepage',32)==29)>29 — PC855 (Kiril)</option>
                                <option value="30" @selected(old('codepage',32)==30)>30 — PC861 (İzlandaca)</option>
                                <option value="31" @selected(old('codepage',32)==31)>31 — PC862 (İbranice)</option>
                                <option value="32" @selected(old('codepage',32)==32)>32 — PC864 / PC1254 (Turkish) ★</option>
                                <option value="33" @selected(old('codepage',32)==33)>33 — PC869 (Yunan)</option>
                                <option value="34" @selected(old('codepage',32)==34)>34 — ISO8859-2 (Latin 2)</option>
                                <option value="35" @selected(old('codepage',32)==35)>35 — ISO8859-15 (Latin 9)</option>
                                <option value="36" @selected(old('codepage',32)==36)>36 — PC1098 (Farsca)</option>
                                <option value="37" @selected(old('codepage',32)==37)>37 — PC1118 (Litvanca)</option>
                                <option value="38" @selected(old('codepage',32)==38)>38 — PC1119 (Litvanca)</option>
                                <option value="39" @selected(old('codepage',32)==39)>39 — PC1125 (Ukraynaca)</option>
                                <option value="40" @selected(old('codepage',32)==40)>40 — WPC1250 (Orta Avrupa)</option>
                                <option value="41" @selected(old('codepage',32)==41)>41 — WPC1251 (Kiril)</option>
                                <option value="42" @selected(old('codepage',32)==42)>42 — WPC1253 (Yunan)</option>
                                <option value="43" @selected(old('codepage',32)==43)>43 — WPC1255 (İbranice)</option>
                                <option value="44" @selected(old('codepage',32)==44)>44 — WPC1256 (Arapça)</option>
                                <option value="45" @selected(old('codepage',32)==45)>45 — WPC1257 (Baltik)</option>
                                <option value="46" @selected(old('codepage',32)==46)>46 — WPC1258 (Vietnamca)</option>
                                <option value="47" @selected(old('codepage',32)==47)>47 — KZ1048 (Kazakistan)</option>
                            </select>
                            <div class="form-text">Yazıcı ekranındaki code page numarasıyla eşleştirin.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i> Kaydet
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- YAZICI LİSTESİ --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2 text-primary"></i>Kayıtlı Yazıcılar
                        <span class="badge bg-primary ms-2">{{ $printers->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($printers->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-print fa-3x mb-3 opacity-25 d-block"></i>
                            <p class="mb-0">Henüz yazıcı eklenmemiş.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Yazıcı Adı</th>
                                        <th>IP Adresi</th>
                                        <th>Şube</th>
                                        <th>Durum</th>
                                        <th class="text-end pe-3">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($printers as $printer)
                                    <tr>
                                        <td class="ps-3 text-muted" style="font-size:13px;">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-semibold" style="font-size:14px;">{{ $printer->name }}</div>
                                        </td>
                                        <td>
                                            <code style="font-size:13px;">{{ $printer->ip_address }}</code>
                                        </td>
                                        <td style="font-size:13px;">{{ optional($printer->branch)->name ?? '-' }}</td>
                                        <td>
                                            @if($printer->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Pasif</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('printers.edit', $printer) }}"
                                               class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('printers.destroy', $printer) }}" method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Bu yazıcıyı silmek istediğinize emin misiniz?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
