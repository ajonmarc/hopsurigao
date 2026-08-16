<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Inclusion;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PickupLocation;
use App\Models\PickupSchedule;
use App\Models\Reminder;
use App\Models\TourDate;
use App\Models\TourTime;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TourismSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Packages
        |--------------------------------------------------------------------------
        */

        $package1 = Package::create([
            'package_name' => 'Island Hopping Adventure',
            'destination' => 'Siargao Island',
            'description' => 'Explore the beautiful islands and beaches of Siargao.',
            'image' => 'packages/island-hopping.jpg',
            'price' => 2500.00,
            'status' => 'active',
        ]);

        $package2 = Package::create([
            'package_name' => 'Sugba Lagoon Adventure',
            'destination' => 'Del Carmen, Siargao',
            'description' => 'Experience the beautiful waters and natural scenery of Sugba Lagoon.',
            'image' => 'packages/sugba-lagoon.jpg',
            'price' => 2200.00,
            'status' => 'active',
        ]);

        $package3 = Package::create([
            'package_name' => 'Surigao Cultural Tour',
            'destination' => 'Surigao City',
            'description' => 'Discover the culture, history, and local attractions of Surigao.',
            'image' => 'packages/cultural-tour.jpg',
            'price' => 1500.00,
            'status' => 'active',
        ]);

        $package4 = Package::create([
            'package_name' => 'Mountain Escape',
            'destination' => 'Surigao del Norte',
            'description' => 'Enjoy a relaxing nature and mountain adventure.',
            'image' => 'packages/mountain-escape.jpg',
            'price' => 1800.00,
            'status' => 'inactive',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Inclusions
        |--------------------------------------------------------------------------
        */

        foreach ([
            'Boat transportation',
            'Tour guide',
            'Entrance fees',
            'Lunch',
            'Drinking water',
        ] as $description) {
            Inclusion::create([
                'package_id' => $package1->id,
                'description' => $description,
            ]);
        }

        foreach ([
            'Boat transportation',
            'Tour guide',
            'Entrance fee',
            'Lunch',
        ] as $description) {
            Inclusion::create([
                'package_id' => $package2->id,
                'description' => $description,
            ]);
        }

        foreach ([
            'Tour guide',
            'Transportation',
            'Entrance fees',
            'Local snacks',
        ] as $description) {
            Inclusion::create([
                'package_id' => $package3->id,
                'description' => $description,
            ]);
        }

        foreach ([
            'Transportation',
            'Tour guide',
            'Entrance fees',
        ] as $description) {
            Inclusion::create([
                'package_id' => $package4->id,
                'description' => $description,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Reminders
        |--------------------------------------------------------------------------
        */

        foreach ([
            'Bring a valid ID.',
            'Bring sunscreen.',
            'Arrive 30 minutes before departure.',
            'Bring extra clothes.',
        ] as $description) {
            Reminder::create([
                'package_id' => $package1->id,
                'description' => $description,
            ]);
        }

        foreach ([
            'Bring waterproof bags.',
            'Wear comfortable clothing.',
            'Follow the tour guide instructions.',
        ] as $description) {
            Reminder::create([
                'package_id' => $package2->id,
                'description' => $description,
            ]);
        }

        foreach ([
            'Respect cultural and heritage sites.',
            'Bring a valid ID.',
            'Wear comfortable clothing.',
        ] as $description) {
            Reminder::create([
                'package_id' => $package3->id,
                'description' => $description,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Tour Dates
        |--------------------------------------------------------------------------
        */

        $date1 = TourDate::create([
            'package_id' => $package1->id,
            'tour_date' => '2026-09-01',
            'capacity' => 12,
        ]);

        $date2 = TourDate::create([
            'package_id' => $package1->id,
            'tour_date' => '2026-09-05',
            'capacity' => 15,
        ]);

        $date3 = TourDate::create([
            'package_id' => $package2->id,
            'tour_date' => '2026-09-03',
            'capacity' => 10,
        ]);

        $date4 = TourDate::create([
            'package_id' => $package3->id,
            'tour_date' => '2026-09-10',
            'capacity' => 20,
        ]);

        $date5 = TourDate::create([
            'package_id' => $package4->id,
            'tour_date' => '2026-09-15',
            'capacity' => 8,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Tour Times
        |--------------------------------------------------------------------------
        */

        $times = [
            [$date1->id, '07:30:00', 'Meet-up at jump-off point'],
            [$date1->id, '08:00:00', 'Boat departure'],
            [$date1->id, '12:00:00', 'Lunch'],
            [$date1->id, '04:30:00', 'Return to jump-off point'],

            [$date2->id, '07:30:00', 'Registration'],
            [$date2->id, '08:00:00', 'Boat departure'],
            [$date2->id, '12:00:00', 'Lunch'],
            [$date2->id, '05:00:00', 'Tour ends'],

            [$date3->id, '06:30:00', 'Pickup'],
            [$date3->id, '07:00:00', 'Departure'],
            [$date3->id, '09:00:00', 'Arrival at Sugba Lagoon'],
            [$date3->id, '04:00:00', 'Return trip'],

            [$date4->id, '08:00:00', 'Pickup'],
            [$date4->id, '09:00:00', 'Cultural tour begins'],
            [$date4->id, '12:00:00', 'Lunch'],
            [$date4->id, '04:00:00', 'Tour ends'],

            [$date5->id, '06:00:00', 'Pickup'],
            [$date5->id, '07:00:00', 'Mountain trek begins'],
            [$date5->id, '12:00:00', 'Lunch'],
            [$date5->id, '03:00:00', 'Return trip'],
        ];

        foreach ($times as [$tourDateId, $time, $description]) {
            TourTime::create([
                'tour_date_id' => $tourDateId,
                'time' => $time,
                'description' => $description,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Pickup Locations
        |--------------------------------------------------------------------------
        */

        $pickup1 = PickupLocation::create([
            'name' => 'Surigao City Tourism Office',
            'address' => 'Surigao City, Surigao del Norte',
            'description' => 'Main tourism office pickup point.',
            'status' => 'active',
        ]);

        $pickup2 = PickupLocation::create([
            'name' => 'Surigao Port',
            'address' => 'Port Area, Surigao City',
            'description' => 'Pickup point near the port.',
            'status' => 'active',
        ]);

        $pickup3 = PickupLocation::create([
            'name' => 'Surigao City Bus Terminal',
            'address' => 'Surigao City, Surigao del Norte',
            'description' => 'Pickup location for arriving tourists.',
            'status' => 'active',
        ]);

        $pickup4 = PickupLocation::create([
            'name' => 'Provincial Capitol',
            'address' => 'Surigao City, Surigao del Norte',
            'description' => 'Alternative meeting point.',
            'status' => 'inactive',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pickup Schedules
        |--------------------------------------------------------------------------
        |
        | Bookings now reference a specific pickup schedule (tour date + pickup
        | location + pickup time) instead of a pickup location directly.
        |
        */

        $schedule1 = PickupSchedule::create([
            'tour_date_id' => $date1->id,
            'pickup_location_id' => $pickup1->id,
            'pickup_time' => '07:30:00',
        ]);

        $schedule2 = PickupSchedule::create([
            'tour_date_id' => $date2->id,
            'pickup_location_id' => $pickup2->id,
            'pickup_time' => '07:30:00',
        ]);

        $schedule3 = PickupSchedule::create([
            'tour_date_id' => $date3->id,
            'pickup_location_id' => $pickup3->id,
            'pickup_time' => '06:30:00',
        ]);

        // A couple of extra schedules so date4/date5 have options too,
        // even though no booking uses them yet.
        PickupSchedule::create([
            'tour_date_id' => $date4->id,
            'pickup_location_id' => $pickup1->id,
            'pickup_time' => '08:00:00',
        ]);

        PickupSchedule::create([
            'tour_date_id' => $date5->id,
            'pickup_location_id' => $pickup2->id,
            'pickup_time' => '06:00:00',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Bookings
        |--------------------------------------------------------------------------
        |
        | Uses the first existing users in your database.
        |
        */

        $users = User::orderBy('id')->take(3)->get();

        if ($users->count() >= 3) {
            $booking1 = Booking::create([
                'user_id' => $users[0]->id,
                'tour_date_id' => $date1->id,
                'pickup_schedule_id' => $schedule1->id,
                'number_of_guests' => 4,
                'phone_number' => '09171234567',
                'nationality' => 'Filipino',
                'special_request' => 'Vegetarian meal.',
                'booking_status' => 'confirmed',
                'qr_token' => 'QR-' . Str::random(32) . '-1',
            ]);

            $booking2 = Booking::create([
                'user_id' => $users[1]->id,
                'tour_date_id' => $date2->id,
                'pickup_schedule_id' => $schedule2->id,
                'number_of_guests' => 2,
                'phone_number' => '09179876543',
                'nationality' => 'Filipino',
                'special_request' => null,
                'booking_status' => 'pending',
                'qr_token' => 'QR-' . Str::random(32) . '-2',
            ]);

            $booking3 = Booking::create([
                'user_id' => $users[2]->id,
                'tour_date_id' => $date3->id,
                'pickup_schedule_id' => $schedule3->id,
                'number_of_guests' => 5,
                'phone_number' => '09175555555',
                'nationality' => 'Filipino',
                'special_request' => 'Need additional drinking water.',
                'booking_status' => 'completed',
                'qr_token' => 'QR-' . Str::random(32) . '-3',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Payments
            |--------------------------------------------------------------------------
            */

            Payment::create([
                'booking_id' => $booking1->id,
                'amount' => 10000.00,
                'payment_method' => 'GCash',
                'payment_status' => 'paid',
                'transaction_reference' => 'GCASH-TEST-001',
                'proof_of_payment' => 'payments/test-receipt-001.jpg',
                'notes' => 'Full payment.',
                'paid_at' => '2026-08-15 10:00:00',
            ]);

            Payment::create([
                'booking_id' => $booking2->id,
                'amount' => 5000.00,
                'payment_method' => 'Bank Transfer',
                'payment_status' => 'pending',
                'transaction_reference' => null,
                'proof_of_payment' => null,
                'notes' => 'Waiting for payment verification.',
                'paid_at' => null,
            ]);

            Payment::create([
                'booking_id' => $booking3->id,
                'amount' => 11000.00,
                'payment_method' => 'Cash',
                'payment_status' => 'paid',
                'transaction_reference' => 'CASH-TEST-001',
                'proof_of_payment' => null,
                'notes' => 'Paid at tourism office.',
                'paid_at' => '2026-08-10 09:30:00',
            ]);
        }
    }
}