<script>
import AuthenticatedLayout from "@/Layouts/Student/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";

export default {
  components: {
    AuthenticatedLayout,
    Head,
    useForm,
  },
  props: {
    errors: Object,
    courses: Object,
  }
};
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Overview
      </h2>
    </template>

    <div class="py-12 px-3 md:px-0">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
          <!-- courses card -->
          <div class="bg-white shadow-sm rounded-md p-6" v-for="course in courses" :key="course">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined">
                  menu_book
                </span>
              </div>
              <div>
                <p class="text-md font-bold">{{ course.course_code }}: {{ course.title }}</p>
                <div v-if="course.session.length >= 1">
                  <div class="w-full mt-5 h-2 bg-gray-300 rounded">
                    <div class="h-full bg-gray-800 rounded"
                      :style="{ width: (course.attendance.length / course.session.length) * 100 + '%' }"></div>
                  </div>
                  <p class="mt-1 text-sm font-bold">{{ course.attendance.length }} of {{ course.session.length }}
                    lecture<span v-show="course.session.length != 1">s</span> attended</p>
                </div>
                <div class="mt-7" v-else>
                  <p class="text-sm font-bold">No session yet</p>
                </div>
              </div>
            </div>
          </div>
          <!-- end of courses card -->

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
