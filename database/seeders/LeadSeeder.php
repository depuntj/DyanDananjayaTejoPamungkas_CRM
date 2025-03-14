<?php
namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $salesUser = User::where('role', 'sales')->first();

        $leads = [
            [
                'name' => 'John Doe',
                'company_name' => 'Acme Corporation',
                'email' => 'john.doe@example.com',
                'phone' => '1234567890',
                'address' => '123 Business St, City, Country',
                'status' => 'new',
                'assigned_to' => $salesUser->id,
            ],
            [
                'name' => 'Jane Smith',
                'company_name' => 'Tech Innovations',
                'email' => 'jane.smith@example.com',
                'phone' => '0987654321',
                'address' => '456 Tech Ave, City, Country',
                'status' => 'contacted',
                'assigned_to' => $salesUser->id,
            ],
        ];

        foreach ($leads as $leadData) {
            Lead::create($leadData);
        }
    }
}
