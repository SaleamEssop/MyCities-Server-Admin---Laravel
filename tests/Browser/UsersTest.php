<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AdminAuthentication;
use App\Models\User;

/**
 * Tests for admin users management functionality.
 */
class UsersTest extends DuskTestCase
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
     * Test that the users list page loads.
     *
     * @return void
     */
    public function test_users_list_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/users')
                ->assertPathIs('/admin/users')
                ->assertSee('Users');
        });
    }

    /**
     * Test that the add user form loads.
     *
     * @return void
     */
    public function test_add_user_form_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/users/add')
                ->assertPathIs('/admin/users/add')
                ->assertPresent('input[name="name"]')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]');
        });
    }

    /**
     * Test that a new user can be created.
     *
     * @return void
     */
    public function test_can_create_new_user()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/admin/users/add')
                ->type('name', 'New Test User')
                ->type('email', 'newuser@example.com')
                ->type('contact_number', '0987654321')
                ->type('password', 'newpassword123')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/users');
        });
    }

    /**
     * Test that the edit user form loads.
     *
     * @return void
     */
    public function test_edit_user_form_loads()
    {
        $this->browse(function (Browser $browser) {
            // Get a user to edit
            $user = User::where('email', 'testuser@example.com')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/users/edit/' . $user->id)
                ->assertPresent('input[name="name"]')
                ->assertPresent('input[name="email"]');
        });
    }

    /**
     * Test that an existing user can be edited.
     *
     * @return void
     */
    public function test_can_edit_existing_user()
    {
        $this->browse(function (Browser $browser) {
            // Get a user to edit
            $user = User::where('email', 'testuser@example.com')->first();
            
            $this->loginAsAdmin($browser)
                ->visit('/admin/users/edit/' . $user->id)
                ->clear('name')
                ->type('name', 'Updated User Name')
                ->press('Submit')
                ->pause(2000)
                ->assertPathIs('/admin/users');
        });
    }

    /**
     * Test that a user can be deleted.
     *
     * @return void
     */
    public function test_can_delete_user()
    {
        $this->browse(function (Browser $browser) {
            // Create a user specifically for deletion
            $userToDelete = User::create([
                'name' => 'Delete Me',
                'email' => 'deleteme@example.com',
                'password' => bcrypt('password'),
            ]);

            $this->loginAsAdmin($browser)
                ->visit('/admin/users')
                ->assertSee('Delete Me');
            
            // Visit delete URL directly
            $browser->visit('/admin/users/delete/' . $userToDelete->id)
                ->pause(1000)
                ->assertPathIs('/admin/users');
        });
    }
}
