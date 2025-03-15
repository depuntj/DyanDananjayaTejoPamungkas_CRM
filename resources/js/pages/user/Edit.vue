<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

const props = defineProps<{
    user: User;
}>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

const showPasswordModal = ref(false);

const submit = () => {
    form.put(route('users.update', props.user.id));
};

const updatePassword = () => {
    passwordForm.put(route('users.password.update', props.user.id), {
        onSuccess: () => {
            showPasswordModal.value = false;
            passwordForm.reset();
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
    <Head title="Edit User" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Edit User</h1>
                <Dialog v-model:open="showPasswordModal">
                    <DialogTrigger asChild>
                        <Button variant="outline">Change Password</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Change Password</DialogTitle>
                            <DialogDescription>Set a new password for {{ user.name }}</DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="updatePassword" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="password">New Password</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    v-model="passwordForm.password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Minimum 8 characters"
                                />
                                <InputError :message="passwordForm.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    v-model="passwordForm.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Confirm new password"
                                />
                                <InputError :message="passwordForm.errors.password_confirmation" />
                            </div>
                            <DialogFooter>
                                <Button type="button" variant="outline" @click="showPasswordModal = false">Cancel</Button>
                                <Button type="submit" :disabled="passwordForm.processing">Update Password</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit User Information</CardTitle>
                        <CardDescription>Update user details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name" required>Name</Label>
                                <Input id="name" v-model="form.name" required autocomplete="off" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email" required>Email</Label>
                                <Input id="email" type="email" v-model="form.email" required autocomplete="off" />
                                <InputError :message="form.errors.email" />
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
                        <Button type="button" variant="outline" :disabled="form.processing" @click="$router.back()"> Cancel </Button>
                        <Button type="submit" :disabled="form.processing">Update User</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
