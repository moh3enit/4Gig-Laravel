<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        Notification::fake();

        $response = $this->json('post', route('v1.register'), [
            'email' => 'test@test.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'fcm_key' => '::fcm_key::',
        ]);

        $response->assertSessionDoesntHaveErrors()
            ->assertOk()
            ->assertJsonStructure(['token']);

        $response_data = $response->json();

        [$id] = explode('|', $response_data['token'], 2);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com',
            'email_verified_at' => null,
            'fcm_key' => '::fcm_key::',
        ]);

        $this->assertNotNull(User::first()->verify_code);

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $id,
            'name' => 'test-token',
        ]);

        $this->json('get', route('v1.user.current'), [], ['Authorization' => 'Bearer '.$response->json('token')])
            ->assertOk()
            ->assertJsonStructure(['id', 'username', 'email', 'mobile', 'email_verified_at', 'mobile_verified_at', 'is_online']);

        Notification::assertSentTo(User::first(), \App\Notifications\VerifyEmailNotification::class);
    }

    /** @test */
    public function email_is_required()
    {
        Notification::fake();

        $response = $this->json('post', route('v1.register'), [
            'email' => '',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        $response->assertUnprocessable();

        Notification::assertNothingSent();
    }

    /** @test */
    public function email_must_be_valid()
    {
        Notification::fake();

        $response = $this->json('post', route('v1.register'), [
            'email' => 'emailtest.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        $response->assertUnprocessable();

        Notification::assertNothingSent();
    }

    /** @test */
    public function password_is_required()
    {
        Notification::fake();

        $response = $this->json('post', route('v1.register'), [
            'email' => 'test@test.com',
            'password' => '',
            'password_confirmation' => 'secret',
        ]);

        $response->assertUnprocessable();

        Notification::assertNothingSent();
    }

    /** @test */
    public function password_must_be_more_than_or_equal_to_6()
    {
        Notification::fake();

        $response = $this->json('post', route('v1.register'), [
            'email' => 'test@test.com',
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);

        $response->assertUnprocessable();

        Notification::assertNothingSent();
    }

    /** @test */
    public function password_confirmation_must_be_more_than_or_equal_to_6()
    {
        Notification::fake();

        $response = $this->json('post', route('v1.register'), [
            'email' => 'test@test.com',
            'password' => '123456',
            'password_confirmation' => '12345',
        ]);

        $response->assertUnprocessable();

        Notification::assertNothingSent();
    }

    /** @test */
    public function password_confirmation_is_required()
    {
        Notification::fake();

        $response = $this->json('post', route('v1.register'), [
            'email' => 'test@test.com',
            'password' => '123456',
            'password_confirmation' => '',
        ]);

        $response->assertUnprocessable();

        Notification::assertNothingSent();
    }

    /** @test */
    public function password_and_password_confirmation_muse_be_equal()
    {
        Notification::fake();

        $response = $this->json('post', route('v1.register'), [
            'email' => 'test@test.com',
            'password' => '123456',
            'password_confirmation' => 'secret',
        ]);

        $response->assertUnprocessable();

        Notification::assertNothingSent();
    }
}
