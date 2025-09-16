<script>
import { ref } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/inertia-vue3";

export default {
  components: {
    ApplicationLogo,
    Dropdown,
    DropdownLink,
    NavLink,
    ResponsiveNavLink,
    Link,
  },
  data() {
    const showingNavigationDropdown = ref(false);

    return {
      showingNavigationDropdown,
    };
  },
};
</script>

<template>
  <div>
    <div class="min-h-screen bg-gray-100">
      <nav class="bg-white border-b border-gray-100">
        <!-- Primary Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex">
              <!-- Logo -->
              <div class="shrink-0 flex items-center">
                <Link :href="route('admin.dashboard')">
                  <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                </Link>
              </div>

              <!-- Navigation Links -->
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink
                  :href="route('admin.dashboard')"
                  :active="route().current('admin.dashboard')"
                >
                  Dashboard
                </NavLink>

                <div class="hidden sm:flex sm:items-center sm:ms-10">
                  <!-- Setup Dropdown -->
                  <div class="relative">
                    <Dropdown align="right" width="48">
                      <template #trigger>
                        <span class="inline-flex rounded-md">
                          <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                          >
                            Setup & Configuration
                            <svg
                              class="ms-2 -me-0.5 h-4 w-4"
                              xmlns="http://www.w3.org/2000/svg"
                              viewBox="0 0 20 20"
                              fill="currentColor"
                            >
                              <path
                                fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                              />
                            </svg>
                          </button>
                        </span>
                      </template>

                      <template #content>
                        <DropdownLink
                          :href="route('admin.index')"
                          :active="route().current('admin.index')"
                        >
                          Administrators
                        </DropdownLink>
                        <DropdownLink
                          :href="route('lecturer.index')"
                          :active="route().current('lecturer.index')"
                        >
                          Lecturers
                        </DropdownLink>
                        <DropdownLink
                          :href="route('student.index')"
                          :active="route().current('student.index')"
                        >
                          Students
                        </DropdownLink>
                        <DropdownLink
                          :href="route('department.index')"
                          :active="route().current('department.index')"
                        >
                          Departments
                        </DropdownLink>
                        <DropdownLink
                          :href="route('level.index')"
                          :active="route().current('level.index')"
                        >
                          Levels
                        </DropdownLink>
                        <DropdownLink
                          :href="route('section.index')"
                          :active="route().current('section.index')"
                        >
                          Sections
                        </DropdownLink>
                        <DropdownLink
                          :href="route('classe.index')"
                          :active="route().current('classe.index')"
                        >
                          Classes
                        </DropdownLink>
                        <DropdownLink
                          :href="route('course.index')"
                          :active="route().current('course.index')"
                        >
                          Courses
                        </DropdownLink>
                        <DropdownLink
                          :href="route('venue.index')"
                          :active="route().current('venue.index')"
                        >
                          Venues
                        </DropdownLink>
                      </template>
                    </Dropdown>
                  </div>
                </div>
              </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
              <!-- Settings Dropdown -->
              <div class="ml-3 relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                      >
                        {{ $page.props.auth.user.name }}

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <DropdownLink :href="route('admin.profile.edit')">
                      Profile
                    </DropdownLink>
                    <DropdownLink :href="route('admin.logout')" method="post" as="button">
                      Log Out
                    </DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
              >
                <svg
                  class="h-6 w-6"
                  stroke="currentColor"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
          :class="{
            block: showingNavigationDropdown,
            hidden: !showingNavigationDropdown,
          }"
          class="sm:hidden"
        >
          <div class="pt-2 pb-3 space-y-1">
            <ResponsiveNavLink
              :href="route('admin.dashboard')"
              :active="route().current('admin.dashboard')"
            >
              Dashboard
            </ResponsiveNavLink>
          </div>

          <!-- Responsive Setup Options -->
          <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
              <div class="font-medium text-base text-gray-800">Setup & Configuration</div>
            </div>

            <div class="mt-3 space-y-1">
                <ResponsiveNavLink
                          :href="route('admin.index')"
                          :active="route().current('admin.index')"
                        >
                          Administrators
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                          :href="route('lecturer.index')"
                          :active="route().current('lecturer.index')"
                        >
                          Lecturers
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                          :href="route('student.index')"
                          :active="route().current('student.index')"
                        >
                          Students
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                          :href="route('department.index')"
                          :active="route().current('department.index')"
                        >
                          Departments
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                          :href="route('level.index')"
                          :active="route().current('level.index')"
                        >
                          Levels
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                          :href="route('section.index')"
                          :active="route().current('section.index')"
                        >
                          Sections
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                          :href="route('classe.index')"
                          :active="route().current('classe.index')"
                        >
                          Classes
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                          :href="route('course.index')"
                          :active="route().current('course.index')"
                        >
                          Courses
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                          :href="route('venue.index')"
                          :active="route().current('venue.index')"
                        >
                          Venues
                        </ResponsiveNavLink>
            </div>
          </div>

          <!-- Responsive Settings Options -->
          <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
              <div class="font-medium text-base text-gray-800">
                {{ $page.props.auth.user.name }}
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('admin.profile.edit')">
                Profile
              </ResponsiveNavLink>
              <ResponsiveNavLink :href="route('admin.logout')" method="post" as="button">
                Log Out
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white shadow" v-if="$slots.header">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main>
        <slot />
      </main>
    </div>
  </div>
</template>
