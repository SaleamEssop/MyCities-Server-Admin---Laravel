<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AdminAuthentication;
use App\Models\Site;
use App\Models\User;

/**
 * Tests for admin sites management functionality.
 */
class SitesTest extends DuskTestCase
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
     * Test that the sites list page loads.
     *
     * @return void
     */
    public function test_sites_list_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/sites')
                ->assertPathIs('/admin/sites')
                ->assertSee('Sites');
        });
    }

    /**
     * Test that the add site form loads.
     *
     * @return void
     */
    public function test_add_site_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/sites/add')
                ->assertPathIs('/admin/sites/add')
                ->assertPresent('input[name="title"]');
        });
    }

    /**
     * Test that a new site can be created.
     *
     * @return void
     */
    public function test_can_create_new_site()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('email', 'testuser@example.com')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/sites/add')
                ->select('user_id', $user->id)
                ->type('title', 'New Test Site')
                ->type('address', '456 New Street')
                ->type('email', 'newsite@example.com')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/sites');
        });
    }

    /**
     * Test that the edit site form loads.
     *
     * @return void
     */
    public function test_edit_site_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $site = Site::where('title', 'Test Site')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/sites/edit/' . $site->id)
                ->assertPresent('input[name="title"]');
        });
    }

    /**
     * Test that an existing site can be edited.
     *
     * @return void
     */
    public function test_can_edit_existing_site()
    {
        $this->browse(function (Browser $browser) {
            $site = Site::where('title', 'Test Site')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/sites/edit/' . $site->id)
                ->clear('title')
                ->type('title', 'Updated Site Name')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/sites');
        });
    }

    /**
     * Test that a site can be deleted.
     *
     * @return void
     */
    public function test_can_delete_site()
    {
        $this->browse(function (Browser $browser) {
            // Create a site specifically for deletion
            $user = User::where('email', 'testuser@example.com')->first();
            $siteToDelete = Site::create([
                'user_id' => $user->id,
                'title' => 'Delete Site',
                'lat' => '-26.0',
                'lng' => '28.0',
                'address' => 'Delete Street',
                'email' => 'delete@example.com',
                'billing_type' => 'date_to_date',
            ]);

            $this->loginAsAdmin($browser)
                ->visit('/admin/sites')
                ->assertSee('Delete Site');
            
            $browser->visit('/admin/sites/delete/' . $siteToDelete->id)
                ->pause(1000)
                ->assertPathIs('/admin/sites');
        });
    }
}
