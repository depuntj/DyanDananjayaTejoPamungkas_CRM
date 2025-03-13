<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle, Edit, ExternalLink, Trash2, UserCheck, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    project: {
        id: number;
        name: string;
        status: string;
        notes: string | null;
        created_at: string;
        approved_at: string | null;
        lead: {
            id: number;
            name: string;
            company_name: string | null;
            email: string;
            phone: string;
        };
        assignedUser: {
            id: number;
            name: string;
        } | null;
        approvedBy: {
            id: number;
            name: string;
        } | null;
        products: Array<{
            id: number;
            name: string;
            description: string;
            price: number;
            pivot: {
                quantity: number;
                price: number;
            };
        }>;
    };
}>();

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const deleteConfirmOpen = ref(false);

const deleteProject = () => {
    router.delete(route('projects.destroy', props.project.id), {
        onSuccess: () => {
            deleteConfirmOpen.value = false;
        },
    });
};

const approveProject = () => {
    router.post(route('projects.approve', props.project.id));
};

const rejectProject = () => {
    if (confirm('Are you sure you want to reject this project?')) {
        router.post(route('projects.reject', props.project.id));
    }
};

const convertToCustomer = () => {
    router.post(route('projects.convert', props.project.id));
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
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

// Calculate total project value
const totalProjectValue = props.project.products.reduce((total, product) => {
    return total + product.pivot.price * product.pivot.quantity;
}, 0);
</script>

<template>
    <Head :title="project.name" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold">{{ project.name }}</h1>
                    <p class="text-muted-foreground">Project details and information</p>
                </div>
                <div class="flex gap-2">
                    <Link v-if="project.status !== 'approved' && project.status !== 'completed'" :href="route('projects.edit', project.id)">
                        <Button variant="outline" size="sm" class="flex items-center">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </Button>
                    </Link>
                    <Dialog v-model:open="deleteConfirmOpen" v-if="project.status !== 'approved' && project.status !== 'completed'">
                        <DialogTrigger asChild>
                            <Button variant="destructive" size="sm" class="flex items-center">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Are you sure?</DialogTitle>
                                <DialogDescription>
                                    This action cannot be undone. This will permanently delete the project "{{ project.name }}" and remove all associated
                                    data.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter class="mt-4">
                                <Button variant="outline" @click="deleteConfirmOpen = false">Cancel</Button>
                                <Button variant="destructive" @click="deleteProject">Delete</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                    <Button
                        v-if="project.status === 'pending'"
                        variant="outline"
                        size="sm"
                        class="flex items-center text-green-600"
                        @click="approveProject"
                    >
                        <CheckCircle class="mr-2 h-4 w-4" />
                        Approve
                    </Button>
                    <Button
                        v-if="project.status === 'pending'"
                        variant="outline"
                        size="sm"
                        class="flex items-center text-red-600"
                        @click="rejectProject"
                    >
                        <XCircle class="mr-2 h-4 w-4" />
                        Reject
                    </Button>
                    <Button
                        v-if="project.status === 'approved'"
                        size="sm"
                        class="flex items-center"
                        @click="convertToCustomer"
                    >
                        <UserCheck class="mr-2 h-4 w-4" />
                        Convert to Customer
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Project Info Card -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Project Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Status</h3>
                                <span
                                    class="mt-1 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="getStatusColor(project.status)"
                                >
                                    {{ project.status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase()) }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Lead</h3>
                                <p>
                                    <Link :href="route('leads.show', project.lead.id)" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ project.lead.name }} {{ project.lead.company_name ? `(${project.lead.company_name})` : '' }}
                                    </Link>
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Assigned To</h3>
                                <p>{{ project.assignedUser ? project.assignedUser.name : 'Unassigned' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Created On</h3>
                                <p>{{ formatDate(project.created_at) }}</p>
                            </div>
                            <div v-if="project.status === 'approved' || project.status === 'completed'">
                                <h3 class="text-sm font-medium text-muted-foreground">Approved By</h3>
                                <p>{{ project.approvedBy ? project.approvedBy.name : 'N/A' }}</p>
                            </div>
                            <div v-if="project.status === 'approved' || project.status === 'completed'">
                                <h3 class="text-sm font-medium text-muted-foreground">Approved On</h3>
                                <p>{{ formatDate(project.approved_at) }}</p>
                            </div>
                        </div>

                        <Separator class="my-4" />

                        <div v-if="project.notes">
                            <h3 class="mb-2 text-sm font-medium text-muted-foreground">Notes</h3>
                            <p class="whitespace-pre-line">{{ project.notes }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Lead Info Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Lead Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Name</h3>
                                <p>{{ project.lead.name }}</p>
                            </div>
                            <div v-if="project.lead.company_name">
                                <h3 class="text-sm font-medium text-muted-foreground">Company</h3>
                                <p>{{ project.lead.company_name }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Email</h3>
                                <p>
                                    <a :href="`mailto:${project.lead.email}`" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ project.lead.email }}
                                    </a>
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Phone</h3>
                                <p>
                                    <a :href="`tel:${project.lead.phone}`" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ project.lead.phone }}
                                    </a>
                                </p>
                            </div>
                            <div class="pt-2">
                                <Link :href="route('leads.show', project.lead.id)" class="inline-flex items-center text-sm text-blue-600 hover:underline dark:text-blue-400">
                                    <ExternalLink class="mr-1 h-3 w-3" />
                                    View Lead Details
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Products Card -->
            <Card class="mt-6">
                <CardHeader>
                    <CardTitle>Project Products</CardTitle>
                    <CardDescription>Products included in this project</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Product</TableHead>
                                <TableHead>Speed</TableHead>
                                <TableHead class="text-right">Unit Price</TableHead>
                                <TableHead class="text-right">Quantity</TableHead>
                                <TableHead class="text-right">Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="product in project.products" :key="product.id">
                                <TableCell>
                                    <div class="font-medium">{{ product.name }}</div>
                                    <div class="text-xs text-muted-foreground truncate max-w-xs">{{ product.description }}</div>
                                </TableCell>
                                <TableCell>{{ product.speed }}</TableCell>
                                <TableCell class="text-right">{{ formatCurrency(product.pivot.price) }}</TableCell>
                                <TableCell class="text-right">{{ product.pivot.quantity }}</TableCell>
                                <TableCell class="text-right">{{ formatCurrency(product.pivot.price * product.pivot.quantity) }}</TableCell>
                            </TableRow>
                            <TableRow>
                                <TableCell colspan="4" class="text-right font-medium">Total</TableCell>
                                <TableCell class="text-right font-bold">{{ formatCurrency(totalProjectValue) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
