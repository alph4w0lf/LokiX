<template>
  <div class="content">
    <div class="md-layout">

      
      
      <div
        class="md-layout-item md-medium-size-50 md-xsmall-size-100 md-size-25"
      >
        <stats-card data-background-color="green">
          <template slot="header">
            <md-icon>devices</md-icon>
          </template>

          <template slot="content">
            <p class="category">Total Endpoints</p>
            <h3 class="title">{{ stats.total_endpoints }}</h3>
          </template>
        </stats-card>
      </div>
      <div
        class="md-layout-item md-medium-size-50 md-xsmall-size-100 md-size-25"
      >
        <stats-card data-background-color="blue">
          <template slot="header">
            <md-icon>check_box</md-icon>
          </template>

          <template slot="content">
            <p class="category">Completed Scans</p>
            <h3 class="title"><a href="#/completed" style="color:black">{{ stats.completed_scans }}</a></h3>
          </template>

        </stats-card>
      </div>
      <div
        class="md-layout-item md-medium-size-50 md-xsmall-size-100 md-size-25"
      >
        <stats-card data-background-color="orange">
          <template slot="header">
            <md-icon>hourglass_bottom</md-icon>
          </template>

          <template slot="content">
            <p class="category">Running Scans</p>
            <h3 class="title"><a href="#/progress" style="color:black">{{ stats.running_scans }}</a></h3>
          </template>
        </stats-card>
      </div>
      <div
        class="md-layout-item md-medium-size-50 md-xsmall-size-100 md-size-25"
      >
        <stats-card data-background-color="red">
          <template slot="header">
            <md-icon>error</md-icon>
          </template>

          <template slot="content">
            <p class="category">Failed Scans</p>
            <h3 class="title"><a href="#/failed" style="color:black">{{ stats.failed_scans }}</a></h3>
          </template>
        </stats-card>
      </div>

      <div
        class="md-layout-item md-large-size-100 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-header data-background-color="red">
            <h4 class="title">
              <md-icon>bug_report</md-icon>
              Top Infected
              </h4>
            <p class="category">Top 10 Endpoints Only</p>
          </md-card-header>
          <md-card-content>
            <top-ten-table table-header-color="green" :results="infected_list"></top-ten-table>
          </md-card-content>
        </md-card>
      </div>

      <div
        class="md-layout-item md-large-size-100 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-header data-background-color="orange">
            <h4 class="title">
              <md-icon>warning</md-icon>
              Top Suspected
              </h4>
            <p class="category">Top 10 Endpoints Only</p>
          </md-card-header>
          <md-card-content>
            <top-ten-table table-header-color="green" :results="suspected_list"></top-ten-table>
          </md-card-content>
        </md-card>
      </div>

    </div>
  </div>
</template>

<script>
import {
  StatsCard,
  TopTenTable
} from "@/components";
// API and Authentication Connector Mixin
import Api from "../api";

export default {
  components: {
    StatsCard,
    TopTenTable
  },
  mixins: [Api],
  methods: {

  },
  beforeMount(){
    this.checkAuth();
  },
  mounted(){
    // Force the platform to identify non-responsive agents
    this.axios.get(this.PLATFORM_URL+'/api/check/heartbeat');

    // Get dashboard stats
    this.axios.get(this.PLATFORM_URL+'/api/dashboard_stats')
    .then(response => {
      this.stats.total_endpoints = response.data.total_endpoints;
      this.stats.completed_scans = response.data.completed_scans;
      this.stats.running_scans = response.data.running_scans;
      this.stats.failed_scans = response.data.failed_scans;
    })
    .catch(e => {
      this.checkAuth();
    });
    // Get dashboard top ten
    this.axios.get(this.PLATFORM_URL+'/api/top_infected')
    .then(response => {
      if(response.data.top_infected.length < 1){
        this.infected_list[0].endpoint = 'N/A';
      } else {
        this.infected_list = response.data.top_infected;
      } 
    })
    .catch(e => {
      this.checkAuth();
    });
    this.axios.get(this.PLATFORM_URL+'/api/top_suspected')
    .then(response => {
      if(response.data.top_suspected.length < 1){
        this.suspected_list[0].endpoint = 'N/A';
      } else {
        this.suspected_list = response.data.top_suspected;
      } 
    })
    .catch(e => {
      this.checkAuth();
    });
  },
  data() {
    return {
        stats:
          {
            total_endpoints: '',
            completed_scans: '',
            running_scans: '',
            failed_scans: ''
          },
        infected_list: [
        {
          endpoint: "Loading",
          ipaddress: "",
          alerts: "",
          warnings: "",
          notices: "",
          controls: ""
        }   
      ],
      suspected_list: [
        {
          endpoint: "Loading",
          ipaddress: "",
          alerts: "",
          warnings: "",
          notices: "",
          controls: ""
        }   
      ]
      };
  }
};
</script>
