<template>
  <div class="content">
    <div class="md-layout">



      <div
        class="md-layout-item md-medium-size-100 md-xsmall-size-100 md-size-100"
      >
        <md-card>
          <md-card-header data-background-color="green">
            <h4 class="title">
              <md-icon>person_add</md-icon>
              Modify User Profile
              </h4>
          </md-card-header>
          <md-card-content>
            <div class="md-layout-item md-medium-size-100 md-size-100">
                <md-field>
                  <label>Fullname</label>
                  <md-input v-model="fullname" type="text"></md-input>
                </md-field>
            </div>
            <div class="md-layout-item md-medium-size-100 md-size-100">
            <md-field>
              <label>Email Address</label>
              <md-input v-model="emailaddress" type="email"></md-input>
            </md-field>
          </div>
          <div class="md-layout-item md-medium-size-100 md-size-100">
            <md-field>
              <label> Current Password</label>
              <md-input v-model="password" type="password"></md-input>
            </md-field>
          </div>
          <div class="md-layout-item md-medium-size-100 md-size-100">
            <md-field>
              <label>New Password</label>
              <md-input v-model="new_password" type="password"></md-input>
            </md-field>
          </div>
          <div class="md-layout-item md-medium-size-100 md-size-100">
            <md-field>
              <label>Confirm New Password</label>
              <md-input v-model="confirm_new_password" type="password"></md-input>
            </md-field>
          </div>
          <div class="md-layout-item md-size-100 text-center">
            <md-button v-if='!show_spinner' class="md-raised md-success" @click='update_profile'>Update</md-button>
            <md-progress-spinner v-if='show_spinner'  md-mode="indeterminate"/>
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
  fetch_profile(){
    this.axios.get(this.PLATFORM_URL+'/api/user_profile')
    .then(response => {
      this.emailaddress = response.data.email;
      this.fullname = response.data.full_name;
    })
    .catch(e => {
      this.checkAuth();
    });
  },
  update_profile(){
    // validate user-input frontend side
    if(this.new_password != this.confirm_new_password || this.new_password == ''){
      this.notifyVue('New Password and Confirm New Password fields do not match', 'danger', 'error');
      return;
    }
    this.show_spinner = true;
    this.axios.defaults.withCredentials = true;
    this.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    this.axios.get(this.PLATFORM_URL+'/sanctum/csrf-cookie').then(response => {
      this.axios.post(this.PLATFORM_URL+'/api/update/profile',
        {
          'full_name' : this.fullname,
          'email' : this.emailaddress,
          'current_password' : this.password,
          'new_password' : this.new_password
        }
      )
      .then(response => {
        if(response.data.state == 'success'){
          this.notifyVue('Profile updated successfully.', 'success', 'success');
        } else {
          this.notifyVue(response.data.reason, 'danger', 'error');
        }
        this.show_spinner = false;
      })
      .catch(e => {
        this.checkAuth();
      });
    });
  }
  },
  beforeMount(){
    this.checkAuth();
  },
  mounted(){
    this.fetch_profile();
  },
  data() {
    return {
        emailaddress: '',
        fullname: '',
        password: '',
        new_password: '',
        confirm_new_password: '',
        show_spinner: false
      };
  }
};
</script>
