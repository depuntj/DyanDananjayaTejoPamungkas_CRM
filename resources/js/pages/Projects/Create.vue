<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    leads: Array<{
        id: number;
        name: string;
        company_name: string | null;
    }>;
    salesUsers: Array<{
        id: number;
        name: string;
    }>
    products: Array<{
        id: number;
        name: string;
        price: number;
        speed: string;
        productType: string;
    }>
    lead_id?: number;
}>();A

const form = useForm({
    name: '',
    lead_id: props.lead_id || '',
    assigned_to: '',
    notes: '',
    products: [{ id: '', quantity: 1, price: '' }],
});

// Format currency
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

// Handle adding new product
const addProduct = () => {
    form.products.push({ id: '', quantity: 1, price: '' });
};

// Handle removing a product
const removeProduct = (index: number) => {
    form.products.splice(index, 1);
};

// Set product price based on selected product
const setProductPrice = (index: number, productId: string) => {
    const product = props.products.find((p) => p.id.toString() === productId);
    if (product) {
        form.products[index].price = product.price.toString();
    }
};

// Calculate total
const total = computed(() => {
    return form.products.reduce((sum, item) => {
        const price = parseFloat(item.price) || 0;
        const quantity = parseInt(item.quantity.toString()) || 0;
        return sum + price * quantity;
    }, 0);
});

const submit = () => {
    form.post(route('projects.store'));
};
</script>

<template>
    <Head title="Create Project" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Create Project</h1>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main Project Info -->
                    <Card class="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Project Information</CardTitle>
                            <CardDescription>Create a new project for a lead</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Project Name -->
                            <div class="space-y-2">
                                <Label for="name" required>Project Name</Label>
                                <Input id="name" v-model="form.name" required />
                                <InputError :message="form.errors.name" />
                            </div>

                            <!-- Lead Selection -->
                            <div class="space-y-2">
                                <Label for="lead_id" required>Lead</Label>
                                <Select v-model="form.lead_id" required>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a lead" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="lead in leads" :key="lead.id" :value="lead.id.toString()">
                                            {{ lead.name }} {{ lead.company_name ? `(${lead.company_name})` : '' }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.lead_id" />
                            </div>

                            <!-- Assigned To -->
                            <div class="space-y-2">
                                <Label for="assigned_to">Assign To</Label>
                                <Select v-model="form.assigned_to">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a sales representative" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="">Unassigned</SelectItem>
                                        <SelectItem v-for="user in salesUsers" :key="user.id" :value="user.id.toString()">
                                            {{ user.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.assigned_to" />
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2">
                                <Label for="notes">Notes</Label>
                                <Textarea id="notes" v-model="form.notes" rows="4" />
                                <InputError :message="form.errors.notes" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Project Summary Card -->
                    <Card class="lg:row-start-1 lg:col-start-3">
                        <CardHeader>
                            <CardTitle>Project Summary</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="font-medium">Total Products</dt>
                                        <dd>{{ form.products.length }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="font-medium">Total Amount</dt>
                                        <dd class="font-semibold">{{ formatCurrency(total) }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </CardContent>
                        <div class="px-6 pb-4">
                            <p class="text-sm text-muted-foreground">
                                This project will be created with a pending status and will need to be approved by a manager before it can be
                                converted to a customer.
                            </p>
                        </div>
                    </Card>

                    <!-- Products Card -->
                    <Card class="lg:col-span-3">
                        <CardHeader>
                            <CardTitle>Project Products</CardTitle>
                            <CardDescription>Add products that will be included in this project</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-6">
                                <!-- Products List -->
                                <div v-for="(product, index) in form.products" :key="index" class="space-y-6 rounded-lg border p-4">
                                    <!-- Product Header -->
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-medium">Product {{ index + 1 }}</h3>
                                        <Button v-if="index > 0" type="button" variant="ghost" size="sm" @click="removeProduct(index)">
                                            Remove
                                        </Button>
                                    </div>

                                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                        <!-- Product Selection -->
                                        <div class="space-y-2">
                                            <Label :for="`product-${index}`" required>Product</Label>
                                            <Select
                                                v-model="form.products[index].id"
                                                required
                                                @update:modelValue="setProductPrice(index, $event)"
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select a product" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="product in products" :key="product.id" :value="product.id.toString()">
                                                        {{ product.name }} ({{ product.speed }})
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <InputError :message="form.errors[`products.${index}.id`]" />
                                        </div>

                                        <!-- Price -->
                                        <div class="space-y-2">
                                            <Label :for="`price-${index}`" required>Price (IDR)</Label>
                                            <Input :id="`price-${index}`" type="number" v-model="form.products[index].price" required min="0" />
                                            <InputError :message="form.errors[`products.${index}.price`]" />
                                        </div>

                                        <!-- Quantity -->
                                        <div class="space-y-2">
                                            <Label :for="`quantity-${index}`" required>Quantity</Label>
                                            <Input :id="`quantity-${index}`" type="number" v-model="form.products[index].quantity" required min="1" />
                                            <InputError :message="form.errors[`products.${index}.quantity`]" />
                                        </div>
                                    </div>

                                    <!-- Subtotal -->
                                    <div class="flex justify-end font-medium">
                                        <span class="text-sm text-muted-foreground">
                                            Subtotal:
                                            {{
                                                formatCurrency(
                                                    (parseFloat(form.products[index].price) || 0) *
                                                        (parseInt(form.products[index].quantity.toString()) || 0),
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Add Product Button -->
                                <Button type="button" variant="outline" @click="addProduct">Add Another Product</Button>
                            </div>
                        </CardContent>
                        <CardFooter class="flex justify-between">
                            <Button type="button" variant="outline" :disabled="form.processing" @click="$router.go(-1)"> Cancel </Button>
                            <Button type="submit" :disabled="form.processing"> Create Project </Button>
                        </CardFooter>
                    </Card>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
