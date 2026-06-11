@push('styles')
<style>
.mr-page{padding-bottom:32px}
.mr-title h4{margin:0;font-weight:700;color:#18212f}
.mr-title span{font-size:.82rem;color:#6b7280}
.mr-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0 16px}
.mr-tab{display:inline-flex;align-items:center;gap:7px;border:1px solid #dce3ea;border-radius:8px;padding:8px 12px;background:#fff;color:#344054;font-weight:700;font-size:.78rem}
.mr-tab:hover{color:#111827;border-color:#aebdcc}
.mr-tab.active{background:#162033;color:#fff;border-color:#162033}
.mr-card{background:#fff;border:1px solid #e6ebf1;border-radius:8px;box-shadow:0 1px 5px rgba(15,23,42,.05)}
.mr-kpi{height:100%;padding:15px;border-left:4px solid var(--mr-color)}
.mr-kpi-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:var(--mr-bg);color:var(--mr-color)}
.mr-kpi-value{font-size:1.55rem;line-height:1;font-weight:800;color:#172033;margin-top:10px}
.mr-kpi-label{font-size:.68rem;letter-spacing:.05em;text-transform:uppercase;font-weight:800;color:var(--mr-color);margin-top:6px}
.mr-kpi-sub{font-size:.72rem;color:#7a8594;margin-top:3px}
.mr-filter{padding:14px}
.mr-section-title{display:flex;align-items:center;gap:8px;padding:14px 16px;border-bottom:1px solid #edf1f5;font-weight:800;color:#243044}
.mr-section-title i{color:#64748b}
.mr-note-list{padding:14px 18px;margin:0}
.mr-note-list li{margin:0 0 8px 0;color:#344054;font-size:.86rem;line-height:1.55}
.mr-note-list li:last-child{margin-bottom:0}
.mr-table{margin:0}
.mr-table th{background:#f8fafc;color:#64748b;font-size:.68rem;letter-spacing:.04em;text-transform:uppercase;border-bottom:1px solid #e6ebf1;padding:.7rem .85rem}
.mr-table td{font-size:.82rem;color:#344054;vertical-align:middle;padding:.72rem .85rem;border-bottom:1px solid #eef2f6}
.mr-pill{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:3px 9px;font-size:.68rem;font-weight:800}
.mr-pill.green{background:#ecfdf3;color:#067647}
.mr-pill.amber{background:#fffaeb;color:#b54708}
.mr-pill.red{background:#fef3f2;color:#b42318}
.mr-pill.blue{background:#eff8ff;color:#175cd3}
.mr-pill.gray{background:#f2f4f7;color:#475467}
.mr-progress{height:6px;border-radius:99px;background:#eef2f6;overflow:hidden;min-width:80px}
.mr-progress span{display:block;height:100%;border-radius:99px;background:var(--mr-color)}
.mr-chart{position:relative;height:260px;padding:16px}
.mr-empty{padding:22px;text-align:center;color:#8a94a6;font-size:.86rem}
.mr-compact-stat{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border-bottom:1px solid #eef2f6}
.mr-compact-stat:last-child{border-bottom:0}
.mr-compact-stat strong{color:#172033}
@media (max-width: 767.98px){
    .mr-chart{height:220px}
    .mr-tab{width:100%;justify-content:center}
}
</style>
@endpush
