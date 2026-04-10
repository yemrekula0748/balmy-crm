@extends('layouts.default')

@section('title', $agentComputer->hostname . ' — Uzak Kontrol')

@push('styles')
<script>tailwind = { corePlugins: { preflight: false } }</script>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  #vnc-canvas-wrap {
    position: relative;
    background: #0f172a;
    border-radius: 0 0 .75rem .75rem;
    overflow: hidden;
    min-height: 480px;
    line-height: 0;
  }
  #vnc-container {
    width: 100%;
    min-height: 480px;
  }
  #vnc-container canvas {
    width: 100% !important;
    height: auto !important;
    display: block;
    cursor: default;
  }
  #vnc-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .75rem;
    background: rgba(15,23,42,.92);
    border-radius: 0 0 .75rem .75rem;
    z-index: 10;
  }
  .vnc-stat {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: .5rem;
    padding: .35rem .8rem;
    font-size: .75rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: .4rem;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: .4; }
  }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">

{{-- Breadcrumb --}}
<div class="row page-titles mx-0">
    <div class="col-sm-6 p-md-0">
        <div class="welcome-text">
            <h4>{{ $agentComputer->hostname }} — Uzak Kontrol</h4>
            <span>Bilgi İşlem — Ajan Envanter — VNC</span>
        </div>
    </div>
    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
            <li class="breadcrumb-item"><a href="{{ route('it.agent.index') }}">Ajan Envanter</a></li>
            <li class="breadcrumb-item"><a href="{{ route('it.agent.show', $agentComputer) }}">{{ $agentComputer->hostname }}</a></li>
            <li class="breadcrumb-item active">Uzak Kontrol</li>
        </ol>
    </div>
</div>

{{-- Control Bar --}}
<div class="card border-0 shadow-sm mb-0" style="border-radius:.75rem .75rem 0 0;border-bottom:1px solid #e2e8f0">
    <div class="card-body py-3 px-4 d-flex align-items-center gap-3 flex-wrap">

        {{-- Computer Info --}}
        <div class="d-flex align-items-center gap-2">
            <div style="width:34px;height:34px;background:#1e293b;border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-desktop" style="color:#7dd3fc;font-size:.85rem"></i>
            </div>
            <div>
                <div style="font-size:.875rem;font-weight:600;color:#1e293b">{{ $agentComputer->hostname }}</div>
                @php $activeIp = $agentComputer->ip_address; @endphp
                @if($activeIp)
                <code style="font-size:.7rem;background:#f1f5f9;padding:1px 6px;border-radius:3px;color:#475569">{{ $activeIp }}</code>
                @endif
            </div>
        </div>

        {{-- Status badge --}}
        <div id="vncStatus" class="vnc-stat" style="display:none">
            <span id="vncStatusDot" style="width:7px;height:7px;border-radius:9999px;background:#22c55e;display:inline-block;animation:pulse 1.5s infinite"></span>
            <span id="vncStatusText">Bağlanıyor...</span>
        </div>

        {{-- Connect Form --}}
        <div id="connectForm" class="d-flex align-items-center gap-2 ms-auto flex-wrap">
            <input type="password" id="vncPassword" placeholder="VNC Şifresi (opsiyonel)"
                   class="form-control form-control-sm" style="max-width:180px;font-size:.8rem">
            <button id="btnConnect" class="btn btn-primary btn-sm px-3">
                <i class="fas fa-plug me-1" style="font-size:.7rem"></i>Bağlan
            </button>
            <a href="{{ route('it.agent.show', $agentComputer) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1" style="font-size:.7rem"></i>Geri
            </a>
        </div>

        {{-- Disconnect --}}
        <div id="disconnectForm" style="display:none" class="d-flex align-items-center gap-2 ms-auto flex-wrap">
            <button id="btnDisconnect" class="btn btn-outline-danger btn-sm px-3">
                <i class="fas fa-power-off me-1" style="font-size:.7rem"></i>Bağlantıyı Kes
            </button>
            <a href="{{ route('it.agent.show', $agentComputer) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1" style="font-size:.7rem"></i>Geri
            </a>
        </div>

    </div>
</div>

{{-- VNC Canvas --}}
<div id="vnc-canvas-wrap">
    <div id="vnc-container"></div>
    <div id="vnc-overlay">
        <div style="width:64px;height:64px;background:rgba(255,255,255,.06);border-radius:1rem;display:flex;align-items:center;justify-content:center">
            <i class="fas fa-desktop" style="color:#475569;font-size:1.75rem"></i>
        </div>
        <div style="font-weight:600;font-size:1rem;color:#e2e8f0">Uzak Kontrol</div>
        <div style="font-size:.8rem;color:#64748b;text-align:center;max-width:320px">
            VNC sifresini girin ve <strong style="color:#94a3b8">Baglan</strong> butonuna tiklayin.<br>
            Agent bilgisayarda websockify calisyor olmalidir (port 6080).
        </div>
        <div id="overlaySpinner" style="display:none;margin-top:.5rem">
            <i class="fas fa-spinner fa-spin" style="color:#60a5fa;font-size:1.25rem"></i>
            <span style="color:#94a3b8;font-size:.8rem;margin-left:.5rem">Baglaniliyor...</span>
        </div>
        <div id="overlayError" style="display:none;margin-top:.5rem;color:#f87171;font-size:.82rem;text-align:center;max-width:320px"></div>
    </div>
</div>

</div>

@push('scripts')
<script type="module">
import RFB from 'https://cdn.skypack.dev/@novnc/novnc';

const VNC_CONNECT_URL = '{{ route("it.agent.vnc.connect", $agentComputer) }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

let rfb = null;

const overlay        = document.getElementById('vnc-overlay');
const overlaySpinner = document.getElementById('overlaySpinner');
const overlayError   = document.getElementById('overlayError');
const connectForm    = document.getElementById('connectForm');
const disconnectForm = document.getElementById('disconnectForm');
const vncStatus      = document.getElementById('vncStatus');
const vncStatusText  = document.getElementById('vncStatusText');
const vncStatusDot   = document.getElementById('vncStatusDot');

function showError(msg) {
    overlaySpinner.style.display = 'none';
    overlayError.textContent = msg;
    overlayError.style.display = '';
}
function clearError() {
    overlayError.style.display = 'none';
    overlayError.textContent = '';
}

async function vncConnect() {
    clearError();
    overlaySpinner.style.display = '';

    let wsUrl, password;
    try {
        const resp = await fetch(VNC_CONNECT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ password: document.getElementById('vncPassword').value }),
        });
        const data = await resp.json();
        if (!data.ok) {
            showError(data.error ?? 'Bağlantı bilgileri alınamadı.');
            return;
        }
        wsUrl    = data.ws_url;
        password = data.password;
    } catch (e) {
        showError('Sunucuya ulaşılamadı: ' + e.message);
        return;
    }

    try {
        rfb = new RFB(
            document.getElementById('vnc-container'),
            wsUrl,
            password ? { credentials: { password } } : {}
        );
    } catch (e) {
        showError('noVNC başlatılamadı: ' + e.message);
        return;
    }

    rfb.scaleViewport = true;
    rfb.resizeSession = false;

    rfb.addEventListener('connect', () => {
        overlay.style.display         = 'none';
        connectForm.style.display     = 'none';
        disconnectForm.style.display  = 'flex';
        vncStatus.style.display       = 'flex';
        vncStatusText.textContent     = 'Bağlı';
        vncStatusDot.style.background = '#22c55e';
    });

    rfb.addEventListener('disconnect', e => {
        if (rfb === null) return;
        vncStatusText.textContent     = 'Bağlantı kesildi';
        vncStatusDot.style.background = '#ef4444';
        vncStatusDot.style.animation  = 'none';
        overlay.style.display         = 'flex';
        overlaySpinner.style.display  = 'none';
        showError('Bağlantı kesildi. ' + (e.detail?.reason ?? ''));
        connectForm.style.display     = 'flex';
        disconnectForm.style.display  = 'none';
        rfb = null;
    });

    rfb.addEventListener('credentialsrequired', () => {
        const pw = prompt('VNC şifresi girin:');
        if (pw !== null) rfb.sendCredentials({ password: pw });
        else vncDisconnect();
    });

    rfb.addEventListener('securityfailure', e => {
        showError('Kimlik doğrulama başarısız: ' + (e.detail?.reason ?? 'Yanlış şifre?'));
    });
}

function vncDisconnect() {
    if (rfb) { rfb.disconnect(); rfb = null; }
    overlay.style.display        = 'flex';
    overlaySpinner.style.display = 'none';
    connectForm.style.display    = 'flex';
    disconnectForm.style.display = 'none';
    vncStatus.style.display      = 'none';
    clearError();
}

document.getElementById('btnConnect').addEventListener('click', vncConnect);
document.getElementById('btnDisconnect').addEventListener('click', vncDisconnect);
document.getElementById('vncPassword').addEventListener('keydown', e => {
    if (e.key === 'Enter') vncConnect();
});
</script>
@endpush

@endsection
