<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Görev Hatırlatıcı</title>
    <style>
        body { margin:0; padding:0; background:#f4f6fb; font-family:'Segoe UI',Arial,sans-serif; }
        .wrapper { max-width:560px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
        .header  { background:linear-gradient(135deg,#4361ee,#7b8cde); padding:32px 36px; }
        .header h1 { margin:0; color:#fff; font-size:22px; font-weight:700; letter-spacing:-.3px; }
        .header p  { margin:6px 0 0; color:rgba(255,255,255,.8); font-size:13px; }
        .body    { padding:28px 36px; }
        .task-box { border:1.5px solid #e0e7ff; border-radius:12px; padding:20px 22px; background:#f8f9ff; margin:18px 0; }
        .task-title { font-size:18px; font-weight:700; color:#1e2a5e; margin-bottom:10px; }
        .task-meta  { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
        .badge      { border-radius:20px; padding:3px 12px; font-size:12px; font-weight:600; display:inline-block; }
        .badge-high   { background:#fee2e2; color:#dc2626; }
        .badge-medium { background:#ffedd5; color:#ea580c; }
        .badge-low    { background:#dcfce7; color:#16a34a; }
        .badge-deadline { background:#ede9fe; color:#7c3aed; }
        .desc { color:#4b5563; font-size:13px; line-height:1.6; margin-top:10px; }
        .cta  { text-align:center; margin:24px 0; }
        .cta a { background:linear-gradient(135deg,#4361ee,#6b7de8); color:#fff; text-decoration:none; padding:12px 32px; border-radius:30px; font-weight:700; font-size:14px; display:inline-block; }
        .footer { background:#f8f9fb; padding:18px 36px; text-align:center; font-size:11px; color:#9ca3af; border-top:1px solid #f0f0f0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>⏰ Görev Hatırlatıcı</h1>
        <p>{{ config('app.name') }} — Kişisel Görev Yönetimi</p>
    </div>
    <div class="body">
        <p style="color:#374151;font-size:14px;margin-top:0">
            Merhaba <strong>{{ $task->user->name }}</strong>,
        </p>
        <p style="color:#374151;font-size:14px">
            Aşağıdaki görevinizin bitiş tarihi <strong style="color:#ef4444">yarın</strong>! Görevi zamanında tamamlamayı unutmayın.
        </p>

        <div class="task-box">
            <div class="task-title">{{ $task->title }}</div>
            <div class="task-meta">
                <span class="badge badge-{{ $task->priority }}">
                    {{ $task->priority_label }} Öncelik
                </span>
                <span class="badge badge-deadline">
                    📅 Son tarih: {{ $task->due_date->format('d.m.Y') }}
                </span>
            </div>
            @if($task->description)
            <div class="desc">{{ $task->description }}</div>
            @endif
        </div>

        <div class="cta">
            <a href="{{ url('/islerim') }}">Görevlerime Git →</a>
        </div>

        <p style="color:#9ca3af;font-size:12px;text-align:center;margin-bottom:0">
            Bu e-posta otomatik olarak gönderilmiştir.
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }} · Tüm hakları saklıdır.
    </div>
</div>
</body>
</html>
