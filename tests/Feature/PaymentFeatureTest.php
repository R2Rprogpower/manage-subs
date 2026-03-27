<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Modules\Payments\Enums\Permission as PaymentPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_crud_payment(): void
    {
        $manager = $this->createManagerUser(PaymentPermission::values());
        Sanctum::actingAs($manager);

        $subscription = Subscription::factory()->create([
            'user_id' => User::factory()->create()->id,
            'plan_id' => Plan::factory()->create()->id,
        ]);

        $storeResponse = $this->postJson('/api/payments', [
            'subscription_id' => $subscription->id,
            'provider' => 'stripe',
            'provider_payment_id' => 'pi_1001',
            'status' => 'pending',
            'amount_minor' => 1999,
            'currency' => 'usd',
        ]);

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $id = (int) $storeResponse->json('data.id');

        $this->patchJson("/api/payments/{$id}", [
            'status' => 'paid',
            'paid_at' => now()->toIso8601String(),
        ])->assertOk()->assertJsonPath('data.status', 'paid');

        $this->getJson("/api/payments/{$id}")
            ->assertOk()
            ->assertJsonPath('data.subscription_id', $subscription->id);

        $this->deleteJson("/api/payments/{$id}")
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $this->assertDatabaseMissing((new Payment)->getTable(), ['id' => $id]);
    }

    public function test_user_without_permission_cannot_create_payment(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $subscription = Subscription::factory()->create();

        $this->postJson('/api/payments', [
            'subscription_id' => $subscription->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount_minor' => 100,
            'currency' => 'USD',
        ])->assertForbidden();
    }

    public function test_manager_can_create_liqpay_checkout_payload(): void
    {
        Config::set('services.liqpay.public_key', 'public_key_test');
        Config::set('services.liqpay.private_key', 'private_key_test');
        Config::set('services.liqpay.base_url', 'https://www.liqpay.ua');

        $manager = $this->createManagerUser(PaymentPermission::values());
        Sanctum::actingAs($manager);

        $subscription = Subscription::factory()->create([
            'user_id' => User::factory()->create()->id,
            'plan_id' => Plan::factory()->create()->id,
            'status' => 'pending',
        ]);

        $payment = Payment::query()->create([
            'subscription_id' => $subscription->id,
            'provider' => 'liqpay',
            'provider_payment_id' => null,
            'status' => 'pending',
            'amount_minor' => 2599,
            'currency' => 'UAH',
            'paid_at' => null,
        ]);

        $response = $this->postJson('/api/payments/checkout/liqpay', [
            'payment_id' => $payment->id,
            'description' => 'Pro plan',
            'action' => 'pay',
            'sandbox' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.provider', 'liqpay')
            ->assertJsonPath('data.checkout_url', 'https://www.liqpay.ua/api/3/checkout')
            ->assertJsonPath('data.form_fields.data', fn ($value) => is_string($value) && $value !== '')
            ->assertJsonPath('data.form_fields.signature', fn ($value) => is_string($value) && $value !== '');
    }

    public function test_liqpay_webhook_marks_payment_paid_and_activates_subscription(): void
    {
        Config::set('services.liqpay.public_key', 'public_key_test');
        Config::set('services.liqpay.private_key', 'private_key_test');

        $subscription = Subscription::factory()->create([
            'user_id' => User::factory()->create()->id,
            'plan_id' => Plan::factory()->create()->id,
            'status' => 'pending',
        ]);

        $payment = Payment::query()->create([
            'subscription_id' => $subscription->id,
            'provider' => 'liqpay',
            'provider_payment_id' => null,
            'status' => 'pending',
            'amount_minor' => 1999,
            'currency' => 'UAH',
            'paid_at' => null,
        ]);

        $event = [
            'status' => 'success',
            'payment_id' => 'liqpay-payment-1001',
            'order_id' => 'payment-'.$payment->id,
            'amount' => 19.99,
            'currency' => 'UAH',
        ];

        $encodedData = base64_encode((string) json_encode($event, JSON_UNESCAPED_SLASHES));
        $privateKey = (string) config('services.liqpay.private_key');
        $signature = base64_encode(sha1($privateKey.$encodedData.$privateKey, true));

        $this->post('/api/payments/webhooks/liqpay', [
            'data' => $encodedData,
            'signature' => $signature,
        ])->assertOk()->assertJsonPath('ok', true);

        $this->assertDatabaseHas((new Payment)->getTable(), [
            'id' => $payment->id,
            'status' => 'paid',
            'provider_payment_id' => 'liqpay-payment-1001',
        ]);

        $this->assertDatabaseHas((new Subscription)->getTable(), [
            'id' => $subscription->id,
            'status' => 'active',
        ]);
    }

    public function test_liqpay_webhook_rejects_invalid_signature(): void
    {
        Config::set('services.liqpay.public_key', 'public_key_test');
        Config::set('services.liqpay.private_key', 'private_key_test');

        $event = [
            'status' => 'success',
            'payment_id' => 'liqpay-payment-invalid',
            'order_id' => 'payment-9999',
            'amount' => 10,
            'currency' => 'UAH',
        ];

        $encodedData = base64_encode((string) json_encode($event, JSON_UNESCAPED_SLASHES));

        $this->post('/api/payments/webhooks/liqpay', [
            'data' => $encodedData,
            'signature' => 'invalid-signature',
        ])->assertStatus(400)->assertJsonPath('message', 'Invalid webhook signature.');
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function createManagerUser(array $permissions): User
    {
        $user = User::factory()->create();

        foreach ($permissions as $permissionName) {
            $permission = Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $user->givePermissionTo($permission);
        }

        return $user;
    }
}
