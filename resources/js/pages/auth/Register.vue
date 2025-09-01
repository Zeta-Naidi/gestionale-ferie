<script>
import { store } from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

export default {
    components: {
        InputError,
        TextLink,
        Button,
        Input,
        Label,
        AuthBase,
        Form,
        Head,
        LoaderCircle
    },
    props: {
        organizationalUnits: {
            type: Array,
            default: () => []
        }
    },
    methods: {
        login() {
            return login();
        },
        store() {
            return store;
        }
    }
};
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <Form
            v-bind="store().form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Nome Completo</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name" placeholder="Nome e cognome" />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email Aziendale</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" name="email" placeholder="nome.cognome@company.com" />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="organizational_unit_id">Unità Organizzativa</Label>
                    <select 
                        id="organizational_unit_id" 
                        name="organizational_unit_id" 
                        required 
                        :tabindex="3"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">Seleziona la tua unità organizzativa</option>
                        <option 
                            v-for="unit in organizationalUnits" 
                            :key="unit.id" 
                            :value="unit.id"
                        >
                            {{ unit.display_name }}
                        </option>
                    </select>
                    <InputError :message="errors.organizational_unit_id" />
                    <p class="text-xs text-muted-foreground">
                        Seleziona l'unità a cui appartieni. Il tuo manager verrà assegnato automaticamente.
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" required :tabindex="4" autocomplete="new-password" name="password" placeholder="Password" />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Conferma Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="5"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Conferma password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" tabindex="6" :disabled="processing">
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                    Crea Account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Hai già un account?
                <TextLink :href="login()" class="underline underline-offset-4" :tabindex="7">Accedi</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
