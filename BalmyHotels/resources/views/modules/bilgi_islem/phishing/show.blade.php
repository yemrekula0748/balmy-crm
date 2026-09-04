@extends('layouts.default')

@section('title', $campaign->name . ' — Oltalama Testi')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-lg-7 p-md-0">
            <div class="welcome-text">
                <h4><i class="fas fa-chart-bar me-2 text-primary"></i>{{ $campaign->name }}</h4>
                <span class="text-muted">Kampanya #{{ $campaign->id }} · {{ $campaign->created_at->format('d.m.Y H:i') }}</span>
            </div>
        </div>
        <div class="col-lg-5 p-md-0 d-flex flex-wrap justify-content-lg-end align-items-center gap-2 mt-2 mt-lg-0">
            @if(auth()->user()->hasPermission('it_phishing_tests', 'create'))
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTargetsModal">
                    <i class="fas fa-user-plus me-1"></i>Kişi ekle
                </button>
            @endif
            <a href="{{ route('it.phishing.export', $campaign) }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-csv me-1"></i>CSV indir
            </a>
            <a href="{{ route('it.phishing.report', $campaign) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                <i class="fas fa-file-signature me-1"></i>İmzalı form
            </a>
            @if(auth()->user()->hasPermission('it_phishing_tests', 'edit'))
                <form method="POST" action="{{ route('it.phishing.status', $campaign) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $campaign->status === 'active' ? 'closed' : 'active' }}">
                    <button class="btn btn-sm {{ $campaign->status === 'active' ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                        <i class="fas {{ $campaign->status === 'active' ? 'fa-stop-circle' : 'fa-play-circle' }} me-1"></i>
                        {{ $campaign->status === 'active' ? 'Kampanyayı kapat' : 'Yeniden aç' }}
                    </button>
                </form>
            @endif
            <a href="{{ route('it.phishing.index') }}" class="btn btn-sm btn-outline-secondary">Geri</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="alert alert-info border-0 shadow-sm py-3">
        <i class="fas fa-lock me-2"></i>
        <strong>Parola güvenliği:</strong> Yazılan e-posta veya parola tarayıcıdan gönderilmez. Rapor yalnızca butona basılma olayını gösterir.
        Kişisel bağlantılar hedef kişi dışında paylaşılmamalıdır.
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'Hedef', 'value' => $stats['total'], 'detail' => 'Toplam çalışan', 'color' => '#4361ee'],
            ['label' => 'Bağlantıyı açtı', 'value' => $stats['clicked'], 'detail' => '%' . $stats['click_rate'], 'color' => '#d97706'],
            ['label' => 'Bilgi göndermeyi denedi', 'value' => $stats['attempted'], 'detail' => '%' . $stats['attempt_rate'], 'color' => '#dc2626'],
            ['label' => 'Henüz açmadı', 'value' => $stats['unopened'], 'detail' => 'Takip bekliyor', 'color' => '#64748b'],
        ] as $card)
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid {{ $card['color'] }} !important">
                    <div class="card-body">
                        <div class="text-muted small">{{ $card['label'] }}</div>
                        <div class="d-flex justify-content-between align-items-end mt-1">
                            <span class="fs-3 fw-bold" style="color:{{ $card['color'] }}">{{ number_format($card['value']) }}</span>
                            <span class="small text-muted">{{ $card['detail'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Çalışan ara</label>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Ad veya e-posta">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Durum</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        <option value="unopened" @selected(request('status') === 'unopened')>Açmadı</option>
                        <option value="clicked" @selected(request('status') === 'clicked')>Bağlantıyı açtı</option>
                        <option value="attempted" @selected(request('status') === 'attempted')>Bilgi göndermeyi denedi</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-sm btn-primary flex-grow-1"><i class="fas fa-search"></i></button>
                    <a href="{{ route('it.phishing.show', $campaign) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13px">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Çalışan</th>
                            <th>Şube / Departman</th>
                            <th>Durum</th>
                            <th>Açılma</th>
                            <th>Bilgi denemesi</th>
                            <th style="min-width:330px">Kişisel bağlantı</th>
                            <th class="text-end pe-4">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($targets as $target)
                            @php
                                $url = route('phishing-simulation.show', $target->token);
                                $attempted = (bool) $target->first_credential_attempted_at;
                                $clicked = (bool) $target->first_clicked_at;
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $target->target_name }}</div>
                                    <div class="small text-muted">{{ $target->target_email }}</div>
                                </td>
                                <td>
                                    <div>{{ $target->user?->branch?->name ?? '—' }}</div>
                                    <div class="small text-muted">{{ $target->user?->department?->name ?? '—' }}</div>
                                </td>
                                <td>
                                    @if($attempted)
                                        <span class="badge bg-danger">Bilgi göndermeyi denedi</span>
                                    @elseif($clicked)
                                        <span class="badge bg-warning text-dark">Bağlantıyı açtı</span>
                                    @else
                                        <span class="badge bg-light text-secondary border">Açmadı</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $target->click_count }} kez</div>
                                    <small class="text-muted">{{ optional($target->first_clicked_at)->format('d.m.Y H:i') ?? '—' }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $target->credential_attempt_count }} kez</div>
                                    <small class="text-muted">{{ optional($target->first_credential_attempted_at)->format('d.m.Y H:i') ?? '—' }}</small>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control personal-link" value="{{ $url }}" readonly aria-label="Kişisel bağlantı">
                                        <button type="button" class="btn btn-outline-primary copy-link" data-url="{{ $url }}" title="Bağlantıyı kopyala">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary prepare-email"
                                                data-name="{{ $target->target_name }}"
                                                data-email="{{ $target->target_email }}"
                                                data-url="{{ $url }}"
                                                data-draft-url="{{ route('it.phishing.draft', [$campaign, $target]) }}">
                                            <i class="fas fa-envelope me-1"></i>Maili hazırla
                                        </button>
                                        @if(auth()->user()->hasPermission('it_phishing_tests', 'create'))
                                            <form method="POST" action="{{ route('it.phishing.targets.remove', [$campaign, $target]) }}"
                                                  onsubmit="return confirm('Bu kişiyi hedef listesinden çıkarmak istediğinize emin misiniz? Varsa bağlantı açma ve bilgi denemesi kayıtları da silinecektir.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hedef listesinden çıkar" aria-label="Hedef listesinden çıkar">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">Filtreye uygun kayıt bulunamadı.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($targets->hasPages())
                <div class="px-4 py-3 border-top">{{ $targets->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>

@if(auth()->user()->hasPermission('it_phishing_tests', 'create'))
<div class="modal fade" id="addTargetsModal" tabindex="-1" aria-labelledby="addTargetsTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('it.phishing.targets.add', $campaign) }}" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="addTargetsTitle"><i class="fas fa-user-plus me-2 text-primary"></i>Kampanyaya Kişi Ekle</h5>
                    <div class="small text-muted">Kampanyada bulunmayan aktif kullanıcılar gösteriliyor.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body p-0">
                @if($campaign->status !== 'active')
                    <div class="alert alert-warning rounded-0 border-0 mb-0 small">Kampanya kapalı. Kişiler eklenir ancak bağlantıları kampanya yeniden açılana kadar kullanılamaz.</div>
                @endif
                <div class="p-3 border-bottom">
                    <div class="d-flex gap-2">
                        <input type="search" class="form-control" id="addTargetSearch" placeholder="Ad, e-posta, şube veya departman ara..." autocomplete="off">
                        <button type="button" class="btn btn-outline-primary text-nowrap" id="selectVisibleTargets">Görünenleri seç</button>
                    </div>
                    <div class="small text-muted mt-2"><span id="addTargetCount">0</span> kişi seçildi</div>
                </div>
                <div class="table-responsive" style="max-height:430px;overflow:auto">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr><th class="ps-4" style="width:50px"></th><th>Çalışan</th><th>Şube</th><th>Departman</th></tr>
                        </thead>
                        <tbody>
                            @forelse($availableUsers as $availableUser)
                                <tr class="add-target-row" data-search="{{ Illuminate\Support\Str::lower($availableUser->name . ' ' . $availableUser->email . ' ' . ($availableUser->branch?->name ?? '') . ' ' . ($availableUser->department?->name ?? '')) }}">
                                    <td class="ps-4"><input type="checkbox" class="form-check-input add-target-check" name="user_ids[]" value="{{ $availableUser->id }}" id="add-target-{{ $availableUser->id }}"></td>
                                    <td><label for="add-target-{{ $availableUser->id }}" class="mb-0"><span class="fw-semibold">{{ $availableUser->name }}</span><br><small class="text-muted">{{ $availableUser->email }}</small></label></td>
                                    <td>{{ $availableUser->branch?->name ?? '—' }}</td>
                                    <td>{{ $availableUser->department?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-5">Eklenebilecek başka aktif kullanıcı bulunmuyor.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" class="btn btn-primary" @disabled($availableUsers->isEmpty())>
                    <i class="fas fa-user-plus me-1"></i>Seçilenleri ekle
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<div class="modal fade" id="emailComposerModal" tabindex="-1" aria-labelledby="emailComposerTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="emailComposerTitle"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Kişiye Özel Mail</h5>
                    <div class="small text-muted" id="emailRecipient"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="alert alert-warning py-2 small">
                    Yalnızca şirketçe onaylanmış güvenlik farkındalık kampanyasında kullanın. Mail gerçek marka taklidi veya parola toplama içermez.
                </div>

                <label class="form-label small fw-semibold">Konu başlığı</label>
                <div class="input-group mb-3">
                    <input type="text" id="emailSubject" class="form-control" readonly>
                    <button type="button" class="btn btn-outline-primary" id="copySubjectButton">
                        <i class="fas fa-copy me-1"></i>Konuyu kopyala
                    </button>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label small fw-semibold mb-0">Outlook önizlemesi</label>
                    <span class="small text-success fw-semibold" id="emailCopyStatus" aria-live="polite"></span>
                </div>
                <div class="border rounded bg-white p-3 overflow-auto">
                    <div id="emailPreview" style="min-width:560px"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kapat</button>
                <a href="#" class="btn btn-outline-primary" id="downloadOutlookDraftButton">
                    <i class="fas fa-file-download me-1"></i>Outlook taslağını indir
                </a>
                <button type="button" class="btn btn-primary" id="copyRichEmailButton">
                    <i class="fas fa-copy me-1"></i>Butonlu maili kopyala
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let preparedEmailHtml = '';
let preparedEmailText = '';

function escapeEmailHtml(value) {
    return String(value || '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function buildPersonalEmail(name, url) {
    const safeName = escapeEmailHtml(name);
    const safeUrl = escapeEmailHtml(url);

    return `
<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="width:100%;margin:0;padding:0;background:#eef2f6;font-family:Arial,Helvetica,sans-serif">
  <tr>
    <td align="center" style="padding:28px 12px">
      <table role="presentation" width="600" border="0" cellpadding="0" cellspacing="0" style="width:600px;max-width:100%;background:#ffffff;border:1px solid #dce3eb">
        <tr>
          <td style="height:6px;background:#1d4f91;font-size:0;line-height:0">&nbsp;</td>
        </tr>
        <tr>
          <td style="padding:26px 34px 18px;border-bottom:1px solid #e5eaf0">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td width="46" height="46" align="center" valign="middle" bgcolor="#1d4f91" style="width:46px;height:46px;background:#1d4f91;color:#ffffff;font-size:23px;font-weight:bold">M</td>
                <td style="padding-left:14px">
                  <div style="font-size:18px;line-height:22px;font-weight:bold;color:#1b2a3a">Mail Giriş</div>
                  <div style="font-size:12px;line-height:18px;color:#6b7785">Kurumsal hesap güvenliği</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:30px 34px 34px">
            <h1 style="margin:0 0 18px;font-size:23px;line-height:30px;color:#172033;font-weight:bold">Mail şifrenizin süresi doldu</h1>
            <p style="margin:0 0 16px;font-size:15px;line-height:24px;color:#3f4b59">Merhaba <strong>${safeName}</strong>,</p>
            <p style="margin:0 0 22px;font-size:15px;line-height:24px;color:#3f4b59">Kurumsal e-posta hesabınıza ait şifrenin kullanım süresi dolmuştur. E-posta erişiminizde kesinti yaşamamak için aşağıdaki bağlantı üzerinden gerekli işlemi tamamlayınız.</p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin:0 0 24px">
              <tr>
                <td align="center" bgcolor="#1d4f91" style="background:#1d4f91;mso-padding-alt:14px 28px">
                  <a href="${safeUrl}" style="display:inline-block;padding:14px 28px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:18px;font-weight:bold;color:#ffffff;text-decoration:none">Şifremi Güncelle</a>
                </td>
              </tr>
            </table>
            <p style="margin:0 0 8px;font-size:13px;line-height:20px;color:#697586">Bu bağlantı kişiye özeldir. Lütfen başka kişilerle paylaşmayınız.</p>
          </td>
        </tr>
        <tr>
          <td style="padding:16px 34px;background:#f7f9fb;border-top:1px solid #e5eaf0;font-size:11px;line-height:17px;color:#7a8592">Bu bildirim kurumsal hesap güvenliği kapsamında oluşturulmuştur.</td>
        </tr>
      </table>
    </td>
  </tr>
</table>`;
}

async function copyText(value) {
    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(value);
        return;
    }
    const input = document.createElement('textarea');
    input.value = value;
    input.style.position = 'fixed';
    input.style.opacity = '0';
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    input.remove();
}

async function copyRichEmail() {
    if (navigator.clipboard && window.ClipboardItem && window.isSecureContext) {
        await navigator.clipboard.write([new ClipboardItem({
            'text/html': new Blob([preparedEmailHtml], {type: 'text/html'}),
            'text/plain': new Blob([preparedEmailText], {type: 'text/plain'})
        })]);
        return;
    }

    const preview = document.getElementById('emailPreview');
    const range = document.createRange();
    range.selectNodeContents(preview);
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);
    document.execCommand('copy');
    selection.removeAllRanges();
}

document.addEventListener('click', async function (event) {
    const prepareButton = event.target.closest('.prepare-email');
    if (prepareButton) {
        const name = prepareButton.dataset.name;
        const email = prepareButton.dataset.email;
        const url = prepareButton.dataset.url;
        preparedEmailHtml = buildPersonalEmail(name, url);
        preparedEmailText = `Merhaba ${name},\n\nKurumsal e-posta hesabınıza ait şifrenin kullanım süresi dolmuştur. E-posta erişiminizde kesinti yaşamamak için aşağıdaki kişisel bağlantı üzerinden gerekli işlemi tamamlayınız.\n\nŞifremi Güncelle: ${url}\n\nBu bağlantı kişiye özeldir. Lütfen başka kişilerle paylaşmayınız.`;
        document.getElementById('emailSubject').value = 'E-posta Şifrenizin Süresi Doldu';
        document.getElementById('emailRecipient').textContent = name + ' · ' + email;
        document.getElementById('emailPreview').innerHTML = preparedEmailHtml;
        document.getElementById('downloadOutlookDraftButton').href = prepareButton.dataset.draftUrl;
        document.getElementById('emailCopyStatus').textContent = '';
        new bootstrap.Modal(document.getElementById('emailComposerModal')).show();
        return;
    }

    const button = event.target.closest('.copy-link');
    if (!button) return;

    try {
        await navigator.clipboard.writeText(button.dataset.url);
        const icon = button.querySelector('i');
        icon.className = 'fas fa-check';
        button.classList.replace('btn-outline-primary', 'btn-success');
        setTimeout(() => {
            icon.className = 'fas fa-copy';
            button.classList.replace('btn-success', 'btn-outline-primary');
        }, 1300);
    } catch (error) {
        const input = button.parentElement.querySelector('.personal-link');
        input.select();
        document.execCommand('copy');
    }
});

document.getElementById('copySubjectButton').addEventListener('click', async function () {
    await copyText(document.getElementById('emailSubject').value);
    document.getElementById('emailCopyStatus').textContent = 'Konu kopyalandı.';
});

document.getElementById('copyRichEmailButton').addEventListener('click', async function () {
    try {
        await copyRichEmail();
        document.getElementById('emailCopyStatus').textContent = 'Butonlu mail kopyalandı; Outlook mesajına yapıştırabilirsiniz.';
    } catch (error) {
        document.getElementById('emailCopyStatus').textContent = 'Kopyalama başarısız; önizlemeyi seçerek kopyalayın.';
    }
});

const addTargetSearch = document.getElementById('addTargetSearch');
if (addTargetSearch) {
    const addTargetRows = Array.from(document.querySelectorAll('.add-target-row'));
    const addTargetChecks = Array.from(document.querySelectorAll('.add-target-check'));
    const addTargetCount = document.getElementById('addTargetCount');
    const updateAddTargetCount = () => {
        addTargetCount.textContent = addTargetChecks.filter(check => check.checked).length;
    };

    addTargetSearch.addEventListener('input', function () {
        const term = this.value.toLocaleLowerCase('tr-TR').trim();
        addTargetRows.forEach(row => row.style.display = !term || row.dataset.search.includes(term) ? '' : 'none');
    });
    document.getElementById('selectVisibleTargets').addEventListener('click', function () {
        addTargetRows.filter(row => row.style.display !== 'none').forEach(row => row.querySelector('.add-target-check').checked = true);
        updateAddTargetCount();
    });
    addTargetChecks.forEach(check => check.addEventListener('change', updateAddTargetCount));
}
</script>
@endpush
