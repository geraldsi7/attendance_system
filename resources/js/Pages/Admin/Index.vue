<script>
import AuthenticatedLayout from "@/Layouts/Admin/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";

export default {
  components: {
    AuthenticatedLayout,
    Head,
    useForm,
  },
  props: {
    errors: Object,
    number_of_admins: String,
    number_of_lecturers: String,
    number_of_students: String,
    number_of_classes: String,
    number_of_courses: String,
    number_of_venues: String,
    number_of_sessions: String
  },
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
        <div class="grid md:grid-cols-3 gap-5">
          <!-- admins label -->
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined">
                  supervisor_account
                </span>
              </div>
              <div>
                <p class="text-xl font-bold">{{ number_of_admins }}</p>
                <span class="text-gray-500 text-sm font-bold uppercase">
                  Administrators
                </span>
              </div>
            </div>
          </div>
          <!-- end of admins label -->
          
          <!-- lecturers label -->
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined">
                  person
                </span>
              </div>
              <div>
                <p class="text-xl font-bold">{{ number_of_lecturers }}</p>
                <span class="text-gray-500 text-sm font-bold uppercase">
                  Lecturers
                </span>
              </div>
            </div>
          </div>
          <!-- end of lecturers label -->

          <!-- students label -->
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined"> school </span>
              </div>
              <div>
                <p class="text-xl font-bold">{{ number_of_students }}</p>
                <span class="text-gray-500 text-sm font-bold uppercase">
                  students
                </span>
              </div>
            </div>
          </div>
          <!-- end of students label -->

          <!-- class label -->
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined"> local_library </span>
              </div>
              <div>
                <p class="text-xl font-bold">{{ number_of_classes }}</p>
                <span class="text-gray-500 text-sm font-bold uppercase">
                  classes
                </span>
              </div>
            </div>
          </div>
          <!-- end of classes label -->

          <!-- courses label -->
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined"> menu_book </span>
              </div>
              <div>
                <p class="text-xl font-bold">{{ number_of_courses }}</p>
                <span class="text-gray-500 text-sm font-bold uppercase">
                  courses
                </span>
              </div>
            </div>
          </div>
          <!-- end of courses label -->

          <!-- venues label -->
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined"> domain </span>
              </div>
              <div>
                <p class="text-xl font-bold">{{ number_of_venues }}</p>
                <span class="text-gray-500 text-sm font-bold uppercase">
                  venues
                </span>
              </div>
            </div>
          </div>
          <!-- end of venues label -->

          <!-- session label -->
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined"> script </span>
              </div>
              <div>
                <p class="text-xl font-bold">{{ number_of_sessions }}</p>
                <span class="text-gray-500 text-sm font-bold uppercase">
                  sessions
                </span>
              </div>
            </div>
          </div>
          <!-- end of session label -->

          
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
