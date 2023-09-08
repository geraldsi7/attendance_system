<script>
import { ref } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import SidebarNavLink from "@/Components/SidebarNavLink.vue";
import { Link } from "@inertiajs/inertia-vue3";

export default {
  components: {
    ApplicationLogo,
    SidebarNavLink,
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
  <div class="sidebar" data-color="white">
    <!--
    Tip 1: You can change the color of the sidebar using: data-color="blue | green | orange | red | yellow"
-->
    <div class="logo">
      <Link :href="route('dashboard')">
        <ApplicationLogo class="mx-auto" />
      </Link>
    </div>

    <div class="sidebar-wrapper" id="sidebar-wrapper">
      <ul class="nav">
        <SidebarNavLink
          :href="route('student.dashboard')"
          :active="route().current('student.dashboard')"
          :icon="'now-ui-icons design_app'"
        >
          Dashboard
        </SidebarNavLink>

        <SidebarNavLink
          :href="route('dashboard')"
          :active="route().current('dashboard')"
        >
          Dashboard 2
        </SidebarNavLink>

        <SidebarNavLink
          class="profile"
          :href="route('dashboard')"
          :active="route().current('dashboard')"
          :icon="'now-ui-icons users_single-02'"
          >My Profile
        </SidebarNavLink>

        <SidebarNavLink
          class="logout"
          :href="route('logout')"
          @click="
            event.preventDefault();
            document.getElementById('logout-form').submit();
          "
          :active="route().current('logout')"
          :icon="'now-ui-icons media-1_button-power'"
          >logout
        </SidebarNavLink>
      </ul>
    </div>
  </div>
</template>