<template>
  <div>
    <md-table v-model="results" :table-header-color="tableHeaderColor">
      <md-table-row slot="md-table-row" slot-scope="{ item }">
        <md-table-cell md-label="Endpoint">{{ item.endpoint }}</md-table-cell>
        <md-table-cell md-label="IP Address">{{ item.ipaddress }}</md-table-cell>
        <md-table-cell md-label="#Alerts">{{ item.alerts }}</md-table-cell>
        <md-table-cell md-label="#Warnings">{{ item.warnings }}</md-table-cell>
        <md-table-cell md-label="#Notices">{{ item.notices }}</md-table-cell>
        <md-table-cell md-label="Controls"><a v-if='item.controls > 0' v-bind:href="'/#/results/show/'+item.controls"><md-button class="md-raised md-success">VIEW</md-button></a></md-table-cell>
      </md-table-row>
    </md-table>
  </div>
</template>

<script>
export default {
  name: "top-ten-table",
  props: {
    tableHeaderColor: {
      type: String,
      default: ""
    },
    results: {
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
