<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Service::truncate();
        \App\Models\ServiceProcedure::truncate();
        \App\Models\ServicePackage::truncate();
        \App\Models\ServicePriceItem::truncate();
        \App\Models\ServiceFaq::truncate();
        \App\Models\ServiceReview::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Weight Loss Surgery & Bariatric Procedures
        $bariatric = Service::create([
            'name'                 => 'Weight Loss Surgery & Bariatric Procedures',
            'slug'                 => 'bariatric-surgery',
            'tagline'              => 'TRANSFORM YOUR HEALTH WITH EXPERT BARIATRIC SURGERY',
            'short_description'    => 'At Vital Global Care, we offer life-changing bariatric surgery solutions tailored to your unique health needs. Whether you\'re seeking a new beginning or a revision to past procedures, we are here to support you every step of the way.',
            'status'               => 'active',
            'sort_order'           => 1,
            'benefits'             => [
                'Significant weight loss',
                'Resolution of obesity-related conditions (e.g. diabetes, hypertension)',
                'Improved quality of life and confidence',
                'Longer life expectancy',
                'Enhanced mobility and physical activity',
            ],
            'why_us_points'        => [
                'World-Class Surgeons: Renowned experts specializing in bariatric and metabolic surgery.',
                'State-of-the-Art Hospitals: Equipped with advanced surgical technology and premium facilities.',
                'Affordable and Transparent Packages: Save significantly without compromising quality.',
                'Comprehensive Care: From initial consultation to lifelong support.',
                'Global Expertise: Trusted by patients from the UK, Europe, and beyond.',
            ],
            'packages_tagline'     => 'ALL-IN-INCLUSIVE',
            'packages_description' => 'We offer all-inclusive packages to make your journey seamless and stress-free.',
            'packages_include'     => [
                'Initial consultation with a bariatric specialist',
                'Pre-surgery tests and evaluations',
                'Surgery in a modern, accredited hospital',
                'Accommodation at a luxury hotel post-surgery',
                'Airport and hospital transfers',
                'Post-surgery follow-ups and aftercare',
            ]
        ]);

        $bariatricProcedures = [
            'Gastric Sleeve Surgery (Sleeve Gastrectomy)',
            'Gastric Bypass Surgery (Roux-en-Y)',
            'Mini Gastric Bypass (MGB)',
            'Revisional Bariatric Surgery',
        ];

        foreach ($bariatricProcedures as $p) {
            $bariatric->proceduresRelation()->create(['name' => $p, 'sort_order' => 0]);
        }

        $bariatric->packages()->create([
            'name'        => 'Gastric Sleeve Surgery (Sleeve Gastrectomy)',
            'description' => 'Removes approximately 80% of the stomach to create a sleeve-like structure, limiting food intake and reducing hunger hormones.',
            'content'     => '<h4>BENEFITS</h4><ul><li>Long-term weight loss</li><li>Improves conditions like Type 2 diabetes, sleep apnea, and high blood pressure</li><li>Quick recovery with minimal scarring</li></ul><h4>WHO IT\'S FOR</h4><ul><li>Patients with a BMI of 35+</li><li>Patients with obesity-related health problems</li></ul>'
        ]);

        $bariatric->packages()->create([
            'name'        => 'Gastric Bypass Surgery (Roux-en-Y)',
            'description' => 'Creates a smaller stomach pouch and reroutes a portion of the small intestine, reducing calorie absorption.',
            'content'     => '<h4>BENEFITS</h4><ul><li>Rapid weight loss</li><li>Effective for severe obesity and related health issues</li><li>Long-lasting results</li></ul><h4>WHO IT\'S FOR</h4><ul><li>Patients with a BMI of 40+</li><li>Ideal for individuals with severe conditions like diabetes</li></ul>'
        ]);

        // Price Items
        $bariatric->priceItems()->createMany([
            [
                'name'       => 'Classic Gastric Sleeve',
                'price'      => 'From £2,800 to £3,200',
                'note'       => null,
                'sort_order' => 1
            ],
            [
                'name'       => 'Nanotechnology Gastric Sleeve',
                'price'      => 'From £3,000 to £3,500',
                'note'       => 'no scar, no pain',
                'sort_order' => 2
            ]
        ]);

        // FAQs
        $bariatric->faqs()->createMany([
            [
                'question'   => 'What is BMI and why do I need to provide it?',
                'answer'     => 'BMI (Body Mass Index) helps us assess your overall health and determine the most suitable treatment plan for you.',
                'sort_order' => 1
            ],
            [
                'question'   => 'How long will my treatment take?',
                'answer'     => 'Usually, the treatment and immediate post-op observation take between 3 to 7 days.',
                'sort_order' => 2
            ],
            [
                'question'   => 'Are there any side effects to the treatment?',
                'answer'     => 'Bariatric surgery, like any surgery, carries risks. Potential temporary side effects will be fully explained during your consultation.',
                'sort_order' => 3
            ],
            [
                'question'   => 'Is financing available for the treatment?',
                'answer'     => 'We provide options for installment plans and flexible payment terms. Speak to our agents for details.',
                'sort_order' => 4
            ],
            [
                'question'   => 'Will I need follow-up appointments after the treatment?',
                'answer'     => 'Yes, long-term follow-ups are crucial and are included as part of our package offerings.',
                'sort_order' => 5
            ]
        ]);

        // Reviews
        $bariatric->reviews()->createMany([
            [
                'reviewer_name'     => 'Sarah M.',
                'reviewer_location' => 'UK',
                'rating'            => 5,
                'content'           => 'I feel like I have a new lease on life after my gastric sleeve surgery with Vital Global Care. The team was amazing, and the results speak for themselves!',
                'source'            => 'website',
                'status'            => 'selected',
            ],
            [
                'reviewer_name'     => 'Ahmed H.',
                'reviewer_location' => 'Egypt',
                'rating'            => 5,
                'content'           => 'The support I received before and after my gastric bypass was incredible. I highly recommend Vital Global Care to anyone considering bariatric surgery.',
                'source'            => 'website',
                'status'            => 'selected',
            ]
        ]);

        // 2. Cosmetic & Reconstructive Surgery
        $cosmetic = Service::create([
            'name'              => 'Cosmetic & Reconstructive Surgery',
            'slug'              => 'cosmetic-reconstructive-surgery',
            'tagline'           => 'Enhance Your Confidence with Expert Cosmetic Surgery',
            'short_description' => 'At Vital Global Care we connect you with Egypt’s leading plastic and reconstructive surgeons for natural-looking, confidence-boosting results in accredited, state-of-the-art facilities.',
            'status'            => 'active',
            'sort_order'        => 2,
            'benefits'          => [
                'Enhanced body confidence',
                'Natural, proportionate results',
                'Restored function and comfort',
                'Safe, modern surgical environment'
            ]
        ]);

        $cosmeticProcedures = [
            'Tummy Tuck (Abdominoplasty)',
            'Mommy Makeover',
            'Mini Abdominoplasty (Mini Tummy Tuck)',
            'Rhinoplasty (Nose Surgery)',
            'Thigh Lift (Thighplasty)',
        ];

        foreach ($cosmeticProcedures as $p) {
            $cosmetic->proceduresRelation()->create(['name' => $p, 'sort_order' => 0]);
        }

        // 3. Hair Transplant & Restoration Surgery
        $hair = Service::create([
            'name'              => 'Hair Transplant & Restoration Surgery',
            'slug'              => 'hair-transplant',
            'tagline'           => 'Restore Your Hair, Restore Your Confidence',
            'short_description' => 'Restore a fuller, natural hairline with Egypt’s leading hair restoration specialists. We offer the full range of modern transplant and regenerative techniques.',
            'status'            => 'active',
            'sort_order'        => 3,
        ]);

        $hairProcedures = [
            'Follicular Unit Transplantation (FUT)',
            'Direct Hair Implantation (DHI)',
            'Beard and Eyebrow Hair Transplants',
            'Follicular Unit Extraction (FUE)',
            'Platelet-Rich Plasma (PRP) Therapy',
            'Hairline Lowering (Forehead Reduction)'
        ];

        foreach ($hairProcedures as $p) {
            $hair->proceduresRelation()->create(['name' => $p, 'sort_order' => 0]);
        }
    }
}
