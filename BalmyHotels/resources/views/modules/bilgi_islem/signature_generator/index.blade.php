@extends('layouts.default')

@section('title', 'E-posta İmza Oluşturucu')

@push('styles')
<style>
    @font-face {
        font-family: 'Cera Pro Light';
        src: url('{{ asset('fonts/cera-pro/CeraPro-Light.ttf') }}') format('truetype');
        font-style: normal;
        font-weight: 300;
        font-display: swap;
    }

    .signature-shell {
        overflow: hidden;
        border: 1px solid #e1e5ea;
        border-radius: 6px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(27, 37, 51, .07);
    }

    .signature-controls {
        height: 100%;
        padding: 24px;
        border-right: 1px solid #e1e5ea;
        background: #fff;
    }

    .signature-preview-panel {
        min-height: 100%;
        padding: 24px;
        background: #f4f6f8;
    }

    .signature-section-title {
        margin: 0;
        color: #27313d;
        font-size: 15px;
        font-weight: 700;
    }

    .signature-label {
        margin-bottom: 6px;
        color: #46515e;
        font-size: 12px;
        font-weight: 600;
    }

    .signature-input {
        min-height: 40px;
        border-color: #d9dee5;
        border-radius: 5px;
        color: #26303c;
        font-size: 13px;
    }

    .signature-input:focus {
        border-color: #697783;
        box-shadow: 0 0 0 3px rgba(105, 119, 131, .12);
    }

    .signature-preview-stage {
        display: flex;
        min-height: 310px;
        align-items: center;
        justify-content: center;
        overflow: auto;
        padding: 28px;
        border: 1px solid #dce1e6;
        border-radius: 6px;
        background-color: #fff;
        background-image:
            linear-gradient(45deg, #eef1f4 25%, transparent 25%),
            linear-gradient(-45deg, #eef1f4 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #eef1f4 75%),
            linear-gradient(-45deg, transparent 75%, #eef1f4 75%);
        background-position: 0 0, 0 10px, 10px -10px, -10px 0;
        background-size: 20px 20px;
    }

    .signature-canvas-wrap {
        width: min(850px, 100%);
        aspect-ratio: 850 / 147;
        background: transparent;
    }

    #signatureCanvas {
        display: block;
        width: 100%;
        height: auto;
        aspect-ratio: 850 / 147;
    }

    .signature-status {
        display: inline-flex;
        min-height: 28px;
        align-items: center;
        gap: 7px;
        color: #53606d;
        font-size: 12px;
    }

    .signature-status-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        border-radius: 50%;
        background: #2f9e67;
    }

    .signature-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .signature-actions .btn {
        min-height: 40px;
        border-radius: 5px;
    }

    .signature-error {
        display: none;
        margin-top: 14px;
        border-radius: 5px;
        font-size: 13px;
    }

    @media (max-width: 991.98px) {
        .signature-controls {
            border-right: 0;
            border-bottom: 1px solid #e1e5ea;
        }

        .signature-preview-stage {
            min-height: 230px;
            padding: 18px;
        }
    }

    @media (max-width: 575.98px) {
        .signature-controls,
        .signature-preview-panel {
            padding: 18px;
        }

        .signature-preview-stage {
            min-height: 170px;
            padding: 12px;
        }

        .signature-actions {
            width: 100%;
            justify-content: stretch;
        }

        .signature-actions .btn {
            flex: 1 1 140px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>E-posta İmza Oluşturucu</h4>
                <span>Bilgi İşlem</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><span class="text-muted">Bilgi İşlem</span></li>
                <li class="breadcrumb-item active">İmza Oluşturucu</li>
            </ol>
        </div>
    </div>

    <div class="signature-shell">
        <div class="row g-0">
            <div class="col-xl-4 col-lg-5">
                <div class="signature-controls">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="signature-section-title">İmza Bilgileri</h5>
                        <i class="fas fa-id-card text-muted" aria-hidden="true"></i>
                    </div>

                    <form id="signatureForm" autocomplete="off">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label signature-label" for="firstName">Ad</label>
                                <input class="form-control signature-input" id="firstName" type="text"
                                       value="Mercan" maxlength="40">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label signature-label" for="lastName">Soyad</label>
                                <input class="form-control signature-input" id="lastName" type="text"
                                       value="Balcı" maxlength="40">
                            </div>
                            <div class="col-12">
                                <label class="form-label signature-label" for="primaryTitle">Birinci Ünvan</label>
                                <input class="form-control signature-input" id="primaryTitle" type="text"
                                       value="Global IT Executive" maxlength="80">
                            </div>
                            <div class="col-12">
                                <label class="form-label signature-label" for="secondaryTitle">İkinci Ünvan</label>
                                <input class="form-control signature-input" id="secondaryTitle" type="text"
                                       value="Grup Bilgi İşlem Sorumlusu" maxlength="80">
                            </div>
                            <div class="col-12">
                                <label class="form-label signature-label" for="address">Adres</label>
                                <input class="form-control signature-input" id="address" type="text"
                                       value="Başkomutan Atatürk Cd. No: 143/1 07985 Kemer - Antalya / Türkiye"
                                       maxlength="180">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label signature-label" for="phone">Telefon</label>
                                <input class="form-control signature-input" id="phone" type="text"
                                       value="+90 242 824 84 31" maxlength="50">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label signature-label" for="website">Web Sitesi</label>
                                <input class="form-control signature-input" id="website" type="text"
                                       value="www.balmyhotels.com" maxlength="80">
                            </div>
                            <div class="col-12">
                                <label class="form-label signature-label" for="extraContact">Ek İletişim</label>
                                <input class="form-control signature-input" id="extraContact" type="text"
                                       placeholder="ornek@balmyhotels.com" maxlength="100">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7">
                <div class="signature-preview-panel">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="signature-section-title">Önizleme</h5>
                        <span class="signature-status">
                            <span class="signature-status-dot" aria-hidden="true"></span>
                            Hazır
                        </span>
                    </div>

                    <div class="signature-preview-stage">
                        <div class="signature-canvas-wrap">
                            <canvas id="signatureCanvas" width="850" height="147"
                                    aria-label="E-posta imzası önizlemesi"></canvas>
                        </div>
                    </div>

                    <div class="alert alert-danger signature-error" id="signatureError" role="alert"></div>

                    <div class="signature-actions mt-3">
                        <button class="btn btn-outline-secondary" id="resetSignature" type="button">
                            <i class="fas fa-undo me-1" aria-hidden="true"></i>
                            Örneğe Dön
                        </button>
                        <button class="btn btn-success" id="downloadSignature" type="button" disabled>
                            <i class="fas fa-download me-1" aria-hidden="true"></i>
                            PNG İndir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const canvas = document.getElementById('signatureCanvas');
    const context = canvas.getContext('2d');
    const downloadButton = document.getElementById('downloadSignature');
    const resetButton = document.getElementById('resetSignature');
    const errorBox = document.getElementById('signatureError');
    const form = document.getElementById('signatureForm');
    const brandImage = new Image();
    const signatureColor = '#6b6c6f';
    const fontFamily = '"Cera Pro Light"';
    const brandStartX = 500;
    let assetsReady = false;

    const fields = {
        firstName: document.getElementById('firstName'),
        lastName: document.getElementById('lastName'),
        primaryTitle: document.getElementById('primaryTitle'),
        secondaryTitle: document.getElementById('secondaryTitle'),
        address: document.getElementById('address'),
        phone: document.getElementById('phone'),
        website: document.getElementById('website'),
        extraContact: document.getElementById('extraContact'),
    };

    const defaults = Object.fromEntries(
        Object.entries(fields).map(([key, input]) => [key, input.value])
    );

    function clean(value) {
        return value.trim().replace(/\s+/g, ' ');
    }

    function joined(values) {
        return values.map(clean).filter(Boolean).join(' | ');
    }

    function fitText(text, x, baseline, maxWidth, preferredSize, minimumSize, letterSpacing = 0) {
        let fontSize = preferredSize;

        do {
            context.font = `300 ${fontSize}px ${fontFamily}`;
            if ('letterSpacing' in context) {
                context.letterSpacing = `${letterSpacing}px`;
            }
            if (context.measureText(text).width <= maxWidth || fontSize <= minimumSize) {
                break;
            }
            fontSize -= 0.25;
        } while (fontSize >= minimumSize);

        context.fillText(text, x, baseline);
        if ('letterSpacing' in context) {
            context.letterSpacing = '0px';
        }
    }

    function renderSignature() {
        context.clearRect(0, 0, canvas.width, canvas.height);

        if (!assetsReady) {
            return;
        }

        context.drawImage(brandImage, brandStartX, 0);

        const firstName = clean(fields.firstName.value);
        const lastName = clean(fields.lastName.value).toLocaleUpperCase('tr-TR');
        const fullName = [firstName, lastName].filter(Boolean).join(' ');
        const titleLine = joined([fields.primaryTitle.value, fields.secondaryTitle.value]);
        const addressLine = clean(fields.address.value);
        const phone = clean(fields.phone.value);
        const phoneLabel = phone && !/^[A-Za-z]:\s*/.test(phone) ? `P: ${phone}` : phone;
        const contactLine = joined([phoneLabel, fields.website.value, fields.extraContact.value]);

        context.fillStyle = signatureColor;
        context.textAlign = 'left';
        context.textBaseline = 'alphabetic';

        fitText(fullName, 31, 53, 440, 27, 16, 1.5);
        fitText(titleLine, 31, 70, 440, 12, 8);
        fitText(addressLine, 31, 101, 440, 12.28, 8);
        fitText(contactLine, 30, 115.25, 440, 12, 8, .32);
    }

    function showError(message) {
        errorBox.textContent = message;
        errorBox.style.display = 'block';
    }

    function fileSlug(value) {
        const replacements = {
            'ı': 'i',
            'ğ': 'g',
            'ü': 'u',
            'ş': 's',
            'ö': 'o',
            'ç': 'c',
        };

        return value
            .toLocaleLowerCase('tr-TR')
            .split('')
            .map((character) => replacements[character] ?? character)
            .join('')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
    }

    form.addEventListener('input', renderSignature);

    resetButton.addEventListener('click', () => {
        Object.entries(fields).forEach(([key, input]) => {
            input.value = defaults[key];
        });
        renderSignature();
    });

    downloadButton.addEventListener('click', () => {
        renderSignature();

        canvas.toBlob((blob) => {
            if (!blob) {
                showError('PNG dosyası oluşturulamadı. Lütfen tekrar deneyin.');
                return;
            }

            const personName = fileSlug(
                `${fields.firstName.value} ${fields.lastName.value}`
            ) || 'personel';
            const downloadUrl = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = downloadUrl;
            link.download = `balmy-imza-${personName}.png`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(downloadUrl);
        }, 'image/png');
    });

    brandImage.onload = async () => {
        try {
            await document.fonts.load(`27px ${fontFamily}`);
            await document.fonts.ready;
            assetsReady = true;
            downloadButton.disabled = false;
            renderSignature();
        } catch (error) {
            showError('İmza fontu yüklenemedi. Sayfayı yenileyip tekrar deneyin.');
        }
    };

    brandImage.onerror = () => {
        showError('Balmy imza görseli yüklenemedi. Sayfayı yenileyip tekrar deneyin.');
    };

    brandImage.src = @json(asset('images/signature/balmy-signature-reference.png'));
});
</script>
@endpush
