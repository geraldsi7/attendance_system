<script>
import AuthenticatedLayout from "@/Layouts/Admin/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

export default {
    components: {
        Head,
        useForm,
        AuthenticatedLayout,
        InputError,
        InputLabel,
        TextInput,
        Modal,
        PrimaryButton
    },
    props: {
        errors: Object
    },
    data() {

        const form = useForm({
            id: null,
            title: null
        });

        return {
            form,
            action: null,
            editContent: false,
            formModal: false,
            destroyModal: false,
            selectedRow: null
        };
    },
    mounted() {
        this.fetch();

        $(document).on("click", ".edit", (evt) => {
            const data = $(evt.currentTarget).data("id");

            this.showFormModal('edit', data);
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
                    url: route("level.fetch"),
                },
                columns: [
                    { data: "title", name: "title" },
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
                    { "width": "3%", "targets": -1 }
                ]
            });
        },
        showFormModal(action, data) {
            this.action = action

            if (this.action === 'edit') {
                this.editContent = true

                axios
                    .post(route("level.edit"), {
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
        submit() {
            if (!this.editContent) {
                this.form.post(route("level.store"), {
                    onSuccess: () => {
                        this.hideFormModal();
                        toastr.success("Record successfully saved");
                        this.fetch();
                    },
                    onError: (errors) => {
                        toastr.error("Something went wrong");
                    },
                });
            } else {
                this.form.put(route("level.update", this.form.id), {
                    onSuccess: () => {
                        this.hideFormModal();
                        toastr.success("Record successfully updated");
                        this.fetch();
                    },
                    onError: (errors) => {
                        toastr.error("Something went wrong");
                    },
                });
            }
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
                .post(route("level.destroy"), {
                    id: this.selectedRow,
                })
                .then((response) => [
                    this.hideDestroyModal(),
                    toastr.success("Record successfully deleted"),
                    this.fetch(),
                ])
                .catch((error) => console.log(error));
        }
    },
};
</script>

<template>
    <Head title="Setup / Levels" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Setup / Levels
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-4 justify-end">
                            <PrimaryButton type="button" class="capitalize text-sm" @click="showFormModal('add')">
                                Add item
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
                                                        title
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
        <Modal :show="formModal" :closeable="true" :modalTitle="this.action + ' item'" @close="hideFormModal"
            :maxWidth="'md'">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-12 gap-5">
                    <div class="col-span-full">
                        <InputLabel for="title" value="Title" :required="true" />
                        <TextInput id="title" type="text" class="w-full" v-model="form.title" :placeholder="'Title'"
                            autocomplete="title" :class="{ 'border-red-600': form.errors.title }" />
                        <InputError :message="form.errors.title" class="mt-2" />
                    </div>
                </div>

                <PrimaryButton type="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }"
                    class="mt-4 uppercase tracking-widest text-xs">
                    <div v-if="editContent == false" v-text="'Save'"></div>
                    <div v-else v-text="'Update'"></div>
                </PrimaryButton>
            </form>
        </Modal>
        <!-- end of form modal -->

        <!-- destroy modal  -->
        <Modal :show="destroyModal" :closeable="true" :modalTitle="action + ' item'" @close="hideDestroyModal"
            :maxWidth="'md'">

            <div class="flex justify-center mt-4">
                <p class="text-lg">Are you sure you want to delete this item?</p>
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

