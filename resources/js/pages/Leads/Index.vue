<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreHorizontal, Plus, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    leads: {
        data: Array<{
            id: number;
            name: string;
            company_name: string | null;
            email: string;
            phone: string;
            status: string;
            created_at: string;
            assignedUser: {
                id: number;
                name: string;
            } | null;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        meta: {
            current_page: number;
            last_page: number;
            from: number;
            to: number;
            total: number;
            per_page: number;
        };
    };
    filters: {
        search?: string;
        status?: string;
    };
}>();

// Initialize search with props value
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

// Debounce search input
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// Apply filters immediately when status filter changes
watch(statusFilter, () => {
    applyFilters();
});

function applyFilters() {
    router.get(
        route('leads.index'),
        {
            search: search.value,
            status: statusFilter.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

const statusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'new', label: 'New' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'qualified', label: 'Qualified' },
    { value: 'proposal', label: 'Proposal' },
    { value: 'negotiation', label: 'Negotiation' },
    { value: 'lost', label: 'Lost' },
    { value: 'converted', label: 'Converted' },
];

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
    <Head title="Leads" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-bold">Leads</h1>
                <Link :href="route('leads.create')">
                    <Button size="sm">
                        <Plus class="mr-2 h-4 w-4" />
                        Add New Lead
                    </Button>
                </Link>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div class="w-full sm:max-w-xs">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input type="search" placeholder="Search leads..." class="pl-8" v-model="search" />
                    </div>
                </div>
                <div class="flex w-full items-center gap-3 sm:w-auto">
                    <select v-model="statusFilter" class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Leads Table -->
            <div v-if="leads.data.length > 0" class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Company</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Assigned To</TableHead>
                            <TableHead>Created</TableHead>
                            <TableHead class="w-[80px]">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="lead in leads.data" :key="lead.id">
                            <TableCell>
                                <div class="font-medium">
                                    <Link :href="route('leads.show', lead.id)" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ lead.name }}
                                    </Link>
                                </div>
                                <div class="text-xs text-muted-foreground">{{ lead.email }}</div>
                            </TableCell>
                            <TableCell>{{ lead.company_name || 'N/A' }}</TableCell>
                            <TableCell>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="getStatusColor(lead.status)"
                                >
                                    {{ lead.status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase()) }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <template v-if="lead.assignedUser">
                                    {{ lead.assignedUser.name }}
                                </template>
                                <template v-else-if="lead.assigned_to"> Sales User {{ lead.assigned_to }} </template>
                                <template v-else> Unassigned </template>
                            </TableCell>
                            <TableCell>{{ formatDate(lead.created_at) }}</TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 p-0">
                                            <MoreHorizontal class="h-4 w-4" />
                                            <span class="sr-only">Open menu</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem asChild>
                                            <Link :href="route('leads.show', lead.id)">View</Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem asChild>
                                            <Link :href="route('leads.edit', lead.id)">Edit</Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem asChild>
                                            <Link :href="route('projects.create', { lead_id: lead.id })">Create Project</Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- No Leads Found Message -->
            <div v-else class="flex h-64 flex-col items-center justify-center text-center text-muted-foreground">
                <p class="mb-4">No leads found. Try adjusting your search or filters.</p>
                <Link :href="route('leads.create')">
                    <Button variant="outline">
                        <Plus class="mr-2 h-4 w-4" />
                        Add New Lead
                    </Button>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="leads.meta.last_page > 1" class="mt-4 flex items-center justify-between">
                <div class="text-sm text-muted-foreground">Showing {{ leads.meta.from }} to {{ leads.meta.to }} of {{ leads.meta.total }} leads</div>
                <div class="flex items-center space-x-2">
                    <Link
                        v-if="leads.meta.current_page > 1"
                        :href="route('leads.index', { page: leads.meta.current_page - 1, search: search, status: statusFilter })"
                        class="rounded-md bg-muted px-3 py-1 hover:bg-muted-foreground/10"
                    >
                        Previous
                    </Link>
                    <Link
                        v-for="page in leads.meta.last_page"
                        :key="page"
                        :href="route('leads.index', { page, search: search, status: statusFilter })"
                        class="rounded-md px-3 py-1"
                        :class="page === leads.meta.current_page ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted-foreground/10'"
                    >
                        {{ page }}
                    </Link>
                    <Link
                        v-if="leads.meta.current_page < leads.meta.last_page"
                        :href="route('leads.index', { page: leads.meta.current_page + 1, search: search, status: statusFilter })"
                        class="rounded-md bg-muted px-3 py-1 hover:bg-muted-foreground/10"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
