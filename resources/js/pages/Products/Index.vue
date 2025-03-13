<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreHorizontal, Plus, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const { products } = defineProps<{
    products: {
        data: Array<{
            id: number;
            name: string;
            description: string;
            price: number;
            speed: string;
            type: string;
            is_active: boolean;
            created_at: string;
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
}>();

const search = ref('');
const filterType = ref('');

// Trigger filtering with a delay after typing
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// Apply filters immediately when type filter changes
watch(filterType, () => {
    applyFilters();
});

const applyFilters = () => {
    const params: Record<string, string> = {};

    if (search.value) {
        params.search = search.value;
    }

    if (filterType.value) {
        params.type = filterType.value;
    }

    window.location.href = route('products.index', params);
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const deleteProduct = (productId: number) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('products.destroy', productId));
    }
};

const typeOptions = [
    { value: '', label: 'All Types' },
    { value: 'Residential', label: 'Residential' },
    { value: 'Business', label: 'Business' },
    { value: 'Enterprise', label: 'Enterprise' },
];
</script>

<template>
    <Head title="Products" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-bold">Internet Service Products</h1>
                <Link :href="route('products.create')">
                    <Button size="sm">
                        <Plus class="mr-2 h-4 w-4" />
                        Add New Product
                    </Button>
                </Link>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div class="w-full sm:max-w-xs">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input type="search" placeholder="Search products..." class="pl-8" v-model="search" />
                    </div>
                </div>
                <div class="flex w-full items-center gap-3 sm:w-auto">
                    <select v-model="filterType" class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Products Table -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Speed</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead>Price</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="w-[80px]">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="product in products.data" :key="product.id">
                            <TableCell>
                                <div class="font-medium">{{ product.name }}</div>
                                <div class="max-w-xs truncate text-xs text-muted-foreground">{{ product.description }}</div>
                            </TableCell>
                            <TableCell>{{ product.speed }}</TableCell>
                            <TableCell>{{ product.type }}</TableCell>
                            <TableCell>{{ formatCurrency(product.price) }}</TableCell>
                            <TableCell>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="
                                        product.is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100'
                                    "
                                >
                                    {{ product.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </TableCell>
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
                                            <Link :href="route('products.edit', product.id)">Edit</Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="deleteProduct(product.id)" class="text-red-600 focus:text-red-600">
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="products.data.length === 0">
                            <TableCell colspan="6" class="py-6 text-center text-muted-foreground"> No products found </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="products.meta.last_page > 1" class="mt-4 flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Showing {{ products.meta.from }} to {{ products.meta.to }} of {{ products.meta.total }} products
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        v-if="products.meta.current_page > 1"
                        :href="route('products.index', { page: products.meta.current_page - 1 })"
                        class="rounded-md bg-muted px-3 py-1 hover:bg-muted-foreground/10"
                    >
                        Previous
                    </Link>
                    <div v-for="(link, i) in products.links.slice(1, -1)" :key="i">
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
                        v-if="products.meta.current_page < products.meta.last_page"
                        :href="route('products.index', { page: products.meta.current_page + 1 })"
                        class="rounded-md bg-muted px-3 py-1 hover:bg-muted-foreground/10"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
