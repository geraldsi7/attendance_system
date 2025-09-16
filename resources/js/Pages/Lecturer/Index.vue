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
    number_of_courses: String,
    number_of_sessions: String,
    number_of_classes: String,
    number_of_students: String,
    classeData: Object
  },
  data() {
    const filter = useForm({
      'id': 10
    })

    return {
      filter,
      attendance: null,
      attendance_chart: null
    };
  },
  mounted() {
    this.fetchAttendanceAnalytics()
    const students_by_class = document.getElementById("students_by_class").getContext('2d');

    let labels = this.classeData.map(data => data.label);
    let students = this.classeData.map(data => data.students);


    const students_by_class_chart = new Chart(students_by_class, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Students',
          data: students,
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
        maintainAspectRatio: false,
        scales: {
          x: {
            grid: {
              display: false,
            },
            // ticks: {
                  //     display: false, // Hide x-axis labels
                  // }
          },
          y: {
            beginAtZero: true,
            grid: {
              display: false,
            },
            ticks: {
                      display: false, // Hide x-axis labels
                  }
          }
        }
      }
    });

  },
  methods: {
    fetchAttendanceAnalytics() {
      axios
        .get(route("lecturer.attendance.fetch.analytics"), {
          params: {
            session: this.filter.id
          }
        })
        .then((response) => {

          let attendance_canvas = document.getElementById("attendance_analytics").getContext('2d')

          if (this.attendance_chart) {
            this.attendance_chart.destroy();
          }

          let labels = response.data.sessions_data.map(session => session.label);
          let attendance = response.data.sessions_data.map(session => session.attendance);

          this.attendance_chart = new Chart(attendance_canvas, {
            type: 'line',
            data: {
              labels: labels,
              datasets: [{
                label: 'Students Present',
                data: attendance,
                borderWidth: 3,
                fill: false,
                backgroundColor: '#1F2937',
                borderColor: '#1F2937',
              }]
            },
            options: {
              tension: 0.4,
              responsive: true,
              maintainAspectRatio: false,
              scales: {
                x: {
                  grid: {
                    display: false,
                  },
                  // ticks: {
                  //     display: false, // Hide x-axis labels
                  // }
                },
                y: {
                  beginAtZero: true,
                  grid: {
                    display: false,
                  },
                  ticks: {
                    display: false, // Hide x-axis labels
                  }
                }
              }
            }
          });
        })
        .catch((error) => console.log(error));
    },
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

          <!-- classes label -->
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined">
                  local_library
                </span>
              </div>
              <div>
                <p class="text-xl font-bold">{{ number_of_classes }}</p>
                <span class="text-gray-500 text-sm font-bold uppercase">
                  Classes
                </span>
              </div>
            </div>
          </div>
          <!-- end of classes label -->

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
        <div class="mt-7 grid grid-cols-1 lg:grid-cols-5 gap-5">
          <div class="col-span-full lg:col-span-3 bg-white shadow-sm rounded-md p-6">
            <div class="flex justify-between items-center">
              <p class="text-lg font-bold capitalize">attendance report</p>
              <div class="flex items-center gap-x-2">
                <span class="text-sm text-slate-600">Last
                  <SelectInput class="w-20" id="search" v-model="filter.id" @change="fetchAttendanceAnalytics">
                    <option :value="10">
                      10
                    </option>
                    <option :value="25">
                      25
                    </option>
                    <option :value="50">
                      50
                    </option>
                    <option :value="100">
                      100
                    </option>
                  </SelectInput>
                  sessions
                </span>
              </div>
            </div>

            <div class="w-full h-96">
              <canvas id="attendance_analytics"></canvas>
            </div>
          </div>

          <!-- students by class tab -->
          <div class="col-span-full lg:col-span-2 bg-white shadow-sm rounded-md p-6">
            <p class="text-lg font-bold capitalize">Students by class</p>
            <div class="w-full h-96">
              <canvas id="students_by_class"></canvas>
            </div>
          </div>
          <!-- end of students by class tab -->
        </div>
        <!-- end of attendance report -->


      </div>
    </div>
  </AuthenticatedLayout>
</template>
