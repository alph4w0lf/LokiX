<template>
  <div>
    <md-table v-model="users" :table-header-color="tableHeaderColor">
      <md-table-row slot="md-table-row" slot-scope="{ item }">
        <md-table-cell md-label="Full Name">{{ item.fullname }}</md-table-cell>
        <md-table-cell md-label="Email">{{ item.email }}</md-table-cell>
        <md-table-cell md-label="Last Login"><span v-if='item.last_login == null'>N/A</span>{{ get_date(item.last_login) }}</md-table-cell>
        <md-table-cell md-label="Controls"><md-button v-if='item.controls > 0' class="md-mini md-danger md-icon-button" title="Delete User" @click='delete_user(item.controls)'><md-icon>delete</md-icon></md-button></md-table-cell>
      </md-table-row>
    </md-table>
  </div>
</template>

<script>
// API and Authentication Connector Mixin
import Api from "../../api";

export default {
  name: "users-table",
  mixins: [Api],
  methods: {
    delete_user(user_id){
      this.axios.defaults.withCredentials = true;
      this.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
      this.axios.get(this.PLATFORM_URL+'/sanctum/csrf-cookie').then(response => {
        this.axios.post(this.PLATFORM_URL+'/api/delete_user/'+user_id).then(response => {
            if(response.data.state == 'failed'){
              this.$root.$emit('show_msg', response.data.reason, 'danger', 'error');
            } else {
              this.$root.$emit('show_msg', 'User deleted successfully.', 'success', 'success');
              this.$root.$emit('refresh_users_list');
            }
        });
      });
    },
    get_date(utc_date){
      // Convert from UTC to User timezone based on the one configured for the browser
      return new Date(utc_date+" UTC").toLocaleString();
    }
  },
  props: {
    tableHeaderColor: {
      type: String,
      default: ""
    },
    users: {
      type: Array,
    }
  },
  data() {
    return {
      selected: [],
    };
  }
};
</script>
