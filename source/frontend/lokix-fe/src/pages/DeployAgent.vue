<template>
  <div class="content">
    <div class="md-layout">

      
      
      

      <div
        class="md-layout-item md-large-size-100 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-header data-background-color="green">
            <h4 class="title">
              <md-icon>in</md-icon>
              Manual Agent Deployment
              </h4>
          </md-card-header>
          <md-card-content>
            <div class="md-layout md-size-100 text-left">
              <p><b><br />STEP 1:</b> Download LokiX Agent</p>
            </div>
            <div class="md-layout-item md-size-100 text-left">
              <md-button class="md-raised md-success" :href="this.PLATFORM_URL+'/api/agent/download'" target="_blank">Download</md-button>
            </div>

            <div class="md-layout md-size-100 text-left">
              <p><b><br />STEP 2:</b> Deploy the agent using <b>one</b> OR <b>more</b> of the following options:</p>
            </div>

            <div class="md-layout-item md-size-100 text-left ml-md-3">
              <p><b class="text-success">OPTION 1:</b> Copy the agent to the filesystem of the endpoint you would like to scan, then execute it.</p>
            </div>
            <div class="md-layout-item md-size-100 text-left">
              <p><b class="text-success">OPTION 2:</b> Deploy the agent using (<a class="text-success" href="https://docs.microsoft.com/en-us/mem/configmgr/apps/get-started/create-and-deploy-an-application" target="_blank">SCCM</a>) to the endpoints you would like to scan.</p>
            </div>
            <div class="md-layout-item md-size-100 text-left">
              <p><b class="text-success">OPTION 3:</b> Deploy the agent using (<a class="text-success" href="https://success.trendmicro.com/solution/1122330-methods-of-deploying-through-group-policy-object-gpo" target="_blank">Windows Group Policy</a>) to the endpoints you would like to scan.</p>
            </div>

            <div class="md-layout md-size-100 text-left">
              <p><b><br />NOTE:</b> The agent will auto-delete itself and all its files from the system after scanning is finished.</p>
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
  beforeMount(){
    this.checkAuth();
  },
};
</script>
