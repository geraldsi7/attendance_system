<script>
import AuthenticatedLayout from "@/Layouts/Lecturer/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";
import SelectInput from "@/Components/SelectInput.vue";

export default {
  components: {
    AuthenticatedLayout,
    Head,
    useForm,
    SelectInput
  },
  props: {
    errors: Object,
    classes: Object,
  },
  data() {
    const classFilter = useForm({
      class_id: this.classes[0].id,
    });

    return {
      classFilter,
      students: []
    }
  },
  mounted() {
    this.fetchStudents();
  },
  methods: {
    fetchStudents() {
      $("#students_table").DataTable({
        destroy: true,
        stateSave: true,
        processing: false,
        serverSide: true,
        ajax: {
          url: route("lecturer.fetch.students"),
          data: {
            class_id: this.classFilter.class_id,
          },
        },
        columns: [
          { data: "name", name: "name" },
          { data: "student_id", name: "student_id" },
          { data: "index_number", name: "index_number" },
          { data: "email", name: "email" }
        ],
        lengthMenu: [
          [10, 25, 50, 100, -1],
          ["10", "25", "50", "100", "All"],
        ],
      });
    },
  }
};
</script>

<template>

  <Head title="Students" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Students
      </h2>
    </template>

    <div class="py-12 px-3 md:px-0">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- students label -->
        <div class="bg-white shadow-sm rounded-md p-6">
          <span class="text-sm text-slate-600">Show
            <SelectInput v-model="classFilter.class_id" name="class_id" @change="fetchStudents" class="
                    w-3/4
                    md:w-1/2
                    lg:w-1/4
                  ">
              <option value="" disabled>-- Select class --</option>
              <option v-for="classe in classes" :key="classe.id" :value="classe.id">
                {{ classe.title }}
              </option>
            </SelectInput>
            students
          </span>

          <div class="mt-3 relative overflow-x-auto shadow-sm rounded-md">
            <table id="students_table" class="w-full text-sm text-left table-striped">
              <thead class="text-gray-700 capitalize bg-gray-100">
                <tr>
                  <th scope="col" class="px-6 py-3">
                    name
                  </th>
                  <th scope="col" class="px-6 py-3">
                    student ID
                  </th>
                  <th scope="col" class="px-6 py-3">
                    index No.
                  </th>
                  <th scope="col" class="px-6 py-3">
                    email
                  </th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
        <!-- end of students label -->
      </div>
    </div>
  </AuthenticatedLayout>
</template>
