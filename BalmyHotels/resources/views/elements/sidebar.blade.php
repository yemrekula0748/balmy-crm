@php $user = auth()->user(); @endphp

<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">

            {{-- Dashboard — herkese görünür --}}
            <li>
                <a href="{{ url('index') }}" class="ai-icon" aria-expanded="false">
                    <i class="flaticon-dashboard-1"></i>
                    <span class="nav-text">Ana Sayfa</span>
                </a>
            </li>

            {{-- KULLANICI YÖNETİMİ --}}
            @if($user->hasPermission('users', 'index'))
            <li @class(['mm-active' => request()->is('kullanicilar*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span class="nav-text">Çalışanlar</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('users.index') }}">Tüm Kullanıcılar</a></li>
                    @if($user->hasPermission('users', 'create'))
                    <li><a href="{{ route('users.create') }}">Yeni Kullanıcı Ekle</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- DEPARTMAN --}}
            @if($user->hasPermission('departments', 'index'))
            <li @class(['mm-active' => request()->is('departmanlar*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <rect x="2" y="7" width="20" height="14" rx="2"></rect>
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"></path>
                    </svg>
                    <span class="nav-text">Departmanlar</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('departments.index') }}">Tüm Departmanlar</a></li>
                    @if($user->hasPermission('departments', 'create'))
                    <li><a href="{{ route('departments.create') }}">Yeni Departman Ekle</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- KAPI GİRİŞ/ÇIKIŞ --}}
            @if($user->hasPermission('door_logs', 'index'))
            <li @class(['mm-active' => request()->is('kapi-giris*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <path d="M13 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span class="nav-text">Kapı Giriş/Çıkış</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('door-logs.index') }}">Kayıtlar</a></li>
                    @if($user->hasPermission('door_logs', 'create'))
                    <li><a href="{{ route('door-logs.create') }}">Manuel Kayıt Ekle</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- MİSAFİR KAYITLARI --}}
            @if($user->hasPermission('guest_logs', 'index'))
            <li @class(['mm-active' => request()->is('misafir-giris*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                        <path d="M12 11v4"></path>
                        <path d="M10 13h4"></path>
                    </svg>
                    <span class="nav-text">Ziyaretçi Kayıtları</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('guest-logs.index') }}">Tüm Kayıtlar</a></li>
                    @if($user->hasPermission('guest_logs', 'create'))
                    <li><a href="{{ route('guest-logs.create') }}">Ziyaretçi Ekle</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- TEKNİK ARIZA --}}
            @if($user->hasPermission('faults', 'index') || $user->hasPermission('faults', 'create'))
            <li @class(['mm-active' => request()->is('arizalar*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"></path>
                        <line x1="12" y1="2" x2="12" y2="5"></line>
                        <line x1="12" y1="19" x2="12" y2="22"></line>
                        <line x1="2" y1="12" x2="5" y2="12"></line>
                        <line x1="19" y1="12" x2="22" y2="12"></line>
                    </svg>
                    <span class="nav-text">Teknik Arıza</span>
                </a>
                <ul aria-expanded="false">
                    @if($user->hasPermission('faults', 'create'))
                    <li><a href="{{ route('faults.create') }}">Arıza Bildir</a></li>
                    @endif
                    @if($user->hasPermission('faults', 'index'))
                    <li><a href="{{ route('faults.my-reports') }}">Bildirdiklerim</a></li>
                    @if($user->department_id)
                    <li><a href="{{ route('faults.incoming') }}">Gelen Arızalar</a></li>
                    <li><a href="{{ route('faults.my-department') }}">Departmanım</a></li>
                    @endif
                    @if($user->hasAnyRole(['super_admin', 'branch_manager']))
                    <li><a href="{{ route('faults.index') }}">Tüm Arızalar</a></li>
                    @endif
                    @endif
                    @if($user->hasPermission('fault_locations', 'index'))
                    <li><a href="{{ route('faults.locations.index') }}">Konumlar</a></li>
                    @endif
                    @if($user->hasPermission('fault_types', 'index'))
                    <li><a href="{{ route('faults.types.index') }}">Arıza Türleri</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- DEMİRBAŞ --}}
            @if($user->hasPermission('assets', 'index') || $user->hasPermission('asset_exits', 'index') || $user->hasPermission('asset_categories', 'index'))
            <li @class(['mm-active' => request()->is('demirbaslar*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    <span class="nav-text">Demirbaş</span>
                </a>
                <ul aria-expanded="false">
                    @if($user->hasPermission('assets', 'index'))
                    <li><a href="{{ route('assets.index') }}">Tüm Demirbaşlar</a></li>
                    @endif
                    @if($user->hasPermission('assets', 'create'))
                    <li><a href="{{ route('assets.create') }}">Demirbaş Ekle</a></li>
                    @endif
                    @if($user->hasPermission('asset_exits', 'index'))
                    <li><a href="{{ route('asset-exits.index') }}">Çıkış Formları</a></li>
                    @endif
                    @if($user->hasPermission('asset_exits', 'create'))
                    <li><a href="{{ route('asset-exits.create') }}">Çıkış Formu Oluştur</a></li>
                    @endif
                    @if($user->hasPermission('asset_categories', 'index'))
                    <li><a href="{{ route('asset-categories.index') }}">Kategoriler</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- ARAÇ YÖNETİMİ --}}
            @if($user->hasPermission('vehicles', 'index'))
            <li @class(['mm-active' => request()->is('araclar*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <rect x="1" y="3" width="15" height="13" rx="2"></rect>
                        <path d="M16 8h4l3 5v3h-7V8z"></path>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                    <span class="nav-text">Araç Yönetimi</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('vehicles.index') }}">Tüm Araçlar</a></li>
                    @if($user->hasPermission('vehicles', 'create'))
                    <li><a href="{{ route('vehicles.create') }}">Yeni Araç Ekle</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- QR MENÜ --}}
            @if($user->hasPermission('qrmenus', 'index'))
            <li @class(['mm-active' => request()->is('qr-menuler*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                        <rect x="14" y="14" width="3" height="3"></rect>
                        <rect x="18" y="14" width="3" height="3"></rect>
                        <rect x="14" y="18" width="3" height="3"></rect>
                        <rect x="18" y="18" width="3" height="3"></rect>
                    </svg>
                    <span class="nav-text">QR Menü</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('qrmenus.index') }}">Tüm Menüler</a></li>
                    @if($user->hasPermission('qrmenus', 'create'))
                    <li><a href="{{ route('qrmenus.create') }}">Yeni Menü Oluştur</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- MİSAFİR ANKET --}}
            @if($user->hasPermission('surveys', 'index'))
            <li @class(['mm-active' => request()->is('anketler*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <span class="nav-text">Misafir Anket</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('surveys.index') }}">Anketlerim</a></li>
                    @if($user->hasPermission('surveys', 'create'))
                    <li><a href="{{ route('surveys.create') }}">Yeni Anket Oluştur</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- YEMEK İSİMLİK --}}
            @if($user->hasPermission('food_labels', 'index'))
            <li @class(['mm-active' => request()->is('yemek-isimlikler*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <path d="M3 11l19-9-9 19-2-8-8-2z"></path>
                    </svg>
                    <span class="nav-text">Yemek İsimlik</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('food-labels.index') }}">İsimlikler</a></li>
                    @if($user->hasPermission('food_labels', 'create'))
                    <li><a href="{{ route('food-labels.create') }}">Yeni İsimlik</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- PERSONEL ANKET --}}
            @if($user->hasPermission('staff_surveys', 'index'))
            <li @class(['mm-active' => request()->is('personel-anketleri*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <span class="nav-text">Personel Anket</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('staff-surveys.index') }}">Anketlerim</a></li>
                    @if($user->hasPermission('staff_surveys', 'create'))
                    <li><a href="{{ route('staff-surveys.create') }}">Yeni Anket</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- YETKİ YÖNETİMİ — sadece super_admin --}}
            @if($user->isSuperAdmin())
            <li @class(['mm-active' => request()->is('roller*')])>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="min-width:20px">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span class="nav-text">Yetki Yönetimi</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('roles.index') }}">Roller &amp; İzinler</a></li>
                </ul>
            </li>
            @endif

        </ul>
    </div>
</div>
