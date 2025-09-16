<script>
import AuthenticatedLayout from "@/Layouts/Student/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";

export default {
  components: {
    AuthenticatedLayout,
    Head,
    useForm,
    Inertia,
    PrimaryButton,
    TextInput,
    Modal,
    Dropdown,
  },
  props: {
    errors: Object,
    courses: Object,
  },
  data() {
    const filterForm = useForm({
      date: new Date().toISOString().substr(0, 10),
    });

    const sessionForm = useForm({
      id: null,
    });

    return {
      sessionForm,

      filterForm,
      isLoadingData: true,
      sessions: [],

      selectedRow: null,
      sessionOpenModal: false,

      userLocation: null,
      sessionVenue: null,
    };
  },
  computed: {
    isWithinRadius() {
      if (!this.userLocation) return false;

      const userLocationLat = this.userLocation.latitude;
      const userLocationLong = this.userLocation.longitude;
      const venueSessionLat = this.sessionVenue.latitude;
      const venueSessionLong = this.sessionVenue.longitude;

      // Calculate the distance using the Haversine formula
      const R = 6371000; // Earth radius in meters
      const lat1 = (userLocationLat * Math.PI) / 180;
      const lat2 = (venueSessionLat * Math.PI) / 180;
      const deltaLat = ((venueSessionLat - userLocationLat) * Math.PI) / 180;
      const deltaLong = ((venueSessionLong - userLocationLong) * Math.PI) / 180;

      const a =
        Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
        Math.cos(lat1) *
          Math.cos(lat2) *
          Math.sin(deltaLong / 2) *
          Math.sin(deltaLong / 2);

      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      const distance = R * c; // Distance in meters

      return distance <= 50 * 10; // Check if the distance is less than or equal to 50 meters
    },
  },
  mounted() {
    this.fetchSessions();
    setInterval(this.reloadPage, 1000 * 63);
  },
  methods: {
    reloadPage() {
      Inertia.reload({
        preserveScroll: true,
      });
    },
    fetchSessions() {
      this.isLoadingData = true;

      axios
        .get(route("student.fetch.sessions"), {
          params: {
            date: this.filterForm.date,
          },
        })
        .then((response) => [(this.sessions = response.data.row)])
        .catch((error) => toastr.error("Something went wrong"))
        .finally(() => {
          this.isLoadingData = false; // Set loading state to false
        });
    },
    showSessionOpenModal(data) {
      this.selectedRow = data;

      Object.assign(this.sessionForm, this.selectedRow);

      this.getLocation();

      this.sessionVenue = {
        latitude: this.selectedRow.venue.latitude,
        longitude: this.selectedRow.venue.longitude,
      };

      this.sessionOpenModal = true;
    },
    hideSessionOpenModal() {
      this.selectedRow = null;
      this.sessionOpenModal = false;

      this.userLocation = null;
      this.sessionVenue = null;
    },
    getLocation() {
      if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
          this.successCallback,
          this.errorCallback
        );
      } else {
        toastr.error("Geolocation is not supported in this browser.");
      }
    },
    successCallback(position) {
      this.userLocation = {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
      };

      // console.log(this.userLocation);
    },
    errorCallback(error) {
      toast.error("Permission denied!");
    },
    signSession() {
      this.sessionForm.post(route("student.sessions.sign"), {
        onSuccess: () => {
          this.hideSessionOpenModal();
          toastr.success("Record successfully saved");
          this.fetchSessions();
        },
        onError: (errors) => {
          toastr.error("Something went wrong");
        },
      });
    },
  },
};
</script>

<template>
  <Head title="Attendance" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Attendance</h2>
    </template>

    <div class="py-12 px-3 md:px-0">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
          <!-- courses card -->
          <div
            class="bg-white shadow-sm rounded-md p-6"
            v-for="course in courses"
            :key="course"
          >
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined"> menu_book </span>
              </div>
              <div class="w-full">
                <p class="text-md font-bold">{{ course.code }}: {{ course.title }}</p>
                <div v-if="course.session.length >= 1">
                  <div class="w-full mt-5 h-2 bg-gray-300 rounded">
                    <div
                      class="h-full bg-gray-800 rounded"
                      :style="{
                        width:
                          (course.attendance_count / course.session.length) * 100 + '%',
                      }"
                    ></div>
                  </div>
                  <p class="mt-1 text-sm font-bold">
                    {{ course.attendance_count }} of
                    {{ course.session.length }} lecture<span
                      v-show="course.session.length != 1"
                      >s</span
                    >
                    attended
                  </p>
                </div>
                <div class="mt-7" v-else>
                  <p class="text-sm font-bold">No session yet</p>
                </div>
              </div>
            </div>
          </div>
          <!-- end of courses card -->
        </div>

        <!-- attendance board -->
        <div class="mt-10">
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex justify-between items-center">
              <p class="text-lg font-bold capitalize">sessions</p>
              <div>
                <TextInput
                  id="calendar"
                  type="date"
                  v-model="filterForm.date"
                  class="w-full"
                  @change="fetchSessions"
                />
              </div>
            </div>

            <div v-if="isLoadingData" class="h-96 flex justify-center items-center">
              <p
                class="text-center text-md text-gray-800 inline-flex items-center gap-x-3"
              >
                <svg
                  aria-hidden="true"
                  class="w-8 h-8 text-gray-300 animate-spin fill-black"
                  viewBox="0 0 100 101"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                    fill="currentColor"
                  />
                  <path
                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                    fill="currentFill"
                  />
                </svg>
                <span class="sr-only">Loading...</span> Loading data. Please wait.
              </p>
            </div>
            <div v-else>
              <div v-if="sessions.length != 0" class="flex flex-col space-y-4 mt-12">
                <div
                  v-for="session in sessions"
                  @click="showSessionOpenModal(session)"
                  class="hover:cursor-pointer border border-gray-300 py-3 px-4 flex flex-col md:flex-row md:items-center justify-between rounded-lg shadow-sm"
                >
                  <div
                    class="w-full flex flex-col md:flex-row md:items-center gap-x-3 divide-y md:divide-y-0 md:divide-x"
                  >
                    <div class="flex flex-col md:text-right md:w-1/4 pb-2 md:pb-0">
                      <p class="text-sm font-bold">{{ session.starts }}</p>
                      <p class="text-sm">{{ session.ends }}</p>
                    </div>

                    <div class="flex flex-col md:w-3/4 pt-3 md:pt-0 md:ps-4">
                      <p
                        class="font-semibold overflow-hidden whitespace-nowrap text-ellipsis"
                      >
                        {{ session.course.code }}: {{ session.title }}
                      </p>
                      <p class="text-sm overflow-hidden whitespace-nowrap text-ellipsis">
                        Host: {{ session.lecturer.name }}
                      </p>
                      <p class="text-sm overflow-hidden whitespace-nowrap text-ellipsis">
                        Status: {{ session.status }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="h-96 flex flex-col justify-center items-center">
                <p class="text-center text-md text-gray-700">No sessions scheduled</p>

                <PrimaryButton
                  type="button"
                  @click="showSessionFormModal('add')"
                  class="uppercase tracking-widest text-xs mt-5"
                  >create Attendance
                </PrimaryButton>
              </div>
            </div>
          </div>
        </div>
        <!-- end of attendance board -->
      </div>
    </div>

    <!-- open session modal -->
    <Modal
      :show="sessionOpenModal"
      :closeable="true"
      :modalTitle="''"
      @close="hideSessionOpenModal"
      :maxWidth="'lg'"
    >
      <div class="divide-y divide-gray-800">
        <div class="pb-3">
          <p class="font-bold text-xl overflow-hidden whitespace-nowrap text-ellipsis">
            {{ this.selectedRow.course.code }}: {{ this.selectedRow.title }}
          </p>
          <p class="font-bold">{{ this.selectedRow.classe.title }}</p>
          <p class="text-md">
            {{ this.selectedRow.full_date }} at {{ this.selectedRow.venue.title }}
          </p>
          <p class="mt-4 text-gray-600 text-sm">{{ this.selectedRow.status }}</p>
        </div>
        <div class="py-3">
          <div
            class="flex flex-col justify-center mt-2 text-gray-700 text-center text-sm gap-2"
          >
            <div v-if="!userLocation">
              <p>No location data available.</p>
            </div>
            <div v-else-if="!isWithinRadius">
              <p>
                This session is currently not available in your area. Please try again.
              </p>
            </div>

            <div class="mt-2" v-if="selectedRow.status !== 'Running'">
              <p>
                This session has
                <span v-if="selectedRow.status === 'Scheduled'">not started.</span>
                <span v-else-if="selectedRow.status === 'Ended'">ended.</span>
              </p>
            </div>

            <div class="mt-3" v-if="isWithinRadius && selectedRow.status === 'Running'">
              <div v-if="selectedRow.attended">
                <p>You have already signed for this session.</p>
              </div>
              <div v-else class="flex justify-center items-center gap-x-4">
                <form @submit.prevent="signSession">
                  <PrimaryButton
                    type="submit"
                    :disabled="sessionForm.processing"
                    :class="{ 'opacity-25': sessionForm.processing }"
                    class="mt-4 uppercase tracking-widest text-xs"
                  >
                    Sign
                  </PrimaryButton>
                </form>

                <button
                  @click="hideSessionOpenModal"
                  type="button"
                  class="mt-4 block items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Modal>
    <!-- end of opened session modal -->
  </AuthenticatedLayout>
</template>
