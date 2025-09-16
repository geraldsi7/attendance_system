<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";
import TextInput from "@/Components/TextInput.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Checkbox from "@/Components/Checkbox.vue"

export default {
    components: {
        Head,
        useForm,
        AuthenticatedLayout,
        InputError,
        InputLabel,
        TextInput,
        SelectInput,
        Modal,
        PrimaryButton,
        Checkbox
    },
    props: {
        errors: Object,
        branches: Object,
        companies: Object
    },
    data() {
        const search = useForm({
            id: '',
            company_id: ''
        });

        const form = useForm({
            name: null,
            username: null,
            email: null,
            company_id: '',
            branch_id: '',
            password: null,
            password_confirmation: null,
        });

        const profileForm = useForm({
            id: null,
            name: null,
            username: null,
            email: null,
            company_id: '',
            branch_id: ''
        });

        const passwordForm = useForm({
            id: null,
            password: null,
            password_confirmation: null,
        });

        return {
            search,
            form,
            company_branches: null,
            form_branches: null,
            profileForm,
            passwordForm,
            action: null,
            editContent: false,
            formModal: false,
            profileFormModal: false,
            passwordFormModal: false,
            destroyModal: false,
            selectedRow: null
        };
    },
    mounted() {
        this.fetch();

        $(document).on("click", ".edit", (evt) => {
            const data = $(evt.currentTarget).data("id");

            this.showProfileFormModal('edit', data);
        });

        $(document).on("click", ".reset", (evt) => {
            const data = $(evt.currentTarget).data("id");

            this.showPasswordFormModal('edit', data);
        });

        $(document).on("click", ".delete", (evt) => {
            const data = $(evt.currentTarget).data("id");

            this.showDestroyModal('delete', data);
        });

    },
    methods: {
        // staff methods
        fetch() {
            $("#data_table").DataTable({
                destroy: true,
                stateSave: true,
                processing: false,
                serverSide: true,
                ajax: {
                    url: route("user.fetch"),
                    data: {
                        id: this.search.id,
                        company_id: this.search.company_id
                    },
                },
                columns: [
                    { data: "name", name: "name" },
                    { data: "username", name: "username" },
                    { data: "email", name: "email" },
                    {
                        data: "action",
                        name: "action",
                        orderable: false,
                        searchable: false,
                    },
                ],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    ["10", "25", "50", "100", "All"],
                ],
                columnDefs: [
                    { "width": "5%", "targets": 3 }
                ]
            });
        },

        fetchBranches() {
            this.search.reset('id')
            axios
                .get(route("user.fetch.branches"), {
                   params: {
                    id: this.search.company_id,
                    },
                })
                .then((response) => [
                   this.company_branches = response.data.row,
                ])
                .catch((error) => console.log(error));
        },

        fetchFormBranches() {
            this.form.reset('branch_id')
            axios
                .get(route("user.fetch.form.branches"), {
                   params: {
                    id: this.form.company_id,
                    },
                })
                .then((response) => [
                   this.form_branches = response.data.row,
                ])
                .catch((error) => console.log(error));
        },

        fetchProfileFormBranches() {
            this.profileForm.reset('branch_id')
            axios
                .get(route("user.fetch.form.branches"), {
                   params: {
                    id: this.profileForm.company_id,
                    },
                })
                .then((response) => [
                   this.form_branches = response.data.row,
                ])
                .catch((error) => console.log(error));
        },

        showFormModal(action) {
            this.action = action
            this.formModal = true
        },
        hideFormModal() {
            this.formModal = false
            this.editContent = false
            this.action = null
            this.form.reset()
            this.form.clearErrors()
        },
        showProfileFormModal(action, data) {
            this.action = action
            axios
                .post(route("user.edit.profile"), {
                    id: data,
                })
                .then((response) => [
                    Object.assign(this.profileForm, response.data.row),
                    this.form_branches = response.data.branches
                ])
                .catch((error) => console.log(error));

            this.profileFormModal = true
        },
        hideProfileFormModal() {
            this.profileFormModal = false
            this.action = null
            this.profileForm.reset()
            this.profileForm.clearErrors()
        },
        showPasswordFormModal(action, data) {
            this.action = action
            this.passwordForm.id = data

            this.passwordFormModal = true
        },
        hidePasswordFormModal() {
            this.passwordFormModal = false
            this.action = null
            this.passwordForm.reset()
            this.passwordForm.clearErrors()
        },
        submit() {
            this.form.post(route("user.store"), {
                onSuccess: () => {
                    this.hideFormModal();
                    toastr.success("User successfully saved");
                    this.fetch();
                },
                onError: (errors) => {
                    toastr.error("Something went wrong");
                },
            });
        },
        submitProfile() {
            this.profileForm.put(route("user.update.profile", this.profileForm.id), {
                onSuccess: () => {
                    this.hideProfileFormModal();
                    toastr.success("User successfully updated");
                    this.fetch();
                },
                onError: (errors) => {
                    toastr.error("Something went wrong");
                },
            });
        },
        submitPassword() {
            this.passwordForm.put(route("user.update.password", this.passwordForm.id), {
                onSuccess: () => {
                    this.hidePasswordFormModal();
                    toastr.success("Password successfully updated");
                },
                onError: (errors) => {
                    toastr.error("Something went wrong");
                },
            });
        },
        showDestroyModal(action, data) {
            this.action = action
            this.selectedRow = data
            this.destroyModal = true
        },
        hideDestroyModal() {
            this.destroyModal = false
            this.selectedRow = null
            this.action = null
        },
        destroy() {
            axios
                .post(route("user.destroy"), {
                    id: this.selectedRow,
                })
                .then((response) => [
                    this.hideDestroyModal(),
                    toastr.success("User successfully removed"),
                    this.fetch(),
                ])
                .catch((error) => console.log(error));
        }
    },
};
</script>

<template>
    <Head title="Setup / Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Setup / Users
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-2">
                                <div v-if="$page.props.auth.level == 4" class="flex items-center gap-2">
                                <span class="text-sm text-slate-600">Show</span>
                                <SelectInput v-model="search.company_id" @change="fetch(), fetchBranches()">
                                    <option value="" disabled selected>
                                        -- Select Company --
                                    </option>
                                    <option v-for="company in companies" :key="company.id" :value="company.id">
                                        {{ company.title }}
                                    </option>
                                </SelectInput>
                                </div>

                                <div v-if="$page.props.auth.level == 4 || $page.props.auth.level == 3" class="flex items-center gap-2">
                                <span class="text-sm text-slate-600">Show</span>
                                <SelectInput v-model="search.id" @change="fetch">
                                    <option value="" disabled selected>
                                        -- Select Branch --
                                    </option>
                                    <option v-if="$page.props.auth.level == 4" v-for="branch in company_branches" :key="branch.id" :value="branch.id">
                                        {{ branch.title }}
                                    </option>
                                    <option v-if="$page.props.auth.level == 3" v-for="branch in branches" :key="branch.id" :value="branch.id">
                                        {{ branch.title }}
                                    </option>
                                </SelectInput>
                            </div>
                            </div>
                            <PrimaryButton type="button" class="capitalize text-sm" @click="showFormModal('add')">
                                Add user
                            </PrimaryButton>
                        </div>

                        <div class="flex flex-col">
                            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="py-2 inline-w-full sm:px-6 lg:px-8">
                                    <div class="overflow-x-auto">
                                        <table id="data_table" class="w-full table-striped">
                                            <thead class="capitalize border-b bg-gray-100 font-medium">
                                                <tr>
                                                    <th scope="col" class="text-sm text-gray-900 px-6 py-4 text-left">
                                                        name
                                                    </th>
                                                    <th scope="col" class="text-sm text-gray-900 px-6 py-4 text-left">
                                                        username
                                                    </th>
                                                    <th scope="col" class="text-sm text-gray-900 px-6 py-4 text-left">
                                                        email
                                                    </th>
                                                    <th scope="col" class="text-sm text-gray-900 px-6 py-4 text-left">
                                                        action
                                                    </th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- form modal -->
        <Modal :show="formModal" :closeable="true" :modalTitle="this.action + ' user'" @close="hideFormModal"
            :maxWidth="'md'">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-12 gap-5">
                    <div class="col-span-full">
                        <InputLabel for="name" value="Name" :required="true" />
                        <TextInput id="name" type="text" class="w-full" v-model="form.name" :placeholder="'Name'"
                            autocomplete="name" :class="{ 'border-red-600': form.errors.name }" />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="col-span-full">
                        <InputLabel for="email" value="Email" :required="true" />
                        <TextInput id="email" type="email" class="w-full" v-model="form.email" :placeholder="'Email'"
                            autocomplete="email" :class="{ 'border-red-600': form.errors.email }" />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div class="col-span-full">
                        <InputLabel for="username" value="Username" :required="true" />
                        <TextInput id="username" type="text" class="w-full" v-model="form.username"
                            :placeholder="'Username'" autocomplete="username"
                            :class="{ 'border-red-600': form.errors.username }" />
                        <InputError :message="form.errors.username" class="mt-2" />
                    </div>

                    <div v-if="$page.props.auth.level == 4" class="col-span-full">
                        <InputLabel for="company" value="Company" :required="true" />
                        <SelectInput id="company" v-model="form.company_id" class="w-full" :class="{ 'border-red-600': form.errors.company_id }" @change="fetchFormBranches">
                            <option value="" disabled selected>
                                -- Select Company --
                            </option>
                            <option v-for="company in companies" :key="company.id" :value="company.id">
                                {{ company.title }}
                            </option>
                        </SelectInput>
                        <InputError :message="form.errors.company_id" class="mt-2" />
                    </div>

                    <div class="col-span-full" v-if="$page.props.auth.level == 4 || $page.props.auth.level == 3">
                        <InputLabel for="branch" value="Branch" :required="true" />
                        <SelectInput id="branch" v-model="form.branch_id" class="w-full" :class="{ 'border-red-600': form.errors.branch_id }">
                            <option value="" disabled selected>
                                -- Select Branch --
                            </option>
                            <option v-if="$page.props.auth.level == 4" v-for="branch in form_branches" :key="branch.id" :value="branch.id">
                                        {{ branch.title }}
                                    </option>
                                    <option v-if="$page.props.auth.level == 3" v-for="branch in branches" :key="branch.id" :value="branch.id">
                                        {{ branch.title }}
                                    </option>
                        </SelectInput>
                        <InputError :message="form.errors.branch_id" class="mt-2" />
                    </div>

                    <div class="col-span-full">
                        <InputLabel for="password" value="Password" :required="true" />
                        <TextInput id="password" type="password" class="w-full" v-model="form.password"
                            :placeholder="'Password'" autocomplete="password"
                            :class="{ 'border-red-600': form.errors.password }" />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div class="col-span-full">
                        <InputLabel for="password_confirmation" value="Confirm Password" :required="true" />
                        <TextInput id="password_confirmation" type="password" class="w-full"
                            v-model="form.password_confirmation" :placeholder="'Confirm Password'"
                            autocomplete="password_confirmation"
                            :class="{ 'border-red-600': form.errors.password_confirmation }" />
                        <InputError :message="form.errors.password_confirmation" class="mt-2" />
                    </div>
                </div>

                <PrimaryButton type="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }"
                    class="mt-4 uppercase tracking-widest text-xs">save
                </PrimaryButton>
            </form>
        </Modal>
        <!-- end of form modal -->

        <!-- profile form modal -->
        <Modal :show="profileFormModal" :closeable="true" :modalTitle="this.action + ' user'" @close="hideProfileFormModal"
            :maxWidth="'md'">
            <form @submit.prevent="submitProfile">
                <div class="grid grid-cols-12 gap-5">
                    <div class="col-span-full">
                        <InputLabel for="name" value="Name" :required="true" />
                        <TextInput id="name" type="text" class="w-full" v-model="profileForm.name" :placeholder="'Name'"
                            autocomplete="name" :class="{ 'border-red-600': profileForm.errors.name }" />
                        <InputError :message="profileForm.errors.name" class="mt-2" />
                    </div>

                    <div class="col-span-full">
                        <InputLabel for="email" value="Email" :required="true" />
                        <TextInput id="email" type="email" class="w-full" v-model="profileForm.email" :placeholder="'Email'"
                            autocomplete="email" :class="{ 'border-red-600': profileForm.errors.email }" />
                        <InputError :message="profileForm.errors.email" class="mt-2" />
                    </div>

                    <div class="col-span-full">
                        <InputLabel for="username" value="Username" :required="true" />
                        <TextInput id="username" type="text" class="w-full" v-model="profileForm.username"
                            :placeholder="'Username'" autocomplete="username"
                            :class="{ 'border-red-600': profileForm.errors.username }" />
                        <InputError :message="profileForm.errors.username" class="mt-2" />
                    </div>

                    <div v-if="$page.props.auth.level == 4" class="col-span-full">
                        <InputLabel for="company" value="Company" :required="true" />
                        <SelectInput id="company" v-model="profileForm.company_id" class="w-full" :class="{ 'border-red-600': profileForm.errors.company_id }" @change="fetchProfileFormBranches">
                            <option value="" disabled selected>
                                -- Select Company --
                            </option>
                            <option v-for="company in companies" :key="company.id" :value="company.id">
                                {{ company.title }}
                            </option>
                        </SelectInput>
                        <InputError :message="profileForm.errors.company_id" class="mt-2" />
                    </div>

                    <div class="col-span-full" v-if="$page.props.auth.level == 4 || $page.props.auth.level == 3">
                        <InputLabel for="branch" value="Branch" :required="true" />
                        <SelectInput id="branch" v-model="profileForm.branch_id" class="w-full" :class="{ 'border-red-600': profileForm.errors.branch_id }">
                            <option value="" disabled selected>
                                -- Select Branch --
                            </option>
                            <option v-if="$page.props.auth.level == 4" v-for="branch in form_branches" :key="branch.id" :value="branch.id">
                                        {{ branch.title }}
                                    </option>
                                    <option v-if="$page.props.auth.level == 3" v-for="branch in branches" :key="branch.id" :value="branch.id">
                                        {{ branch.title }}
                                    </option>
                        </SelectInput>
                        <InputError :message="profileForm.errors.branch_id" class="mt-2" />
                    </div>
                </div>

                <PrimaryButton type="submit" :disabled="profileForm.processing" :class="{ 'opacity-25': form.processing }"
                    class="mt-4 uppercase tracking-widest text-xs">update
                </PrimaryButton>
            </form>
        </Modal>
        <!-- end of profile form modal -->

        <!-- password form modal -->
        <Modal :show="passwordFormModal" :closeable="true" :modalTitle="this.action + ' password'"
            @close="hidePasswordFormModal" :maxWidth="'md'">
            <form @submit.prevent="submitPassword">
                <div class="grid grid-cols-12 gap-5">
                    <div class="col-span-full">
                        <InputLabel for="password" value="Password" :required="true" />
                        <TextInput id="password" type="password" class="w-full" v-model="passwordForm.password"
                            :placeholder="'Password'" autocomplete="password"
                            :class="{ 'border-red-600': passwordForm.errors.password }" />
                        <InputError :message="passwordForm.errors.password" class="mt-2" />
                    </div>

                    <div class="col-span-full">
                        <InputLabel for="password_confirmation" value="Confirm Password" :required="true" />
                        <TextInput id="password_confirmation" type="password" class="w-full"
                            v-model="passwordForm.password_confirmation" :placeholder="'Confirm Password'"
                            autocomplete="password_confirmation"
                            :class="{ 'border-red-600': passwordForm.errors.password_confirmation }" />
                        <InputError :message="passwordForm.errors.password_confirmation" class="mt-2" />
                    </div>
                </div>

                <PrimaryButton type="submit" :disabled="passwordForm.processing" :class="{ 'opacity-25': form.processing }"
                    class="mt-4 uppercase tracking-widest text-xs">update
                </PrimaryButton>
            </form>
        </Modal>
        <!-- end of password form modal -->

        <!-- destroy modal  -->
        <Modal :show="destroyModal" :closeable="true" :modalTitle="action + ' user'" @close="hideDestroyModal"
            :maxWidth="'md'">

            <div class="flex justify-center mt-4">
                <p class="text-lg">Are you sure you want to delete this user?</p>
            </div>

            <div class="flex justify-center mt-6 gap-4">
                <PrimaryButton @click="destroy" type="submit" class="mt-4 uppercase text-sm">
                    Yes
                </PrimaryButton>

                <button @click="hideDestroyModal" type="button"
                    class="mt-4 block items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
                    Cancel
                </button>
            </div>
        </Modal>
        <!-- end of destroy modal-->
    </AuthenticatedLayout>
</template>

