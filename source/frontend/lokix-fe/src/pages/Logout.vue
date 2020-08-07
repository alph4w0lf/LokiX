<template>
  <div class="content">
    <div class="md-layout">



      <div
        class="md-layout-item md-medium-size-100 md-xsmall-size-100 md-size-100 text-center"
      >
        <md-card>
          <md-card-header data-background-color="green">
            <h4 class="title">
              Logout in progress...
              </h4>
          </md-card-header>
          <md-card-content>
            <md-progress-spinner md-mode="indeterminate"/>
          </md-card-content>
        </md-card>
      </div>



    </div>
  </div>
</template>

<script>
// API and Authentication Connector Mixin
import Api from "../api";

export default {
  mixins: [Api],
  methods: {
  logout_user(){
    this.axios.defaults.withCredentials = true;
    this.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    this.axios.get(this.PLATFORM_URL+'/sanctum/csrf-cookie').then(response => {
      this.axios.post(this.PLATFORM_URL+'/api/logout')
      .then(response => {
        this.$router.push('login');
      })
      .catch(e => {
        this.$router.push('login');
      });
    });
  }
  },
  mounted(){
    this.logout_user();
  }
};
</script>
