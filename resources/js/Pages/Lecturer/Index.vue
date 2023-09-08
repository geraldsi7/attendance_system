<script>
import AuthenticatedLayout from "@/Layouts/Lecturer/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";

export default {
  components: {
    AuthenticatedLayout,
    Head,
    useForm,
  },
  props: {
    errors: Object,
    number_of_courses: String,
    number_of_students: String,
    number_of_sessions: String,
    gradeChartLabels: Object,
    gradeChartData: Object,
    attendanceReportChartLabels: Object,
    attendanceReportChartData: Object,
  },
  mounted() {

    const attendance_report = document.getElementById("attendance_report").getContext('2d');

    const attendance_report_chart = new Chart(attendance_report, {
      type: 'line',
      data: {
        labels: this.attendanceReportChartLabels,
        datasets: [{
          label: 'Number of Students Present',
          data: this.attendanceReportChartData,
          borderWidth: 2,
          fill: false,
          backgroundColor: '#1F2937',
          borderColor: '#1F2937',
        },
        ]
      },
      options: {
        tension: 0.4,
        responsive: true,
        scales: {
          x: {
            grid: {
              display: false,
            }
          },
          y: {
            beginAtZero: true,
            grid: {
              display: false,
            }
          }
        }
      }
    });

    const students_by_class = document.getElementById("students_by_class").getContext('2d');

    const students_by_class_chart = new Chart(students_by_class, {
      type: 'bar',
      data: {
        labels: this.gradeChartLabels,
        datasets: [{
          label: 'Number of Students',
          data: this.gradeChartData,
          borderWidth: 0,
          fill: false,
          backgroundColor: '#1F2937',
          borderColor: '#1F2937',
        },
        ]
      },
      options: {
        tension: 0.4,
        responsive: true,
        scales: {
          x: {
            grid: {
              display: false,
            }
          },
          y: {
            beginAtZero: true,
            grid: {
              display: false,
            }
          }
        }
      }
    });

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
          <!-- courses label -->
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined">
                  menu_book
                </span>
              </div>
              <div>
                <p class="text-xl font-bold">{{ number_of_courses }}</p>
                <span class="text-gray-500 text-sm font-bold uppercase">
                  Courses
                </span>
              </div>
            </div>
          </div>
          <!-- end of courses label -->

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

          <!-- session label -->
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined"> calendar_month </span>
              </div>
              <div>
                <p class="text-xl font-bold">{{ number_of_sessions }}</p>
                <span class="text-gray-500 text-sm font-bold uppercase">
                  sessions
                </span>
              </div>
            </div>
          </div>
          <!-- end of sessions label -->
        </div>

        <!-- attendance report -->
        <div class="mt-7 grid md:grid-cols-5 gap-5">
          <div class="md:col-span-3 bg-white shadow-sm rounded-md p-6">
            <div class="flex justify-between items-center">
              <p class="text-lg font-bold capitalize">attendance report</p>
              <p class="text-sm font-bold capitalize">last 7 sessions</p>
            </div>

            <div style="height: 50vh;">
              <canvas id="attendance_report" style="width: 100%; height: 100%"></canvas>
            </div>
          </div>

          <!-- students by class tab -->
          <div class="md:col-span-2 bg-white shadow-sm rounded-md p-6">
            <p class="text-lg font-bold capitalize">Students by class</p>
            <div style="height: 50vh;">
              <canvas id="students_by_class" style="width: 100%; height: 100%"></canvas>
            </div>
          </div>
          <!-- end of students by class tab -->
        </div>
        <!-- end of attendance report -->


      </div>
    </div>
  </AuthenticatedLayout>
</template>
