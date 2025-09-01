<?php

namespace Database\Seeders;

use App\Models\OrganizationalUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizationalStructureSeeder extends Seeder
{
    public function run(): void
    {
        // Create organizational structure
        $company = OrganizationalUnit::create([
            'name' => 'COMPANY',
            'code' => 'COMPANY',
            'level' => 0,
            'description' => 'Root company organization'
        ]);

        // IT Department
        $it = OrganizationalUnit::create([
            'name' => 'IT',
            'code' => 'IT',
            'parent_id' => $company->id,
            'level' => 1,
            'description' => 'Information Technology Department'
        ]);

        $dev = OrganizationalUnit::create([
            'name' => 'DEV',
            'code' => 'IT-DEV',
            'parent_id' => $it->id,
            'level' => 2,
            'description' => 'Development Team'
        ]);

        $infra = OrganizationalUnit::create([
            'name' => 'INFRA',
            'code' => 'IT-INFRA',
            'parent_id' => $it->id,
            'level' => 2,
            'description' => 'Infrastructure Team'
        ]);

        // HR Department
        $hr = OrganizationalUnit::create([
            'name' => 'HR',
            'code' => 'HR',
            'parent_id' => $company->id,
            'level' => 1,
            'description' => 'Human Resources Department'
        ]);

        // Marketing Department
        $mkt = OrganizationalUnit::create([
            'name' => 'MKT',
            'code' => 'MKT',
            'parent_id' => $company->id,
            'level' => 1,
            'description' => 'Marketing Department'
        ]);

        // Sales Department
        $sales = OrganizationalUnit::create([
            'name' => 'SALES',
            'code' => 'SALES',
            'parent_id' => $company->id,
            'level' => 1,
            'description' => 'Sales Department'
        ]);

        // Create users
        
        // Admin IT (not part of leave workflow)
        $adminIT = User::create([
            'name' => 'Admin IT',
            'email' => 'admin@company.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN_IT,
            'organizational_unit_id' => $it->id,
        ]);

        // HR Department (2 specialists)
        $hr1 = User::create([
            'name' => 'Maria Rossi',
            'email' => 'maria.rossi@company.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_HR,
            'organizational_unit_id' => $hr->id,
        ]);

        $hr2 = User::create([
            'name' => 'Giuseppe Bianchi',
            'email' => 'giuseppe.bianchi@company.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_HR,
            'organizational_unit_id' => $hr->id,
        ]);

        // DEV Team (1 manager + 3 developers)
        $devManager = User::create([
            'name' => 'Luca Verdi',
            'email' => 'luca.verdi@company.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MANAGER,
            'organizational_unit_id' => $dev->id,
            'is_manager' => true,
        ]);

        $devs = [
            ['name' => 'Anna Ferrari', 'email' => 'anna.ferrari@company.com'],
            ['name' => 'Marco Russo', 'email' => 'marco.russo@company.com'],
            ['name' => 'Sofia Romano', 'email' => 'sofia.romano@company.com'],
        ];

        foreach ($devs as $devData) {
            User::create([
                'name' => $devData['name'],
                'email' => $devData['email'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_EMPLOYEE,
                'organizational_unit_id' => $dev->id,
                'manager_id' => $devManager->id,
            ]);
        }

        // INFRA Team (1 manager + 2 technicians)
        $infraManager = User::create([
            'name' => 'Paolo Conti',
            'email' => 'paolo.conti@company.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MANAGER,
            'organizational_unit_id' => $infra->id,
            'is_manager' => true,
        ]);

        $infraTechs = [
            ['name' => 'Elena Ricci', 'email' => 'elena.ricci@company.com'],
            ['name' => 'Davide Marino', 'email' => 'davide.marino@company.com'],
        ];

        foreach ($infraTechs as $techData) {
            User::create([
                'name' => $techData['name'],
                'email' => $techData['email'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_EMPLOYEE,
                'organizational_unit_id' => $infra->id,
                'manager_id' => $infraManager->id,
            ]);
        }

        // Marketing Team (1 manager + 2 employees)
        $mktManager = User::create([
            'name' => 'Francesca Galli',
            'email' => 'francesca.galli@company.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MANAGER,
            'organizational_unit_id' => $mkt->id,
            'is_manager' => true,
        ]);

        $mktEmployees = [
            ['name' => 'Roberto Costa', 'email' => 'roberto.costa@company.com'],
            ['name' => 'Chiara Fontana', 'email' => 'chiara.fontana@company.com'],
        ];

        foreach ($mktEmployees as $empData) {
            User::create([
                'name' => $empData['name'],
                'email' => $empData['email'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_EMPLOYEE,
                'organizational_unit_id' => $mkt->id,
                'manager_id' => $mktManager->id,
            ]);
        }

        // Sales Team (1 manager + 3 employees)
        $salesManager = User::create([
            'name' => 'Andrea Lombardi',
            'email' => 'andrea.lombardi@company.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MANAGER,
            'organizational_unit_id' => $sales->id,
            'is_manager' => true,
        ]);

        $salesEmployees = [
            ['name' => 'Valentina Moretti', 'email' => 'valentina.moretti@company.com'],
            ['name' => 'Simone Bruno', 'email' => 'simone.bruno@company.com'],
            ['name' => 'Giulia Rizzo', 'email' => 'giulia.rizzo@company.com'],
        ];

        foreach ($salesEmployees as $empData) {
            User::create([
                'name' => $empData['name'],
                'email' => $empData['email'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_EMPLOYEE,
                'organizational_unit_id' => $sales->id,
                'manager_id' => $salesManager->id,
            ]);
        }

        $this->command->info('Organizational structure and users created successfully!');
        $this->command->info('Created:');
        $this->command->info('- 1 Admin IT (admin@company.com)');
        $this->command->info('- 2 HR specialists');
        $this->command->info('- 4 managers (DEV, INFRA, MKT, SALES)');
        $this->command->info('- 10 employees across all departments');
        $this->command->info('Default password for all users: password');
    }
}
