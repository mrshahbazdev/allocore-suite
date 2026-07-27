<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IndustrySeeder extends Seeder
{
    public function run(): void
    {
        $clusters = [
            'Skilled trades' => [
                'Carpenters',
                'Dental technicians',
                'Electricians',
                'Plumbers',
                'Masons',
                'Painters',
                'Roofers',
            ],
            'Consultants' => [
                'Management consultants',
                'Security consultants',
                'Fire safety consultants',
                'Hazardous materials consultants',
                'Data protection consultants',
                'IT consultants',
                'Financial consultants',
            ],
            'Healthcare' => [
                'Physicians',
                'Dentists',
                'Therapists',
                'Pharmacies',
                'Care homes',
            ],
            'Retail & E-commerce' => [
                'Boutiques',
                'Online shops',
                'Wholesalers',
                'Consumer electronics',
            ],
            'Construction & Real estate' => [
                'General contractors',
                'Architects',
                'Property managers',
                'Real estate agents',
            ],
            'Manufacturing' => [
                'Metalworking',
                'Plastics',
                'Food & beverage',
                'Textiles',
                'Electronics manufacturing',
            ],
            'Services' => [
                'Cleaning services',
                'Catering',
                'Event management',
                'Marketing agencies',
            ],
            'Technology' => [
                'SaaS',
                'Software agencies',
                'AI & data',
                'Hardware',
            ],
            'Other' => [
                'Other',
            ],
        ];

        foreach ($clusters as $clusterName => $subIndustries) {
            $cluster = Industry::firstOrCreate(
                ['slug' => Str::slug($clusterName)],
                ['name' => $clusterName, 'is_active' => true, 'sort_order' => 0]
            );

            foreach ($subIndustries as $index => $subName) {
                Industry::firstOrCreate(
                    ['slug' => Str::slug($subName)],
                    [
                        'parent_id' => $cluster->id,
                        'name' => $subName,
                        'is_active' => true,
                        'sort_order' => $index,
                    ]
                );
            }
        }
    }
}
