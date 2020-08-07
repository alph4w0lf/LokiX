<template>
  <div>
    <md-table v-model="results" :table-header-color="tableHeaderColor">
      <md-table-row slot="md-table-row" slot-scope="{ item }">
        <md-table-cell md-label="Endpoint">{{ item.hostname }}</md-table-cell>
        <md-table-cell md-label="IP Address">{{ item.ip_address }}</md-table-cell>
        <md-table-cell md-label="Scan Start">{{ get_date(item.scan_start) }}</md-table-cell>
        <md-table-cell md-label="Scan Failed">{{ get_date(item.scan_end) }}</md-table-cell>
        <md-table-cell md-label="Reason"><b class="text-danger">{{ item.reason }}</b></md-table-cell>
      </md-table-row>
    </md-table>
  </div>
</template>

<script>
export default {
  name: "failed-table",
  props: {
    tableHeaderColor: {
      type: String,
      default: ""
    },
    results: {
      type: Array,
    }
  },
  methods: {
    get_date(utc_date){
      // Convert from UTC to User timezone based on the one configured for the browser
      return new Date(utc_date+" UTC").toLocaleString();
    },
  },
  data() {
    return {
      selected: [],
    };
  }
};
</script>
