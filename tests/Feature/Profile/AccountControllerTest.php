<?php

namespace Tests\Feature\Profile;

use App\Actions\DeleteUserAccount;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_delete_account_page_is_returned_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $response = $this->actingAs($user)->get(route('account.delete-page'));

        $response->assertOk();
        $response->assertViewIs('auth.delete-account');
    }

    public function test_destroy_deletes_account_and_redirects_to_signin(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'password' => bcrypt('secret123'),
        ]);

        $deleteUserAccount = Mockery::mock(DeleteUserAccount::class);
        $deleteUserAccount->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->is($user)));

        $this->app->instance(DeleteUserAccount::class, $deleteUserAccount);

        $response = $this->actingAs($user)->delete(route('account.destroy'), [
            'password' => 'secret123',
            'confirmation_text' => 'DELETE',
        ]);

        $response->assertRedirect('/signin');
        $response->assertSessionHas('success');
    }

    public function test_destroy_redirects_back_with_error_when_action_throws_exception(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'password' => bcrypt('secret123'),
        ]);

        $deleteUserAccount = Mockery::mock(DeleteUserAccount::class);
        $deleteUserAccount->shouldReceive('execute')
            ->once()
            ->andThrow(new \RuntimeException('Deletion failed'));

        $this->app->instance(DeleteUserAccount::class, $deleteUserAccount);

        $response = $this->actingAs($user)->from('/delete-account')->delete(route('account.destroy'), [
            'password' => 'secret123',
            'confirmation_text' => 'DELETE',
        ]);

        $response->assertRedirect('/delete-account');
        $response->assertSessionHas('error');
    }

    public function test_destroy_shows_debug_error_when_app_debug_is_true(): void
    {
        config(['app.debug' => true]);

        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'password' => bcrypt('secret123'),
        ]);

        $deleteUserAccount = Mockery::mock(DeleteUserAccount::class);
        $deleteUserAccount->shouldReceive('execute')
            ->once()
            ->andThrow(new \RuntimeException('Detailed error message'));

        $this->app->instance(DeleteUserAccount::class, $deleteUserAccount);

        $response = $this->actingAs($user)->from('/delete-account')->delete(route('account.destroy'), [
            'password' => 'secret123',
            'confirmation_text' => 'DELETE',
        ]);

        $response->assertSessionHas('error', 'Detailed error message');
    }

    public function test_destroy_shows_generic_error_when_app_debug_is_false(): void
    {
        config(['app.debug' => false]);

        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'password' => bcrypt('secret123'),
        ]);

        $deleteUserAccount = Mockery::mock(DeleteUserAccount::class);
        $deleteUserAccount->shouldReceive('execute')
            ->once()
            ->andThrow(new \RuntimeException('Detailed error message'));

        $this->app->instance(DeleteUserAccount::class, $deleteUserAccount);

        $response = $this->actingAs($user)->from('/delete-account')->delete(route('account.destroy'), [
            'password' => 'secret123',
            'confirmation_text' => 'DELETE',
        ]);

        $response->assertSessionHas('error', 'Something went wrong while deleting your account.');
    }

    public function test_destroy_returns_validation_error_when_password_is_wrong(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->actingAs($user)->from('/delete-account')->delete(route('account.destroy'), [
            'password' => 'wrong-password',
            'confirmation_text' => 'DELETE',
        ]);

        $response->assertRedirect('/delete-account');
        $response->assertSessionHasErrors(['password']);
    }

    public function test_destroy_returns_validation_error_when_confirmation_text_is_wrong(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($user)->from('/delete-account')->delete(route('account.destroy'), [
            'password' => 'secret123',
            'confirmation_text' => 'delete',
        ]);

        $response->assertRedirect('/delete-account');
        $response->assertSessionHasErrors(['confirmation_text']);
    }

    public function test_destroy_returns_validation_error_when_confirmation_text_is_missing(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($user)->from('/delete-account')->delete(route('account.destroy'), [
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/delete-account');
        $response->assertSessionHasErrors(['confirmation_text']);
    }

    public function test_guest_cannot_access_delete_account_page(): void
    {
        $response = $this->get(route('account.delete-page'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_delete_account(): void
    {
        $response = $this->delete(route('account.destroy'), [
            'password' => 'secret123',
            'confirmation_text' => 'DELETE',
        ]);

        $response->assertRedirect();
    }
}
