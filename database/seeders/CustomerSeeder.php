<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Get approved projects to convert to customers
        $approvedProjects = Project::where('status', 'approved')
            ->with('lead', 'products')
            ->get();

        $products = Product::all();

        if ($approvedProjects->isEmpty()) {
            $this->command->info('Cannot create customers: No approved projects found. Run the ProjectSeeder first.');
            return;
        }

        foreach ($approvedProjects as $project) {
            // Create customer from project
            $customer = Customer::create([
                'name' => $project->lead->name,
                'company_name' => $project->lead->company_name,
                'email' => $project->lead->email,
                'phone' => $project->lead->phone,
                'address' => $project->lead->address,
                'lead_id' => $project->lead->id,
                'project_id' => $project->id,
                'customer_id' => 'CUST-' . str_pad(rand(1, 999), 6, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);

            // Add services from project products
            foreach ($project->products as $product) {
                $customer->services()->attach($product->id, [
                    'price' => $product->pivot->price,
                    'start_date' => now()->subDays(rand(1, 30)),
                    'end_date' => now()->addMonths(rand(6, 24)),
                    'status' => 'active',
                ]);
            }

            // Add 1-2 additional random services
            $additionalProducts = $products->diff($project->products)->random(rand(1, 2));
            foreach ($additionalProducts as $product) {
                $customer->services()->attach($product->id, [
                    'price' => $product->price,
                    'start_date' => now()->subDays(rand(1, 15)),
                    'end_date' => now()->addMonths(rand(6, 12)),
                    'status' => 'active',
                ]);
            }

            // Update the project status to completed
            $project->update(['status' => 'completed']);

            // Mark the lead as converted
            $project->lead->update(['status' => 'converted']);
        }

        // Create a few more customers directly (not from projects)
        $directCustomers = [
            [
                'name' => 'Sarah Johnson',
                'company_name' => 'Johnson Family',
                'email' => 'sarah.johnson@example.com',
                'phone' => '555-123-4567',
                'address' => '789 Residential Ave, Hometown, ST 12345',
            ],
            [
                'name' => 'Tech Innovations Inc',
                'company_name' => 'Tech Innovations Inc',
                'email' => 'info@techinnovations.example',
                'phone' => '555-987-6543',
                'address' => '456 Business Park, Enterprise City, ST 54321',
            ],
        ];

        foreach ($directCustomers as $index => $customerData) {
            $customer = Customer::create([
                'name' => $customerData['name'],
                'company_name' => $customerData['company_name'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'],
                'address' => $customerData['address'],
                'lead_id' => null,
                'project_id' => null,
                'customer_id' => 'CUST-' . str_pad(1000 + $index, 6, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);

            // Add 2-3 random services
            $customerProducts = $products->random(rand(2, 3));
            foreach ($customerProducts as $product) {
                $customer->services()->attach($product->id, [
                    'price' => $product->price,
                    'start_date' => now()->subMonths(rand(1, 6)),
                    'end_date' => now()->addMonths(rand(6, 12)),
                    'status' => 'active',
                ]);
            }
        }
    }
}
