<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea/Index';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

// Define types for form data
interface LeadForm {
    name: string;
    company_name: string | null;
    email: string;
    phone: string;
    address: string;
    notes: string | null;
    status: string;
    assigned_to: number | null;
    [key: string]: any;
}

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
        assigned_to: number | null;
    };
    salesUsers: Array<{
        id: number;
        name: string;
    }>;
}>();

// Initialize form with proper type
const form = useForm<LeadForm>({
    name: props.lead.name,
    company_name: props.lead.company_name,
    email: props.lead.email,
    phone: props.lead.phone,
    address: props.lead.address,
    notes: props.lead.notes,
    status: props.lead.status,
    assigned_to: props.lead.assigned_to,
});

const notesValue = computed({
    get: () => form.notes as string | undefined,
    set: (value: string | undefined) => {
        form.notes = value === undefined ? null : value;
    },
});

const companyNameValue = computed({
    get: () => form.company_name as string | undefined,
    set: (value: string | undefined) => {
        form.company_name = value === undefined ? null : value;
    },
});

// Type-safe computed property for assigned_to
const assignedToValue = computed<string>({
    get: () => form.assigned_to?.toString() ?? '',
    set: (value: string) => {
        form.assigned_to = value ? Number(value) : null;
    },
});

const submit = () => {
    form.put(route('leads.update', props.lead.id));
};

const statusOptions = [
    { value: 'new', label: 'New' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'qualified', label: 'Qualified' },
    { value: 'proposal', label: 'Proposal' },
    { value: 'negotiation', label: 'Negotiation' },
    { value: 'lost', label: 'Lost' },
    { value: 'converted', label: 'Converted' },
];
</script>

<template>
    <Head title="Edit Lead" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Edit Lead</h1>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Lead Information</CardTitle>
                        <CardDescription>Update lead details and status</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name" required>Name</Label>
                                <Input id="name" v-model="form.name" required />
                                <InputError :message="form.errors.name" />
                            </div>

                            <!-- Company Name -->
                            <div class="space-y-2">
                                <Label for="company_name">Company Name</Label>
                                <Input id="company_name" v-model="companyNameValue" />
                                <InputError :message="form.errors.company_name" />
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email" required>Email</Label>
                                <Input id="email" type="email" v-model="form.email" required />
                                <InputError :message="form.errors.email" />
                            </div>

                            <!-- Phone -->
                            <div class="space-y-2">
                                <Label for="phone" required>Phone</Label>
                                <Input id="phone" v-model="form.phone" required />
                                <InputError :message="form.errors.phone" />
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <Label for="status" required>Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" />
                            </div>

                            <!-- Assigned To -->
                            <div class="space-y-2">
                                <Label for="assigned_to">Assign To</Label>
                                <Select v-model="assignedToValue">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a sales representative" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Unassigned</SelectItem>
                                        <SelectItem v-for="user in salesUsers" :key="user.id" :value="user.id.toString()">
                                            {{ user.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.assigned_to" />
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="space-y-2">
                            <Label for="address" required>Address</Label>
                            <Textarea id="address" v-model="form.address" required rows="3" />
                            <InputError :message="form.errors.address" />
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Textarea id="notes" v-model="notesValue" rows="4" />
                            <InputError :message="form.errors.notes" />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Button type="button" variant="outline" :disabled="form.processing" @click="router.visit(route('leads.index'))">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing"> Update Lead </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
