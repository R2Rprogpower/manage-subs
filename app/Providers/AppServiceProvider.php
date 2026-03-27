<?php

namespace App\Providers;

use App\Infrastructure\Services\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Services\LiqPayGateway;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserIdentity;
use App\Modules\Auth\Contracts\Repositories\AuthUserRepositoryInterface;
use App\Modules\Auth\Contracts\Services\AuthServiceInterface;
use App\Modules\Auth\Contracts\Services\MfaServiceInterface;
use App\Modules\Auth\Contracts\Services\TokenServiceInterface;
use App\Modules\Auth\Repositories\AuthUserRepository;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Services\MfaService;
use App\Modules\Auth\Services\TokenService;
use App\Modules\Payments\Contracts\Repositories\PaymentRepositoryInterface;
use App\Modules\Payments\Contracts\Services\PaymentServiceInterface;
use App\Modules\Payments\Policies\PaymentPolicy;
use App\Modules\Payments\Repositories\PaymentRepository;
use App\Modules\Payments\Services\PaymentService;
use App\Modules\Permissions\Contracts\Repositories\PermissionRepositoryInterface;
use App\Modules\Permissions\Contracts\Repositories\RoleRepositoryInterface;
use App\Modules\Permissions\Contracts\Services\PermissionServiceInterface;
use App\Modules\Permissions\Contracts\Services\RoleServiceInterface;
use App\Modules\Permissions\Policies\PermissionPolicy;
use App\Modules\Permissions\Policies\RolePolicy;
use App\Modules\Permissions\Repositories\PermissionRepository;
use App\Modules\Permissions\Repositories\RoleRepository;
use App\Modules\Permissions\Services\PermissionService;
use App\Modules\Permissions\Services\RoleService;
use App\Modules\Plans\Contracts\Repositories\PlanRepositoryInterface;
use App\Modules\Plans\Contracts\Services\PlanServiceInterface;
use App\Modules\Plans\Policies\PlanPolicy;
use App\Modules\Plans\Repositories\PlanRepository;
use App\Modules\Plans\Services\PlanService;
use App\Modules\Subscriptions\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;
use App\Modules\Subscriptions\Policies\SubscriptionPolicy;
use App\Modules\Subscriptions\Repositories\SubscriptionRepository;
use App\Modules\Subscriptions\Services\SubscriptionService;
use App\Modules\UserIdentities\Contracts\Repositories\UserIdentityRepositoryInterface;
use App\Modules\UserIdentities\Contracts\Services\UserIdentityServiceInterface;
use App\Modules\UserIdentities\Policies\UserIdentityPolicy;
use App\Modules\UserIdentities\Repositories\UserIdentityRepository;
use App\Modules\UserIdentities\Services\UserIdentityService;
use App\Modules\Users\Contracts\Repositories\UserRepositoryInterface;
use App\Modules\Users\Contracts\Services\UserServiceInterface;
use App\Modules\Users\Policies\UserPolicy;
use App\Modules\Users\Repositories\UserRepository;
use App\Modules\Users\Services\UserService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthUserRepositoryInterface::class, AuthUserRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(MfaServiceInterface::class, MfaService::class);
        $this->app->bind(TokenServiceInterface::class, TokenService::class);

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);

        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);

        $this->app->bind(PlanRepositoryInterface::class, PlanRepository::class);
        $this->app->bind(PlanServiceInterface::class, PlanService::class);

        $this->app->bind(UserIdentityRepositoryInterface::class, UserIdentityRepository::class);
        $this->app->bind(UserIdentityServiceInterface::class, UserIdentityService::class);

        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(SubscriptionServiceInterface::class, SubscriptionService::class);

        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(PaymentGatewayInterface::class, LiqPayGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(UserIdentity::class, UserIdentityPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
    }
}
