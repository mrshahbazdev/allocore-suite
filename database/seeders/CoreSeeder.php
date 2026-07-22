<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate('admin');
        Role::findOrCreate('user');

        $modules = [
            ['key' => 'invoice-maker', 'name' => 'InvoiceMaker', 'description' => 'Invoices, estimates, expenses, clients & payments.', 'icon' => 'document-text', 'route_prefix' => 'invoices'],
            ['key' => 'audit', 'name' => 'AuditPro', 'description' => 'Business maturity audits with radar charts & reports.', 'icon' => 'chart-radar', 'route_prefix' => 'audit'],
            ['key' => 'keyword-cluster', 'name' => 'ClusterForge', 'description' => 'AI keyword & topic cluster generator.', 'icon' => 'sparkles', 'route_prefix' => 'clusters'],
            ['key' => 'lead-quality', 'name' => 'LeadOS', 'description' => 'B2B lead generation, AI scoring & CRM pipeline.', 'icon' => 'users', 'route_prefix' => 'leads'],
            ['key' => 'time-butler', 'name' => 'TimeButler', 'description' => 'Employee vacation, absence & time tracking with team calendar.', 'icon' => 'clock', 'route_prefix' => 'timebutler'],
            ['key' => 'plan-hive', 'name' => 'PlanHive', 'description' => 'Multi-tenant project management with tasks, goals, calendar, contacts & documents.', 'icon' => 'folder', 'route_prefix' => 'planhive'],
            ['key' => 'kpi-tool', 'name' => 'KpiTool', 'description' => 'Bilingual KPI catalog, monthly spreadsheet, targets, charts & CSV export.', 'icon' => 'chart-bar', 'route_prefix' => 'kpitool'],
            ['key' => 'loop-engine', 'name' => 'LoopEngine', 'description' => 'Decision loop SOP builder with step-by-step execution, self-checking loops, audit trails and webhooks.', 'icon' => 'arrow-path', 'route_prefix' => 'loopengine'],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(['key' => $module['key']], $module);
        }

        $plans = [
            ['name' => 'InvoiceMaker Solo', 'slug' => 'invoice-maker-solo', 'price_monthly' => 9.99, 'price_yearly' => 99, 'modules' => ['invoice-maker']],
            ['name' => 'AuditPro Solo', 'slug' => 'audit-solo', 'price_monthly' => 19.99, 'price_yearly' => 199, 'modules' => ['audit']],
            ['name' => 'ClusterForge Solo', 'slug' => 'keyword-cluster-solo', 'price_monthly' => 14.99, 'price_yearly' => 149, 'modules' => ['keyword-cluster']],
            ['name' => 'LeadOS Solo', 'slug' => 'lead-quality-solo', 'price_monthly' => 24.99, 'price_yearly' => 249, 'modules' => ['lead-quality']],
            ['name' => 'All Tools Bundle', 'slug' => 'all-tools', 'price_monthly' => 89.99, 'price_yearly' => 899, 'modules' => ['invoice-maker', 'audit', 'keyword-cluster', 'lead-quality', 'time-butler', 'plan-hive', 'kpi-tool', 'loop-engine']],
            ['name' => 'TimeButler Solo', 'slug' => 'time-butler-solo', 'price_monthly' => 9.99, 'price_yearly' => 99, 'modules' => ['time-butler']],
            ['name' => 'PlanHive Solo', 'slug' => 'plan-hive-solo', 'price_monthly' => 12.99, 'price_yearly' => 129, 'modules' => ['plan-hive']],
            ['name' => 'KpiTool Solo', 'slug' => 'kpi-tool-solo', 'price_monthly' => 14.99, 'price_yearly' => 149, 'modules' => ['kpi-tool']],
            ['name' => 'LoopEngine Solo', 'slug' => 'loop-engine-solo', 'price_monthly' => 16.99, 'price_yearly' => 169, 'modules' => ['loop-engine']],
        ];

        foreach ($plans as $data) {
            $moduleKeys = $data['modules'];
            unset($data['modules']);
            $plan = Plan::updateOrCreate(['slug' => $data['slug']], $data + ['currency' => 'EUR', 'billable_scope' => 'both']);
            $plan->modules()->sync(Module::whereIn('key', $moduleKeys)->pluck('id'));
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@allocore.test'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );
        $admin->assignRole('admin');
    }
}
