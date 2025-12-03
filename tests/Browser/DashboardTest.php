<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AdminAuthentication;

/**
 * Tests for admin dashboard functionality.
 */
class DashboardTest extends DuskTestCase
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
     * Test that the dashboard loads after login.
     *
     * @return void
     */
    public function test_dashboard_loads_after_login()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->assertPathIs('/admin')
                ->assertPresent('.container-fluid');
        });
    }
}
