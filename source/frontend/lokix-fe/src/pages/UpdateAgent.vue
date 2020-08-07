<template>
  <div class="content">
    <div class="md-layout">

      
      
      

      <div
        class="md-layout-item md-large-size-100 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-header data-background-color="green">
            <h4 class="title">
              <md-icon>system_update_alt</md-icon>
              Loki Software Status
              </h4>
          </md-card-header>
          <md-card-content>
            <div class="md-layout-item md-size-100 text-left">
              <p><b>Loki Last Update:</b> {{ update_date }}</p>
              <div v-if='show_error' class="alert alert-danger">
                  <span
                    ><b> LAST UPDATE ERROR: </b> {{ error_msg }}</span
                  >
                </div>
            </div>
            <div v-if='show_progress_bar' class="md-progress-bar md-accent md-buffer md-theme">
              <div class="md-progress-bar-track"></div> 
              <div class="md-progress-bar-fill"></div> 
              <div style="left: calc(10% + 8px)" class="md-progress-bar-buffer"></div>
            </div>
            <div class="md-layout-item md-size-100 text-left">
              <md-progress-spinner v-if='!enable_update && !show_progress_bar'  md-mode="indeterminate"/>
              <md-button v-if='!show_progress_bar && enable_update' class="md-raised md-success" @click='start_update'>Update Loki</md-button>
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
    start_update(){
      // Instruct the platform to update the loki agent
      this.show_progress_bar = true;
      this.update_date = 'Updating ...';
      this.notifyVue('Agent update is started.', 'success', 'success');
      this.axios.defaults.withCredentials = true;
      this.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
      this.axios.get(this.PLATFORM_URL+'/sanctum/csrf-cookie').then(response => {
        // Login...
        this.axios.post(this.PLATFORM_URL+'/api/agent/update').then(response => {
            if(response.data.state == 'failed'){
              this.notifyVue(response.data.reason, 'danger', 'error');
              this.checkAuth();
              this.get_status();
            } else {
              this.show_error = false;
              this.polling = setInterval(() => {
                  this.get_status();
                },10000);
            }
        });
      });
      
    },
    get_date(utc_date){
      // Convert from UTC to User timezone based on the one configured for the browser
      return new Date(utc_date+" UTC").toLocaleString();
    },
    get_status(){
      // Get agent update status
      this.axios.get(this.PLATFORM_URL+'/api/agent/status')
      .then(response => {
        this.update_date = this.get_date(response.data.last_update);
        this.is_ready = response.data.is_ready;
        this.error_msg = response.data.last_error;
        if(this.update_date == null){
          this.update_date = 'N/A - An update is required!';
        }
        if(response.data.is_updating > 0){
          this.show_progress_bar = true;
          this.update_date = 'Updating ...';
        } else {
          this.show_progress_bar = false;
          this.enable_update = true;
          clearInterval(this.polling);
        }
        if(!this.is_ready && !this.show_progress_bar && this.error_msg != null){
          this.show_error = true;
        }

      })
      .catch(e => {
        this.checkAuth();
      });
    },
  },
  beforeMount(){
    this.checkAuth();
  },
  mounted(){
    this.get_status();
    this.polling = setInterval(() => {
      this.get_status();
    },10000);
  },
  data() {
    return {
        update_date: "Loading...",
        show_progress_bar: false,
        error_msg: '',
        show_error: false,
        enable_update: false,
        is_ready: false,
        polling: null
      };
  }
};
</script>
