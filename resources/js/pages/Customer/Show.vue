<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Edit, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Service {
    id: number;
    name: string;
    description: string;
    price: number;
    speed: string;
    type: string;
    pivot: {
        price: number;
        start_date: string;
        end_date: string | null;
        status: string;
    };
}

interface Customer {
    id: number;
    name: string;
    company_name: string | null;
    email: string;
    phone: string;
    address: string;
    customer_id: string;
    is_active: boolean;
    created_at: string;
    lead: {
        id: number;
        name: string;
    } | null;
    project: {
        id: number;
        name: string;
    } | null;
    services: Service[];
}

const props = defineProps<{
    customer: Customer;
}>();

// Modal states
const addServiceDialogOpen = ref(false);
const editServiceDialogOpen = ref(false);
const selectedServiceId = ref<number | null>(null);

// Form data for adding/editing services
const serviceForm = ref({
    product_id: '',
    price: '',
    start_date: '',
    end_date: '',
    status: 'active',
});

// Reset form
const resetServiceForm = () => {
    serviceForm.value = {
        product_id: '',
        price: '',
        start_date: '',
        end_date: '',
        status: 'active',
    };
    selectedServiceId.value = null;
};

// Edit service
const editService = (service: Service) => {
    selectedServiceId.value = service.id;
    serviceForm.value = {
        product_id: service.id.toString(),
        price: service.pivot.price.toString(),
        start_date: service.pivot.start_date,
        end_date: service.pivot.end_date || '',
        status: service.pivot.status,
    };
    editServiceDialogOpen.value = true;
};

// Add service
const addService = () => {
    router.post(route('customers.services.add', props.customer.id), serviceForm.value, {
        onSuccess: () => {
            addServiceDialogOpen.value = false;
            resetServiceForm();
        },
    });
};

// Update service
const updateService = () => {
    if (selectedServiceId.value) {
        router.put(route('customers.services.update', [props.customer.id, selectedServiceId.value]), serviceForm.value, {
            onSuccess: () => {
                editServiceDialogOpen.value = false;
                resetServiceForm();
            },
        });
    }
};

// Remove service
const removeService = (serviceId: number) => {
    if (confirm('Are you sure you want to remove this service?')) {
        router.delete(route('customers.services.remove', [props.customer.id, serviceId]));
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        case 'suspended':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100';
        case 'terminated':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100';
    }
};

// Calculate total monthly revenue
const totalMonthlyRevenue = props.customer.services
    .filter((service) => service.pivot.status === 'active')
    .reduce((total, service) => total + service.pivot.price, 0);
</script>

<template>
    <Head :title="customer.name" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold">{{ customer.name }}</h1>
                    <p class="text-muted-foreground">Customer ID: {{ customer.customer_id }}</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('customers.edit', customer.id)">
                        <Button variant="outline" size="sm" class="flex items-center">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </Button>
                    </Link>
                    <Dialog v-model:open="addServiceDialogOpen">
                        <DialogTrigger asChild>
                            <Button size="sm" class="flex items-center">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Service
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Add New Service</DialogTitle>
                                <DialogDescription>Add a new service for this customer.</DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="addService" class="space-y-4">
                                <div class="grid gap-2">
                                    <label for="product_id" class="text-sm font-medium">Product</label>
                                    <select id="product_id" v-model="serviceForm.product_id" required class="rounded-md border px-3 py-2">
                                        <option value="">Select a product</option>
                                        <!-- This would need to be populated with actual product data -->
                                        <option value="1">Residential Basic</option>
                                        <option value="2">Residential Plus</option>
                                        <option value="3">Residential Premium</option>
                                    </select>
                                </div>
                                <div class="grid gap-2">
                                    <label for="price" class="text-sm font-medium">Price (IDR)</label>
                                    <input
                                        id="price"
                                        type="number"
                                        v-model="serviceForm.price"
                                        required
                                        min="0"
                                        class="rounded-md border px-3 py-2"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <label for="start_date" class="text-sm font-medium">Start Date</label>
                                    <input
                                        id="start_date"
                                        type="date"
                                        v-model="serviceForm.start_date"
                                        required
                                        class="rounded-md border px-3 py-2"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <label for="end_date" class="text-sm font-medium">End Date (Optional)</label>
                                    <input id="end_date" type="date" v-model="serviceForm.end_date" class="rounded-md border px-3 py-2" />
                                </div>
                                <div class="grid gap-2">
                                    <label for="status" class="text-sm font-medium">Status</label>
                                    <select id="status" v-model="serviceForm.status" required class="rounded-md border px-3 py-2">
                                        <option value="active">Active</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                </div>
                                <DialogFooter class="mt-4">
                                    <Button type="button" variant="outline" @click="addServiceDialogOpen = false">Cancel</Button>
                                    <Button type="submit">Add Service</Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <div
                class="mb-6 inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                :class="
                    customer.is_active
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100'
                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100'
                "
            >
                {{ customer.is_active ? 'Active' : 'Inactive' }}
            </div>

            <!-- Customer Information -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Customer Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Name</h3>
                                <p>{{ customer.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Company</h3>
                                <p>{{ customer.company_name || 'N/A' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Email</h3>
                                <p>
                                    <a :href="`mailto:${customer.email}`" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ customer.email }}
                                    </a>
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Phone</h3>
                                <p>
                                    <a :href="`tel:${customer.phone}`" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ customer.phone }}
                                    </a>
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <h3 class="text-sm font-medium text-muted-foreground">Address</h3>
                                <p>{{ customer.address }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Customer Since</h3>
                                <p>{{ formatDate(customer.created_at) }}</p>
                            </div>
                        </div>

                        <Separator class="my-4" />

                        <div v-if="customer.lead || customer.project" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div v-if="customer.lead">
                                <h3 class="text-sm font-medium text-muted-foreground">Original Lead</h3>
                                <p>
                                    <Link :href="route('leads.show', customer.lead.id)" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ customer.lead.name }}
                                    </Link>
                                </p>
                            </div>
                            <div v-if="customer.project">
                                <h3 class="text-sm font-medium text-muted-foreground">From Project</h3>
                                <p>
                                    <Link
                                        :href="route('projects.show', customer.project.id)"
                                        class="text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        {{ customer.project.name }}
                                    </Link>
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Subscription Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle>Subscription Summary</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Active Services</span>
                                <span class="text-lg font-semibold">
                                    {{ customer.services.filter((s) => s.pivot.status === 'active').length }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Monthly Revenue</span>
                                <span class="text-lg font-semibold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(totalMonthlyRevenue) }}
                                </span>
                            </div>
                            <div v-if="customer.services.length > 0" class="space-y-1 pt-2">
                                <span class="text-xs font-medium text-muted-foreground">Next renewals:</span>
                                <div
                                    v-for="service in customer.services.filter((s) => s.pivot.status === 'active')"
                                    :key="service.id"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <Calendar class="h-3 w-3 text-muted-foreground" />
                                    <span>{{ service.name }}: {{ formatDate(service.pivot.end_date || 'N/A') }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Services -->
            <Card class="mt-6">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Active Services</CardTitle>
                        <Dialog v-model:open="addServiceDialogOpen">
                            <DialogTrigger asChild>
                                <Button size="sm">Add Service</Button>
                            </DialogTrigger>
                        </Dialog>
                    </div>
                    <CardDescription>Internet services subscribed by this customer</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Service</TableHead>
                                <TableHead>Speed</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Start Date</TableHead>
                                <TableHead>End Date</TableHead>
                                <TableHead class="text-right">Monthly Fee</TableHead>
                                <TableHead class="w-[120px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="service in customer.services" :key="service.id">
                                <TableCell>
                                    <div class="font-medium">{{ service.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ service.type }}</div>
                                </TableCell>
                                <TableCell>{{ service.speed }}</TableCell>
                                <TableCell>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="getStatusColor(service.pivot.status)"
                                    >
                                        {{ service.pivot.status.charAt(0).toUpperCase() + service.pivot.status.slice(1) }}
                                    </span>
                                </TableCell>
                                <TableCell>{{ formatDate(service.pivot.start_date) }}</TableCell>
                                <TableCell>{{ formatDate(service.pivot.end_date) }}</TableCell>
                                <TableCell class="text-right font-medium">{{ formatCurrency(service.pivot.price) }}</TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="editService(service)">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-red-600" @click="removeService(service.id)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="customer.services.length === 0">
                                <TableCell colspan="7" class="py-6 text-center text-muted-foreground">
                                    No services found for this customer
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Edit Service Dialog -->
            <Dialog v-model:open="editServiceDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Service</DialogTitle>
                        <DialogDescription>Update service details for this customer.</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="updateService" class="space-y-4">
                        <div class="grid gap-2">
                            <label for="edit_price" class="text-sm font-medium">Price (IDR)</label>
                            <input id="edit_price" type="number" v-model="serviceForm.price" required min="0" class="rounded-md border px-3 py-2" />
                        </div>
                        <div class="grid gap-2">
                            <label for="edit_start_date" class="text-sm font-medium">Start Date</label>
                            <input id="edit_start_date" type="date" v-model="serviceForm.start_date" required class="rounded-md border px-3 py-2" />
                        </div>
                        <div class="grid gap-2">
                            <label for="edit_end_date" class="text-sm font-medium">End Date (Optional)</label>
                            <input id="edit_end_date" type="date" v-model="serviceForm.end_date" class="rounded-md border px-3 py-2" />
                        </div>
                        <div class="grid gap-2">
                            <label for="edit_status" class="text-sm font-medium">Status</label>
                            <select id="edit_status" v-model="serviceForm.status" required class="rounded-md border px-3 py-2">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                        <DialogFooter class="mt-4">
                            <Button type="button" variant="outline" @click="editServiceDialogOpen = false">Cancel</Button>
                            <Button type="submit">Update Service</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
