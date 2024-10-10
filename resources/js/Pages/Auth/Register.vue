<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import { ref } from 'vue';
import { v4 as uuidv4 } from 'uuid';

// Initialize form data
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    municipality: '',
    qr_code_data: '', // Field to send QR code data to the server
    qr_code_id: '',   // Field to send QR code ID to the server
});

const qrCodeData = ref('');
const qrCodeId = ref(''); // State to store the generated QR code ID

const municipalities = [
    { id: 1, name: 'San Pablo City' },
    { id: 2, name: 'Santa Cruz' },
    { id: 3, name: 'Nagcarlan' },
];

const generateQRCode = async (municipality) => {
    try {
        const uniqueId = uuidv4(); // Generate a unique ID for the QR code
        const qrCodeText = `Municipality: ${municipality}, ID: ${uniqueId}`;
        const dataUrl = await QRCode.toDataURL(qrCodeText); // Generate QR code as Data URL

        qrCodeData.value = dataUrl;
        qrCodeId.value = uniqueId;
        
        // Attach QR code data to the form to be sent to the server
        form.qr_code_data = dataUrl;
        form.qr_code_id = uniqueId;

        console.log('QR Code generated:', dataUrl);
        console.log('QR Code ID:', uniqueId);
    } catch (error) {
        console.error('Error generating QR code:', error);
    }
};

const submit = async () => {
    await generateQRCode(form.municipality); // Generate the QR code before submitting the form

    console.log('Form data before submission:', form.data()); // Debugging output to ensure QR code data is included

    form.post(route('register'), {
        onSuccess: () => {
            console.log('Form submission successful');
            form.reset('password', 'password_confirmation');
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        },
    });
};

</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4">
                <InputLabel for="municipality" value="Municipality/City" />
                <select
                    id="municipality"
                    class="mt-1 block w-full"
                    v-model="form.municipality"
                    required
                >
                    <option value="" disabled>Select Municipality/City</option>
                    <option v-for="municipality in municipalities" :key="municipality.id" :value="municipality.name">
                        {{ municipality.name }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.municipality" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Already registered?
                </Link>
                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
