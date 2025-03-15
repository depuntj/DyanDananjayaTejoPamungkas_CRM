<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'sales',
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const roleOptions = [
    { value: 'admin', label: 'Admin' },
    { value: 'manager', label: 'Manager' },
    { value: 'sales', label: 'Sales' },
];
</script>

<template>
    <Head title="Create User" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Create User</h1>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>New User Information</CardTitle>
                        <CardDescription>Add a new user to the system</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name" required>Name</Label>
                                <Input id="name" v-model="form.name" required autocomplete="off" placeholder="Enter full name" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email" required>Email</Label>
                                <Input id="email" type="email" v-model="form.email" required autocomplete="off" placeholder="email@example.com" />
                                <InputError :message="form.errors.email" />
                            </div>

                            <!-- Password -->
                            <div class="space-y-2">
                                <Label for="password" required>Password</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Minimum 8 characters"
                                />
                                <InputError :message="form.errors.password" />
                            </div>

                            <!-- Password Confirmation -->
                            <div class="space-y-2">
                                <Label for="password_confirmation" required>Confirm Password</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    v-model="form.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Confirm password"
                                />
                                <InputError :message="form.errors.password_confirmation" />
                            </div>

                            <!-- Role Selection -->
                            <div class="space-y-2 md:col-span-2">
                                <Label for="role" required>Role</Label>
                                <div class="relative">
                                    <select
                                        id="role"
                                        v-model="form.role"
                                        required
                                        class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    >
                                        <option v-for="option in roleOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="h-4 w-4 opacity-50"
                                        >
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <InputError :message="form.errors.role" />
                                <div class="mt-2 text-xs text-muted-foreground">
                                    <ul class="ml-5 list-disc">
                                        <li>Admin: Full access to all features</li>
                                        <li>Manager: Can approve projects and manage sales team</li>
                                        <li>Sales: Can manage leads and create projects</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Button type="button" variant="outline" :disabled="form.processing" @click="router.visit(route('users.index'))">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing">Create User</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
