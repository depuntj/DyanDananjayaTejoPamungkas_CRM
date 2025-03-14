<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle, MoreHorizontal, Plus, Search, XCircle } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Project {
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
    approvedBy: {
        id: number;
        name: string;
    } | null;
}

interface Pagination {
    data: Project[];
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
}

const props = defineProps<{
    projects: Pagination;
}>();

const search = ref('');
const filterStatus = ref('');

// Trigger filtering with a delay after typing
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// Apply filters immediately when status filter changes
watch(filterStatus, () => {
    applyFilters();
});

const applyFilters = () => {
    const params: Record<string, string> = {};

    if (search.value) {
        params.search = search.value;
    }

    if (filterStatus.value) {
        params.status = filterStatus.value;
    }

    router.get(route('projects.index'), params);
};

const getStatusColor = (status: string) => {
    switch (status) {
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

const approveProject = (projectId: number) => {
    router.post(route('projects.approve', projectId));
};

const rejectProject = (projectId: number) => {
    if (confirm('Are you sure you want to reject this project?')) {
        router.post(route('projects.reject', projectId));
    }
};

const convertToCustomer = (projectId: number) => {
    router.post(route('projects.convert', projectId));
};

const statusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'pending', label: 'Pending' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'completed', label: 'Completed' },
];
</script>

<template>
    <Head title="Projects" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-bold">Projects</h1>
                <Link :href="route('projects.create')">
                    <Button size="sm">
                        <Plus class="mr-2 h-4 w-4" />
                        Add New Project
                    </Button>
                </Link>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div class="w-full sm:max-w-xs">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input type="search" placeholder="Search projects..." class="pl-8" v-model="search" />
                    </div>
                </div>
                <div class="flex w-full items-center gap-3 sm:w-auto">
                    <Select v-model="filterStatus">
                        <SelectTrigger class="w-full sm:w-[180px]">
                            <SelectValue :placeholder="statusOptions[0].label" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Projects Table -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Project</TableHead>
                            <TableHead>Lead</TableHead>
                            <TableHead>Assigned To</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Created</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="project in projects.data" :key="project.id">
                            <TableCell>
                                <div class="font-medium">
                                    <Link :href="route('projects.show', project.id)" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ project.name }}
                                    </Link>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Link :href="route('leads.show', project.lead.id)" class="text-blue-600 hover:underline dark:text-blue-400">
                                    {{ project.lead.name }}
                                </Link>
                            </TableCell>
                            <TableCell>{{ project.assignedUser ? project.assignedUser.name : 'Unassigned' }}</TableCell>
                            <TableCell>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="getStatusColor(project.status)"
                                >
                                    {{ project.status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase()) }}
                                </span>
                            </TableCell>
                            <TableCell>{{ formatDate(project.created_at) }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button
                                        v-if="project.status === 'pending'"
                                        variant="outline"
                                        size="sm"
                                        class="text-green-600"
                                        @click="approveProject(project.id)"
                                    >
                                        <CheckCircle class="mr-1 h-4 w-4" />
                                        Approve
                                    </Button>
                                    <Button
                                        v-if="project.status === 'pending'"
                                        variant="outline"
                                        size="sm"
                                        class="text-red-600"
                                        @click="rejectProject(project.id)"
                                    >
                                        <XCircle class="mr-1 h-4 w-4" />
                                        Reject
                                    </Button>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 p-0">
                                                <MoreHorizontal class="h-4 w-4" />
                                                <span class="sr-only">Open menu</span>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem asChild>
                                                <Link :href="route('projects.show', project.id)">View</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem v-if="project.status !== 'approved' && project.status !== 'completed'" asChild>
                                                <Link :href="route('projects.edit', project.id)">Edit</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem v-if="project.status === 'approved'" @click="convertToCustomer(project.id)">
                                                Convert to Customer
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="projects.data.length === 0">
                            <TableCell colspan="6" class="py-6 text-center text-muted-foreground"> No projects found </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="projects.meta.last_page > 1" class="mt-4 flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Showing {{ projects.meta.from }} to {{ projects.meta.to }} of {{ projects.meta.total }} projects
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        v-if="projects.meta.current_page > 1"
                        :href="route('projects.index', { page: projects.meta.current_page - 1 })"
                        class="rounded-md bg-muted px-3 py-1 hover:bg-muted-foreground/10"
                    >
                        Previous
                    </Link>
                    <div v-for="(link, i) in projects.links.slice(1, -1)" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-md px-3 py-1"
                            :class="link.active ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted-foreground/10'"
                        >
                            {{ link.label }}
                        </Link>
                    </div>
                    <Link
                        v-if="projects.meta.current_page < projects.meta.last_page"
                        :href="route('projects.index', { page: projects.meta.current_page + 1 })"
                        class="rounded-md bg-muted px-3 py-1 hover:bg-muted-foreground/10"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
