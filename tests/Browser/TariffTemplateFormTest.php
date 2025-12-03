<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Regions;
use App\Models\AccountType;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Browser tests for the Tariff Template Form.
 * 
 * These tests verify that the Tariff Template form works correctly,
 * including form loading, dynamic sections visibility, and tier management.
 * 
 * The tests use Laravel Dusk to run real browser interactions with Chrome.
 */
class TariffTemplateFormTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Set up test data before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user for authentication
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        // Create test region
        Regions::factory()->create([
            'name' => 'Test Region',
        ]);
        
        // Create test account type
        AccountType::factory()->create([
            'type' => 'Residential',
        ]);
    }

    /**
     * Test that the Tariff Template form page loads without JavaScript errors.
     * 
     * This verifies the basic page structure loads correctly and Vue.js initializes.
     */
    public function test_form_page_loads_without_javascript_errors(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->assertPresent('#tariff-template-app')
                    // Check that the Vue app has rendered (loading spinner replaced with form)
                    ->waitUntilMissing('.spinner-border', 15)
                    // Check console for JavaScript errors
                    ->assertConsoleLogMissing('error');
        });
    }

    /**
     * Test that basic form fields are visible when the page loads.
     * 
     * Verifies Template Name, Region, Account Type dropdowns and date fields exist.
     */
    public function test_basic_form_fields_are_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    // Check Template Name field
                    ->assertPresent('input[name="template_name"]')
                    // Check Region dropdown
                    ->assertPresent('select[name="region_id"]')
                    // Check Account Type dropdown
                    ->assertPresent('select[name="account_type_id"]')
                    // Check Start Date field
                    ->assertPresent('input[name="start_date"]')
                    // Check End Date field
                    ->assertPresent('input[name="end_date"]')
                    // Check VAT Percentage field
                    ->assertPresent('input[name="vat_percentage"]')
                    // Check Billing Day field
                    ->assertPresent('input[name="billing_day"]')
                    // Check Read Day field
                    ->assertPresent('input[name="read_day"]')
                    // Check Ratable Value field
                    ->assertPresent('input[name="ratable_value"]');
        });
    }

    /**
     * Test that checking the Water checkbox shows the water tier sections.
     * 
     * Verifies water_in_section and water_out_section become visible when Water is checked.
     */
    public function test_water_checkbox_shows_water_sections(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    // Water sections should be hidden initially
                    ->assertMissing('.water_in_section:not([style*="display: none"])')
                    ->assertMissing('.water_out_section:not([style*="display: none"])')
                    // Check the Water checkbox
                    ->check('#waterchk')
                    ->pause(500)
                    // Water In section should now be visible
                    ->assertVisible('.water_in_section')
                    // Water Out section should now be visible
                    ->assertVisible('.water_out_section');
        });
    }

    /**
     * Test that the Water In Cost section appears when Water is enabled.
     * 
     * Verifies the "Add Water In Cost" label and tier input fields are visible.
     */
    public function test_water_in_cost_section_appears(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    ->check('#waterchk')
                    ->pause(500)
                    // Check for Water In section heading
                    ->assertSee('Add Water In Cost')
                    // Check for Water In tier input fields
                    ->assertPresent('input[name="waterin[0][min]"]')
                    ->assertPresent('input[name="waterin[0][max]"]')
                    ->assertPresent('input[name="waterin[0][cost]"]');
        });
    }

    /**
     * Test that the Water Out section appears when Water is enabled.
     * 
     * Verifies the "Add Water Out Cost" label and tier input fields are visible.
     */
    public function test_water_out_section_appears(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    ->check('#waterchk')
                    ->pause(500)
                    // Check for Water Out section heading
                    ->assertSee('Add Water Out Cost')
                    // Check for Water Out tier input fields
                    ->assertPresent('input[name="waterout[0][min]"]')
                    ->assertPresent('input[name="waterout[0][max]"]')
                    ->assertPresent('input[name="waterout[0][percentage]"]')
                    ->assertPresent('input[name="waterout[0][cost]"]');
        });
    }

    /**
     * Test that clicking the + button adds a new water tier row.
     * 
     * Verifies that the Add button creates additional tier input rows.
     */
    public function test_add_water_tier_button_adds_new_row(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    ->check('#waterchk')
                    ->pause(500)
                    // Verify only first tier row exists
                    ->assertPresent('input[name="waterin[0][min]"]')
                    ->assertMissing('input[name="waterin[1][min]"]')
                    // Click the add button in Water In section
                    ->click('.water_in_section button.btn-circle')
                    ->pause(500)
                    // Verify second tier row was added
                    ->assertPresent('input[name="waterin[1][min]"]')
                    ->assertPresent('input[name="waterin[1][max]"]')
                    ->assertPresent('input[name="waterin[1][cost]"]');
        });
    }

    /**
     * Test that checking the Electricity checkbox shows the electricity tier sections.
     * 
     * Verifies ele_section becomes visible when Electricity is checked.
     */
    public function test_electricity_checkbox_shows_electricity_sections(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    // Electricity section should be hidden initially
                    ->assertMissing('.ele_section:not([style*="display: none"])')
                    // Check the Electricity checkbox
                    ->check('#electricitychk')
                    ->pause(500)
                    // Electricity section should now be visible
                    ->assertVisible('.ele_section')
                    // Check for Electricity section heading
                    ->assertSee('Electricity');
        });
    }

    /**
     * Test that the Electricity tier section appears with input fields.
     * 
     * Verifies the Electricity tier input fields are visible.
     */
    public function test_electricity_tier_section_appears(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    ->check('#electricitychk')
                    ->pause(500)
                    // Check for Electricity tier input fields
                    ->assertPresent('input[name="electricity[0][min]"]')
                    ->assertPresent('input[name="electricity[0][max]"]')
                    ->assertPresent('input[name="electricity[0][cost]"]');
        });
    }

    /**
     * Test that clicking the + button adds a new electricity tier row.
     * 
     * Verifies that the Add button creates additional electricity tier input rows.
     */
    public function test_add_electricity_tier_button_adds_new_row(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    ->check('#electricitychk')
                    ->pause(500)
                    // Verify only first tier row exists
                    ->assertPresent('input[name="electricity[0][min]"]')
                    ->assertMissing('input[name="electricity[1][min]"]')
                    // Click the add button in Electricity section
                    ->click('.ele_section button.btn-circle')
                    ->pause(500)
                    // Verify second tier row was added
                    ->assertPresent('input[name="electricity[1][min]"]')
                    ->assertPresent('input[name="electricity[1][max]"]')
                    ->assertPresent('input[name="electricity[1][cost]"]');
        });
    }

    /**
     * Test that the Fixed Costs section is visible by default.
     * 
     * Verifies the Fixed Costs section is always displayed.
     */
    public function test_fixed_costs_section_is_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    // Check for Fixed Costs section heading
                    ->assertSee('Fixed Costs (Customer Cannot Edit)')
                    // Check for Fixed Costs input fields
                    ->assertPresent('input[name="fixed_costs[0][name]"]')
                    ->assertPresent('input[name="fixed_costs[0][value]"]');
        });
    }

    /**
     * Test that clicking + adds a new Fixed Costs row.
     * 
     * Verifies that the Add button creates additional Fixed Costs input rows.
     * Uses JavaScript to click the button since it's embedded in a dynamic Vue component.
     */
    public function test_add_fixed_cost_button_adds_new_row(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    // Verify only first row exists
                    ->assertPresent('input[name="fixed_costs[0][name]"]')
                    ->assertMissing('input[name="fixed_costs[1][name]"]')
                    // Scroll to ensure the section is visible
                    ->scrollTo('input[name="fixed_costs[0][name]"]')
                    ->pause(300);
            
            // Click the Fixed Costs add button using a text-based selector
            // Find the button near the "Fixed Costs" label text
            $browser->script("
                Array.from(document.querySelectorAll('label strong')).find(el => 
                    el.textContent.includes('Fixed Costs (Customer Cannot Edit)')
                ).closest('.row').querySelector('button.btn-circle').click()
            ");
                    
            $browser->pause(500)
                    // Verify second row was added
                    ->assertPresent('input[name="fixed_costs[1][name]"]')
                    ->assertPresent('input[name="fixed_costs[1][value]"]');
        });
    }

    /**
     * Test that the Customer Editable Costs section is visible.
     * 
     * Verifies the Customer Editable Costs section is always displayed.
     */
    public function test_customer_editable_costs_section_is_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    // Check for Customer Editable Costs section heading
                    ->assertSee('Customer Editable Costs (Customer Can Modify in App)')
                    // Check for Customer Editable Costs input fields
                    ->assertPresent('input[name="customer_costs[0][name]"]')
                    ->assertPresent('input[name="customer_costs[0][value]"]');
        });
    }

    /**
     * Test that clicking + adds a new Customer Editable Costs row.
     * 
     * Verifies that the Add button creates additional Customer Costs input rows.
     * Uses JavaScript to click the button since it's embedded in a dynamic Vue component.
     */
    public function test_add_customer_cost_button_adds_new_row(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    // Verify only first row exists
                    ->assertPresent('input[name="customer_costs[0][name]"]')
                    ->assertMissing('input[name="customer_costs[1][name]"]')
                    // Scroll to ensure the section is visible
                    ->scrollTo('input[name="customer_costs[0][name]"]')
                    ->pause(300);
            
            // Click the Customer Costs add button using a text-based selector
            // Find the button near the "Customer Editable Costs" label text
            $browser->script("
                Array.from(document.querySelectorAll('label strong')).find(el => 
                    el.textContent.includes('Customer Editable Costs')
                ).closest('.row').querySelector('button.btn-circle').click()
            ");
                    
            $browser->pause(500)
                    // Verify second row was added
                    ->assertPresent('input[name="customer_costs[1][name]"]')
                    ->assertPresent('input[name="customer_costs[1][value]"]');
        });
    }

    /**
     * Test that the Bill Preview section is visible.
     * 
     * Verifies the Bill Preview card/section is always displayed.
     */
    public function test_bill_preview_section_is_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    // Check for Bill Preview heading
                    ->assertSee('BILL PREVIEW')
                    // Check for Summary section elements
                    ->assertSee('Subtotal')
                    ->assertSee('VAT')
                    ->assertSee('TOTAL');
        });
    }

    /**
     * Test that Bill Preview updates when values are entered.
     * 
     * Verifies that the Bill Preview reflects changes made to form inputs.
     */
    public function test_bill_preview_updates_with_values(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                    ->visit('/admin/tariff_template/create')
                    ->waitFor('#tariff-template-app', 10)
                    ->waitUntilMissing('.spinner-border', 15)
                    // Enable water section
                    ->check('#waterchk')
                    ->pause(500)
                    // Enter water usage
                    ->type('input[name="water_used"]', '10')
                    ->pause(300)
                    // Enter water tier values
                    ->type('input[name="waterin[0][min]"]', '0')
                    ->type('input[name="waterin[0][max]"]', '6000')
                    ->type('input[name="waterin[0][cost]"]', '10')
                    ->pause(500)
                    // The Bill Preview should show water charges
                    ->assertSee('WATER CHARGES')
                    ->assertSee('Water In');
        });
    }
}
