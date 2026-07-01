<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scholife — @yield('title', 'Admin')</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f0eb;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Overlay ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 200;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.active { display: block; }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg,
            #8b1c2c 0%,
            #6b1020 25%,
            #3d0a12 55%,
            #1a0408 80%,
            #000000 100%
            );
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            overflow-y: auto;
            z-index: 300;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 24px rgba(0,0,0,0.4);
        }

        .sidebar.open { transform: translateX(0); }

        /* Scrollbar */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }

        /* ── Sidebar header ── */
        .sidebar-header {
            background: linear-gradient(135deg, #c8b89a 0%, #a89070 100%);
            height: 100px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 10px;
            flex-shrink: 0;
        }

        .avatar {
            width: 62px; height: 62px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.9);
            overflow: hidden;
            background: #444;
            box-shadow: 0 2px 12px rgba(0,0,0,0.3);
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }

        .admin-info {
            background: rgba(0,0,0,0.3);
            text-align: center;
            padding: 8px 12px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .admin-name {
            color: #fff;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .admin-role {
            color: rgba(255,255,255,0.6);
            font-size: 0.7rem;
            letter-spacing: 0.3px;
        }

        /* ── Nav ── */
        .nav-section {
            padding: 14px 0 5px 18px;
            color: rgba(255,255,255,0.35);
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 0.82rem;
            transition: all 0.15s;
            border-left: 3px solid transparent;
            position: relative;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
            border-left-color: rgba(255,255,255,0.3);
        }

        .nav-item.active {
            background: rgba(255,255,255,0.12);
            border-left-color: #f0a500;
            color: #fff;
            font-weight: 600;
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: #f0a500;
            border-radius: 0 2px 2px 0;
        }

        .ui-icon {
            display: inline-block;
            vertical-align: -0.15em;
            flex-shrink: 0;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .logout-area {
            margin-top: auto;
            padding: 16px 18px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 16px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 8px;
            color: rgba(255,255,255,0.8);
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.3px;
        }
        .logout-btn:hover {
            background: rgba(220,53,69,0.3);
            border-color: rgba(220,53,69,0.5);
            color: #fff;
        }

        /* ── Main ── */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }

        /* ── Topbar ── */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            background: #fdf8f3;
            border-bottom: 1px solid #e8ddd5;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar h1 {
            flex: 1;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 900;
            color: #8b1c2c;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .hamburger {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: background 0.15s;
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: center;
            justify-content: center;
        }
        .hamburger:hover { background: #f0e8e8; }

        .hamburger span {
            display: block;
            width: 22px;
            height: 2.5px;
            background: #8b1c2c;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .content { padding: 24px; flex: 1; }

        /* ── Stat cards ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .stat-card {
            background: #fff;
            border: 1.5px solid #d9b8bc;
            border-radius: 10px;
            padding: 16px 18px;
        }
        .stat-label { font-size: 0.73rem; color: #666; margin-bottom: 6px; }
        .stat-value { font-size: 1.9rem; font-weight: 800; color: #8b1c2c; line-height: 1; }
        .stat-sub   { font-size: 0.68rem; color: #888; margin-top: 4px; }

        /* ── Panel ── */
        .panel {
            background: #fff;
            border: 1.5px solid #d9b8bc;
            border-radius: 10px;
            padding: 20px 22px;
            margin-bottom: 20px;
        }
        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .panel-title { font-size: 1.05rem; font-weight: 800; color: #8b1c2c; }

        /* ── Buttons ── */
        .btn {
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 0.77rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #8b1c2c;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            line-height: 1.4;
        }
        .btn-primary { background: #8b1c2c; color: #fff; }
        .btn-primary:hover { background: #6e1522; }
        .btn-outline { background: #fff; color: #8b1c2c; }
        .btn-outline:hover { background: #fdf0f1; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            font-size: 0.67rem; font-weight: 700; letter-spacing: 0.8px;
            text-transform: uppercase; color: #8b1c2c;
            padding: 8px 6px; border-bottom: 1px solid #eee; text-align: left;
        }
        tbody td {
            font-size: 0.82rem; color: #333;
            padding: 11px 6px; border-bottom: 1px solid #f0e8e8; vertical-align: middle;
        }

        /* ── Badges ── */
        .badge { display: inline-block; padding: 3px 8px; border-radius: 20px; font-size: 0.64rem; font-weight: 700; }
        .badge-green  { background: #d4edda; color: #155724; }
        .badge-red    { background: #f8d7da; color: #721c24; }
        .badge-yellow { background: #fff3cd; color: #856404; }
        .badge-blue   { background: #cce5ff; color: #004085; }
        .badge-gray   { background: #e2e3e5; color: #383d41; }

        /* ── Layout helpers ── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 20px; }

        /* ── Alerts ── */
        .alert-success {
            background: #d4edda; color: #155724;
            border: 1px solid #c3e6cb; border-radius: 8px;
            padding: 12px 16px; margin-bottom: 16px; font-size: .85rem; font-weight: 600;
        }
        .alert-error {
            background: #f8d7da; color: #721c24;
            border: 1px solid #f5c6cb; border-radius: 8px;
            padding: 12px 16px; margin-bottom: 16px; font-size: .85rem; font-weight: 600;
        }
        .alert-success, .alert-error { display: flex; align-items: center; gap: 8px; }
        .icon-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            color: #8b1c2c;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .icon-button:hover { background: #fdf0f1; color: #6e1522; }
        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 28px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: .85rem;
            font-weight: 700;
            padding: 3px 7px;
            border-radius: 4px;
            color: #333;
            transition: background .15s;
        }
        .toolbar-btn:hover { background: #d1fae5; }
        .rank-gold { color: #f0a500; }
        .rank-silver { color: #8a8f98; }
        .rank-bronze { color: #b36b2c; }

        @media (max-width: 768px) {
            .two-col { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

@php
    $admin = Auth::guard('admin')->user();
    $adminAvatarUrl = $admin && $admin->avatar ? asset('storage/'.$admin->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($admin->name).'&background=8b1c2c&color=fff';
@endphp

{{-- Overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- Sidebar --}}
<aside class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <a href="{{ route('admin.admin-accounts.show', $admin->id) }}" onclick="closeSidebar()">
            <div class="avatar" style="cursor:pointer;transition:opacity .2s" onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                <img src="{{ $adminAvatarUrl }}" alt="avatar">
            </div>
        </a>
    </div>


    <div class="admin-info">
        <div class="admin-name">{{ strtoupper($admin->name) }}</div>
        <div class="admin-role">{{ ucfirst(str_replace('_',' ', $admin->role)) }}</div>
    </div>

    {{-- OVERVIEW --}}
    @if($admin->hasPermission('dashboard') || $admin->hasPermission('analytics'))
        <div class="nav-section">Overview</div>
    @endif

    @if($admin->hasPermission('dashboard'))
        <a href="{{ route('admin.dashboard') }}"
           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="home" class="nav-icon" /> Dashboard
        </a>
    @endif

    @if($admin->hasPermission('analytics'))
        <a href="{{ route('admin.analytics') }}"
           class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="chart" class="nav-icon" /> Analytics
        </a>
    @endif

    {{-- MANAGEMENT --}}
    @if($admin->hasPermission('users') || $admin->hasPermission('announcements') || $admin->hasPermission('events') || $admin->hasPermission('organizations') || $admin->hasPermission('admin-accounts'))
        <div class="nav-section">Management</div>
    @endif

    @if($admin->hasPermission('users'))
        <a href="{{ route('admin.users') }}"
           class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="users" class="nav-icon" /> Users
        </a>
    @endif

    @if($admin->hasPermission('announcements'))
        <a href="{{ route('admin.announcements') }}"
           class="nav-item {{ request()->routeIs('admin.announcements') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="megaphone" class="nav-icon" /> Announcements
        </a>
    @endif

    @if($admin->hasPermission('events'))
        <a href="{{ route('admin.events') }}"
           class="nav-item {{ request()->routeIs('admin.events') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="calendar" class="nav-icon" /> Events
        </a>
    @endif

    @if($admin->hasPermission('organizations'))
        <a href="{{ route('admin.organizations') }}"
           class="nav-item {{ request()->routeIs('admin.organizations') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="building" class="nav-icon" /> Organizations
        </a>
    @endif

    @if($admin->hasPermission('admin-accounts'))
        <a href="{{ route('admin.admin-accounts') }}"
           class="nav-item {{ request()->routeIs('admin.admin-accounts') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="shield" class="nav-icon" /> Admin Accounts
        </a>
    @endif

    {{-- MODERATION --}}
    @if($admin->hasPermission('reports'))
        <div class="nav-section">Moderation</div>
        <a href="{{ route('admin.reports') }}"
           class="nav-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="flag" class="nav-icon" /> Reports
        </a>
    @endif

    {{-- ACADEMIC --}}
    @if($admin->hasPermission('academic-notices') || $admin->hasPermission('points'))
        <div class="nav-section">Academic</div>
    @endif

    @if($admin->hasPermission('academic-notices'))
        <a href="{{ route('admin.academic-notices') }}"
           class="nav-item {{ request()->routeIs('admin.academic-notices') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="clipboard" class="nav-icon" /> Academic Notices
        </a>
    @endif

    @if($admin->hasPermission('points'))
        <a href="{{ route('admin.points') }}"
           class="nav-item {{ request()->routeIs('admin.points') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="trophy" class="nav-icon" /> Points System
        </a>
    @endif

    {{-- SYSTEM --}}
    @if($admin->hasPermission('logs'))
        <div class="nav-section">System</div>
        <a href="{{ route('admin.logs') }}"
           class="nav-item {{ request()->routeIs('admin.logs') ? 'active' : '' }}"
           onclick="closeSidebar()">
            <x-icon name="pencil" class="nav-icon" /> Activity Logs
        </a>
    @endif

    {{-- Logout --}}
    <div class="logout-area">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <x-icon name="logout" /> LOG OUT
            </button>
        </form>
    </div>

</aside>

{{-- Main --}}
<div class="main">
    <div class="topbar">
        <button class="hamburger" onclick="openSidebar()" title="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <h1>@yield('title', 'Dashboard')</h1>

        <img src="{{ asset('scholife-logo.png') }}" width="38" height="38" style="object-fit:contain;border-radius:4px" alt="Scholife">
    </div>

    <div class="content">
        @if(session('success'))
            <div class="alert-success"><x-icon name="check-circle" /> {{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert-error"><x-icon name="x-circle" /> {{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</div>

<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });
</script>

</body>
</html>

