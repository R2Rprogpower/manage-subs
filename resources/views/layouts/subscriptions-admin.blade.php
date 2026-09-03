<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Manage Subs')</title>
    @include('layouts.head-css')
    <style>
        body { background: #f5f7fb; }
        .admin-shell { min-width: 1180px; }
        .admin-sidebar { width: 250px; min-height: 100vh; background: #2a3042; position: fixed; inset: 0 auto 0 0; padding: 24px 18px; color: #fff; }
        .admin-main { margin-left: 250px; min-height: 100vh; }
        .admin-nav .nav-link { color: #a6b0cf; border-radius: 6px; margin-bottom: 6px; }
        .admin-nav .nav-link.active, .admin-nav .nav-link:hover { color: #fff; background: rgba(255,255,255,.09); }
        .stat-card { border: 0; box-shadow: 0 2px 8px rgba(30, 42, 64, .08); }
        .table td { vertical-align: middle; }
        .channel-card { border: 1px solid #e7eaf0; height: 100%; }
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <h4 class="mb-1">Manage Subs</h4>
        <p class="text-white-50 mb-4">Telegram subscriptions</p>
        <nav class="nav flex-column admin-nav" id="admin-nav">
            <a class="nav-link active" href="#overview"><i class="bx bx-grid-alt me-2"></i>Overview</a>
            <a class="nav-link" href="#catalog"><i class="bx bx-store me-2"></i>Channel catalog</a>
            <a class="nav-link" href="#my-subscriptions"><i class="bx bx-user-check me-2"></i>My subscriptions</a>
            <a class="nav-link" href="#channels"><i class="bx bxl-telegram me-2"></i>My channels</a>
            <a class="nav-link" href="#plans"><i class="bx bx-purchase-tag me-2"></i>Subscription types</a>
            <a class="nav-link admin-only" href="#subscriptions"><i class="bx bx-list-check me-2"></i>All subscriptions</a>
        </nav>
    </aside>
    <main class="admin-main">
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <div><strong id="current-user-name">Loading…</strong><span class="text-muted ms-2" id="current-user-role"></span></div>
            <button class="btn btn-outline-secondary btn-sm" id="logout-button"><i class="bx bx-log-out me-1"></i>Log out</button>
        </header>
        <div class="p-4">@yield('content')</div>
    </main>
</div>
@include('layouts.vendor-scripts')
@yield('script')
</body>
</html>
