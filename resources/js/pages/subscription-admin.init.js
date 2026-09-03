(function () {
    'use strict';

    var token = window.sessionStorage.getItem('access_token');
    var storedUser = window.sessionStorage.getItem('auth_user');
    var user = storedUser ? JSON.parse(storedUser) : null;
    var state = { catalog: [], mine: [], channels: [], plans: [], subscriptions: [], users: [] };
    var isAdmin = user && Array.isArray(user.permissions) && user.permissions.indexOf('subscriptions.view') !== -1;

    if (!token || !user) {
        window.location.href = '/login';
        return;
    }

    document.getElementById('current-user-name').textContent = user.name || user.email;
    document.getElementById('current-user-role').textContent = Array.isArray(user.roles) ? user.roles.join(', ') : '';
    if (!isAdmin) {
        document.querySelectorAll('.admin-only').forEach(function (element) { element.classList.add('d-none'); });
    }

    function escapeHtml(value) {
        var node = document.createElement('div');
        node.textContent = value === null || value === undefined ? '' : String(value);
        return node.innerHTML;
    }

    function showAlert(message, type) {
        var box = document.getElementById('app-alert');
        box.className = 'alert alert-' + (type || 'success');
        box.textContent = message;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function api(path, options) {
        var config = options || {};
        config.headers = Object.assign({ Accept: 'application/json', 'Content-Type': 'application/json', Authorization: 'Bearer ' + token }, config.headers || {});
        var response = await fetch(path, config);
        var payload = {};
        try { payload = await response.json(); } catch (error) { payload = {}; }
        if (!response.ok) {
            var validation = payload.errors ? Object.values(payload.errors).flat().join(' ') : '';
            throw new Error(validation || payload.message || 'Request failed.');
        }
        return payload.data;
    }

    function formatDate(value) {
        return value ? new Date(value).toLocaleDateString() : 'Unlimited';
    }

    function statusBadge(status) {
        var color = status === 'active' ? 'success' : (status === 'draft' ? 'warning' : 'secondary');
        return '<span class="badge bg-' + color + '">' + escapeHtml(status) + '</span>';
    }

    function syncPlanKindFields(kind) {
        var achievement = kind === 'achievement';
        document.getElementById('achievement-configuration').classList.toggle('d-none', !achievement);
        document.querySelector('[name="achievement_key"]').required = achievement;
        document.querySelector('[name="price_minor"]').required = !achievement;
        document.querySelector('[name="currency"]').required = !achievement;
    }

    function renderCatalog() {
        var target = document.getElementById('catalog-list');
        if (!state.catalog.length) {
            target.innerHTML = '<div class="col-12"><div class="alert alert-light border">No channels with available subscription types.</div></div>';
            return;
        }
        target.innerHTML = state.catalog.map(function (channel) {
            var plans = channel.plans.map(function (plan) {
                var price = plan.kind === 'money' ? (plan.price_minor / 100).toFixed(2) + ' ' + plan.currency : 'Achievement';
                var action = plan.kind === 'money'
                    ? '<button class="btn btn-primary btn-sm subscribe-button" data-plan-id="' + plan.id + '">Subscribe</button>'
                    : '<button class="btn btn-light btn-sm" disabled>Coming later</button>';
                return '<div class="d-flex justify-content-between align-items-center border-top py-3"><div><strong>' + escapeHtml(plan.name) + '</strong><br><span class="text-muted">' + escapeHtml(plan.kind) + ' · ' + price + ' · ' + (plan.duration_days ? plan.duration_days + ' days' : 'unlimited') + '</span></div>' + action + '</div>';
            }).join('');
            return '<div class="col-4"><div class="card channel-card"><div class="card-body"><div class="d-flex align-items-center mb-3"><div class="avatar-sm me-3"><span class="avatar-title rounded-circle bg-primary-subtle text-primary"><i class="bx bxl-telegram font-size-24"></i></span></div><div><h5 class="mb-0">' + escapeHtml(channel.title) + '</h5><span class="text-muted">' + (channel.username ? '@' + escapeHtml(channel.username) : escapeHtml(channel.telegram_chat_id)) + '</span></div></div><p class="text-muted">' + escapeHtml(channel.description || 'No description') + '</p>' + plans + '</div></div></div>';
        }).join('');
    }

    function renderMine() {
        document.getElementById('my-subscriptions-body').innerHTML = state.mine.length ? state.mine.map(function (subscription) {
            return '<tr><td>' + escapeHtml(subscription.channel ? subscription.channel.title : '—') + '</td><td>' + escapeHtml(subscription.plan ? subscription.plan.name : '—') + '</td><td>' + statusBadge(subscription.status) + '</td><td>' + (subscription.has_access ? '<span class="text-success">Allowed</span>' : '<span class="text-danger">Denied</span>') + '</td><td>' + formatDate(subscription.ends_at) + '</td></tr>';
        }).join('') : '<tr><td colspan="5" class="text-center text-muted py-4">No subscriptions yet.</td></tr>';
    }

    function renderAdmin() {
        document.getElementById('channels-body').innerHTML = state.channels.map(function (channel) {
            return '<tr><td><strong>' + escapeHtml(channel.title) + '</strong><br><small class="text-muted">' + escapeHtml(channel.owner ? channel.owner.email : '') + '</small></td><td>' + (channel.username ? '@' + escapeHtml(channel.username) : escapeHtml(channel.telegram_chat_id)) + '</td><td>' + statusBadge(channel.status) + '</td><td>' + channel.plans.length + '</td></tr>';
        }).join('') || '<tr><td colspan="4" class="text-center text-muted">No channels.</td></tr>';

        document.getElementById('plan-channel').innerHTML = '<option value="">Choose channel</option>' + state.channels.map(function (channel) { return '<option value="' + channel.id + '">' + escapeHtml(channel.title) + '</option>'; }).join('');
        document.getElementById('plans-body').innerHTML = state.plans.map(function (plan) {
            var price = plan.kind === 'money' ? (plan.price_minor / 100).toFixed(2) + ' ' + escapeHtml(plan.currency) : '—';
            return '<tr><td>' + escapeHtml(plan.channel ? plan.channel.title : 'Unassigned') + '</td><td><strong>' + escapeHtml(plan.name) + '</strong><br><small class="text-muted">' + escapeHtml(plan.code) + '</small></td><td>' + escapeHtml(plan.kind) + '</td><td>' + price + '</td><td>' + (plan.duration_days ? plan.duration_days + ' days' : 'Unlimited') + '</td><td>' + statusBadge(plan.is_active ? 'active' : 'inactive') + '</td></tr>';
        }).join('') || '<tr><td colspan="6" class="text-center text-muted">No subscription types.</td></tr>';

        if (!isAdmin) { return; }
        document.getElementById('grant-user').innerHTML = '<option value="">Choose user</option>' + state.users.map(function (item) { return '<option value="' + item.id + '">' + escapeHtml(item.name + ' · ' + item.email) + '</option>'; }).join('');
        document.getElementById('grant-plan').innerHTML = '<option value="">Choose type</option>' + state.plans.filter(function (plan) { return plan.is_active && plan.telegram_channel_id; }).map(function (plan) { return '<option value="' + plan.id + '">' + escapeHtml((plan.channel ? plan.channel.title + ' · ' : '') + plan.name) + '</option>'; }).join('');
        document.getElementById('subscriptions-body').innerHTML = state.subscriptions.map(function (subscription) {
            var actions = '<div class="btn-group btn-group-sm">';
            if (subscription.status === 'draft') { actions += '<button class="btn btn-outline-primary lifecycle-button" data-action="pending" data-id="' + subscription.id + '">Submit</button>'; }
            if (['pending', 'suspended', 'cancelled', 'expired'].indexOf(subscription.status) !== -1) { actions += '<button class="btn btn-outline-success lifecycle-button" data-action="activate" data-id="' + subscription.id + '">Activate</button>'; }
            if (subscription.status === 'active') { actions += '<button class="btn btn-outline-warning lifecycle-button" data-action="suspend" data-id="' + subscription.id + '">Suspend</button>'; }
            if (['draft', 'pending', 'active', 'suspended'].indexOf(subscription.status) !== -1) { actions += '<button class="btn btn-outline-danger lifecycle-button" data-action="cancel" data-id="' + subscription.id + '">Cancel</button>'; }
            if (['active', 'cancelled', 'expired'].indexOf(subscription.status) !== -1) { actions += '<button class="btn btn-outline-primary lifecycle-button" data-action="renew" data-id="' + subscription.id + '">Renew</button>'; }
            actions += '</div>';
            return '<tr><td>' + escapeHtml(subscription.user ? subscription.user.email : subscription.user_id) + '</td><td>' + escapeHtml(subscription.channel ? subscription.channel.title : '—') + '</td><td>' + escapeHtml(subscription.plan ? subscription.plan.name : '—') + '</td><td>' + statusBadge(subscription.status) + '</td><td>' + (subscription.has_access ? 'Allowed' : 'Denied') + '</td><td>' + formatDate(subscription.ends_at) + '</td><td>' + actions + '</td></tr>';
        }).join('') || '<tr><td colspan="7" class="text-center text-muted">No subscriptions.</td></tr>';
    }

    function renderStats() {
        document.getElementById('stat-channels').textContent = state.catalog.length;
        document.getElementById('stat-my-active').textContent = state.mine.filter(function (item) { return item.has_access; }).length;
        document.getElementById('stat-managed-channels').textContent = state.channels.length;
        if (isAdmin) {
            document.getElementById('stat-subscriptions').textContent = state.subscriptions.length;
        }
    }

    async function loadData() {
        try {
            var common = await Promise.all([api('/api/channels/available'), api('/api/subscriptions/mine')]);
            state.catalog = common[0]; state.mine = common[1];
            var managed = await Promise.all([api('/api/channels'), api('/api/plans')]);
            state.channels = managed[0]; state.plans = managed[1];
            if (isAdmin) {
                var admin = await Promise.all([api('/api/subscriptions'), api('/api/users')]);
                state.subscriptions = admin[0]; state.users = admin[1];
            }
            renderCatalog(); renderMine(); renderAdmin(); renderStats();
        } catch (error) { showAlert(error.message, 'danger'); }
    }

    document.addEventListener('click', async function (event) {
        var subscribeButton = event.target.closest('.subscribe-button');
        var lifecycleButton = event.target.closest('.lifecycle-button');
        if (subscribeButton) {
            if (!window.confirm('MVP payment placeholder: no money will be charged and no payment details are collected. Confirm subscription?')) { return; }
            try {
                await api('/api/subscriptions/checkout', { method: 'POST', body: JSON.stringify({ plan_id: Number(subscribeButton.dataset.planId), confirm_placeholder: true }) });
                showAlert('Subscription activated. No money was charged.'); await loadData();
            } catch (error) { showAlert(error.message, 'danger'); }
        }
        if (lifecycleButton) {
            try {
                await api('/api/subscriptions/' + lifecycleButton.dataset.id + '/' + lifecycleButton.dataset.action, { method: 'POST', body: '{}' });
                showAlert('Subscription updated.'); await loadData();
            } catch (error) { showAlert(error.message, 'danger'); }
        }
    });

    document.getElementById('channel-form').addEventListener('submit', async function (event) {
        event.preventDefault(); var data = Object.fromEntries(new FormData(event.target).entries());
        if (!data.username) { data.username = null; }
        try { await api('/api/channels', { method: 'POST', body: JSON.stringify(data) }); event.target.reset(); showAlert('Channel registered. Add an active subscription type to publish it.'); await loadData(); } catch (error) { showAlert(error.message, 'danger'); }
    });

    document.getElementById('plan-form').addEventListener('submit', async function (event) {
        event.preventDefault(); var data = Object.fromEntries(new FormData(event.target).entries());
        data.telegram_channel_id = Number(data.telegram_channel_id); data.is_active = true; data.duration_days = data.duration_days ? Number(data.duration_days) : null;
        if (data.kind === 'achievement') { data.configuration = { achievement_key: data.achievement_key }; delete data.price_minor; delete data.currency; } else { data.price_minor = Number(data.price_minor); data.configuration = null; }
        delete data.achievement_key;
        try { await api('/api/plans', { method: 'POST', body: JSON.stringify(data) }); event.target.reset(); syncPlanKindFields('money'); showAlert('Subscription type added.'); await loadData(); } catch (error) { showAlert(error.message, 'danger'); }
    });

    document.getElementById('plan-kind').addEventListener('change', function (event) {
        syncPlanKindFields(event.target.value);
    });

    document.getElementById('grant-free-form').addEventListener('submit', async function (event) {
        event.preventDefault(); var data = Object.fromEntries(new FormData(event.target).entries()); data.user_id = Number(data.user_id); data.plan_id = Number(data.plan_id);
        try { await api('/api/subscriptions/grant-free', { method: 'POST', body: JSON.stringify(data) }); event.target.reset(); showAlert('Free access granted.'); await loadData(); } catch (error) { showAlert(error.message, 'danger'); }
    });

    document.getElementById('refresh-button').addEventListener('click', loadData);
    document.getElementById('logout-button').addEventListener('click', async function () {
        try { await api('/api/auth/logout', { method: 'POST', body: '{}' }); } catch (error) { /* local logout still applies */ }
        window.sessionStorage.clear(); window.location.href = '/login';
    });

    loadData();
})();
