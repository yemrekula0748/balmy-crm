@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4><i class="fas fa-edit me-2 text-primary"></i>Yazıcı Düzenle</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('printers.index') }}">Yazıcılar</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">{{ $printer->name }}</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('printers.update', $printer) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">Şube seçin...</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id', $printer->branch_id) == $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Yazıcı Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $printer->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">IP Adresi <span class="text-danger">*</span></label>
                            <input type="text" name="ip_address" class="form-control"
                                   value="{{ old('ip_address', $printer->ip_address) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Karakter Seti (Codepage) <span class="text-danger">*</span></label>
                            <select name="codepage" class="form-select" required>
                                <option value="0"  @selected(old('codepage',$printer->codepage)==0)>0 — PC437 (USA / Standart)</option>
                                <option value="1"  @selected(old('codepage',$printer->codepage)==1)>1 — Katakana</option>
                                <option value="2"  @selected(old('codepage',$printer->codepage)==2)>2 — PC850 (Multilingual)</option>
                                <option value="3"  @selected(old('codepage',$printer->codepage)==3)>3 — PC860 (Portekizce)</option>
                                <option value="4"  @selected(old('codepage',$printer->codepage)==4)>4 — PC863 (Kanada Fransızcası)</option>
                                <option value="5"  @selected(old('codepage',$printer->codepage)==5)>5 — PC865 (Nordik)</option>
                                <option value="6"  @selected(old('codepage',$printer->codepage)==6)>6 — PC851</option>
                                <option value="7"  @selected(old('codepage',$printer->codepage)==7)>7 — PC852 (Latin 2)</option>
                                <option value="8"  @selected(old('codepage',$printer->codepage)==8)>8 — PC858 (Multilingual+Euro)</option>
                                <option value="9"  @selected(old('codepage',$printer->codepage)==9)>9 — PC866 (Kiril)</option>
                                <option value="10" @selected(old('codepage',$printer->codepage)==10)>10 — PC928 (Yunan)</option>
                                <option value="11" @selected(old('codepage',$printer->codepage)==11)>11 — PC770</option>
                                <option value="12" @selected(old('codepage',$printer->codepage)==12)>12 — PC857 (DOS Turkish)</option>
                                <option value="13" @selected(old('codepage',$printer->codepage)==13)>13 — PC737 (Yunan)</option>
                                <option value="14" @selected(old('codepage',$printer->codepage)==14)>14 — ISO8859-7 (Yunan)</option>
                                <option value="15" @selected(old('codepage',$printer->codepage)==15)>15 — WPC1252 (Batı Avrupa)</option>
                                <option value="16" @selected(old('codepage',$printer->codepage)==16)>16 — PC866 (Kiril)</option>
                                <option value="17" @selected(old('codepage',$printer->codepage)==17)>17 — PC852 (Latin 2)</option>
                                <option value="18" @selected(old('codepage',$printer->codepage)==18)>18 — PC858</option>
                                <option value="19" @selected(old('codepage',$printer->codepage)==19)>19 — Thai42</option>
                                <option value="20" @selected(old('codepage',$printer->codepage)==20)>20 — Thai11</option>
                                <option value="21" @selected(old('codepage',$printer->codepage)==21)>21 — Thai13</option>
                                <option value="22" @selected(old('codepage',$printer->codepage)==22)>22 — Thai14</option>
                                <option value="23" @selected(old('codepage',$printer->codepage)==23)>23 — Thai16</option>
                                <option value="24" @selected(old('codepage',$printer->codepage)==24)>24 — Thai17</option>
                                <option value="25" @selected(old('codepage',$printer->codepage)==25)>25 — Thai18</option>
                                <option value="26" @selected(old('codepage',$printer->codepage)==26)>26 — TCVN-3 (Vietnamca)</option>
                                <option value="27" @selected(old('codepage',$printer->codepage)==27)>27 — PC720 (Arapça)</option>
                                <option value="28" @selected(old('codepage',$printer->codepage)==28)>28 — WPC775 (Baltik)</option>
                                <option value="29" @selected(old('codepage',$printer->codepage)==29)>29 — PC855 (Kiril)</option>
                                <option value="30" @selected(old('codepage',$printer->codepage)==30)>30 — PC861 (İzlandaca)</option>
                                <option value="31" @selected(old('codepage',$printer->codepage)==31)>31 — PC862 (İbranice)</option>
                                <option value="32" @selected(old('codepage',$printer->codepage)==32)>32 — PC864 / PC1254 (Turkish) ★</option>
                                <option value="33" @selected(old('codepage',$printer->codepage)==33)>33 — PC869 (Yunan)</option>
                                <option value="34" @selected(old('codepage',$printer->codepage)==34)>34 — ISO8859-2 (Latin 2)</option>
                                <option value="35" @selected(old('codepage',$printer->codepage)==35)>35 — ISO8859-15 (Latin 9)</option>
                                <option value="36" @selected(old('codepage',$printer->codepage)==36)>36 — PC1098 (Farsca)</option>
                                <option value="37" @selected(old('codepage',$printer->codepage)==37)>37 — PC1118 (Litvanca)</option>
                                <option value="38" @selected(old('codepage',$printer->codepage)==38)>38 — PC1119 (Litvanca)</option>
                                <option value="39" @selected(old('codepage',$printer->codepage)==39)>39 — PC1125 (Ukraynaca)</option>
                                <option value="40" @selected(old('codepage',$printer->codepage)==40)>40 — WPC1250 (Orta Avrupa)</option>
                                <option value="41" @selected(old('codepage',$printer->codepage)==41)>41 — WPC1251 (Kiril)</option>
                                <option value="42" @selected(old('codepage',$printer->codepage)==42)>42 — WPC1253 (Yunan)</option>
                                <option value="43" @selected(old('codepage',$printer->codepage)==43)>43 — WPC1255 (İbranice)</option>
                                <option value="44" @selected(old('codepage',$printer->codepage)==44)>44 — WPC1256 (Arapça)</option>
                                <option value="45" @selected(old('codepage',$printer->codepage)==45)>45 — WPC1257 (Baltik)</option>
                                <option value="46" @selected(old('codepage',$printer->codepage)==46)>46 — WPC1258 (Vietnamca)</option>
                                <option value="47" @selected(old('codepage',$printer->codepage)==47)>47 — KZ1048 (Kazakistan)</option>
                            </select>
                            <div class="form-text">Yazıcı ekranındaki code page numarasıyla eşleştirin.</div>
                        </div>
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       id="isActiveCheck" @checked(old('is_active', $printer->is_active))>
                                <label class="form-check-label fw-semibold" for="isActiveCheck">Aktif</label>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Güncelle
                            </button>
                            <a href="{{ route('printers.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
