<script>
import AuthenticatedLayout from "@/Layouts/Lecturer/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import SelectInput from "@/Components/SelectInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
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
    SelectInput,
    InputError,
    InputLabel,
    Dropdown,
  },
  props: {
    errors: Object,
    courses: Object,
    venues: Object,
  },
  data() {
    const filterForm = useForm({
      date: new Date().toISOString().substr(0, 10),
    });

    const sessionForm = useForm({
      id: null,
      title: null,
      course_id: "",
      classe_id: "",
      venue_id: "",
      starts_at: null,
      ends_at: null,
    });

    // const attendanceFilter = useForm({
    //   status: "present",
    // });

    return {
      filterForm,
      sessionForm,
      isLoadingData: true,
      sessions: [],
      classes: [],

      sessionFormModal: false,
      editContent: false,
      action: "",

      selectedRow: null,
      sessionOpenModal: false,
      sessionDestroyModal: false,

      // students_present: null,
      // students_absent: null,
      // students_present_percentage: null,
      // students_absent_percentage: null,

      // attendanceFilter,
    };
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
        .get(route("lecturer.fetch.sessions"), {
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
    showSessionFormModal(action, data) {
      this.action = action;

      if (this.action === "edit") {
        this.editContent = true;

        Object.assign(this.sessionForm, data);
        this.sessionForm.starts_at = new Date(data.starts_at).toISOString().slice(0, 16)
        this.sessionForm.ends_at = new Date(data.ends_at).toISOString().slice(0, 16)
        this.fetchClass();
      }

      this.sessionFormModal = true;
    },
    hideSessionFormModal() {
      this.sessionFormModal = false;
      this.editContent = false;

      this.sessionForm.reset();
      this.sessionForm.clearErrors();

      this.action = "";
    },
    showSessionOpenModal(data) {
      this.selectedRow = data;

      this.sessionOpenModal = true;
      this.$nextTick(() => {
        let table = $("#attendance_data_table").DataTable();
        table.destroy();
        $("#attendance_data_table").DataTable({
          order: [["0", "desc"]],
        });
      });
    },
    hideSessionOpenModal() {
      this.selectedRow = null;
      this.sessionOpenModal = false;
    },

    showViewSessionModal(data) {
      this.showViewModal = true;
      this.selectedSessionRow = data;

      axios
        .get(route("lecturer.sessions.show.attendance"), {
          params: {
            id: data,
          },
        })
        .then((response) => [
          (this.students_present = response.data.students_present),
          (this.students_absent = response.data.students_absent),
          (this.students_present_percentage = response.data.students_present_percentage),
          (this.students_absent_percentage = response.data.students_absent_percentage),
          this.fetchAttendanceData(),
        ])
        .catch((error) => console.log(error));
    },

    fetchAttendanceData() {
      $("#attendance_table").DataTable({
        destroy: true,
        stateSave: false,
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
      this.showViewModal = false;
      this.selectedSessionRow = null;

      (this.students_present = null),
        (this.students_absent = null),
        (this.students_present_percentage = null),
        (this.students_absent_percentage = null),
        this.attendanceFilter.reset();
    },

    startSession(data) {
      axios
        .post(route("lecturer.sessions.start"), {
          id: data.id,
        })
        .then(toastr.success("Session has started"), this.fetchSessions())
        .catch((error) => console.log(error));
    },
    endSession(data) {
      axios
        .post(route("lecturer.sessions.end"), {
          id: data.id,
        })
        .then(toastr.success("Session has ended"), this.fetchSessions())
        .catch((error) => console.log(error));
    },
    showSessionDestroyModal(data) {
      this.selectedRow = data;
      this.sessionDestroyModal = true;
    },
    hideSessionDestroyModal() {
      this.selectedRow = null;
      this.sessionDestroyModal = false;
    },
    destroySession(data) {
      axios
        .post(route("lecturer.sessions.destroy"), {
          id: data.id,
        })
        .then(
          this.hideSessionDestroyModal(),
          toastr.success("Session deleted"),
          this.fetchSessions()
        )
        .catch((error) => console.log(error));
    },
    fetchClass() {
      axios
        .get(route("lecturer.fetch.sessions.classes"), {
          params: {
            id: this.sessionForm.course_id,
          },
        })
        .then((response) => [(this.classes = response.data.row)])
        .catch((error) => console.log(error));
    },
    submitSession() {
      if (!this.editContent) {
        this.sessionForm.post(route("lecturer.sessions.store"), {
          onSuccess: () => {
            this.hideSessionFormModal();
            toastr.success("Session successfully created");
            this.fetchSessions();
          },
          onError: (errors) => {
            toastr.error("Something went wrong");
          },
        });
      } else {
        this.sessionForm.put(route("lecturer.sessions.update", this.sessionForm.id), {
          onSuccess: () => {
            this.hideSessionFormModal();
            toastr.success("Session successfully updated");
            this.fetchSessions();
          },
          onError: (errors) => {
            toastr.error("Something went wrong");
          },
        });
      }
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
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-9">
          <div class="bg-white shadow-sm rounded-md p-6">
            <div class="flex justify-center">
              <span class="material-symbols-outlined text-4xl lg:text-6xl"> add </span>
            </div>
            <div class="text-center mt-3">
              <p class="font-bold text-md lg:text-lg">New Attendance</p>
            </div>
            <div class="flex justify-center mt-5">
              <PrimaryButton
                type="button"
                @click="showSessionFormModal('add')"
                class="uppercase tracking-widest text-xs"
              >
                create
              </PrimaryButton>
            </div>
          </div>
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
                  class="border border-gray-300 py-3 px-4 flex flex-col md:flex-row md:items-center justify-between rounded-lg shadow-sm"
                >
                  <div
                    class="w-full md:w-11/12 flex flex-col md:flex-row md:items-center gap-x-3 divide-y md:divide-y-0 md:divide-x"
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
                        Class: {{ session.classe.title }}
                      </p>
                      <p class="text-sm overflow-hidden whitespace-nowrap text-ellipsis">
                        Status: {{ session.status }}
                      </p>
                    </div>
                  </div>

                  <div class="mt-3 md:mt-0 flex justify-end md:justify-start">
                    <Dropdown align="right" width="48">
                      <template #trigger>
                        <button
                          type="button"
                          class="px-3 py-2 leading-4 font-medium hover:text-gray-800 focus:outline-none transition ease-in-out duration-150"
                        >
                          <span class="material-symbols-outlined"> more_horiz </span>
                        </button>
                      </template>

                      <template #content>
                        <button
                          @click="startSession(session)"
                          type="button"
                          :disabled="session.status === 'Running'"
                          class="start block w-full px-4 py-2 text-start text-sm leading-5 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out disabled:cursor-not-allowed disabled:opacity-25"
                        >
                          Start
                        </button>

                        <button
                          @click="showSessionFormModal('edit', session)"
                          type="button"
                          class="edit block w-full px-4 py-2 text-start text-sm leading-5 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out disabled:cursor-not-allowed disabled:opacity-25"
                        >
                          Edit
                        </button>

                        <button
                          @click="showSessionOpenModal(session)"
                          type="button"
                          class="open block w-full px-4 py-2 text-start text-sm leading-5 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out disabled:cursor-not-allowed disabled:opacity-25"
                        >
                          Open
                        </button>

                        <button
                          @click="endSession(session)"
                          type="button"
                          :disabled="
                            session.status === 'Scheduled' || session.status === 'Ended'
                          "
                          class="end block w-full px-4 py-2 text-start text-sm leading-5 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out disabled:cursor-not-allowed disabled:opacity-25"
                        >
                          End
                        </button>

                        <button
                          @click="showSessionDestroyModal(session)"
                          type="button"
                          class="delete block w-full px-4 py-2 text-start text-sm leading-5 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out disabled:cursor-not-allowed disabled:opacity-25"
                        >
                          Delete
                        </button>
                      </template>
                    </Dropdown>
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

    <!-- sessions form modal -->
    <Modal
      :show="sessionFormModal"
      :closeable="true"
      :modalTitle="this.action + ' session'"
      @close="hideSessionFormModal"
      :maxWidth="'md'"
    >
      <form @submit.prevent="submitSession" class="mt-4 px-2 pb-2">
        <div class="grid grid-cols-2 gap-5">
          <div class="col-span-full">
            <InputLabel for="title" value="Title" :required="true" />
            <TextInput
              id="title"
              type="text"
              class="w-full"
              v-model="sessionForm.title"
              :placeholder="'Title'"
              autocomplete="title"
              :class="{ 'border-red-600': sessionForm.errors.title }"
            />
            <InputError :message="sessionForm.errors.title" class="mt-2" />
          </div>

          <div class="col-span-full">
            <InputLabel for="course_id" value="Course" :required="true" />
            <SelectInput
              id="course_id"
              v-model="sessionForm.course_id"
              class="w-full"
              @change="fetchClass"
              :class="{ 'border-red-600': sessionForm.errors.course_id }"
            >
              <option value="" disabled>-- Select Course --</option>
              <option v-for="course in courses" :key="course.id" :value="course.id">
                {{ course.code }} {{ course.title }}
              </option>
            </SelectInput>
            <InputError :message="sessionForm.errors.course_id" class="mt-2" />
          </div>

          <div class="col-span-full">
            <InputLabel for="classe_id" value="Class" :required="true" />
            <SelectInput
              id="classe_id"
              v-model="sessionForm.classe_id"
              class="w-full"
              :class="{ 'border-red-600': sessionForm.errors.classe_id }"
            >
              <option value="" selected disabled>-- Select Class --</option>
              <option v-for="classe in this.classes" :key="classe.id" :value="classe.id">
                {{ classe.title }}
              </option>
            </SelectInput>
            <InputError :message="sessionForm.errors.classe_id" class="mt-2" />
          </div>

          <div class="col-span-full">
            <InputLabel for="venue_id" value="Venue" :required="true" />
            <SelectInput
              id="venue_id"
              v-model="sessionForm.venue_id"
              class="w-full"
              :class="{ 'border-red-600': sessionForm.errors.venue_id }"
            >
              <option value="" disabled>-- Select Venue --</option>
              <option v-for="venue in venues" :key="venue.id" :value="venue.id">
                {{ venue.title }}
              </option>
            </SelectInput>
            <InputError :message="sessionForm.errors.venue_id" class="mt-2" />
          </div>

          <div class="col-span-full lg:col-span-1">
            <InputLabel for="starts_at" value="Starts At" :required="true" />
            <TextInput
              id="starts_at"
              type="datetime-local"
              class="w-full"
              v-model="sessionForm.starts_at"
              :min="new Date().toISOString().slice(0, 16)"
              :class="{ 'border-red-600': sessionForm.errors.starts_at }"
            />
            <InputError :message="sessionForm.errors.starts_at" class="mt-2" />
          </div>

          <div class="col-span-full lg:col-span-1">
            <InputLabel for="ends_at" value="Ends At" :required="true" />
            <TextInput
              id="ends_at"
              type="datetime-local"
              class="w-full"
              v-model="sessionForm.ends_at"
              :min="new Date().toISOString().slice(0, 16)"
              :class="{ 'border-red-600': sessionForm.errors.ends_at }"
            />
            <InputError :message="sessionForm.errors.ends_at" class="mt-2" />
          </div>
        </div>

        <PrimaryButton
          type="submit"
          :disabled="sessionForm.processing"
          :class="{ 'opacity-25': sessionForm.processing }"
          class="mt-5 font-semibold tracking-widest text-xs text-white uppercase"
        >
          <div v-if="editContent == false" v-text="'Save'"></div>
          <div v-else v-text="'Update'"></div>
        </PrimaryButton>
      </form>
    </Modal>
    <!-- end of session form modal -->

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
          <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
              <li class="me-2">
                <p
                  class="font-bold text-lg px-4 py-3.5 border-b-2 text-gray-800 border-gray-800"
                >
                  Attendees ({{ this.selectedRow.attendance.length }})
                </p>
              </li>
            </ul>
          </div>
          <div
            v-if="this.selectedRow.attendance.length != 0"
            class="p-4 flex flex-col space-y-4"
          >
            <div class="flex flex-col">
              <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 inline-w-full sm:px-6 lg:px-8">
                  <div class="overflow-x-auto">
                    <table id="attendance_data_table" class="w-full table-striped">
                      <thead class="capitalize border-b bg-gray-100 font-medium">
                        <tr>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Signed At
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Name
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Student ID
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Index Number
                          </th>
                        </tr>
                      </thead>
                      <tbody class="text-sm">
                        <tr v-for="attendance in this.selectedRow.attendance">
                          <td>{{ attendance.signed_at }}</td>
                          <td>{{ attendance.student.name }}</td>
                          <td>{{ attendance.student.student_id }}</td>
                          <td>{{ attendance.student.index_number }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Modal>
    <!-- end of opened session modal -->

    <!-- destroy session modal -->
    <Modal
      :show="sessionDestroyModal"
      :closeable="true"
      :modalTitle="'delete session'"
      @close="hideSessionDestroyModal"
      :maxWidth="'lg'"
    >
      <div class="flex flex-col justify-center space-y-4">
        <!-- Exclamation Icon -->
        <div class="text-center">
          <span class="material-symbols-outlined text-9xl text-gray-800">
            exclamation
          </span>
        </div>

        <!-- Warning Text -->
        <div class="flex justify-center text-gray-800">
          <p class="text-center lg:w-2/3">
            Deleting this session will result in the session being deleted immediately.
            Once deleted, students will not be able to access the session. All records of
            this session will also be deleted. This action is irreversible.
          </p>
        </div>

        <!-- Confirmation Prompt -->
        <div class="flex justify-center">
          <p class="text-lg">Are you sure you want to delete this session?</p>
        </div>

        <!-- Buttons -->
        <div class="flex justify-center mt-6 gap-4">
          <!-- Delete Button -->
          <PrimaryButton
            @click="destroySession(this.selectedRow)"
            type="button"
            class="text-xs tracking-widest uppercase"
          >
            Yes
          </PrimaryButton>

          <!-- Cancel Button -->
          <button
            @click="hideSessionDestroyModal"
            type="button"
            class="px-4 py-2 bg-gray-500 text-white text-xs uppercase font-semibold rounded-md tracking-widest hover:bg-gray-400 focus:bg-gray-500 active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Cancel
          </button>
        </div>
      </div>
    </Modal>

    <!-- end of destroy session modal-->
  </AuthenticatedLayout>
</template>
