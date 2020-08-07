<template>
  <div class="content">
    <notifications></notifications>
    <div class="md-layout md-alignment-center-center">

      <div
        class="md-layout-item md-large-size-100 md-xsmall-size-100 md-size-100 text-center"
      >
        <h1>LokiX</h1>
      </div>

      <div
        class="md-layout-item md-medium-size-50 md-xsmall-size-100 md-size-50 text-center"
      >
        <md-card>
          <md-card-header data-background-color="green">
            <h4 class="title">
              <md-icon>login</md-icon>
              LokiX Platform Login
              </h4>
          </md-card-header>
          <md-card-content>
            <md-progress-spinner v-if='show_spinner'  md-mode="indeterminate"/>
            <div class="md-layout-item md-medium-size-100 md-size-100">
            <md-field>
              <label>Email Address</label>
              <md-input v-model="payload.email" type="email"></md-input>
            </md-field>
          </div>
          <div class="md-layout-item md-medium-size-100 md-size-100">
            <md-field>
              <label>Password</label>
              <md-input v-model="payload.password" type="password" @keyup.enter='handleLogin'></md-input>
            </md-field>
          </div>
          <div class="md-layout-item md-size-100 text-center">
            <md-button class="md-raised md-success" @click='handleLogin'>Login</md-button>
          </div>
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
    handleLogin(){
      this.show_spinner = true;
      this.axios.defaults.withCredentials = true;
      this.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
      this.axios.get(this.PLATFORM_URL+'/sanctum/csrf-cookie').then(response => {
        // Login...
        this.axios.post(this.PLATFORM_URL+'/api/login', this.payload).then(response => {
            if(response.data.state == 'failed'){
              this.notifyVue(response.data.reason, 'danger', 'error');
            } else {
              this.notifyVue('Successfully loggen in.', 'success', 'success');
              this.$router.push('dashboard');
            }
            this.show_spinner = false;
        });
      });
    }
  },
  mounted(){
    this.axios.get(this.PLATFORM_URL+'/api/check/heartbeat');
  },
  data() {
    return {
      show_spinner: false,
      payload: {
        email: '',
        password: ''
      },
      
    };
  }
};
</script>
