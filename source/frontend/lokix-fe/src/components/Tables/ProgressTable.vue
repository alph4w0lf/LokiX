<template>
  <div>
    <md-table v-model="results" :table-header-color="tableHeaderColor">
      <md-table-row slot="md-table-row" slot-scope="{ item }">
        <md-table-cell md-label="Endpoint">{{ item.hostname }}</md-table-cell>
        <md-table-cell md-label="IP Address">{{ item.ip_address }}</md-table-cell>
        <md-table-cell md-label="Scan Start">{{ get_date(item.scan_start) }}</md-table-cell>
        <md-table-cell md-label="Time Passed"><span v-if='item.ipaddress != ""'>{{ time_passed(item.scan_start) }}</span></md-table-cell>
      </md-table-row>
    </md-table>
  </div>
</template>

<script>
export default {
  name: "progress-table",
  props: {
    tableHeaderColor: {
      type: String,
      default: ""
    },
    results: {
      type: Array,
    }
  },
  methods:{
    get_date(utc_date){
      // Convert from UTC to User timezone based on the one configured for the browser
      return new Date(utc_date+" UTC").toLocaleString();
    },
    time_passed(start_date){
      // Calculate the time passed since the scan has started
      var dateNow = new Date();
      var dateOld = new Date(start_date+" UTC");
      var seconds = Math.floor((dateNow - (dateOld))/1000);
      var minutes = Math.floor(seconds/60);
      var hours = Math.floor(minutes/60);
      var days = Math.floor(hours/24);
      hours = hours-(days*24);
      minutes = minutes-(days*24*60)-(hours*60);
      seconds = seconds-(days*24*60*60)-(hours*60*60)-(minutes*60);
      var output = '';
      if(hours >= 1){
        output = hours + ' Hours ';
      }
      if(minutes >= 1 || hours >= 1){
        output = output + minutes + ' Minutes ';
      }
      if(hours < 1 && minutes < 1){
        output = output + seconds + ' Seconds';
      }
      return output;
    }
  },
  data() {
    return {
      selected: [],
    };
  }
};
</script>
