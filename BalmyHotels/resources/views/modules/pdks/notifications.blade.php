@extends('layouts.default')

@section('content')
@include('modules.pdks._styles')
<div class="container-fluid">
    <div class="pdks-hero mb-3"><div class="pdks-hero-bar"></div><div class="p-4 d-flex justify-content-between"><div><h4 class="mb-1">PDKS Bildirimleri</h4><div class="pdks-muted">Tekil ve toplu okundu işlemleri</div></div><form method="POST" action="{{ route('pdks.notifications.read-all') }}">@csrf<button class="btn btn-sm pdks-btn-main">Tümünü Okundu Yap</button></form></div></div>
    @include('modules.pdks._nav')
    @include('modules.pdks._flash')
    <div class="pdks-card">
        @forelse($notifications as $notification)
            <div class="p-3 border-bottom d-flex justify-content-between gap-3 {{ $notification->read_at ? '' : 'bg-light' }}">
                <div><strong>{{ $notification->title }}</strong><div class="small text-muted">{{ $notification->body }}</div><small>{{ $notification->created_at->format('d.m.Y H:i') }}</small></div>
                @if(!$notification->read_at)<form method="POST" action="{{ route('pdks.notifications.read',$notification) }}">@csrf<button class="btn btn-sm btn-outline-secondary">Okundu</button></form>@endif
            </div>
        @empty
            <div class="text-center text-muted py-5">Bildirim yok.</div>
        @endforelse
        @if($notifications->hasPages())<div class="p-3 border-top">{{ $notifications->links('pagination::bootstrap-5') }}</div>@endif
    </div>
</div>
@endsection
