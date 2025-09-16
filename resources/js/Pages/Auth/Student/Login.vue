<script>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/inertia-vue3";

export default {
  components: {
    Checkbox,
    GuestLayout,
    InputError,
    InputLabel,
    PrimaryButton,
    TextInput,
    Head,
    Link,
    useForm,
    usePage
  },

  props: {
    canResetPassword: Boolean,
    status: String,
  },

  data() {
    const form = useForm({
      student_id: "",
      password: "",
      remember: false,
    });

    return {
      form,
      page: usePage(),
      error: ''
            
    };
  },

  mounted() {
     this.error = this.page.props.flash.error
     if(this.error){
        toastr.error(this.error)
     }
  },
  methods: {
    submit() {
      this.form.post(route("student.login"), {
        onFinish: () => this.form.reset("password"),
      });
    },
  }
};
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <TextInput
          id="student_id"
          type="text"
          class="mt-1 block w-full"
          v-model="form.student_id"
          autofocus
          autocomplete="student_id"
          placeholder="Student ID"
          required
          :class="{ 'border-red-600': form.errors.student_id }"
        />

        <InputError class="mt-2" :message="form.errors.student_id" />
      </div>

      <div class="mt-5">
        <TextInput
          id="password"
          type="password"
          class="mt-1 block w-full"
          v-model="form.password"
          autocomplete="current-password"
          placeholder="Password"
          required
        />

        <InputError class="mt-2" :message="form.errors.password" />
      </div>

     <!--
       <div class="block mt-4">
        <label class="flex items-center">
          <Checkbox name="remember" v-model:checked="form.remember" />
          <span class="ml-2 text-sm text-gray-600">Remember me</span>
        </label>
      </div>

    -->

      <div class="flex items-center justify-end mt-4">
       <Link
          v-if="canResetPassword"
          :href="route('student.password.request')"
          class="text-sm text-gray-700 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
        >
          Forgot your password?
        </Link>

     

        <PrimaryButton
          class="ml-4 font-semibold text-xs text-white uppercase tracking-widest"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Log in
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
