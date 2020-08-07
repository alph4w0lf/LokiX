<template>
  <div class="wrapper" :class="{ 'nav-open': $sidebar.showSidebar }">
    <notifications></notifications>

    <side-bar
      :sidebar-item-color="sidebarBackground"
      :sidebar-background-image="sidebarBackgroundImage"
    >
      <mobile-menu slot="content"></mobile-menu>
      <sidebar-link to="/dashboard">
        <md-icon>dashboard</md-icon>
        <p>Dashboard</p>
      </sidebar-link>
      <sidebar-link to="/update/agent">
        <md-icon>system_update_alt</md-icon>
        <p>Update Agent</p>
      </sidebar-link>
      <sidebar-link to="/deploy/agent">
        <md-icon>import_export</md-icon>
        <p>Deploy Agent</p>
      </sidebar-link>
      <sidebar-link to="/completed">
        <md-icon>check_box</md-icon>
        <p>Completed Scans</p>
      </sidebar-link>
      <sidebar-link to="/users">
        <md-icon>person_add</md-icon>
        <p>User Management</p>
      </sidebar-link>
      <sidebar-link to="/user">
        <md-icon>person</md-icon>
        <p>User Profile</p>
      </sidebar-link>
      <sidebar-link to='/logout'>
        <md-icon>logout</md-icon>
        <p>Logout</p>
      </sidebar-link>
    </side-bar>

    <div class="main-panel">
      <top-navbar></top-navbar>

      <dashboard-content> </dashboard-content>

      <content-footer v-if="!$route.meta.hideFooter"></content-footer>
    </div>
  </div>
</template>

<script>
import TopNavbar from "./TopNavbar.vue";
import ContentFooter from "./ContentFooter.vue";
import DashboardContent from "./Content.vue";
import MobileMenu from "@/pages/Layout/MobileMenu.vue";

export default {
  components: {
    TopNavbar,
    DashboardContent,
    ContentFooter,
    MobileMenu
  },
  data() {
    return {
      sidebarBackground: "green",
      sidebarBackgroundImage: require("@/assets/img/side-bg2.jpg")
    };
  }
};
</script>
