<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, FileSpreadsheet, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    lead: {
        id: number;
        name: string;
        company_name: string | null;
        email: string;
        phone: string;
        address: string;
        notes: string | null;
        status: string;
        created_at: string;
        updated_at: string;
        assignedUser: {
            id: number;
            name: string;
        } | null;
        projects: Array<{
            id: number;
            name: string;
            status: string;
            created_at: string;
        }>;
    };
}>();

const deleteConfirmOpen = ref(false);

const deleteLead = () => {
    router.delete(route('leads.destroy', props.lead.id), {
        onSuccess: () => {
            deleteConfirmOpen.value = false;
        },
    });
};

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
    <Head :title="lead.name" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold">Lead Details</h1>
                    <p class="text-muted-foreground">View and manage lead information</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('leads.edit', lead.id)">
                        <Button variant="outline" size="sm" class="flex items-center">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </Button>
                    </Link>
                    <Dialog v-model:open="deleteConfirmOpen">
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
                                    This action cannot be undone. This will permanently delete the lead "{{ lead.name }}" and remove all associated
                                    data.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter class="mt-4">
                                <Button variant="outline" @click="deleteConfirmOpen = false">Cancel</Button>
                                <Button variant="destructive" @click="deleteLead">Delete</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                    <Link :href="route('projects.create', { lead_id: lead.id })">
                        <Button size="sm" class="flex items-center">
                            <FileSpreadsheet class="mr-2 h-4 w-4" />
                            Create Project
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Lead Info Card -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Lead Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Name</h3>
                                <p>{{ lead.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Company</h3>
                                <p>{{ lead.company_name || 'N/A' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Email</h3>
                                <p>
                                    <a :href="`mailto:${lead.email}`" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ lead.email }}
                                    </a>
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Phone</h3>
                                <p>
                                    <a :href="`tel:${lead.phone}`" class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ lead.phone }}
                                    </a>
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <h3 class="text-sm font-medium text-muted-foreground">Address</h3>
                                <p>{{ lead.address }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Created On</h3>
                                <p>{{ formatDate(lead.created_at) }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Last Updated</h3>
                                <p>{{ formatDate(lead.updated_at) }}</p>
                            </div>
                        </div>

                        <Separator class="my-4" />

                        <div v-if="lead.notes">
                            <h3 class="mb-2 text-sm font-medium text-muted-foreground">Notes</h3>
                            <p class="whitespace-pre-line">{{ lead.notes }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Status Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Lead Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Current Status</h3>
                                <div class="mt-1.5">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="getStatusColor(lead.status)"
                                    >
                                        {{ lead.status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase()) }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Assigned To</h3>
                                <p>{{ lead.assignedUser ? lead.assignedUser.name : 'Unassigned' }}</p>
                            </div>

                            <div v-if="lead.status !== 'converted'">
                                <Link :href="route('leads.edit', lead.id)">
                                    <Button class="w-full">Update Status</Button>
                                </Link>
                            </div>
                            <div v-else>
                                <div class="rounded-md bg-green-100 p-3 text-sm dark:bg-green-900">
                                    <p class="font-medium text-green-800 dark:text-green-100">This lead has been converted to a customer</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Projects -->
            <Card class="mt-6">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Projects</CardTitle>
                        <Link :href="route('projects.create', { lead_id: lead.id })">
                            <Button size="sm">New Project</Button>
                        </Link>
                    </div>
                    <CardDescription>Projects created for this lead</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Created Date</TableHead>
                                <TableHead class="w-[100px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="project in lead.projects" :key="project.id">
                                <TableCell>{{ project.name }}</TableCell>
                                <TableCell>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="getStatusColor(project.status)"
                                    >
                                        {{ project.status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase()) }}
                                    </span>
                                </TableCell>
                                <TableCell>{{ formatDate(project.created_at) }}</TableCell>
                                <TableCell>
                                    <Link :href="route('projects.show', project.id)">
                                        <Button variant="outline" size="sm">View</Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="lead.projects.length === 0">
                                <TableCell colspan="4" class="py-6 text-center text-muted-foreground">
                                    No projects created for this lead yet
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
