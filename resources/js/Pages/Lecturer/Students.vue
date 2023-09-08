<script>
import AuthenticatedLayout from "@/Layouts/Lecturer/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";

export default {
  components: {
    AuthenticatedLayout,
    Head,
    useForm
  },
  props: {
    errors: Object,
    grades: Object,
  },
  data() {
    const classFilter = useForm({
      class_id: this.grades[0].id,
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
          { data: "email", name: "email" },
          { data: "section", name: "section" },
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
          <label class="text-sm">Show
            <select v-model="classFilter.class_id" name="class_id" @change="fetchStudents" class="
                    w-3/4
                    md:w-1/2
                    lg:w-1/3
                    rounded-sm
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-gray-700
                    focus:outline-none
                    focus:ring-gray-700
                    sm:text-sm
                  ">
              <option value="" disabled>-- Select class --</option>
              <option v-for="grade in grades" :key="grade.id" :value="grade.id">
                {{ grade.title }} {{ grade.year }} [{{ grade.iso }} {{ grade.year }}]
              </option>
            </select>
            students
          </label>

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
                  <th scope="col" class="px-6 py-3">
                    Section
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
