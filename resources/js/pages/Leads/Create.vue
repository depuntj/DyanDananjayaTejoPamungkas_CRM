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

defineProps<{
    salesUsers: Array<{
        id: number;
        name: string;
    }>;
}>();

const form = useForm({
    name: '',
    company_name: '',
    email: '',
    phone: '',
    address: '',
    notes: '',
    assigned_to: undefined as string | undefined,
});

const submit = () => {
    form.post(route('leads.store'));
};
</script>

<template>
    <Head title="Create Lead" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Create Lead</h1>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>New Lead Information</CardTitle>
                        <CardDescription>Add a new potential customer to your leads</CardDescription>
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
                                <Input id="company_name" v-model="form.company_name" />
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

                            <!-- Assigned To -->
                            <div class="space-y-2">
                                <Label for="assigned_to">Assign To</Label>
                                <Select v-model="form.assigned_to">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a sales representative" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Unassigned</SelectItem>
                                        <SelectItem v-for="user in salesUsers" :key="user.id" :value="String(user.id)">
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
                            <Textarea id="notes" v-model="form.notes" rows="4" />
                            <InputError :message="form.errors.notes" />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Button type="button" variant="outline" :disabled="form.processing" @click="router.visit(route('leads.index'))">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing"> Create Lead </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
