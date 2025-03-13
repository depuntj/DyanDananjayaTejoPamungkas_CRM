<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalLeads = Lead::when($user->role === 'sales', function ($query) use ($user) {
            return $query->where('assigned_to', $user->id);
        })->count();

        $totalProjects = Project::when($user->role === 'sales', function ($query) use ($user) {
            return $query->where('assigned_to', $user->id);
        })->count();

        $totalCustomers = Customer::count();

        $pendingApprovals = Project::where('status', 'pending')->count();

        $recentLeads = Lead::with('assignedUser')
            ->when($user->role === 'sales', function ($query) use ($user) {
                return $query->where('assigned_to', $user->id);
            })
            ->latest()
            ->take(5)
            ->get();

        $recentProjects = Project::with(['lead', 'assignedUser'])
            ->when($user->role === 'sales', function ($query) use ($user) {
                return $query->where('assigned_to', $user->id);
            })
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalLeads' => $totalLeads,
            'totalProjects' => $totalProjects,
            'totalCustomers' => $totalCustomers,
            'pendingApprovals' => $pendingApprovals,
            'recentLeads' => $recentLeads,
            'recentProjects' => $recentProjects,
        ]);
    }
}
