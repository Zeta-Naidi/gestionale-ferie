<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LeaveRequest;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Create a manager
        $manager = User::create([
            'name' => 'Mario Rossi',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'is_manager' => true,
        ]);

        // Create employees
        $employee1 = User::create([
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'password' => bcrypt('password'),
            'manager_id' => $manager->id,
            'is_manager' => false,
        ]);

        $employee2 = User::create([
            'name' => 'Anna Verdi',
            'email' => 'anna@example.com',
            'password' => bcrypt('password'),
            'manager_id' => $manager->id,
            'is_manager' => false,
        ]);

        // Create some leave requests
        LeaveRequest::create([
            'user_id' => $employee1->id,
            'manager_id' => $manager->id,
            'type' => 'vacation',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(14),
            'days_count' => 5,
            'reason' => 'Vacanze estive',
            'status' => 'approved',
            'approved_at' => Carbon::now(),
        ]);

        LeaveRequest::create([
            'user_id' => $employee1->id,
            'manager_id' => $manager->id,
            'type' => 'personal',
            'start_date' => Carbon::now()->addDays(30),
            'end_date' => Carbon::now()->addDays(30),
            'days_count' => 1,
            'reason' => 'Visita medica',
            'status' => 'pending',
        ]);

        LeaveRequest::create([
            'user_id' => $employee2->id,
            'manager_id' => $manager->id,
            'type' => 'vacation',
            'start_date' => Carbon::now()->addDays(20),
            'end_date' => Carbon::now()->addDays(25),
            'days_count' => 6,
            'reason' => 'Ferie programmate',
            'status' => 'pending',
        ]);
    }
}
