<script>
import AuthenticatedLayout from "@/Layouts/Student/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";
import Modal from "@/Components/Modal.vue";

export default {
  components: {
    AuthenticatedLayout,
    Head,
    useForm,
    Modal
  },
  props: {
    errors: Object,
  },
  data() {
    return {
      sessions: [],
      selectedSessionRow: null,
      showModal: false,
      location: null,
      venue: null,
    };
  },
  mounted() {
    this.fetchSessions()
    this.getLocation()
  },
  computed: {
    isWithinRadius() {
      if (!this.location) return false;

      const userLat = this.location.latitude;
      const userLong = this.location.longitude;
      const venueLat = this.venue.latitude;
      const venueLong = this.venue.longitude;

      // Calculate the distance using the Haversine formula
      const R = 6371000; // Earth radius in meters
      const lat1 = (userLat * Math.PI) / 180;
      const lat2 = (venueLat * Math.PI) / 180;
      const deltaLat = ((venueLat - userLat) * Math.PI) / 180;
      const deltaLong = ((venueLong - userLong) * Math.PI) / 180;

      const a =
        Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
        Math.cos(lat1) *
        Math.cos(lat2) *
        Math.sin(deltaLong / 2) *
        Math.sin(deltaLong / 2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      const distance = R * c; // Distance in meters

      return distance <= 1000; // Check if the distance is less than or equal to 100 meters
    },
  },
  methods: {
    fetchSessions() {
      axios
        .get(route("student.sessions.fetch"))
        .then((response) => [
          (this.sessions = response.data.row),
        ])
        .catch((error) => console.log(error));
    },
    showSessionModal(session) {
      this.selectedSessionRow = session;
      this.showModal = true;
      this.getLocation()

      this.venue = {
        latitude: this.selectedSessionRow.venue.latitude,
        longitude: this.selectedSessionRow.venue.longitude
      }
    },
    closeSessionModal() {
      this.selectedSessionRow = null;
      this.showModal = false;

      this.venue = []
    },
    signSession() {
      axios.post(route("student.sessions.sign"), {
        id: this.selectedSessionRow.id,
      })
        .then(
          this.closeSessionModal(),
          toastr.success("Session successfully signed"),
          this.fetchSessions(),
        )
        .catch((error) => console.log(error));
    },

    getLocation() {
      if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(this.successCallback, this.errorCallback);
      } else {
        alert("Geolocation is not supported in this browser.");
      }
    },
    successCallback(position) {
      this.location = {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude
      };

      // console.log(this.location)
    },
    errorCallback(error) {
      // Handle errors or permission denial here
    }
  }
};
</script>

<template>
  <Head title="Attendance Sessions" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Attendance Sessions
      </h2>
    </template>

    <div class="py-12 px-3 md:px-0">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div v-for="session in sessions" :key="session.id" @click="showSessionModal(session)"
            class="bg-white shadow-sm rounded-md p-6 cursor-pointer">
            <div class="flex items-center gap-4">
              <div>
                <span class="material-symbols-outlined" v-show="session.status == 'Scheduled'">
                  event_upcoming
                </span>
                <span class="material-symbols-outlined" v-show="session.status == 'Running' && !session.presence">
                  calendar_today
                </span>
                <span v-show="session.status != 'Scheduled' && session.presence" class="material-symbols-outlined">
                  event_available
                </span>
                <span v-show="session.status == 'Ended' && !session.presence" class="material-symbols-outlined">
                  event_busy
                </span>
              </div>
              <div class="w-full">
                <p class="text-md font-bold">{{ session.course.course_code }}: {{ session.course.title }}</p>
                <span
                  class="text-xs inline-block py-1 px-2.5 leading-none text-center whitespace-nowrap font-bold bg-gray-800 text-white rounded">{{
                    session.status }}</span>
                <div class="mt-3">
                  <div class="flex justify-between items-center">
                    <p class="text-sm font-bold">
                      Start Date
                      <br>
                      {{ session.starts_at }}
                    </p>
                    <p class="text-right text-sm font-bold">
                      End Date
                      <br>
                      {{ session.ends_at }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="showModal" :closeable="true" :modalTitle="'Sign Session'" @close="closeSessionModal" :maxWidth="'lg'">
      <div class="flex justify-center">
        <span class="material-symbols-outlined text-9xl text-gray-800">
          exclamation
        </span>
      </div>


      <div class="flex justify-center mt-2 text-gray-600 gap-2">
        <p class="text-md text-gray-600" v-show="location && !isWithinRadius">This session is currently not available in
          your area. Try again.</p>
        <p class="text-md text-gray-600" v-show="!location">No location data available.</p>
      </div>

      <div class="flex justify-center mt-2 text-gray-600 gap-2">
        <p class="text-md text-center w-2/3" v-show="selectedSessionRow.status == 'Scheduled'">This session is scheduled
          at {{ selectedSessionRow.starts_at }} and ends at {{ selectedSessionRow.ends_at }}</p>
        <p class="text-md text-center w-2/3" v-show="selectedSessionRow.status == 'Running'">This session is running and
          ends at {{ selectedSessionRow.ends_at }}</p>
        <p class="text-md text-center w-2/3" v-show="selectedSessionRow.status == 'Ended'">This session has ended.</p>
      </div>

      <div class="flex justify-center mt-4">
        <p class="text-lg"
          v-show="selectedSessionRow.status == 'Running' && !selectedSessionRow.presence && location && isWithinRadius">
          Are you sure
          you want to sign this session?</p>
        <p class="text-md text-gray-600" v-show="selectedSessionRow.presence">You have already signed to this session.</p>
      </div>

      <div class="flex justify-center mt-6 gap-4"
        v-show="selectedSessionRow.status == 'Running' && !selectedSessionRow.presence && location && isWithinRadius">
        <button @click="signSession" type="submit"
          class="mt-4 block items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
          Yes
        </button>

        <button @click="closeSessionModal" type="button"
          class="mt-4 block items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
          Cancel
        </button>
      </div>

    </Modal>
  </AuthenticatedLayout>
</template>
