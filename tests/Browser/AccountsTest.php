<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AdminAuthentication;
use App\Models\Account;
use App\Models\Site;

/**
 * Tests for admin accounts management functionality.
 */
class AccountsTest extends DuskTestCase
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
     * Test that the accounts list page loads.
     *
     * @return void
     */
    public function test_accounts_list_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/accounts')
                ->assertPathIs('/admin/accounts')
                ->assertSee('Account');
        });
    }

    /**
     * Test that the add account form loads.
     *
     * @return void
     */
    public function test_add_account_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/accounts/add')
                ->assertPathIs('/admin/accounts/add')
                ->assertPresent('input[name="account_name"]');
        });
    }

    /**
     * Test that a new account can be created.
     *
     * @return void
     */
    public function test_can_create_new_account()
    {
        $this->browse(function (Browser $browser) {
            $site = Site::where('title', 'Test Site')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/accounts/add')
                ->type('account_name', 'New Test Account')
                ->type('account_number', 'ACC-NEW-001')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/accounts');
        });
    }

    /**
     * Test that the edit account form loads.
     *
     * @return void
     */
    public function test_edit_account_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $account = Account::where('account_number', 'TEST-001')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/account/edit/' . $account->id)
                ->assertPresent('input[name="account_name"]');
        });
    }

    /**
     * Test that an existing account can be edited.
     *
     * @return void
     */
    public function test_can_edit_existing_account()
    {
        $this->browse(function (Browser $browser) {
            $account = Account::where('account_number', 'TEST-001')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/account/edit/' . $account->id)
                ->clear('account_name')
                ->type('account_name', 'Updated Account Name')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/accounts');
        });
    }

    /**
     * Test that an account can be deleted.
     *
     * @return void
     */
    public function test_can_delete_account()
    {
        $this->browse(function (Browser $browser) {
            // Create an account specifically for deletion
            $site = Site::where('title', 'Test Site')->first();
            $accountToDelete = Account::create([
                'site_id' => $site->id,
                'account_name' => 'Delete Account',
                'account_number' => 'DEL-001',
            ]);

            $this->loginAsAdmin($browser)
                ->visit('/admin/accounts')
                ->assertSee('Delete Account');
            
            $browser->visit('/admin/account/delete/' . $accountToDelete->id)
                ->pause(1000)
                ->assertPathIs('/admin/accounts');
        });
    }
}
