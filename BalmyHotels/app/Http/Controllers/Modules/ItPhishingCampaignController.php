<?php

namespace App\Http\Controllers\Modules;

use App\Models\PhishingCampaign;
use App\Models\PhishingTarget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;

class ItPhishingCampaignController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'it_phishing_tests',
            ['index', 'show', 'export', 'downloadDraft', 'report', 'downloadReportWord'],
            [],
            ['create', 'store', 'addTargets', 'removeTarget'],
            ['updateStatus'],
            []
        );
    }

    public function index()
    {
        $campaigns = PhishingCampaign::query()
            ->with('creator:id,name')
            ->withCount([
                'targets',
                'targets as clicked_count' => fn ($query) => $query->whereNotNull('first_clicked_at'),
                'targets as attempted_count' => fn ($query) => $query->whereNotNull('first_credential_attempted_at'),
            ])
            ->latest()
            ->paginate(25);

        $stats = [
            'campaigns' => PhishingCampaign::count(),
            'active_campaigns' => PhishingCampaign::where('status', PhishingCampaign::STATUS_ACTIVE)->count(),
            'targets' => PhishingTarget::count(),
            'attempted' => PhishingTarget::whereNotNull('first_credential_attempted_at')->count(),
        ];

        return view('modules.bilgi_islem.phishing.index', compact('campaigns', 'stats'));
    }

    public function create()
    {
        $users = User::query()
            ->with(['branch:id,name', 'department:id,name'])
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where('email', 'not like', '%@users.balmy.invalid')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'branch_id', 'department_id']);

        return view('modules.bilgi_islem.phishing.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'user_ids' => ['required', 'array', 'min:1', 'max:1000'],
            'user_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->where('email', 'not like', '%@users.balmy.invalid')),
            ],
        ]);

        $campaign = DB::transaction(function () use ($data, $request) {
            $campaign = PhishingCampaign::create([
                'name' => $data['name'],
                'status' => PhishingCampaign::STATUS_ACTIVE,
                'redirect_url' => 'https://www.google.com/',
                'created_by' => $request->user()->id,
                'started_at' => now(),
            ]);

            $users = User::query()
                ->whereIn('id', $data['user_ids'])
                ->get(['id', 'name', 'email']);

            $now = now();
            $rows = $users->map(fn (User $user) => [
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'target_name' => $user->name,
                'target_email' => $user->email,
                'token' => Str::random(64),
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            foreach (array_chunk($rows, 250) as $chunk) {
                PhishingTarget::insert($chunk);
            }

            return $campaign;
        });

        return redirect()
            ->route('it.phishing.show', $campaign)
            ->with('success', 'Oltalama farkındalık kampanyası oluşturuldu. Kişisel bağlantılar hazır.');
    }

    public function show(Request $request, PhishingCampaign $campaign)
    {
        $query = $campaign->targets()
            ->with(['user.branch:id,name', 'user.department:id,name'])
            ->orderBy('target_name');

        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->where(function ($search) use ($term) {
                $search->where('target_name', 'like', '%' . $term . '%')
                    ->orWhere('target_email', 'like', '%' . $term . '%');
            });
        }

        if ($request->status === 'unopened') {
            $query->whereNull('first_clicked_at');
        } elseif ($request->status === 'clicked') {
            $query->whereNotNull('first_clicked_at')->whereNull('first_credential_attempted_at');
        } elseif ($request->status === 'attempted') {
            $query->whereNotNull('first_credential_attempted_at');
        }

        $targets = $query->paginate(100)->withQueryString();
        $stats = $this->campaignStats($campaign);
        $availableUsers = collect();

        if ($request->user()->hasPermission('it_phishing_tests', 'create')) {
            $existingUserIds = $campaign->targets()
                ->whereNotNull('user_id')
                ->pluck('user_id');

            $availableUsers = User::query()
                ->with(['branch:id,name', 'department:id,name'])
                ->where('is_active', true)
                ->whereNotNull('email')
                ->where('email', 'not like', '%@users.balmy.invalid')
                ->whereNotIn('id', $existingUserIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'branch_id', 'department_id']);
        }

        return view('modules.bilgi_islem.phishing.show', compact('campaign', 'targets', 'stats', 'availableUsers'));
    }

    public function addTargets(Request $request, PhishingCampaign $campaign)
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1', 'max:1000'],
            'user_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->where('email', 'not like', '%@users.balmy.invalid')),
            ],
        ]);

        $existingUserIds = $campaign->targets()
            ->whereIn('user_id', $data['user_ids'])
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $users = User::query()
            ->whereIn('id', array_diff($data['user_ids'], $existingUserIds))
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where('email', 'not like', '%@users.balmy.invalid')
            ->get(['id', 'name', 'email']);

        $now = now();
        $rows = $users->map(fn (User $user) => [
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'target_name' => $user->name,
            'target_email' => $user->email,
            'token' => Str::random(64),
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        $inserted = $rows ? PhishingTarget::insertOrIgnore($rows) : 0;

        if (!$inserted) {
            return back()->with('info', 'Seçilen kullanıcıların tamamı kampanyada zaten bulunuyor.');
        }

        return back()->with('success', $inserted . ' kişi kampanyaya eklendi ve kişisel bağlantıları oluşturuldu.');
    }

    public function removeTarget(PhishingCampaign $campaign, PhishingTarget $target)
    {
        abort_unless((int) $target->campaign_id === (int) $campaign->id, 404);

        $targetName = $target->target_name;
        $target->delete();

        return back()->with('success', $targetName . ' kampanyanın hedef listesinden çıkarıldı. Kullanıcı hesabında değişiklik yapılmadı.');
    }

    public function updateStatus(Request $request, PhishingCampaign $campaign)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([
                PhishingCampaign::STATUS_ACTIVE,
                PhishingCampaign::STATUS_CLOSED,
            ])],
        ]);

        $campaign->update([
            'status' => $data['status'],
            'started_at' => $data['status'] === PhishingCampaign::STATUS_ACTIVE
                ? ($campaign->started_at ?: now())
                : $campaign->started_at,
            'ended_at' => $data['status'] === PhishingCampaign::STATUS_CLOSED ? now() : null,
        ]);

        return back()->with('success', $data['status'] === PhishingCampaign::STATUS_ACTIVE
            ? 'Kampanya yeniden açıldı.'
            : 'Kampanya kapatıldı; kişisel bağlantılar artık kayıt oluşturmayacak.');
    }

    public function report(Request $request, PhishingCampaign $campaign)
    {
        $campaign->loadMissing('creator:id,name');
        $targets = $campaign->targets()
            ->with(['user.branch:id,name', 'user.department:id,name'])
            ->orderBy('target_name')
            ->get();
        $preparedBy = $request->user();
        $preparedBy?->loadMissing(['branch:id,name', 'department:id,name']);

        return view('modules.bilgi_islem.phishing.report', [
            'campaign' => $campaign,
            'targets' => $targets,
            'stats' => $this->campaignStats($campaign),
            'preparedBy' => $preparedBy,
            'reportOptions' => $this->defaultReportOptions($preparedBy?->name ?: $campaign->creator?->name),
        ]);
    }

    public function downloadReportWord(Request $request, PhishingCampaign $campaign)
    {
        $data = $request->validate([
            'test_name' => ['required', 'string', 'max:150'],
            'form_code' => ['required', 'string', 'max:50'],
            'form_title' => ['required', 'string', 'max:120'],
            'publication_date' => ['required', 'string', 'max:20'],
            'revision' => ['required', 'string', 'max:20'],
            'secondary_code' => ['required', 'string', 'max:50'],
            'prepared_by_name' => ['nullable', 'string', 'max:150'],
            'approved_by_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $campaign->loadMissing('creator:id,name');
        $targets = $campaign->targets()
            ->with(['user.branch:id,name', 'user.department:id,name'])
            ->orderBy('target_name')
            ->get();
        $stats = $this->campaignStats($campaign);

        Settings::setOutputEscapingEnabled(true);
        $word = new PhpWord();
        $word->setDefaultFontName('Arial');
        $word->setDefaultFontSize(9);
        $word->addTableStyle('ReportHeader', [
            'borderSize' => 8,
            'borderColor' => '777777',
            'cellMargin' => 90,
        ]);
        $word->addTableStyle('InfoTable', [
            'borderSize' => 6,
            'borderColor' => '9A9A9A',
            'cellMargin' => 90,
        ]);
        $word->addTableStyle('TargetTable', [
            'borderSize' => 6,
            'borderColor' => '777777',
            'cellMargin' => 75,
        ]);

        $section = $word->addSection([
            'pageSizeW' => 11906,
            'pageSizeH' => 16838,
            'marginTop' => 620,
            'marginRight' => 680,
            'marginBottom' => 920,
            'marginLeft' => 680,
        ]);

        [$logoPath, $temporaryLogo] = $this->grayLogoForExport();

        try {
            $headerTable = $section->addTable('ReportHeader');
            $headerTable->addRow(900);
            $logoCell = $headerTable->addCell(2600, ['valign' => 'center']);
            if ($logoPath) {
                $logoCell->addImage($logoPath, ['width' => 120, 'alignment' => 'center']);
            }
            $titleCell = $headerTable->addCell(6800, ['valign' => 'center']);
            $titleCell->addText('BİLGİ GÜVENLİĞİ DUYARLILIK TESTİ FORMU', [
                'bold' => true,
                'size' => 14,
                'color' => '555555',
            ], ['alignment' => 'center', 'spaceAfter' => 50]);
            $titleCell->addText('OLTALAMA FARKINDALIK SİMÜLASYONU', [
                'size' => 8,
                'color' => '777777',
            ], ['alignment' => 'center']);

            $section->addTextBreak(1);
            $section->addText('TEST BİLGİLERİ', ['bold' => true, 'color' => '555555'], ['alignment' => 'center']);
            $infoTable = $section->addTable('InfoTable');
            $testDate = $campaign->started_at ?: $campaign->created_at;
            $infoRows = [
                ['Testin Adı', $data['test_name']],
                ['Test Tarihi', $testDate->format('d.m.Y')],
                ['Durumu', $campaign->status === PhishingCampaign::STATUS_ACTIVE ? 'Devam ediyor' : 'Tamamlandı'],
                ['Testi Gerçekleştiren', $data['prepared_by_name'] ?: ($campaign->creator?->name ?? '')],
            ];
            foreach ($infoRows as [$label, $value]) {
                $infoTable->addRow();
                $infoTable->addCell(2200, ['bgColor' => 'EEEEEE'])->addText($label, ['bold' => true]);
                $infoTable->addCell(7200)->addText((string) $value);
            }

            $section->addTextBreak(1);
            $section->addText('SİSTEM KAYITLI TEST SONUÇLARI', ['bold' => true, 'color' => '555555'], ['alignment' => 'center']);
            $resultTable = $section->addTable('InfoTable');
            $resultTable->addRow(780);
            $resultCells = [
                ['Toplam Hedef', $stats['total'], 'çalışan'],
                ['Bağlantıyı Açmadı', $stats['unopened'], '%' . number_format(100 - $stats['click_rate'], 1, ',', '.')],
                ['Bağlantıyı Açtı', $stats['clicked'], '%' . number_format($stats['click_rate'], 1, ',', '.')],
                ['Bilgi Göndermeyi Denedi', $stats['attempted'], '%' . number_format($stats['attempt_rate'], 1, ',', '.')],
            ];
            foreach ($resultCells as [$label, $value, $detail]) {
                $cell = $resultTable->addCell(2350, ['valign' => 'center']);
                $cell->addText($label, ['bold' => true, 'size' => 8], ['alignment' => 'center', 'spaceAfter' => 60]);
                $cell->addText((string) $value, ['bold' => true, 'size' => 15, 'color' => '555555'], ['alignment' => 'center', 'spaceAfter' => 40]);
                $cell->addText($detail, ['size' => 8, 'color' => '777777'], ['alignment' => 'center']);
            }

            $section->addTextBreak(1);
            $section->addText('AMAÇ VE DEĞERLENDİRME', ['bold' => true, 'color' => '555555'], ['alignment' => 'center']);
            $notAttempted = max(0, $stats['total'] - $stats['attempted']);
            $notAttemptedRate = $stats['total'] ? round(($notAttempted / $stats['total']) * 100, 1) : 0;
            $section->addText(
                'Bu çalışma, çalışanların şüpheli e-posta ve bağlantılara karşı bilgi güvenliği farkındalığını ölçmek amacıyla kontrollü bir simülasyon olarak uygulanmıştır. Test kapsamında gerçek kullanıcı adı veya parola kaydedilmemiştir. Toplam ' . $notAttempted . ' kişi (%' . number_format($notAttemptedRate, 1, ',', '.') . ') bilgi gönderme girişiminde bulunmamıştır.',
                ['size' => 9],
                ['alignment' => 'both', 'spaceAfter' => 120]
            );

            $section->addText('AÇIKLAMALAR / DÜZELTİCİ FAALİYETLER', ['bold' => true, 'color' => '555555'], ['alignment' => 'center']);
            $section->addText($data['notes'] ?: 'Bilgi güvenliği farkındalığının sürdürülmesi amacıyla periyodik bilgilendirme yapılması önerilir.', [
                'size' => 9,
            ], ['alignment' => 'both', 'spaceAfter' => 180]);

            $signatureTable = $section->addTable('InfoTable');
            $signatureTable->addRow(1050);
            $preparedCell = $signatureTable->addCell(4700, ['valign' => 'bottom']);
            $preparedCell->addText('TESTİ GERÇEKLEŞTİREN', ['bold' => true, 'size' => 8], ['alignment' => 'center']);
            $preparedCell->addTextBreak(2);
            $preparedCell->addText($data['prepared_by_name'] ?: 'Adı Soyadı', ['bold' => true], ['alignment' => 'center']);
            $preparedCell->addText('Tarih / İmza', ['size' => 8, 'color' => '777777'], ['alignment' => 'center']);
            $approvalCell = $signatureTable->addCell(4700, ['valign' => 'bottom']);
            $approvalCell->addText('KONTROL / ONAY', ['bold' => true, 'size' => 8], ['alignment' => 'center']);
            $approvalCell->addTextBreak(2);
            $approvalCell->addText($data['approved_by_name'] ?: 'Adı Soyadı', ['bold' => true], ['alignment' => 'center']);
            $approvalCell->addText('Tarih / İmza', ['size' => 8, 'color' => '777777'], ['alignment' => 'center']);

            $section->addPageBreak();
            $section->addText('TESTE DAHİL EDİLEN ÇALIŞANLAR', ['bold' => true, 'size' => 13, 'color' => '555555'], ['alignment' => 'center', 'spaceAfter' => 120]);
            $targetTable = $section->addTable('TargetTable');
            $targetTable->addRow();
            foreach ([['No', 550], ['Adı Soyadı', 2800], ['Şube', 1700], ['Departman', 2400], ['Sistem Sonucu', 2450]] as [$heading, $width]) {
                $targetTable->addCell($width, ['bgColor' => 'E8E8E8'])->addText($heading, ['bold' => true, 'size' => 8]);
            }
            foreach ($targets as $index => $target) {
                $targetTable->addRow();
                $targetTable->addCell(550)->addText((string) ($index + 1), ['size' => 8]);
                $targetTable->addCell(2800)->addText($target->target_name, ['size' => 8]);
                $targetTable->addCell(1700)->addText($target->user?->branch?->name ?? '—', ['size' => 8]);
                $targetTable->addCell(2400)->addText($target->user?->department?->name ?? '—', ['size' => 8]);
                $status = $target->first_credential_attempted_at
                    ? 'Bilgi göndermeyi denedi'
                    : ($target->first_clicked_at ? 'Bağlantıyı açtı' : 'Bağlantıyı açmadı');
                $targetTable->addCell(2450)->addText($status, ['size' => 8]);
            }

            $footer = $section->addFooter();
            $footerTable = $footer->addTable([
                'borderTopSize' => 8,
                'borderTopColor' => '555555',
                'cellMargin' => 40,
                'width' => 100 * 50,
                'unit' => 'pct',
            ]);
            $footerTable->addRow();
            $footerTable->addCell(5000)->addText($data['form_code'] . ' ' . $data['form_title'], ['size' => 7, 'bold' => true]);
            $footerTable->addCell(2800)->addText('Yayın Tarihi ' . $data['publication_date'], ['size' => 7], ['alignment' => 'center']);
            $footerTable->addCell(1200)->addText($data['revision'], ['size' => 7], ['alignment' => 'right']);
            $footerTable->addRow();
            $footerTable->addCell(5000)->addText($data['secondary_code'], ['size' => 7]);
            $footerTable->addCell(2800)->addText($data['revision'], ['size' => 7], ['alignment' => 'center']);
            $pageCell = $footerTable->addCell(1200);
            $pageRun = $pageCell->addTextRun(['alignment' => 'right']);
            $pageRun->addText('Syf ', ['size' => 7]);
            $pageRun->addField('PAGE', [], ['PreserveFormat']);
            $pageRun->addText('/', ['size' => 7]);
            $pageRun->addField('NUMPAGES', [], ['PreserveFormat']);

            $temporaryDocx = tempnam(sys_get_temp_dir(), 'bhs-phishing-report-');
            if ($temporaryDocx === false) {
                abort(500, 'Word dosyası için geçici alan oluşturulamadı.');
            }
            $docxPath = $temporaryDocx . '.docx';
            @unlink($temporaryDocx);
            IOFactory::createWriter($word, 'Word2007')->save($docxPath);
        } finally {
            if ($temporaryLogo && $logoPath && is_file($logoPath)) {
                @unlink($logoPath);
            }
        }

        return response()
            ->download($docxPath, 'bilgi-guvenligi-test-formu-' . $campaign->id . '.docx')
            ->deleteFileAfterSend(true);
    }

    public function export(PhishingCampaign $campaign)
    {
        $filename = 'oltalama-testi-' . $campaign->id . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($campaign) {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Kullanıcı', 'E-posta', 'Şube', 'Departman', 'Durum',
                'Bağlantı açılma sayısı', 'İlk açılma', 'Son açılma',
                'Bilgi gönderme denemesi sayısı', 'İlk deneme', 'Son deneme', 'Kişisel bağlantı',
            ], ';');

            $campaign->targets()
                ->with(['user.branch:id,name', 'user.department:id,name'])
                ->orderBy('target_name')
                ->chunk(250, function ($targets) use ($handle) {
                    foreach ($targets as $target) {
                        fputcsv($handle, [
                            $this->csvSafe($target->target_name),
                            $this->csvSafe($target->target_email),
                            $this->csvSafe($target->user?->branch?->name),
                            $this->csvSafe($target->user?->department?->name),
                            $target->statusLabel(),
                            $target->click_count,
                            optional($target->first_clicked_at)->format('d.m.Y H:i:s'),
                            optional($target->last_clicked_at)->format('d.m.Y H:i:s'),
                            $target->credential_attempt_count,
                            optional($target->first_credential_attempted_at)->format('d.m.Y H:i:s'),
                            optional($target->last_credential_attempted_at)->format('d.m.Y H:i:s'),
                            route('phishing-simulation.show', $target->token),
                        ], ';');
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadDraft(PhishingCampaign $campaign, PhishingTarget $target)
    {
        abort_unless((int) $target->campaign_id === (int) $campaign->id, 404);
        abort_unless(filter_var($target->target_email, FILTER_VALIDATE_EMAIL), 422, 'Geçerli bir alıcı e-postası bulunamadı.');

        $name = trim(str_replace(["\r", "\n"], ' ', $target->target_name));
        $email = trim(str_replace(["\r", "\n"], '', $target->target_email));
        $subject = 'E-posta Şifrenizin Süresi Doldu';
        $html = view('emails.phishing_simulation', [
            'targetName' => $name,
            'personalUrl' => route('phishing-simulation.show', $target->token),
        ])->render();

        $encodedName = '=?UTF-8?B?' . base64_encode($name) . '?=';
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $eml = implode("\r\n", [
            'MIME-Version: 1.0',
            'X-Unsent: 1',
            'To: ' . $encodedName . ' <' . $email . '>',
            'Subject: ' . $encodedSubject,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            quoted_printable_encode($html),
        ]);

        return response($eml)
            ->header('Content-Type', 'message/rfc822; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="outlook-mail-' . $target->id . '.eml"')
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    private function defaultReportOptions(?string $preparedByName = null): array
    {
        return [
            'form_code' => 'BHS/BIM-F01/P01',
            'form_title' => 'Bilgi Güvenliği Test Formu',
            'publication_date' => '18.08.2026',
            'revision' => 'Rev00',
            'secondary_code' => 'BHS/GID-F02/T03/P01',
            'prepared_by_name' => $preparedByName ?: 'Adı Soyadı',
            'approved_by_name' => 'Adı Soyadı',
        ];
    }

    private function grayLogoForExport(): array
    {
        $sourcePath = public_path('images/logo.png');
        if (!is_file($sourcePath)) {
            return [null, false];
        }

        if (!function_exists('imagecreatefrompng') || !function_exists('imagefilter')) {
            return [$sourcePath, false];
        }

        $image = @imagecreatefrompng($sourcePath);
        if (!$image) {
            return [$sourcePath, false];
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefilter($image, IMG_FILTER_GRAYSCALE);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'bhs-gray-logo-');
        if ($temporaryPath === false) {
            imagedestroy($image);

            return [$sourcePath, false];
        }

        $pngPath = $temporaryPath . '.png';
        @unlink($temporaryPath);
        $saved = imagepng($image, $pngPath);
        imagedestroy($image);

        return $saved ? [$pngPath, true] : [$sourcePath, false];
    }

    private function campaignStats(PhishingCampaign $campaign): array
    {
        $total = $campaign->targets()->count();
        $clicked = $campaign->targets()->whereNotNull('first_clicked_at')->count();
        $attempted = $campaign->targets()->whereNotNull('first_credential_attempted_at')->count();

        return [
            'total' => $total,
            'clicked' => $clicked,
            'attempted' => $attempted,
            'unopened' => max(0, $total - $clicked),
            'click_rate' => $total ? round(($clicked / $total) * 100, 1) : 0,
            'attempt_rate' => $total ? round(($attempted / $total) * 100, 1) : 0,
        ];
    }

    private function csvSafe(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }
}
