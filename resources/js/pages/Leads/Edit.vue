<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea/Index';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Define type for form data
interface LeadForm {
    name: string;
    company_name: string | null;
    email: string;
    phone: string;
    address: string;
    notes: string | null;
    status: string;
    assigned_to: number | null;
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

const assignedToStringValue = ref(props.lead.assigned_to !== null ? props.lead.assigned_to.toString() : '');

// Initialize form with lead data
const form = ref<LeadForm>({
    name: props.lead.name,
    company_name: props.lead.company_name,
    email: props.lead.email,
    phone: props.lead.phone,
    address: props.lead.address,
    notes: props.lead.notes,
    status: props.lead.status,
    assigned_to: props.lead.assigned_to,
});

// Status options with proper capitalization
const statusOptions = [
    { value: 'new', label: 'New' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'qualified', label: 'Qualified' },
    { value: 'proposal', label: 'Proposal' },
    { value: 'negotiation', label: 'Negotiation' },
    { value: 'lost', label: 'Lost' },
    { value: 'converted', label: 'Converted' },
];

// Computed property to get selected status label
const selectedStatusLabel = computed(() => statusOptions.find((option) => option.value === form.value.status)?.label || 'Select Status');

// Watch for changes in assignedToStringValue and update the form
const handleAssignedToChange = (value) => {
    // Convert empty string to null
    if (value === '') {
        form.value.assigned_to = null;
    } else {
        // Convert string to number
        form.value.assigned_to = parseInt(value, 10);
    }
    console.log('Assigned to changed:', value, 'Form value:', form.value.assigned_to);
};

const submit = () => {
    // Log the form values before submission
    console.log('Submitting form with:', JSON.stringify(form.value, null, 2));

    router.put(route('leads.update', props.lead.id), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: Add success handling
            console.log('Lead updated successfully');
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        },
    });
};
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
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" required placeholder="Enter lead name" />
                            </div>

                            <!-- Company Name -->
                            <div class="space-y-2">
                                <Label for="company_name">Company Name</Label>
                                <Input id="company_name" v-model="form.company_name" placeholder="Enter company name" />
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" v-model="form.email" required placeholder="Enter email address" />
                            </div>

                            <!-- Phone -->
                            <div class="space-y-2">
                                <Label for="phone">Phone</Label>
                                <Input id="phone" v-model="form.phone" required placeholder="Enter phone number" />
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue>
                                            {{ selectedStatusLabel }}
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Assign To -->
                            <div class="space-y-2">
                                <Label for="assigned_to">Assign To</Label>
                                <select
                                    class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    :value="form.assigned_to === null ? '' : form.assigned_to.toString()"
                                    @change="(e) => handleAssignedToChange(e.target.value)"
                                >
                                    <option value="">Unassigned</option>
                                    <option v-for="user in salesUsers" :key="user.id" :value="user.id.toString()">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Textarea id="address" v-model="form.address" required placeholder="Enter address" rows="3" />
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Textarea id="notes" v-model="form.notes" placeholder="Additional notes" rows="4" />
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button type="submit">Update Lead</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
