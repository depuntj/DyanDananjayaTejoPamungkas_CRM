<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreHorizontal, Plus, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

const props = defineProps<{
    users: {
        data: User[];
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
    filters?: {
        search?: string;
        role?: string;
    };
}>();

// Initialize with values from the URL or props
const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');

// Trigger filtering with a delay after typing
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// Apply filters immediately when role filter changes
watch(roleFilter, () => {
    applyFilters();
});

const applyFilters = () => {
    const params: Record<string, string> = {};

    if (search.value) {
        params.search = search.value;
    }

    if (roleFilter.value) {
        params.role = roleFilter.value;
    }

    router.get(route('users.index'), params, {
        preserveState: true,
        replace: true,
    });
};

const roleOptions = [
    { value: '', label: 'All Roles' },
    { value: 'admin', label: 'Admin' },
    { value: 'manager', label: 'Manager' },
    { value: 'sales', label: 'Sales' },
];

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getRoleBadgeColor = (role: string) => {
    switch (role) {
        case 'admin':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
        case 'manager':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100';
        case 'sales':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100';
    }
};

const deleteUser = (userId: number) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('users.destroy', userId));
    }
};
</script>

<template>
    <Head title="Users" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-bold">Users</h1>
                <Link :href="route('users.create')">
                    <Button size="sm">
                        <Plus class="mr-2 h-4 w-4" />
                        Add New User
                    </Button>
                </Link>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div class="w-full sm:max-w-xs">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input type="search" placeholder="Search users..." class="pl-8" v-model="search" />
                    </div>
                </div>
                <div class="flex w-full items-center gap-3 sm:w-auto">
                    <select v-model="roleFilter" class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option v-for="option in roleOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Created</TableHead>
                            <TableHead class="w-[100px] text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in users.data" :key="user.id">
                            <TableCell>
                                <div class="font-medium">{{ user.name }}</div>
                            </TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="getRoleBadgeColor(user.role)"
                                >
                                    {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                </span>
                            </TableCell>
                            <TableCell>{{ formatDate(user.created_at) }}</TableCell>
                            <TableCell class="text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 p-0">
                                            <MoreHorizontal class="h-4 w-4" />
                                            <span class="sr-only">Open menu</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem asChild>
                                            <Link :href="route('users.edit', user.id)">Edit</Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="deleteUser(user.id)" class="text-red-600 hover:text-red-600 focus:text-red-600">
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="users.data.length === 0">
                            <TableCell colspan="5" class="py-6 text-center text-muted-foreground"> No users found </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="users.meta.last_page > 1" class="mt-4 flex items-center justify-between">
                <div class="text-sm text-muted-foreground">Showing {{ users.meta.from }} to {{ users.meta.to }} of {{ users.meta.total }} users</div>
                <div class="flex items-center space-x-2">
                    <Link
                        v-if="users.meta.current_page > 1"
                        :href="route('users.index', { page: users.meta.current_page - 1, search: search, role: roleFilter })"
                        class="rounded-md bg-muted px-3 py-1 hover:bg-muted-foreground/10"
                    >
                        Previous
                    </Link>
                    <div v-for="(link, i) in users.links.slice(1, -1)" :key="i">
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
                        v-if="users.meta.current_page < users.meta.last_page"
                        :href="route('users.index', { page: users.meta.current_page + 1, search: search, role: roleFilter })"
                        class="rounded-md bg-muted px-3 py-1 hover:bg-muted-foreground/10"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
