<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AdminAuthentication;
use App\Models\User;

/**
 * Tests for admin authentication functionality.
 */
class AuthenticationTest extends DuskTestCase
{
    use DatabaseMigrations;
    use AdminAuthentication;

    /**
     * Setup test data before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\DuskTestSeeder']);
    }

    /**
     * Test that the login page loads correctly.
     *
     * @return void
     */
    public function test_login_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/login')
                ->assertSee('Welcome Back')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->assertSee('Login');
        });
    }

    /**
     * Test that a user can login with valid credentials.
     *
     * @return void
     */
    public function test_can_login_with_valid_credentials()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/login')
                ->type('email', 'admin@example.com')
                ->type('password', 'password')
                ->press('Login')
                ->waitForLocation('/admin')
                ->assertPathIs('/admin');
        });
    }

    /**
     * Test that user is redirected to dashboard after successful login.
     *
     * @return void
     */
    public function test_redirects_to_dashboard_after_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/login')
                ->type('email', 'admin@example.com')
                ->type('password', 'password')
                ->press('Login')
                ->waitForLocation('/admin')
                ->assertPathIs('/admin');
        });
    }

    /**
     * Test that user cannot login with invalid credentials.
     *
     * @return void
     */
    public function test_cannot_login_with_invalid_credentials()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/login')
                ->type('email', 'wrong@example.com')
                ->type('password', 'wrongpassword')
                ->press('Login')
                ->pause(1000)
                ->assertPathIs('/admin/login');
        });
    }

    /**
     * Test that logout works correctly.
     *
     * @return void
     */
    public function test_logout_works_correctly()
    {
        $this->browse(function (Browser $browser) {
            // First login
            $browser->visit('/admin/login')
                ->type('email', 'admin@example.com')
                ->type('password', 'password')
                ->press('Login')
                ->waitForLocation('/admin')
                ->assertPathIs('/admin');

            // Then logout
            $browser->visit('/admin/logout')
                ->waitForLocation('/admin/login')
                ->assertPathIs('/admin/login');
        });
    }
}
