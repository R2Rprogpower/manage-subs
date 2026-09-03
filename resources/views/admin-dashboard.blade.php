@extends('layouts.subscriptions-admin')

@section('title', 'Manage Subs — Admin')

@section('content')
<div id="app-alert" class="alert d-none" role="alert"></div>

<section id="overview" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h4 class="mb-1">Subscription overview</h4><p class="text-muted mb-0">Subscription state is the source of truth for channel access.</p></div>
        <button class="btn btn-light" id="refresh-button"><i class="bx bx-refresh me-1"></i>Refresh</button>
    </div>
    <div class="row g-3">
        <div class="col-3"><div class="card stat-card"><div class="card-body"><p class="text-muted mb-2">Available channels</p><h3 id="stat-channels">—</h3></div></div></div>
        <div class="col-3"><div class="card stat-card"><div class="card-body"><p class="text-muted mb-2">My active access</p><h3 id="stat-my-active">—</h3></div></div></div>
        <div class="col-3"><div class="card stat-card"><div class="card-body"><p class="text-muted mb-2">Managed channels</p><h3 id="stat-managed-channels">—</h3></div></div></div>
        <div class="col-3 admin-only"><div class="card stat-card"><div class="card-body"><p class="text-muted mb-2">All subscriptions</p><h3 id="stat-subscriptions">—</h3></div></div></div>
    </div>
</section>

<section id="catalog" class="mb-5">
    <h4>Channel catalog</h4>
    <p class="text-muted">Choose a channel and subscription type. Payment is an MVP placeholder; no money or payment details are collected.</p>
    <div class="row g-3" id="catalog-list"></div>
</section>

<section id="my-subscriptions" class="mb-5">
    <h4>My subscriptions</h4>
    <div class="card"><div class="card-body"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Channel</th><th>Type</th><th>Status</th><th>Access</th><th>Valid until</th></tr></thead><tbody id="my-subscriptions-body"></tbody></table></div></div></div>
</section>

<div>
    <section id="channels" class="mb-5">
        <div class="row g-3">
            <div class="col-4"><div class="card"><div class="card-body"><h5>Register channel</h5><form id="channel-form">
                <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required maxlength="255"></div>
                <div class="mb-3"><label class="form-label">Telegram chat ID</label><input class="form-control" name="telegram_chat_id" required maxlength="100"></div>
                <div class="mb-3"><label class="form-label">Username</label><div class="input-group"><span class="input-group-text">@</span><input class="form-control" name="username" maxlength="100"></div></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
                <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="draft">Draft</option><option value="active">Active</option><option value="unavailable">Unavailable</option></select></div>
                <button class="btn btn-primary w-100">Create channel</button>
            </form></div></div></div>
            <div class="col-8"><div class="card"><div class="card-body"><h5>Managed channels</h5><div class="table-responsive"><table class="table"><thead><tr><th>Channel</th><th>Telegram</th><th>Status</th><th>Types</th></tr></thead><tbody id="channels-body"></tbody></table></div></div></div></div>
        </div>
    </section>

    <section id="plans" class="mb-5">
        <div class="row g-3">
            <div class="col-4"><div class="card"><div class="card-body"><h5>Add subscription type</h5><form id="plan-form">
                <div class="mb-3"><label class="form-label">Channel</label><select class="form-select" name="telegram_channel_id" id="plan-channel" required></select></div>
                <div class="mb-3"><label class="form-label">Code</label><input class="form-control" name="code" required maxlength="100"></div>
                <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
                <div class="row"><div class="col-7 mb-3"><label class="form-label">Price (minor units)</label><input class="form-control" type="number" min="0" name="price_minor" required></div><div class="col-5 mb-3"><label class="form-label">Currency</label><input class="form-control" name="currency" value="USD" maxlength="3" required></div></div>
                <div class="mb-3"><label class="form-label">Duration, days</label><input class="form-control" type="number" min="1" name="duration_days"><div class="form-text">Leave empty for unlimited.</div></div>
                <input type="hidden" name="is_active" value="1"><button class="btn btn-primary w-100">Add type</button>
            </form></div></div></div>
            <div class="col-8"><div class="card"><div class="card-body"><h5>Subscription types</h5><div class="table-responsive"><table class="table"><thead><tr><th>Channel</th><th>Name</th><th>Price</th><th>Duration</th><th>Status</th></tr></thead><tbody id="plans-body"></tbody></table></div></div></div></div>
        </div>
    </section>

    <section id="subscriptions" class="mb-5 admin-only"><div class="d-flex justify-content-between align-items-center mb-3"><div><h4 class="mb-1">All subscriptions</h4><p class="text-muted mb-0">Lifecycle changes are recorded in the audit log.</p></div><form id="grant-free-form" class="d-flex gap-2"><select class="form-select" id="grant-user" name="user_id" required></select><select class="form-select" id="grant-plan" name="plan_id" required></select><button class="btn btn-primary text-nowrap">Grant free access</button></form></div><div class="card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>Channel</th><th>Type</th><th>Status</th><th>Access</th><th>Valid until</th><th>Actions</th></tr></thead><tbody id="subscriptions-body"></tbody></table></div></div></div></section>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('build/js/pages/subscription-admin.init.js') }}"></script>
@endsection
