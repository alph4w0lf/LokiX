<style>

.custom-paginate {
  list-style-type: none;
}
.custom-paginate li {
  display:inline !important;
}

</style>
<template>
  <div class="content">
    <div class="md-layout">


      <div
        class="md-layout-item md-large-size-100 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-content>
            <h4 class="title">
              <md-icon>search</md-icon>
              Search
              </h4>
            <md-field>
                  <label>Search Text</label>
                  <md-input v-model="search_text" type="text" @keyup.enter='searchPage(1)'></md-input>
                </md-field>
                <md-button class="md-raised md-success" @click='searchPage(1)'>Search</md-button>
                <md-button class="md-raised md-grey" style='margin-left:3px' @click='clearSearch'>Clear</md-button>
          </md-card-content>
        </md-card>
      </div>

      <div
        class="md-layout-item md-large-size-100 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-header data-background-color="green">
            <h4 class="title">
              <md-icon>error</md-icon>
              Failed Scans
              </h4>
            <p class="category">List of all endpoints with failed loki scans</p>
          </md-card-header>
          <md-card-content>
            <failed-table table-header-color="green" :results="failed_list"></failed-table>
            <div v-if='show_spinner' class='text-center'>
              <md-progress-spinner md-mode="indeterminate"/>
            </div>
            <md-divider></md-divider>
            <md-divider></md-divider>
            <paginate
              :page-count="pages"
              :click-handler="displayPage"
              :prev-text="'Prev'"
              :next-text="'Next'"
              :container-class="'pagination custom-paginate'"
              v-if='pages > 1'
              >
            </paginate>
            <p><b>Total Results: </b>{{ total_results }}</p>
            <p><b>Show per page: </b>{{ results_per_page }}</p>
          </md-card-content>
        </md-card>
      </div>

    </div>
  </div>
</template>

<script>
import {
  FailedTable,
} from "@/components";
// API and Authentication Connector Mixin
import Api from "../api";

export default {
  components: {
    FailedTable,
  },
  mixins: [Api],
  methods: {
    displayPage(PageNum) {
      if(this.search_active){
        this.searchPage(PageNum);
        return;
      }
      this.progress_list = [
        {
          hostname: "",
          ip_address: "",
          scan_start: "",
          scan_end: "",
          reason: ""
        }
      ];
      this.show_spinner = true;
      this.axios.get(this.PLATFORM_URL+'/api/failed/scans?page='+PageNum)
      .then(response => {
        this.show_spinner = false;
        this.current_page = response.data.current_page;
        this.results_per_page = response.data.per_page;
        this.total_results = response.data.total;
        this.pages = response.data.last_page;
        this.failed_list = response.data.data;
      })
      .catch(e => {
        this.checkAuth();
      });
    },
    clearSearch(){
      this.search_text = '';
      this.search_active = false;
      this.displayPage(1);
    },
    searchPage(PageNum){
      if(this.search_text == ''){
        this.search_active = false;
        this.displayPage(1);
        return;
      }
      this.failed_list = [
        {
          hostname: "",
          ip_address: "",
          scan_start: "",
          scan_end: "",
          reason: ""
        }
      ];
      this.show_spinner = true;
      this.search_active = true;
      this.axios.defaults.withCredentials = true;
      this.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
      this.axios.get(this.PLATFORM_URL+'/sanctum/csrf-cookie').then(response => {
      this.axios.post(this.PLATFORM_URL+'/api/search/failed/scans?page='+PageNum, 
        {
          'search' : this.search_text
        }
      )
        .then(response => {
          this.show_spinner = false;
          this.current_page = response.data.current_page;
          this.results_per_page = response.data.per_page;
          this.total_results = response.data.total;
          this.pages = response.data.last_page;
          this.failed_list = response.data.data;
        })
        .catch(e => {
          this.$router.push('login');
        });
      });
    }
  },
  beforeMount(){
    this.checkAuth();
  },
  mounted(){
    this.displayPage(1);
  },
  data() {
    return {
        search_active: false,
        search_text: "",
        total_results: 0,
        results_per_page: 0,
        current_page: 1,
        pages: 1,
        show_spinner: true,
        failed_list: [
        {
          hostname: "",
          ip_address: "",
          scan_start: "",
          scan_end: "",
          reason: ""
        }
      ]
      };
  }
};
</script>
