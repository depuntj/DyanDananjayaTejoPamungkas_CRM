<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea/Index';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    product: {
        id: number;
        name: string;
        description: string;
        price: number;
        speed: string;
        type: string;
        is_active: boolean;
    };
}>();

const form = useForm({
    name: props.product.name,
    description: props.product.description,
    price: props.product.price.toString(),
    speed: props.product.speed,
    type: props.product.type,
    is_active: props.product.is_active,
});

const submit = () => {
    form.put(route('products.update', props.product.id));
};

const typeOptions = [
    { value: 'Residential', label: 'Residential' },
    { value: 'Business', label: 'Business' },
    { value: 'Enterprise', label: 'Enterprise' },
];
</script>

<template>
    <Head title="Edit Product" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Edit Product</h1>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Product Information</CardTitle>
                        <CardDescription>Update product details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name" required>Product Name</Label>
                                <Input id="name" v-model="form.name" required />
                                <InputError :message="form.errors.name" />
                            </div>

                            <!-- Type -->
                            <div class="space-y-2">
                                <Label for="type" required>Product Type</Label>
                                <Select v-model="form.type" required>
                                    <SelectTrigger>
                                        <SelectValue :placeholder="'Select a product type'">
                                            {{
                                                form.type ? typeOptions.find((option) => option.value === form.type)?.label : 'Select a product type'
                                            }}
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="option in typeOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.type" />
                            </div>

                            <!-- Price -->
                            <div class="space-y-2">
                                <Label for="price" required>Price (IDR)</Label>
                                <Input id="price" type="number" min="0" v-model="form.price" required />
                                <InputError :message="form.errors.price" />
                            </div>

                            <!-- Speed -->
                            <div class="space-y-2">
                                <Label for="speed" required>Internet Speed</Label>
                                <Input id="speed" v-model="form.speed" required placeholder="e.g. 50 Mbps" />
                                <InputError :message="form.errors.speed" />
                            </div>

                            <!-- Is Active -->
                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_active" v-model:checked="form.is_active" />
                                <Label for="is_active">Active Product</Label>
                                <InputError :message="form.errors.is_active" />
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description" required>Description</Label>
                            <Textarea id="description" v-model="form.description" required rows="4" />
                            <InputError :message="form.errors.description" />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Button type="button" variant="outline" :disabled="form.processing" @click="router.visit(route('products.index'))">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing"> Update Product </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
