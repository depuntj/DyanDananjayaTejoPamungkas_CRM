<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea/Index';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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
    services: Service[];
}

interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    speed: string;
    type: string;
}

const props = defineProps<{
    customer: Customer;
    products: Product[];
}>();

const form = useForm({
    name: props.customer.name,
    company_name: props.customer.company_name || '',
    email: props.customer.email,
    phone: props.customer.phone,
    address: props.customer.address,
    is_active: props.customer.is_active,
});

const submit = () => {
    form.put(route('customers.update', props.customer.id));
};
</script>

<template>
    <Head title="Edit Customer" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Edit Customer</h1>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Customer Information</CardTitle>
                        <CardDescription>Update customer details</CardDescription>
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

                            <!-- Customer Status -->
                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_active" v-model:checked="form.is_active" />
                                <Label for="is_active">Active Customer</Label>
                                <InputError :message="form.errors.is_active" />
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="space-y-2">
                            <Label for="address" required>Address</Label>
                            <Textarea id="address" v-model="form.address" required rows="3" />
                            <InputError :message="form.errors.address" />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Button type="button" variant="outline" :disabled="form.processing" @click="$router.go(-1)"> Cancel </Button>
                        <Button type="submit" :disabled="form.processing"> Update Customer </Button>
                    </CardFooter>
                </Card>
            </form>

            <!-- Customer ID Section -->
            <div class="mt-6">
                <h2 class="text-lg font-medium">Customer ID</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Customer ID <strong>{{ customer.customer_id }}</strong> is automatically generated and cannot be modified.
                </p>
            </div>

            <!-- Services Section -->
            <div class="mt-6">
                <h2 class="text-lg font-medium">Services</h2>
                <p class="mt-2 text-sm text-muted-foreground">To manage this customer's services, please go back to the customer details page.</p>
                <div class="mt-4">
                    <Link :href="route('customers.show', customer.id)">
                        <Button type="button" variant="outline">Manage Services</Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
