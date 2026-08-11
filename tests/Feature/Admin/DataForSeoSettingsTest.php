<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DataForSeoSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::setGlobal('setup_wizard_completed', true);
    }

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_view_dataforseo_settings_page(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.dataforseo.index'))
            ->assertOk()
            ->assertSee('DataForSEO');
    }

    public function test_non_admin_cannot_view_dataforseo_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dataforseo.index'))
            ->assertForbidden();
    }

    public function test_admin_can_store_dataforseo_credentials(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->put(route('admin.dataforseo.update'), [
                'login' => 'test_login',
                'password' => 'secret_password',
                'base_url' => 'https://api.dataforseo.com',
                'timeout' => 60,
                'cache_ttl' => 3600,
            ])
            ->assertRedirect(route('admin.dataforseo.index'))
            ->assertSessionHas('success');

        $this->assertEquals('test_login', SiteSetting::value('dataforseo_login'));
        $this->assertEquals('https://api.dataforseo.com', SiteSetting::value('dataforseo_base_url'));
        $this->assertEquals('60', SiteSetting::value('dataforseo_timeout'));
        $this->assertEquals('3600', SiteSetting::value('dataforseo_cache_ttl'));

        $encrypted = SiteSetting::value('dataforseo_password');
        $this->assertNotNull($encrypted);
        $this->assertEquals('secret_password', Crypt::decryptString($encrypted));
    }

    public function test_password_is_not_changed_when_left_blank(): void
    {
        $user = $this->adminUser();

        SiteSetting::setGlobal('dataforseo_password', Crypt::encryptString('existing_password'));

        $this->actingAs($user)
            ->put(route('admin.dataforseo.update'), [
                'login' => 'new_login',
                'password' => '',
            ]);

        $this->assertEquals('new_login', SiteSetting::value('dataforseo_login'));
        $this->assertEquals('existing_password', Crypt::decryptString(SiteSetting::value('dataforseo_password')));
    }
}
