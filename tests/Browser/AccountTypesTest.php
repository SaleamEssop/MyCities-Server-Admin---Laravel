<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AdminAuthentication;
use App\Models\AccountType;

/**
 * Tests for admin account types management functionality.
 */
class AccountTypesTest extends DuskTestCase
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
     * Test that the account types list page loads.
     *
     * @return void
     */
    public function test_account_types_list_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/account_type')
                ->assertPathIs('/admin/account_type')
                ->assertSee('Account Type');
        });
    }

    /**
     * Test that the add account type form loads.
     *
     * @return void
     */
    public function test_add_account_type_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/account_type/add')
                ->assertPathIs('/admin/account_type/add')
                ->assertPresent('input[name="type"]');
        });
    }

    /**
     * Test that a new account type can be created.
     *
     * @return void
     */
    public function test_can_create_new_account_type()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/account_type/add')
                ->type('type', 'Industrial')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/account_type');
        });
    }

    /**
     * Test that the edit account type form loads.
     *
     * @return void
     */
    public function test_edit_account_type_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $accountType = AccountType::where('type', 'Residential')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/account_type/edit/' . $accountType->id)
                ->assertPresent('input[name="type"]');
        });
    }

    /**
     * Test that an existing account type can be edited.
     *
     * @return void
     */
    public function test_can_edit_existing_account_type()
    {
        $this->browse(function (Browser $browser) {
            $accountType = AccountType::where('type', 'Residential')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/account_type/edit/' . $accountType->id)
                ->clear('type')
                ->type('type', 'Updated Type')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/account_type');
        });
    }

    /**
     * Test that an account type can be deleted.
     *
     * @return void
     */
    public function test_can_delete_account_type()
    {
        $this->browse(function (Browser $browser) {
            // Create an account type specifically for deletion
            $accountTypeToDelete = AccountType::create([
                'type' => 'Delete Type',
                'is_active' => 1,
            ]);

            $this->loginAsAdmin($browser)
                ->visit('/admin/account_type')
                ->assertSee('Delete Type');
            
            $browser->visit('/admin/account_type/delete/' . $accountTypeToDelete->id)
                ->pause(1000)
                ->assertPathIs('/admin/account_type');
        });
    }
}
