<?php

namespace Database\Seeders;

use App\Models\JourneyRequest;
use App\Models\Accommodation;
use Illuminate\Database\Seeder;

class JourneyRequestsSeeder extends Seeder
{
    public function run(): void
    {
        $accommodations = Accommodation::pluck('id', 'name');
        $auroraId    = $accommodations->get('Aurora Suites — Deluxe Studio');
        $meridianId  = $accommodations->get('Meridian Residence — One Bedroom');
        $serenityId  = $accommodations->get('Serenity Garden Apartments');

        $records = [
            [
                'full_name'            => 'Bassel Raouf',
                'email'                => 'bassel.raouf50@gmail.com',
                'phone'                => '+201285537088',
                'country_of_residence' => 'Egypt',
                'procedure_sought'     => 'Gastric Bypass Surgery',
                'medical_notes'        => 'Type 2 diabetes, hypertension. No mobility issues.',
                'arrival_date'         => '2026-09-02',
                'departure_date'       => '2026-09-18',
                'fast_track_clearance' => true,
                'accommodation_id'     => $auroraId,
                'nights'               => 16,
                'status'               => 'confirmed',
                'internal_notes'       => 'Priority patient. Pre-op assessment booked for Sept 3.',
            ],
            [
                'full_name'            => 'Sarah Johnson',
                'email'                => 'sarah.j@email.co.uk',
                'phone'                => '+447911123456',
                'country_of_residence' => 'United Kingdom',
                'procedure_sought'     => 'Rhinoplasty',
                'medical_notes'        => null,
                'arrival_date'         => '2026-10-05',
                'departure_date'       => '2026-10-14',
                'fast_track_clearance' => false,
                'accommodation_id'     => $meridianId,
                'nights'               => 9,
                'status'               => 'under_review',
                'internal_notes'       => null,
            ],
            [
                'full_name'            => 'Mohamed Al-Rashidi',
                'email'                => null,
                'phone'                => '+966501234567',
                'country_of_residence' => 'Saudi Arabia',
                'procedure_sought'     => 'Knee Replacement',
                'medical_notes'        => 'Osteoarthritis grade 3. Wheelchair accessible room required.',
                'arrival_date'         => '2026-09-15',
                'departure_date'       => '2026-10-01',
                'fast_track_clearance' => true,
                'accommodation_id'     => $serenityId,
                'nights'               => 16,
                'status'               => 'in_progress',
                'internal_notes'       => 'Patient currently on-site. Surgery on Sept 17. Follow-up Sept 24.',
            ],
            [
                'full_name'            => 'Emma Dupont',
                'email'                => 'emma.dupont@gmail.com',
                'phone'                => '+33612345678',
                'country_of_residence' => 'France',
                'procedure_sought'     => 'IVF Treatment',
                'medical_notes'        => 'Previous failed cycle in France. AMH levels available on request.',
                'arrival_date'         => null,
                'departure_date'       => null,
                'fast_track_clearance' => false,
                'accommodation_id'     => null,
                'nights'               => null,
                'status'               => 'new',
                'internal_notes'       => null,
            ],
            [
                'full_name'            => 'James Okafor',
                'email'                => 'james.ok@yahoo.com',
                'phone'                => '+2348023456789',
                'country_of_residence' => 'Nigeria',
                'procedure_sought'     => 'Hair Transplant (FUE)',
                'medical_notes'        => null,
                'arrival_date'         => '2026-08-10',
                'departure_date'       => '2026-08-15',
                'fast_track_clearance' => false,
                'accommodation_id'     => $auroraId,
                'nights'               => 5,
                'status'               => 'completed',
                'internal_notes'       => 'Successful procedure. Patient left 5-star review.',
            ],
        ];

        foreach ($records as $data) {
            JourneyRequest::create($data);
        }

        $this->command->info('✅ Journey Requests seeded successfully (' . count($records) . ' records).');
    }
}
