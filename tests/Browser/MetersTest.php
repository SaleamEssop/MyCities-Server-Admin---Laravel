<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AdminAuthentication;
use App\Models\Meter;
use App\Models\MeterType;

/**
 * Tests for admin meters management functionality.
 */
class MetersTest extends DuskTestCase
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
     * Test that the meters list page loads.
     *
     * @return void
     */
    public function test_meters_list_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/meters')
                ->assertPathIs('/admin/meters')
                ->assertSee('Meter');
        });
    }

    /**
     * Test that the add meter form loads.
     *
     * @return void
     */
    public function test_add_meter_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/meters/add')
                ->assertPathIs('/admin/meters/add')
                ->assertPresent('input[name="meter_title"]');
        });
    }

    /**
     * Test that a new water meter can be created.
     *
     * @return void
     */
    public function test_can_create_new_water_meter()
    {
        $this->browse(function (Browser $browser) {
            $waterType = MeterType::where('title', 'Water')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/meters/add')
                ->type('meter_title', 'New Water Meter')
                ->type('meter_number', 'WM-NEW-001')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/meters');
        });
    }

    /**
     * Test that a new electricity meter can be created.
     *
     * @return void
     */
    public function test_can_create_new_electricity_meter()
    {
        $this->browse(function (Browser $browser) {
            $electricityType = MeterType::where('title', 'Electricity')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/meters/add')
                ->type('meter_title', 'New Electricity Meter')
                ->type('meter_number', 'EM-NEW-001')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/meters');
        });
    }
}
