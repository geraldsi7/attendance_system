<script>
import AuthenticatedLayout from "@/Layouts/Lecturer/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/inertia-vue3";

export default {
  components: {
    AuthenticatedLayout,
    Head,
  },
  props: {
    errors: Object,
    courses: Object,
  },
  mounted() {
    $('#courses_table').DataTable({
      lengthMenu: [
        [10, 25, 50, 100, -1],
        ["10", "25", "50", "100", "All"],
      ],
    })
  }
};
</script>

<template>

  <Head title="Courses" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Courses
      </h2>
    </template>

    <div class="py-12  px-3 md:px-0">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- courses label -->
        <div class="bg-white shadow-sm rounded-md p-6">

          <div class="relative overflow-x-auto shadow-sm rounded-md">
            <table id="courses_table" class="w-full text-sm text-left table-striped">
              <thead class="text-gray-700 capitalize bg-gray-100">
                <tr>
                  <th scope="col" class="px-6 py-3">
                    course code
                  </th>
                  <th scope="col" class="px-6 py-3">
                    title
                  </th>
                  <th scope="col" class="px-6 py-3">
                    class
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(course, index) in courses" :key="course.code" class="odd:bg-white even:bg-gray-50 border-b">
                  <td class="px-6 py-4 uppercase">
                    {{ course.code }}
                  </td>
                  <td class="px-6 py-4 capitalize">
                    {{ course.title }}
                  </td>
                  <td class="px-6 py-4 grid grid-rows-${course.classe.length} gap-y-3.5">
                    <div v-for="(classe, i) in course.classe">
                      <p>{{ classe.title }}</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <!-- end of courses label -->

      </div>
    </div>
  </AuthenticatedLayout>
</template>
