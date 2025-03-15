<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { MoreHorizontal, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Service {
    id: number;
    name: string;
    pivot: {
        status: string;
    };
}

interface Customer {
    id: number;
    name: string;
    company_name: string | null;
    email: string;
    phone: string;
    customer_id: string;
    is_active: boolean;
    created_at: string;
    services: Service[];
}

interface Pagination {
    data: Customer[];
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

const props = defineProps({
    customers: {
        type: Object,
        required: true,
        default: () => ({
            data: [],
            meta: {
                current_page: 1,
                last_page: 1,
                from: null,
                to: null,
                total: 0,
                per_page: 10,
            },
            links: [],
        }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters?.search || '');
const filterStatus = ref(props.filters?.status || '');

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

    window.location.href = route('customers.index', params);
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getActiveServicesCount = (customer: Customer) => {
    return customer.services.filter((service) => service.pivot.status === 'active').length;
};

const statusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];
</script>

<template>
    <Head title="Customers" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-bold">Customers</h1>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div class="w-full sm:max-w-xs">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input type="search" placeholder="Search customers..." class="pl-8" v-model="search" />
                    </div>
                </div>
                <div class="flex w-full items-center gap-3 sm:w-auto">
                    <select v-model="filterStatus" class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Customers Table -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Company</TableHead>
                            <TableHead>Services</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Since</TableHead>
                            <TableHead class="w-[80px]">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="customer in customers.data" :key="customer.id">
                            <TableCell>{{ customer.customer_id }}</TableCell>
                            <TableCell>
                                <div class="font-medium">
                                    <Link :href="route('customers.show', customer.id)" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ customer.name }}
                                    </Link>
                                </div>
                                <div class="text-xs text-muted-foreground">{{ customer.email }}</div>
                            </TableCell>
                            <TableCell>{{ customer.company_name || 'N/A' }}</TableCell>
                            <TableCell>{{ getActiveServicesCount(customer) }} active</TableCell>
                            <TableCell>
                                <template v-if="lead.assignedUser">
                                    {{ lead.assignedUser.name }}
                                </template>
                                <template v-else> Unassigned </template>
                            </TableCell>
                            <TableCell>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="
                                        customer.is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100'
                                    "
                                >
                                    {{ customer.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </TableCell>
                            <TableCell>{{ formatDate(customer.created_at) }}</TableCell>
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
                                            <Link :href="route('customers.show', customer.id)">View</Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem asChild>
                                            <Link :href="route('customers.edit', customer.id)">Edit</Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="customers.data.length === 0">
                            <TableCell colspan="7" class="py-6 text-center text-muted-foreground"> No customers found </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="customers.meta.last_page > 1" class="mt-4 flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Showing {{ customers.meta.from }} to {{ customers.meta.to }} of {{ customers.meta.total }} customers
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        v-if="customers.meta.current_page > 1"
                        :href="route('customers.index', { page: customers.meta.current_page - 1 })"
                        class="rounded-md bg-muted px-3 py-1 hover:bg-muted-foreground/10"
                    >
                        Previous
                    </Link>
                    <div v-for="(link, i) in customers.links.slice(1, -1)" :key="i">
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
                        v-if="customers.meta.current_page < customers.meta.last_page"
                        :href="route('customers.index', { page: customers.meta.current_page + 1 })"
                        class="rounded-md bg-muted px-3 py-1 hover:bg-muted-foreground/10"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
