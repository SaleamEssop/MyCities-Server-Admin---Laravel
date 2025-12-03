<?php

namespace Tests\Browser\Traits;

use App\Models\User;
use Laravel\Dusk\Browser;

/**
 * Trait AdminAuthentication
 * 
 * Provides helper methods for admin authentication in Dusk tests.
 */
trait AdminAuthentication
{
    /**
     * Login as the admin user.
     *
     * @param Browser $browser
     * @return Browser
     */
    protected function loginAsAdmin(Browser $browser)
    {
        return $browser->visit('/admin/login')
            ->type('email', 'admin@example.com')
            ->type('password', 'password')
            ->press('Login')
            ->waitForLocation('/admin');
    }

    /**
     * Get or create test admin user.
     *
     * @return User
     */
    protected function getTestAdminUser()
    {
        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Test Admin',
                'password' => bcrypt('password'),
            ]
        );
    }
}
