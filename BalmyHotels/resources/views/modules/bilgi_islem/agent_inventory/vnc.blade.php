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
  #vnc-screen {
    display: block;
    width: 100%;
    height: auto;
    cursor: crosshair;
    user-select: none;
    -webkit-user-select: none;
  }
  #vnc-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .75rem;
    background: rgba(15,23,42,.9);
    border-radius: 0 0 .75rem .75rem;
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
                @php $activeIp = $agentComputer->networkAdapters->where('is_active', true)->first()?->ip_address ?? null; @endphp
                @if($activeIp)
                <code style="font-size:.7rem;background:#f1f5f9;padding:1px 6px;border-radius:3px;color:#475569">{{ $activeIp }}</code>
                @endif
            </div>
        </div>

        {{-- Status badge --}}
        <div id="vncStatus" class="vnc-stat" style="display:none">
            <span style="width:7px;height:7px;border-radius:9999px;background:#22c55e;display:inline-block;animation:pulse 1.5s infinite"></span>
            <span id="vncStatusText">Bağlı</span>
        </div>
        <div id="vncFps" class="vnc-stat" style="display:none">
            <i class="fas fa-film" style="font-size:.65rem"></i>
            <span id="vncFpsText">— fps</span>
        </div>

        {{-- Connect Form --}}
        <div id="connectForm" class="d-flex align-items-center gap-2 ms-auto flex-wrap">
            <input type="password" id="vncPassword" placeholder="VNC Şifresi (opsiyonel)"
                   class="form-control form-control-sm" style="max-width:180px;font-size:.8rem">
            <button onclick="vncConnect()" class="btn btn-primary btn-sm px-3">
                <i class="fas fa-plug me-1" style="font-size:.7rem"></i>Bağlan
            </button>
            <a href="{{ route('it.agent.show', $agentComputer) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1" style="font-size:.7rem"></i>Geri
            </a>
        </div>

        {{-- Disconnect --}}
        <div id="disconnectForm" style="display:none" class="d-flex align-items-center gap-2 ms-auto flex-wrap">
            <button onclick="vncDisconnect()" class="btn btn-outline-danger btn-sm px-3">
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
    <canvas id="vnc-screen" width="1920" height="1080"></canvas>
    <div id="vnc-overlay">
        <div style="width:64px;height:64px;background:rgba(255,255,255,.06);border-radius:1rem;display:flex;align-items:center;justify-content:center">
            <i class="fas fa-desktop" style="color:#475569;font-size:1.75rem"></i>
        </div>
        <div style="font-weight:600;font-size:1rem;color:#e2e8f0">Uzak Kontrol</div>
        <div style="font-size:.8rem;color:#64748b;text-align:center;max-width:320px">
            VNC şifresini girin ve <strong style="color:#94a3b8">Bağlan</strong> butonuna tıklayın.<br>
            Ajan bilgisayarda VNC servisi başlatılacak.
        </div>
        <div id="overlaySpinner" style="display:none;margin-top:.5rem">
            <i class="fas fa-spinner fa-spin" style="color:#60a5fa;font-size:1.25rem"></i>
            <span style="color:#94a3b8;font-size:.8rem;margin-left:.5rem">Ajan bekleniyor...</span>
        </div>
    </div>
</div>

</div>

@push('scripts')
<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
</style>
<script>
// ── Route URLs ─────────────────────────────────────────────
const VNC_START_URL = '{{ route('it.agent.vnc.start', $agentComputer) }}';
const VNC_STOP_URL  = '{{ route('it.agent.vnc.stop',  $agentComputer) }}';
const VNC_FRAME_URL = '{{ route('it.agent.vnc.frame', $agentComputer) }}';
const VNC_INPUT_URL = '{{ route('it.agent.vnc.input', $agentComputer) }}';
const CSRF          = document.querySelector('meta[name="csrf-token"]').content;

// ── State ──────────────────────────────────────────────────
let framePoller   = null;
let inputFlusher  = null;
let inputBuffer   = [];
let vncScreenW    = 1920;
let vncScreenH    = 1080;
let lastSeq       = -1;
let mouseMoveTs   = 0;
let fpsCounter    = 0;
let fpsTimer      = null;

const canvas  = document.getElementById('vnc-screen');
const ctx     = canvas.getContext('2d');
const overlay = document.getElementById('vnc-overlay');

// ── X11 KeySym mapping ─────────────────────────────────────
const KEYSYM = {
    8:   0xff08,  // Backspace
    9:   0xff09,  // Tab
    13:  0xff0d,  // Enter/Return
    27:  0xff1b,  // Escape
    32:  0x0020,  // Space
    33:  0xff55,  // Page Up
    34:  0xff56,  // Page Down
    35:  0xff57,  // End
    36:  0xff50,  // Home
    37:  0xff51,  // Left
    38:  0xff52,  // Up
    39:  0xff53,  // Right
    40:  0xff54,  // Down
    45:  0xff63,  // Insert
    46:  0xffff,  // Delete
    16:  0xffe1,  // Left Shift
    17:  0xffe3,  // Left Ctrl
    18:  0xffe9,  // Left Alt
    91:  0xffeb,  // Left Meta / Win
    112: 0xffbe,  // F1
    113: 0xffbf,  // F2
    114: 0xffc0,  // F3
    115: 0xffc1,  // F4
    116: 0xffc2,  // F5
    117: 0xffc3,  // F6
    118: 0xffc4,  // F7
    119: 0xffc5,  // F8
    120: 0xffc6,  // F9
    121: 0xffc7,  // F10
    122: 0xffc8,  // F11
    123: 0xffc9,  // F12
};

function getKeysym(keyCode) {
    if (KEYSYM[keyCode] !== undefined) return KEYSYM[keyCode];
    if (keyCode >= 65 && keyCode <= 90)  return keyCode + 32;       // A-Z → a-z (0x61-0x7a)
    if (keyCode >= 48 && keyCode <= 57)  return keyCode;             // 0-9 (0x30-0x39)
    if (keyCode >= 96 && keyCode <= 105) return keyCode - 96 + 0x30; // Numpad 0-9
    return keyCode;
}

// ── Canvas coordinate scaling ─────────────────────────────
function canvasCoords(e) {
    const rect = canvas.getBoundingClientRect();
    return {
        x: Math.round((e.clientX - rect.left) * vncScreenW / rect.width),
        y: Math.round((e.clientY - rect.top)  * vncScreenH / rect.height),
    };
}

// ── Mouse events ───────────────────────────────────────────
canvas.addEventListener('mousemove', e => {
    const now = Date.now();
    if (now - mouseMoveTs < 50) return; // max 20/s
    mouseMoveTs = now;
    const { x, y } = canvasCoords(e);
    inputBuffer.push({ type: 'mouse_move', x, y });
});

canvas.addEventListener('mousedown', e => {
    e.preventDefault();
    const { x, y } = canvasCoords(e);
    inputBuffer.push({ type: 'mouse_down', x, y, button: e.button + 1 });
});

canvas.addEventListener('mouseup', e => {
    const { x, y } = canvasCoords(e);
    inputBuffer.push({ type: 'mouse_up', x, y, button: e.button + 1 });
});

canvas.addEventListener('wheel', e => {
    e.preventDefault();
    const { x, y } = canvasCoords(e);
    inputBuffer.push({ type: 'scroll', x, y, delta: e.deltaY > 0 ? -1 : 1 });
}, { passive: false });

canvas.addEventListener('contextmenu', e => e.preventDefault());

// ── Keyboard events ────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (!framePoller) return;
    e.preventDefault();
    inputBuffer.push({ type: 'key_down', keysym: getKeysym(e.keyCode) });
});

document.addEventListener('keyup', e => {
    if (!framePoller) return;
    inputBuffer.push({ type: 'key_up', keysym: getKeysym(e.keyCode) });
});

// ── Input batch flush (every 100ms) ───────────────────────
function flushInput() {
    if (!inputBuffer.length) return;
    const events = [...inputBuffer];
    inputBuffer = [];
    fetch(VNC_INPUT_URL, {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify({ events }),
    }).catch(() => {});
}

// ── Frame polling (every 300ms) ────────────────────────────
function pollFrame() {
    fetch(VNC_FRAME_URL, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        if (!data.image_data) return;
        if (data.seq === lastSeq) return; // no change

        lastSeq    = data.seq;
        vncScreenW = data.screen_w || 1920;
        vncScreenH = data.screen_h || 1080;

        if (canvas.width !== vncScreenW || canvas.height !== vncScreenH) {
            canvas.width  = vncScreenW;
            canvas.height = vncScreenH;
        }

        const img = new Image();
        img.onload = () => {
            ctx.drawImage(img, 0, 0);
            fpsCounter++;
        };
        img.src = 'data:image/jpeg;base64,' + data.image_data;
    })
    .catch(() => {});
}

// ── FPS counter ────────────────────────────────────────────
function startFps() {
    fpsTimer = setInterval(() => {
        document.getElementById('vncFpsText').textContent = fpsCounter + ' fps';
        fpsCounter = 0;
    }, 1000);
}

// ── Connect / Disconnect ──────────────────────────────────
function vncConnect() {
    const password = document.getElementById('vncPassword').value;
    const spinner  = document.getElementById('overlaySpinner');
    spinner.style.display = '';

    fetch(VNC_START_URL, {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify({ password }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) throw new Error();
        overlay.style.display = 'none';
        document.getElementById('connectForm').style.display    = 'none';
        document.getElementById('disconnectForm').style.display = 'flex';
        document.getElementById('vncStatus').style.display      = '';
        document.getElementById('vncFps').style.display         = '';

        framePoller  = setInterval(pollFrame, 300);
        inputFlusher = setInterval(flushInput, 100);
        startFps();
        canvas.focus();
    })
    .catch(() => {
        spinner.style.display = 'none';
        alert('Bağlantı isteği gönderilemedi. Ajan çevrimiçi mi?');
    });
}

function vncDisconnect() {
    clearInterval(framePoller);
    clearInterval(inputFlusher);
    clearInterval(fpsTimer);
    framePoller = inputFlusher = fpsTimer = null;
    inputBuffer = [];
    fpsCounter  = 0;

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    overlay.style.display = '';
    document.getElementById('overlaySpinner').style.display     = 'none';
    document.getElementById('connectForm').style.display        = 'flex';
    document.getElementById('disconnectForm').style.display     = 'none';
    document.getElementById('vncStatus').style.display          = 'none';
    document.getElementById('vncFps').style.display             = 'none';

    fetch(VNC_STOP_URL, {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).catch(() => {});
}
</script>
@endpush

@endsection
