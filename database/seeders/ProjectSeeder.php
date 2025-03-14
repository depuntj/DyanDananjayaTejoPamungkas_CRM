<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $leads = Lead::where('status', '!=', 'converted')->get();
        $salesUsers = User::where('role', 'sales')->get();
        $managerUser = User::where('role', 'manager')->first();
        $products = Product::all();

        if ($leads->isEmpty() || $salesUsers->isEmpty() || $products->isEmpty()) {
            $this->command->info('Cannot create projects: Missing leads, sales users, or products. Run those seeders first.');
            return;
        }

        $projects = [
            [
                'name' => 'Office Internet Installation',
                'status' => 'approved',
                'notes' => 'Customer needs high-speed fiber for their new office location.',
            ],
            [
                'name' => 'Residential Package Upgrade',
                'status' => 'approved',
                'notes' => 'Customer wants to upgrade from basic to premium package.',
            ],
            [
                'name' => 'Multi-location Business Setup',
                'status' => 'rejected',
                'notes' => 'Corporate client with multiple branch offices requiring synchronized setup.',
            ],
            [
                'name' => 'Small Business Starter',
                'status' => 'rejected',
                'notes' => 'New cafe opening next month needs reliable internet for point of sale and customer wifi.',
            ],
        ];

        foreach ($projects as $projectData) {
            $lead = $leads->random();
            $salesUser = $salesUsers->random();

            $project = Project::create([
                'name' => $projectData['name'],
                'lead_id' => $lead->id,
                'status' => $projectData['status'],
                'assigned_to' => $salesUser->id,
                'notes' => $projectData['notes'],
            ]);

            if ($project->status === 'approved') {
                $project->update([
                    'approved_by' => $managerUser->id,
                    'approved_at' => now()->subDays(rand(1, 14)),
                ]);
            }

            $projectProducts = $products->random(rand(1, 3));
            foreach ($projectProducts as $product) {
                $project->products()->attach($product->id, [
                    'price' => $product->price * (rand(80, 120) / 100), 
                    'quantity' => rand(1, 3),
                ]);
            }

            if ($project->status === 'pending' || $project->status === 'in_progress') {
                $lead->update(['status' => 'proposal']);
            } elseif ($project->status === 'approved') {
                $lead->update(['status' => 'negotiation']);
            }
        }
    }
}
