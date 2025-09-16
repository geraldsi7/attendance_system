<script>
import AuthenticatedLayout from "@/Layouts/Admin/AuthenticatedLayout.vue";
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
    },
    data() {
        const form = useForm({
            name: null,
            staff_id: null,
            email: null,
            // password: null,
            // password_confirmation: null,
        });

        // const profileForm = useForm({
        //     id: null,
        //     name: null,
        //     staff_id: null,
        //     email: null,
        // });

        const passwordForm = useForm({
            id: null,
            password: null,
            password_confirmation: null,
        });

        const importForm = useForm({
            file: null
        })

        return {
            form,
            // profileForm,
            passwordForm,
            action: null,
            editContent: false,
            formModal: false,
            // profileFormModal: false,
            passwordFormModal: false,
            destroyModal: false,
            selectedRow: null,

            importForm,
            importModal: false,
            importErrors: false,
            importErrorMessage: null,
            importHeadingErrors: null,
            importFailedRows: null,
            importProcessing: false
        };
    },
    mounted() {
        this.fetch();

        $(document).on("click", ".edit", (evt) => {
            const data = $(evt.currentTarget).data("id");

            // this.showProfileFormModal('edit', data);

            this.showFormModal('edit', data);
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
                    url: route("lecturer.fetch"),
                },
                columns: [
                    { data: "name", name: "name" },
                    { data: "staff_id", name: "staff_id" },
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
                    { "width": "5%", "targets": -1 }
                ]
            });
        },
        showFormModal(action, data) {
            this.action = action

            if (this.action === 'edit') {
                this.editContent = true

                axios
                    .post(route("lecturer.edit.profile"), {
                        id: data,
                    })
                    .then((response) => [
                        Object.assign(this.form, response.data.row),
                    ])
                    .catch((error) => console.log(error));
            }

            this.formModal = true
        },
        hideFormModal() {
            this.formModal = false
            this.editContent = false
            this.action = null
            this.form.reset()
            this.form.clearErrors()
        },
        // showProfileFormModal(action, data) {
        //     this.action = action
        //     axios
        //         .post(route("lecturer.edit.profile"), {
        //             id: data,
        //         })
        //         .then((response) => [
        //             Object.assign(this.profileForm, response.data.row),
        //         ])
        //         .catch((error) => console.log(error));

        //     this.profileFormModal = true
        // },
        // hideProfileFormModal() {
        //     this.profileFormModal = false
        //     this.action = null
        //     this.profileForm.reset()
        //     this.profileForm.clearErrors()
        // },
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
        showImportModal() {
            this.importModal = true
        },
        closeImportModal() {
            this.importModal = false
            this.importForm.reset();
            this.importForm.clearErrors()
            this.importErrors = false
            this.importHeadingErrors = null
            this.importErrorMessage = null
            this.importFailedRows = null
            this.importProcessing = false
        },
        tryAgain() {
            this.closeImportModal()
            this.showImportModal()
        },
        submit() {
            if (!this.editContent) {
                this.form.post(route("lecturer.store"), {
                    onSuccess: () => {
                        this.hideFormModal();
                        toastr.success("User successfully saved");
                        this.fetch();
                    },
                    onError: (errors) => {
                        toastr.error("Something went wrong");
                    },
                });
            } else {
                this.form.put(route("lecturer.update.profile", this.form.id), {
                    onSuccess: () => {
                        this.hideFormModal();
                        toastr.success("User successfully updated");
                        this.fetch();
                    },
                    onError: (errors) => {
                        toastr.error("Something went wrong");
                    },
                });
            }
        },
        // submitProfile() {
        //     this.profileForm.put(route("lecturer.update.profile", this.profileForm.id), {
        //         onSuccess: () => {
        //             this.hideProfileFormModal();
        //             toastr.success("User successfully updated");
        //             this.fetch();
        //         },
        //         onError: (errors) => {
        //             toastr.error("Something went wrong");
        //         },
        //     });
        // },
        submitPassword() {
            this.passwordForm.put(route("lecturer.update.password", this.passwordForm.id), {
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
                .post(route("lecturer.destroy"), {
                    id: this.selectedRow,
                })
                .then((response) => [
                    this.hideDestroyModal(),
                    toastr.success("User successfully removed"),
                    this.fetch(),
                ])
                .catch((error) => console.log(error));
        },
        submitImport() {
            this.importProcessing = true
            axios
                .post(route("lecturer.import"), this.importForm, {
                    headers: {
                        'Content-Type': 'multipart/form-data', // Set the Content-Type to 'multipart/form-data'
                    }
                })
                .then((response) => {
                    this.closeImportModal();
                    toastr.success("Records successfully saved");
                    this.fetch();
                })
                .catch((error) => {
                    toastr.error("Something went wrong");
                    this.importProcessing = false

                    if (error.response.status === 400) {
                        this.importForm.errors.file = error.response.data.errors.file[0]
                    } else {
                        this.importErrors = true
                        this.importErrorMessage = error.response.data.message
                    }

                    if (error.response.data.header_errors) {
                        this.importHeadingErrors = error.response.data.header_errors
                    } else if (error.response.data.failed_rows) {
                        this.importFailedRows = error.response.data.failed_rows
                    }
                });
        },
    },
};
</script>

<template>

    <Head title="Setup / Lecturers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Setup / Lecturers
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-end items-center mb-4">
                            <div class="flex gap-1.5">

                                <PrimaryButton type="button" class="capitalize text-sm" @click="showImportModal">
                                    Import
                                </PrimaryButton>

                                <PrimaryButton type="button" class="capitalize text-sm" @click="showFormModal('add')">
                                    Add user
                                </PrimaryButton>
                            </div>
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
                                                        Staff ID
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
                        <InputLabel for="staff_id" value="Staff ID" :required="true" />
                        <TextInput id="staff_id" type="text" class="w-full" v-model="form.staff_id"
                            :placeholder="'Staff ID'" autocomplete="staff_id"
                            :class="{ 'border-red-600': form.errors.staff_id }" />
                        <InputError :message="form.errors.staff_id" class="mt-2" />
                    </div>

                    <!-- <div class="col-span-full">
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
                    </div> -->
                </div>

                <PrimaryButton type="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }"
                    class="mt-4 uppercase tracking-widest text-xs">
                    <div v-if="editContent == false" v-text="'Save'"></div>
                    <div v-else v-text="'Update'"></div>
                </PrimaryButton>
            </form>
        </Modal>
        <!-- end of form modal -->
        <!-- 
        -- profile form modal --
        <Modal :show="profileFormModal" :closeable="true" :modalTitle="this.action + ' user'"
            @close="hideProfileFormModal" :maxWidth="'md'">
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
                        <TextInput id="email" type="email" class="w-full" v-model="profileForm.email"
                            :placeholder="'Email'" autocomplete="email"
                            :class="{ 'border-red-600': profileForm.errors.email }" />
                        <InputError :message="profileForm.errors.email" class="mt-2" />
                    </div>

                    <div class="col-span-full">
                        <InputLabel for="staff_id" value="Staff ID" :required="true" />
                        <TextInput id="staff_id" type="text" class="w-full" v-model="profileForm.staff_id"
                            :placeholder="'Staff ID'" autocomplete="staff_id"
                            :class="{ 'border-red-600': profileForm.errors.staff_id }" />
                        <InputError :message="profileForm.errors.staff_id" class="mt-2" />
                    </div>
                </div>

                <PrimaryButton type="submit" :disabled="profileForm.processing"
                    :class="{ 'opacity-25': form.processing }" class="mt-4 uppercase tracking-widest text-xs">update
                </PrimaryButton>
            </form>
        </Modal>
        end of profile form modal -->

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

                <PrimaryButton type="submit" :disabled="passwordForm.processing"
                    :class="{ 'opacity-25': form.processing }" class="mt-4 uppercase tracking-widest text-xs">update
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

        <!-- import  modal -->
        <Modal :show="importModal" :closeable="true" :modalTitle="'import'" @close="closeImportModal" :maxWidth="'md'">

            <div v-if="this.importErrors">
                <div class="bg-red-100 rounded-md p-4 border border-red-200 text-red-700 text-md">
                    <span class="font-bold">Oops!</span> {{ this.importErrorMessage }}
                </div>

                <div class="mt-2 shadow-sm rounded-md">
                    <table class="w-full text-md text-left">
                        <thead class="text-gray-700 capitalize bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Line
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Error
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="this.importHeadingErrors" v-for="response in  this.importHeadingErrors"
                                class="odd:bg-white even:bg-gray-50 border-b">
                                <td class="px-6 py-4 text-center">
                                    1
                                </td>
                                <td class="px-6 py-4">
                                    {{ response }}
                                </td>
                            </tr>
                            <tr v-if="this.importFailedRows" v-for="(response, index) in  this.importFailedRows"
                                class="odd:bg-white even:bg-gray-50 border-b">
                                <td class="px-6 py-4 text-center">
                                    {{ index }}
                                </td>
                                <td class="px-6 py-4">
                                    <ul>
                                        <li v-for="msg in response">{{ msg }}</li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="this.importErrors">
                <button @click="tryAgain"
                    class="mt-4 block items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
                    try again
                </button>
            </div>

            <form v-else @submit.prevent="submitImport" enctype="multipart/form-data">

                <input type="file" class="
                                block
                                w-full
                                mt-3
                                text-sm text-slate-500
                                file:mr-4
                                file:py-2
                                file:px-4
                                file:rounded-full
                                file:border-0
                                file:text-sm
                                file:font-semibold
                                file:bg-gray-50
                                file:text-gray-700
                                hover:file:bg-gray-100
                              " @input="importForm.file = $event.target.files[0]"
                    :class="{ 'border-red-600': importForm.errors.file }" accept=".csv">

                <InputError :message="importForm.errors.file" class="mt-2" />

                <button type="submit" :disabled="importProcessing" :class="{ 'opacity-25': importProcessing }"
                    class="mt-4 block items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
                    Import
                </button>
            </form>
        </Modal>
        <!-- end of import  modal-->
    </AuthenticatedLayout>
</template>
