<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\AccommodationImage;
use Illuminate\Database\Seeder;

class AccommodationsSeeder extends Seeder
{
    public function run(): void
    {
        $accommodations = [
            [
                'name'            => 'Aurora Suites — Deluxe Studio',
                'slug'            => 'aurora-suites-deluxe-studio',
                'description'     => 'A beautifully appointed studio suite with modern furnishings and a fully equipped kitchenette. Ideal for solo patients seeking comfort and convenience during their recovery. The on-call nurse service ensures peace of mind around the clock.',
                'rating'          => 4.9,
                'distance_text'   => '6 min walk to Medical Center',
                'bedrooms'        => 1,
                'max_guests'      => 2,
                'area_sqm'        => 42,
                'price_per_night' => 145.00,
                'currency'        => 'USD',
                'amenities'       => ['Free Wi-Fi', 'Kitchenette', 'Air conditioning', 'On-call nurse'],
                'status'          => 'active',
                'sort_order'      => 1,
            ],
            [
                'name'            => 'Meridian Residence — One Bedroom',
                'slug'            => 'meridian-residence-one-bedroom',
                'description'     => 'A spacious one-bedroom residence with a separate living area for visiting family. Private parking and elevator access make arrival and discharge days effortless.',
                'rating'          => 4.8,
                'distance_text'   => '11 min walk to Medical Center',
                'bedrooms'        => 1,
                'max_guests'      => 3,
                'area_sqm'        => 54,
                'price_per_night' => 189.00,
                'currency'        => 'USD',
                'amenities'       => ['Free Wi-Fi', 'Kitchenette', 'Air conditioning', 'Private parking', 'Elevator access'],
                'status'          => 'active',
                'sort_order'      => 2,
            ],
            [
                'name'            => 'Serenity Garden Apartments',
                'slug'            => 'serenity-garden-apartments',
                'description'     => 'A peaceful garden-level apartment perfect for patients who want a quiet, green environment to recover in. Comes with private parking, air conditioning, and an on-call nurse service for added safety.',
                'rating'          => 4.7,
                'distance_text'   => '18 min walk to Medical Center',
                'bedrooms'        => 1,
                'max_guests'      => 2,
                'area_sqm'        => 48,
                'price_per_night' => 118.00,
                'currency'        => 'USD',
                'amenities'       => ['Free Wi-Fi', 'Air conditioning', 'Private parking', 'On-call nurse'],
                'status'          => 'active',
                'sort_order'      => 3,
            ],
        ];

        foreach ($accommodations as $data) {
            Accommodation::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        $this->command->info('✅ Accommodations seeded successfully (' . count($accommodations) . ' records).');
    }
}
