<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AdminAuthentication;
use App\Models\Regions;

/**
 * Tests for admin regions management functionality.
 */
class RegionsTest extends DuskTestCase
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
     * Test that the regions list page loads.
     *
     * @return void
     */
    public function test_regions_list_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/regions')
                ->assertPathIs('/admin/regions')
                ->assertSee('Region');
        });
    }

    /**
     * Test that the add region form loads.
     *
     * @return void
     */
    public function test_add_region_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/region/add')
                ->assertPathIs('/admin/region/add')
                ->assertPresent('input[name="name"]');
        });
    }

    /**
     * Test that a new region can be created.
     *
     * @return void
     */
    public function test_can_create_new_region()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/region/add')
                ->type('name', 'New Test Region')
                ->press('Create')
                ->pause(2000)
                ->assertPathIs('/admin/regions');
        });
    }

    /**
     * Test that the edit region form loads.
     *
     * @return void
     */
    public function test_edit_region_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $region = Regions::where('name', 'Test Region')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/region/edit/' . $region->id)
                ->assertPresent('input[name="name"]');
        });
    }

    /**
     * Test that an existing region can be edited.
     *
     * @return void
     */
    public function test_can_edit_existing_region()
    {
        $this->browse(function (Browser $browser) {
            $region = Regions::where('name', 'Test Region')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/region/edit/' . $region->id)
                ->clear('name')
                ->type('name', 'Updated Region Name')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/regions');
        });
    }

    /**
     * Test that a region can be deleted.
     *
     * @return void
     */
    public function test_can_delete_region()
    {
        $this->browse(function (Browser $browser) {
            // Create a region specifically for deletion
            $regionToDelete = Regions::create([
                'name' => 'Delete Region',
            ]);

            $this->loginAsAdmin($browser)
                ->visit('/admin/regions')
                ->assertSee('Delete Region');
            
            $browser->visit('/admin/region/delete/' . $regionToDelete->id)
                ->pause(1000)
                ->assertPathIs('/admin/regions');
        });
    }
}
