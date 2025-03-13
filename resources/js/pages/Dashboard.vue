<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertCircle, FileSpreadsheet, Users } from 'lucide-vue-next';

defineProps<{
    totalLeads: number;
    totalProjects: number;
    totalCustomers: number;
    pendingApprovals: number;
    recentLeads: Array<{
        id: number;
        name: string;
        company_name: string;
        email: string;
        phone: string;
        status: string;
        created_at: string;
        assignedUser: {
            id: number;
            name: string;
        } | null;
    }>;
    recentProjects: Array<{
        id: number;
        name: string;
        status: string;
        created_at: string;
        lead: {
            id: number;
            name: string;
        };
        assignedUser: {
            id: number;
            name: string;
        } | null;
    }>;
}>();

const getStatusColor = (status: string) => {
    switch (status) {
        case 'new':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100';
        case 'contacted':
            return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-100';
        case 'qualified':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
        case 'proposal':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100';
        case 'negotiation':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100';
        case 'lost':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
        case 'converted':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100';
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100';
        case 'completed':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100';
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Dashboard</h1>
            </div>

            <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Leads Card -->
                <Card>
                    <CardContent class="flex items-center p-6">
                        <div class="mr-4 rounded-full bg-blue-100 p-4 dark:bg-blue-900">
                            <Users class="h-6 w-6 text-blue-600 dark:text-blue-300" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Leads</p>
                            <h2 class="text-3xl font-bold">{{ totalLeads }}</h2>
                        </div>
                        <div class="flex-grow text-right">
                            <Link :href="route('leads.index')">
                                <Button variant="ghost" size="sm">View All</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Projects Card -->
                <Card>
                    <CardContent class="flex items-center p-6">
                        <div class="mr-4 rounded-full bg-purple-100 p-4 dark:bg-purple-900">
                            <FileSpreadsheet class="h-6 w-6 text-purple-600 dark:text-purple-300" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Projects</p>
                            <h2 class="text-3xl font-bold">{{ totalProjects }}</h2>
                        </div>
                        <div class="flex-grow text-right">
                            <Link :href="route('projects.index')">
                                <Button variant="ghost" size="sm">View All</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Customers Card -->
                <Card>
                    <CardContent class="flex items-center p-6">
                        <div class="mr-4 rounded-full bg-green-100 p-4 dark:bg-green-900">
                            <Users class="h-6 w-6 text-green-600 dark:text-green-300" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Customers</p>
                            <h2 class="text-3xl font-bold">{{ totalCustomers }}</h2>
                        </div>
                        <div class="flex-grow text-right">
                            <Link :href="route('customers.index')">
                                <Button variant="ghost" size="sm">View All</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pending Approvals Card -->
                <Card>
                    <CardContent class="flex items-center p-6">
                        <div class="mr-4 rounded-full bg-yellow-100 p-4 dark:bg-yellow-900">
                            <AlertCircle class="h-6 w-6 text-yellow-600 dark:text-yellow-300" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Pending Approvals</p>
                            <h2 class="text-3xl font-bold">{{ pendingApprovals }}</h2>
                        </div>
                        <div class="flex-grow text-right">
                            <Link :href="route('projects.index')">
                                <Button variant="ghost" size="sm">View All</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Recent Leads -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-lg font-medium">Recent Leads</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-if="recentLeads.length === 0" class="py-4 text-center text-muted-foreground">No leads to display</div>
                            <div v-for="lead in recentLeads" :key="lead.id" class="flex items-center justify-between py-2">
                                <div class="flex flex-col">
                                    <Link :href="route('leads.show', lead.id)" class="font-medium text-blue-600 hover:underline dark:text-blue-400">
                                        {{ lead.name }}
                                    </Link>
                                    <span class="text-sm text-muted-foreground">{{ lead.company_name || 'N/A' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full px-2 py-1 text-xs" :class="getStatusColor(lead.status)">
                                        {{ lead.status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase()) }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">{{ formatDate(lead.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                        <Separator class="my-4" />
                        <div class="flex justify-end">
                            <Link :href="route('leads.index')">
                                <Button variant="ghost" size="sm">View All Leads</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Projects -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-lg font-medium">Recent Projects</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-if="recentProjects.length === 0" class="py-4 text-center text-muted-foreground">No projects to display</div>
                            <div v-for="project in recentProjects" :key="project.id" class="flex items-center justify-between py-2">
                                <div class="flex flex-col">
                                    <Link
                                        :href="route('projects.show', project.id)"
                                        class="font-medium text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        {{ project.name }}
                                    </Link>
                                    <span class="text-sm text-muted-foreground">Lead: {{ project.lead.name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full px-2 py-1 text-xs" :class="getStatusColor(project.status)">
                                        {{ project.status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase()) }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">{{ formatDate(project.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                        <Separator class="my-4" />
                        <div class="flex justify-end">
                            <Link :href="route('projects.index')">
                                <Button variant="ghost" size="sm">View All Projects</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
