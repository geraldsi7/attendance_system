<script>
import AuthenticatedLayout from "@/Layouts/Lecturer/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";
import Modal from "@/Components/Modal.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";

export default {
  components: {
    AuthenticatedLayout,
    Head,
    useForm,
    Modal,
    InputError,
    InputLabel,
  },
  props: {
    errors: Object,
    courses: Object,
    classes: Object,
    venues: Object,
    students: Object,
    minDateTime: String,
  },
  data() {
    const classFilter = useForm({
      course_id: "",
    });

    const sessionForm = useForm({
      id: null,
      course_id: "",
      class: "",
      section_id: "",
      venue_id: "",
      starts_at: "",
      ends_at: "",
    });

    const attendanceFilter = useForm({
      status: "present",
    });

    return {
      classFilter,
      openDropdownMenu: false,
      sections: [],
      sessionForm,
      showModal: false,
      showPublishModal: false,
      showEndModal: false,
      showViewModal: false,
      showDeleteModal: false,
      action: null,
      editContent: false,
      selectedSessionRow: null,

      students_present: null,
      students_absent: null,
      students_present_percentage : null,
      students_absent_percentage : null,

      attendanceFilter
    };
  },
  mounted() {

    this.fetchSessions();

    $(document).on("click", ".rowDropdown", (evt) => {
      const data = $(evt.target).attr("dropdown-log");

      this.toggleDropdown(data)
    });


    //end of dropdown

    // edit session
    $(document).on("click", ".edit-session", (evt) => {
      const data = $(evt.target).data("logId");
      this.editSession(data);
    });
    //end of edit session

    // publish session
    $(document).on("click", ".publish-session", (evt) => {
      const data = $(evt.target).data("logId");
      this.showPublishSessionModal(data);
    });
    //end of publish session

    // end session
    $(document).on("click", ".end-session", (evt) => {
      const data = $(evt.target).data("logId");
      this.showEndSessionModal(data);
    });
    //end of end session

    // view session
    $(document).on("click", ".view-session", (evt) => {
      const data = $(evt.target).data("logId");
      this.showViewSessionModal(data);
    });
    // end of view session

    // delete session
    $(document).on("click", ".delete-session", (evt) => {
      const data = $(evt.target).data("logId");
      this.showDeleteSessionModal(data);
    });
    //end of delete session
  },
  methods: {
    clearErrors() {
      Object.keys(this.errors).forEach((field) => delete this.errors[field]);
    },

    // create session
    createSession() {
      this.action = 'add'
      this.showModal = true
    },

    // edit session
    editSession(data) {
      this.action = 'edit'
      this.showModal = true
      this.editContent = true
      axios.get(route("lecturer.sessions.edit"), {
        params: {
          id: data,
        },
      })
        .then((response) => [
          (this.sessionForm = useForm(Object.assign({}, response.data.row))),
          this.sections = response.data.sections
        ])
        .catch((error) => console.log(error));
    },

    showPublishSessionModal(data) {
      this.showPublishModal = true
      this.selectedSessionRow = data
    },

    closePublishSessionModal() {
      this.showPublishModal = false
      this.selectedSessionRow = null
    },

    showEndSessionModal(data) {
      this.showEndModal = true
      this.selectedSessionRow = data
    },

    closeEndSessionModal() {
      this.showEndModal = false
      this.selectedSessionRow = null
    },

    showViewSessionModal(data) {
      this.showViewModal = true
      this.selectedSessionRow = data

      axios.get(route("lecturer.sessions.show.attendance"), {
        params: {
          id: data,
        },
      })
        .then((response) => [
          this.students_present = response.data.students_present,
          this.students_absent = response.data.students_absent,
          this.students_present_percentage = response.data.students_present_percentage,
          this.students_absent_percentage = response.data.students_absent_percentage,
          this.fetchAttendanceData()
        ])
        .catch((error) => console.log(error));        
    },

    fetchAttendanceData() {
      $("#attendance_table").DataTable({
        destroy: true,
        stateSave: true,
        processing: false,
        serverSide: true,
        ajax: {
          url: route("lecturer.sessions.attendance.fetch"),
          data: {
            id: this.selectedSessionRow,
            status: this.attendanceFilter.status,
          },
        },
        columns: [          
          { data: "student_id", name: "student_id" },
          { data: "index_number", name: "index_number" },
          { data: "name", name: "name" },
        ],
        lengthMenu: [
          [10, 25, 50, 100, -1],
          ["10", "25", "50", "100", "All"],
        ],
      });
    },

    closeViewSessionModal() {
      this.showViewModal = false
      this.selectedSessionRow = null

      this.students_present = null,
      this.students_absent = null,
      this.students_present_percentage = null,
      this.students_absent_percentage = null,

      this.attendanceFilter.reset()
  
    },

    showDeleteSessionModal(data) {
      this.showDeleteModal = true
      this.selectedSessionRow = data
    },

    closeDeleteSessionModal() {
      this.showDeleteModal = false
      this.selectedSessionRow = null
    },

    publishSession(data) {
      axios.post(route("lecturer.sessions.publish"), {
        id: data,
      })
        .then(
          this.closePublishSessionModal(),
          toastr.success("Session successfully published"),
          this.fetchSessions(),
        )
        .catch((error) => console.log(error));
    },

    endSession(data) {
      axios.post(route("lecturer.sessions.end"), {
        id: data,
      })
        .then(
          this.closeEndSessionModal(),
          toastr.success("Session successfully ended"),
          this.fetchSessions(),
        )
        .catch((error) => console.log(error));
    },

    deleteSession(data) {
      axios.post(route("lecturer.sessions.destroy"), {
        id: data,
      })
        .then(
          this.closeDeleteSessionModal(),
          toastr.success("Session successfully deleted"),
          this.fetchSessions(),
        )
        .catch((error) => console.log(error));
    },
    fetchSessions() {
      $("#sessions_table").DataTable({
        destroy: true,
        stateSave: false,
        processing: false,
        serverSide: true,
        ajax: {
          url: route("lecturer.fetch.sessions"),
        },
        columns: [
          { data: "created_at", name: "created_at" },
          { data: "course", name: "course" },
          { data: "class", name: "class" },
          { data: "section", name: "section" },
          { data: "starts_at", name: "starts_at" },
          { data: "ends_at", name: "ends_at" },
          { data: "venue", name: "venue" },
          { data: "status", name: "status" },
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
        order: [["0", "desc"]],
      });
    },
    closeModal() {
      this.showModal = false
      this.editContent = false

      this.sections = []
      this.sessionForm = useForm({
        id: null,
        course_id: "",
        class: "",
        section_id: "",
        venue_id: "",
        starts_at: "",
        ends_at: "",
      });

      this.clearErrors();
    },
    fetchClass() {
      axios
        .get(route("lecturer.fetch.sessions.classes"), {
          params: {
            id: this.sessionForm.course_id,
          },
        })
        .then((response) => [
          (this.sessionForm.class = response.data.class),
          (this.sections = response.data.sections),
        ])
        .catch((error) => console.log(error));
    },
    submit() {
      if (!this.editContent) {
        this.sessionForm.post(route("lecturer.sessions.store"), {
          forceFormData: true,
          onSuccess: () => {
            this.closeModal();
            toastr.success("Record successfully saved");
            this.fetchSessions();
          },
          onError: (errors) => {
            toastr.error("Something went wrong");
          },
        });
      } else {
        this.sessionForm.post(
          route("lecturer.sessions.update", this.sessionForm.id),
          {
            forceFormData: true,
            _method: "PUT",
            onSuccess: () => {
              this.closeModal();
              toastr.success("Record successfully updated");
              this.fetchSessions();
            },
            onError: (errors) => {
              toastr.error("Something went wrong");
            },
          }
        );
      }
    },
    toggleDropdown(data) {
      this.openDropdownMenu = !this.openDropdownMenu

      const targetDropdown = $('#dropdown-menu' + data)

      if (this.openDropdownMenu) {
        targetDropdown.removeClass('hidden');
      } else {
        targetDropdown.addClass('hidden');
      }
    },
    closeDropdown() {
      $('.dropdown-menu').addClass('hidden')
    },
  
  }
}
</script>


<template>
  <Head title="Attendance Sessions" />

  <AuthenticatedLayout @click="closeDropdown">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Attendance Sessions
      </h2>
    </template>

    <div class="py-12 px-3 md:px-0">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- sessions label -->
        <div class="bg-white shadow-sm rounded-md p-6">
          <div class="flex justify-end">
            <button @click="createSession"
              class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
              <svg width="20" height="20" fill="currentColor" class="mr-2" aria-hidden="true">
                <path
                  d="M10 5a1 1 0 0 1 1 1v3h3a1 1 0 1 1 0 2h-3v3a1 1 0 1 1-2 0v-3H6a1 1 0 1 1 0-2h3V6a1 1 0 0 1 1-1Z" />
              </svg>
              add
            </button>
          </div>

          <div class="mt-3 overflow-auto shadow-sm rounded-md">
            <table id="sessions_table" class="w-full text-sm text-left table-striped">
              <thead class="text-gray-700 capitalize bg-gray-100">
                <tr>
                  <th scope="col" class="px-6 py-3">Created At</th>
                  <th scope="col" class="px-6 py-3">Course</th>
                  <th scope="col" class="px-6 py-3">Class</th>
                  <th scope="col" class="px-6 py-3">section</th>
                  <th scope="col" class="px-6 py-3">starts at</th>
                  <th scope="col" class="px-6 py-3">ends at</th>
                  <th scope="col" class="px-6 py-3">venue</th>
                  <th scope="col" class="px-6 py-3">status</th>
                  <th scope="col" class="px-6 py-3">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
        <!-- end of sessions label -->
      </div>
    </div>
    <!-- sessions form modal -->
    <Modal :show="showModal" :closeable="true" :modalTitle="action + ' session'" @close="closeModal" :maxWidth="'lg'">
      <!-- sessions form -->

      <form @submit.prevent="submit" class="mt-4 px-2 pb-2" enctype="multipart/form-data">
        <div class="grid grid-cols-3 gap-5">
          <div>
            <InputLabel for="course" value="Course" :required="true" />
            <select v-model="sessionForm.course_id" name="course_id" @change="fetchClass"
              class="w-full rounded-sm border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-gray-700 focus:outline-none focus:ring-gray-700 sm:text-sm"
              :class="{ 'border-red-600': sessionForm.errors.course_id }">
              <option value="" disabled>-- Select course --</option>
              <option v-for="course in courses" :key="course.id" :value="course.id">
                {{ course.course_code }} {{ course.title }}
              </option>
            </select>
            <InputError :message="sessionForm.errors.course_id" class="mt-2" />
          </div>

          <div>
            <InputLabel for="class" value="Class" />
            <input v-model="sessionForm.class" name="class" type="text"
              class="date w-full rounded-sm border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-gray-300 sm:text-sm read-only:cursor-not-allowed read-only:bg-gray-200"
              placeholder="Class" :readonly="true" />
          </div>

          <div>
            <InputLabel for="section" value="Section" :required="sections.length > 1" />
            <select v-model="sessionForm.section_id" name="section_id" :disabled="sections.length <= 1"
              class="w-full rounded-sm border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-gray-700 focus:outline-none focus:ring-gray-700 sm:text-sm disabled:cursor-not-allowed disabled:bg-gray-200"
              :class="{ 'border-red-600': sessionForm.errors.section_id }">
              <option value="" disabled>-- Select section --</option>
              <option v-for="section in sections" :key="section.id" :value="section.id">
                {{ section.title }}
              </option>
            </select>
            <InputError :message="sessionForm.errors.section_id" class="mt-2" />
          </div>

          <div>
            <InputLabel for="venue" value="Venue" :required="true" />
            <select v-model="sessionForm.venue_id" name="venue_id"
              class="w-full rounded-sm border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-gray-700 focus:outline-none focus:ring-gray-700 sm:text-sm"
              :class="{ 'border-red-600': sessionForm.errors.venue_id }">
              <option value="" disabled>-- Select venue --</option>
              <option v-for="venue in venues" :key="venue.id" :value="venue.id">
                {{ venue.title }}
              </option>
            </select>
            <InputError :message="sessionForm.errors.venue_id" class="mt-2" />
          </div>

          <div>
            <InputLabel for="starts_at" value="Starts At" :required="true" />
            <input v-model="sessionForm.starts_at" name="starts_at" type="datetime-local"
              class="date w-full rounded-sm border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-gray-700 focus:outline-none focus:ring-gray-700 sm:text-sm"
              :min="minDateTime" :class="{ 'border-red-600': sessionForm.errors.starts_at }" />
            <InputError :message="sessionForm.errors.starts_at" class="mt-2" />
          </div>

          <div>
            <InputLabel for="ends_at" value="Ends At" :required="true" />
            <input v-model="sessionForm.ends_at" name="ends_at" type="datetime-local"
              class="date w-full rounded-sm border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-gray-700 focus:outline-none focus:ring-gray-700 sm:text-sm"
              :min="minDateTime" :class="{ 'border-red-600': sessionForm.errors.ends_at }" />
            <InputError :message="sessionForm.errors.ends_at" class="mt-2" />
          </div>
        </div>

        <div class="flex items-center mt-7 text-gray-600 gap-2">
          <span class="material-symbols-outlined text-md ">
            info
          </span>
          <p class="text-sm">You cannot Edit session after session is published.
            <br>
            <i>If you wish to make changes, then you will need to delete the session and recreate it.</i>
          </p>
        </div>


        <button type="submit" :disabled="sessionForm.processing" :class="{ 'opacity-25': sessionForm.processing }"
          class="mt-4 block items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
          <div v-if="editContent == false" v-text="'Save'"></div>
          <div v-else v-text="'Update'"></div>
        </button>
      </form>

      <!-- end of sessions form  -->
    </Modal>
    <!-- end of sessions form modal -->

    <!-- publish session modal -->
    <Modal :show="showPublishModal" :closeable="true" :modalTitle="'publish session'" @close="closePublishSessionModal"
      :maxWidth="'lg'">

      <div class="flex justify-center">
        <span class="material-symbols-outlined text-9xl text-gray-800">
          exclamation
        </span>
      </div>

      <div class="flex justify-center mt-2 text-gray-600 gap-2">
        <p class="text-md text-center w-2/3">You cannot Edit session after session is published. If you wish to make
          changes, then you will need to delete the session and recreate it.</p>
      </div>

      <div class="flex justify-center mt-4">
        <p class="text-lg">Are you sure you want to publish this session?</p>
      </div>

      <div class="flex justify-center mt-6 gap-4">
        <button @click="publishSession(this.selectedSessionRow)" type="submit"
          class="mt-4 block items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
          Yes
        </button>

        <button @click="closePublishSessionModal" type="button"
          class="mt-4 block items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
          Cancel
        </button>
      </div>

    </Modal>

    <!-- end of  publish session modal-->

    <!-- view session modal -->
    <Modal :show="showViewModal" :closeable="true" :modalTitle="'view session'" @close="closeViewSessionModal"
      :maxWidth="'lg'">

      <div class="flex justify-between items-center">
        <div class="text-md text-left font-bold">
          <span>{{ students_present }}</span>
          <p class="text-sm">Present</p>
        </div>

        <div class="text-md text-right font-bold">
          <span>{{ students_absent }}</span>
          <p class="text-sm">Absent</p>
        </div>
      </div>

      <div class="w-full h-2 bg-gray-300 rounded">
        <div class="h-full bg-gray-800 rounded" :style="{ width: students_present_percentage + '%' }"></div>
      </div>
      <div class="flex justify-between items-center">
        <div class="text-md text-left font-bold">
          <span class="text-sm">{{ students_present_percentage }}%</span>
        </div>

        <div class="text-md text-right font-bold">
          <span class="text-sm">{{ students_absent_percentage }}%</span>
        </div>
      </div>

      <div class="mt-9">
          <label class="text-sm">Show
            <select v-model="attendanceFilter.status" name="status" @change="fetchAttendanceData" class="
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
              <option value="present">Present</option>
              <option value="absent">Absent</option>
            </select>
            students
          </label>

          <div class="mt-3 relative overflow-x-auto shadow-sm rounded-md">
            <table id="attendance_table" class="w-full text-sm text-left table-striped">
              <thead class="text-gray-700 capitalize bg-gray-100">
                <tr>                  
                  <th scope="col" class="px-6 py-3">
                    student ID
                  </th>
                  <th scope="col" class="px-6 py-3">
                    index No.
                  </th>
                  <th scope="col" class="px-6 py-3">
                    name
                  </th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

    </Modal>

    <!-- end of  view session modal-->

    <!-- end session modal -->
    <Modal :show="showEndModal" :closeable="true" :modalTitle="'end session'" @close="closeEndSessionModal"
      :maxWidth="'lg'">

      <div class="flex justify-center">
        <span class="material-symbols-outlined text-9xl text-gray-800">
          exclamation
        </span>
      </div>



      <div class="flex justify-center mt-2 text-gray-600 gap-2">
        <p class="text-md text-center w-2/3">Ending this session will result in the session ending immediately. Students
          will not be able to access the session once it has been ended. Once the session has ended, it cannot be
          re-scheduled.</p>
      </div>

      <div class="flex justify-center mt-4">
        <p class="text-lg">Are you sure you want to end this session?</p>
      </div>

      <div class="flex justify-center mt-6 gap-4">
        <button @click="endSession(this.selectedSessionRow)" type="submit"
          class="mt-4 block items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
          Yes
        </button>

        <button @click="closeEndSessionModal" type="button"
          class="mt-4 block items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
          Cancel
        </button>
      </div>

    </Modal>

    <!-- end of  end session modal-->

    <!-- delete session modal -->
    <Modal :show="showDeleteModal" :closeable="true" :modalTitle="'delete session'" @close="closeDeleteSessionModal"
      :maxWidth="'lg'">

      <div class="flex justify-center">
        <span class="material-symbols-outlined text-9xl text-gray-800">
          exclamation
        </span>
      </div>

      <div class="flex justify-center mt-2 text-gray-600 gap-2">
        <p class="text-md text-center w-2/3">Deleting this session will result in the session deleting immediately. Once the session has been deleted, students
          will not be able to access the session. All records of this session will also be deleted. This action is irreversible.</p>
      </div>

      <div class="flex justify-center mt-4">
        <p class="text-lg">Are you sure you want to delete this session?</p>
      </div>

      <div class="flex justify-center mt-6 gap-4">
        <button @click="deleteSession(this.selectedSessionRow)" type="submit"
          class="mt-4 block items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
          Yes
        </button>

        <button @click="closeDeleteSessionModal" type="button"
          class="mt-4 block items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:cursor-not-allowed">
          Cancel
        </button>
      </div>

    </Modal>

    <!-- end of  delete session modal-->

  </AuthenticatedLayout>
</template>
