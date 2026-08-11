<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GeminiSettingsTest extends TestCase
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

    public function test_admin_can_view_gemini_settings_page(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.gemini.index'))
            ->assertOk()
            ->assertSee('Gemini');
    }

    public function test_non_admin_cannot_view_gemini_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.gemini.index'))
            ->assertForbidden();
    }

    public function test_admin_can_store_gemini_settings(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->put(route('admin.gemini.update'), [
                'api_key' => 'secret_api_key',
                'model' => 'gemini-2.0-flash',
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'timeout' => 90,
                'max_output_tokens' => 16000,
                'max_retries' => 2,
                'retry_base_delay_ms' => 1000,
                'fallback_models' => 'gemini-2.5-flash,gemini-flash-latest',
            ])
            ->assertRedirect(route('admin.gemini.index'))
            ->assertSessionHas('success');

        $this->assertEquals('gemini-2.0-flash', SiteSetting::value('gemini_model'));
        $this->assertEquals('90', SiteSetting::value('gemini_timeout'));
        $this->assertEquals('gemini-2.5-flash,gemini-flash-latest', SiteSetting::value('gemini_fallback_models'));

        $encrypted = SiteSetting::value('gemini_api_key');
        $this->assertNotNull($encrypted);
        $this->assertEquals('secret_api_key', Crypt::decryptString($encrypted));
    }

    public function test_api_key_is_not_changed_when_left_blank(): void
    {
        $user = $this->adminUser();

        SiteSetting::setGlobal('gemini_api_key', Crypt::encryptString('existing_key'));

        $this->actingAs($user)
            ->put(route('admin.gemini.update'), [
                'api_key' => '',
                'model' => 'new-model',
            ]);

        $this->assertEquals('new-model', SiteSetting::value('gemini_model'));
        $this->assertEquals('existing_key', Crypt::decryptString(SiteSetting::value('gemini_api_key')));
    }
}
