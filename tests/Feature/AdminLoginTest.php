<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_is_branded(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();

        $this->assertStringContainsString('TWINS AFRICAN', $html);
        $this->assertStringNotContainsString('>Laravel<', $html);
    }

    public function test_remember_me_sits_below_the_sign_in_button(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();

        $button = strripos($html, 'form-actions');
        $remember = stripos($html, 'name="remember"') ?: stripos($html, 'data.remember');

        $this->assertNotFalse($button, 'Sign in action should render');
        $this->assertNotFalse($remember, 'Remember me should render');
        $this->assertLessThan($remember, $button, 'Remember me must come after the sign in button');
    }

    public function test_an_admin_can_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'boss@example.com',
            'password' => bcrypt('secret-password'),
            'is_admin' => true,
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'boss@example.com',
                'password' => 'secret-password',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_remember_me_still_works_after_being_moved(): void
    {
        // Relocating the field must not break its binding to form state.
        User::factory()->create([
            'email' => 'boss@example.com',
            'password' => bcrypt('secret-password'),
            'is_admin' => true,
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'boss@example.com',
                'password' => 'secret-password',
                'remember' => true,
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticated();
        $this->assertNotNull(Auth::user()->remember_token, 'Ticking remember me should issue a remember token');
    }

    public function test_bad_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'boss@example.com', 'password' => bcrypt('secret-password')]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'boss@example.com', 'password' => 'wrong'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }
}
