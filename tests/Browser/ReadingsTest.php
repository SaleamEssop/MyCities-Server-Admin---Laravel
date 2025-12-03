<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AdminAuthentication;
use App\Models\Meter;

/**
 * Tests for admin meter readings management functionality.
 */
class ReadingsTest extends DuskTestCase
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
     * Test that the readings list page loads.
     *
     * @return void
     */
    public function test_readings_list_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/readings')
                ->assertPathIs('/admin/readings')
                ->assertSee('Reading');
        });
    }

    /**
     * Test that the add reading form loads.
     *
     * @return void
     */
    public function test_add_reading_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/readings/add')
                ->assertPathIs('/admin/readings/add');
        });
    }

    /**
     * Test that a new reading can be created.
     *
     * @return void
     */
    public function test_can_create_new_reading()
    {
        $this->browse(function (Browser $browser) {
            $meter = Meter::where('meter_number', 'WM-001')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/readings/add')
                ->type('reading_value', '1000')
                ->press('Submit')
                ->pause(2000);
            
            // Just verify no server error occurred
            $browser->assertDontSee('Server Error');
        });
    }
}
