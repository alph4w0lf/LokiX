<template>
  <div class="content">
    <div class="md-layout">

      
      
      

      <div
        class="md-layout-item md-medium-size-100 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-header data-background-color="green">
            <h4 class="title">
              <md-icon>people</md-icon>
              Users List
              </h4>
          </md-card-header>
          <md-card-content>
            <users-table table-header-color="green" :users="users_list"></users-table>
          </md-card-content>
        </md-card>
      </div>


      <div
        class="md-layout-item md-medium-size-100 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-header data-background-color="green">
            <h4 class="title">
              <md-icon>person_add</md-icon>
              Add New User
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
              <label>Password</label>
              <md-input v-model="password" type="password"></md-input>
            </md-field>
          </div>
          <div class="md-layout-item md-medium-size-100 md-size-100">
            <md-field>
              <label>Confirm Password</label>
              <md-input v-model="confirm_password" type="password"></md-input>
            </md-field>
          </div>
          <div class="md-layout-item md-size-100 text-center">
            <md-button v-if='!show_spinner' class="md-raised md-success" @click='add_new_user'>Create User</md-button>
            <md-progress-spinner v-if='show_spinner'  md-mode="indeterminate"/>
          </div>
          </md-card-content>
        </md-card>
      </div>



    </div>
  </div>
</template>

<script>
import {
  UsersTable
} from "@/components";
// API and Authentication Connector Mixin
import Api from "../api";

export default {
  components: {
    UsersTable
  },
  mixins: [Api],
  methods: {
  fetch_users_list(){
    this.axios.get(this.PLATFORM_URL+'/api/users_list')
    .then(response => {
      this.users_list = response.data.users;
    })
    .catch(e => {
      this.checkAuth();
    });
  },
  add_new_user(){
      this.show_spinner = true;
      // Frontend side - user input validation
      if(this.password != this.confirm_password){
        this.notifyVue('Password and Confirm Password fields do not match', 'danger', 'error');
        this.show_spinner = false;
        return;
      }
      this.axios.defaults.withCredentials = true;
      this.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
      this.axios.get(this.PLATFORM_URL+'/sanctum/csrf-cookie').then(response => {
        this.axios.post(this.PLATFORM_URL+'/api/add_user', 
          {
            'email' : this.emailaddress,
            'password' : this.password,
            'fullname' : this.fullname
          }
        ).then(response => {
            if(response.data.state == 'failed'){
              this.notifyVue(response.data.reason, 'danger', 'error');
            } else {
              this.notifyVue('New user successfully created.', 'success', 'success');
              this.fetch_users_list();
              this.emailaddress = '';
              this.password = '';
              this.fullname = '';
              this.confirm_password = '';
            }
            this.show_spinner = false;
        });
      });
  }
  },
  beforeMount(){
    this.checkAuth();
  },
  mounted(){
    this.fetch_users_list();
  },
  created(){
    this.$root.$on('show_msg', this.notifyVue);
    this.$root.$on('refresh_users_list', this.fetch_users_list);
  },
  data() {
    return {
        emailaddress: '',
        fullname: '',
        password: '',
        confirm_password: '',
        show_spinner: false,
        users_list: [
          {
            fullname: "Loading...",
            email: "",
            last_login: "",
            controls: ''
          }
        ]
      };
  }
};
</script>
