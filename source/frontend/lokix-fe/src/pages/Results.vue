<style>

.custom-paginate {
  list-style-type: none;
}
.custom-paginate li {
  display:inline !important;
}

.alert_style {
  color: white;
  background-color: #9a092c;
  padding: 3px;
  font-weight: bold;
}
.warning_style {
  color: white;
  background-color: #b2a006;
  padding: 3px;
  font-weight: bold;
}
.notice_style {
  color: white;
  background-color: #338481;
  padding: 3px;
  font-weight: bold;
}
.loki_important {
  font-weight: bold;
}
</style>
<template>
  <div class="content">
    <div class="md-layout">


      <div
        class="md-layout-item md-large-size-30 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-content>
            <h4 class="title">
              <md-icon>computer</md-icon>
              Endpoint
              </h4>
            <b>{{ endpoint }}</b>
          </md-card-content>
        </md-card>
      </div>

      <div
        class="md-layout-item md-large-size-25 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-content>
            <h4 class="title">
              <md-icon>home</md-icon>
              IP Address
              </h4>
            <b>{{ ipaddress }}</b>
          </md-card-content>
        </md-card>
      </div>

      <div
        class="md-layout-item md-large-size-15 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-content>
            <h4 class="title">
              <md-icon>bug_report</md-icon>
              #Alerts
              </h4>
            <b>{{ alert }}</b>
          </md-card-content>
        </md-card>
      </div>

      <div
        class="md-layout-item md-large-size-15 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-content>
            <h4 class="title">
              <md-icon>warning</md-icon>
              #Warnings
              </h4>
            <b>{{ warn }}</b>
          </md-card-content>
        </md-card>
      </div>

      <div
        class="md-layout-item md-large-size-15 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-content>
            <h4 class="title">
              <md-icon>info</md-icon>
              #Notices
              </h4>
            <b>{{ notice }}</b>
          </md-card-content>
        </md-card>
      </div>

      <div
        class="md-layout-item md-large-size-100 md-xsmall-size-100 md-size-50"
      >
        <md-card>
          <md-card-header data-background-color="green">
            <h4 class="title">
              Loki Scan Output
              </h4>
          </md-card-header>
          <md-card-content>
            <div class='text-center'>
              <md-progress-spinner v-if='show_spinner' md-mode="indeterminate"/>
            </div>
            <p v-html="loki_output"></p>
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
    display_results() {
      this.axios.get(this.PLATFORM_URL+'/api/results/'+this.$route.params.id)
      .then(response => {
        if(response.data.state == 'failed'){
          this.show_spinner = false;
          this.loki_output = response.data.reason;
        } else {
          this.endpoint = response.data.endpoint;
          this.ipaddress = response.data.ip_address;
          this.alert = response.data.alerts;
          this.warn = response.data.warnings;
          this.notice = response.data.notices;
          this.show_spinner = false;
          this.loki_output = this.sanitize(response.data.results);
          // Add new lines
          this.loki_output = this.loki_output.replace(/[0-9]{8}[A-Z][0-9]{2}:[0-9]{2}:[0-9]{2}[A-Z]/g, '<br />');
          // Highlight alerts
          this.loki_output = this.loki_output.replace(/Alert:/g, '<label class="alert_style">[Alert]</label>');
          this.loki_output = this.loki_output.replace(/Warning:/g, '<label class="warning_style">[Warning]</label>');
          this.loki_output = this.loki_output.replace(/Notice:/g, '<label class="notice_style">[Notice]</label>');
          // Highlight important parts of the results
          //var report_sections = ['FILE:', 'SCORE:', 'TYPE:', 'MD5:', 'SHA1:', 'SHA256:', 'CREATED:', 'MODIFIED:', 'ACCESSED:', 'REASON_', 'MATCH:', 'DESCRIPTION:', 'MATCHES:', 'DESC:'];
          // Doing this the stupid way because a loop didn't work - optimize it in the next release ******
          this.loki_output = this.loki_output.replace(/FILE:/g, '<label class="loki_important">FILE:</label>');
          this.loki_output = this.loki_output.replace(/SCORE:/g, '<label class="loki_important">SCORE:</label>');
          this.loki_output = this.loki_output.replace(/TYPE:/g, '<label class="loki_important">TYPE:</label>');
          this.loki_output = this.loki_output.replace(/MD5:/g, '<label class="loki_important">MD5:</label>');
          this.loki_output = this.loki_output.replace(/SHA1:/g, '<label class="loki_important">SHA1:</label>');
          this.loki_output = this.loki_output.replace(/SHA256:/g, '<label class="loki_important">SHA256:</label>');
          this.loki_output = this.loki_output.replace(/CREATED:/g, '<label class="loki_important">CREATED:</label>');
          this.loki_output = this.loki_output.replace(/MODIFIED:/g, '<label class="loki_important">MODIFIED:</label>');
          this.loki_output = this.loki_output.replace(/ACCESSED:/g, '<label class="loki_important">ACCESSED:</label>');
          this.loki_output = this.loki_output.replace(/REASON_/g, '<label class="loki_important">REASON_</label>');
          this.loki_output = this.loki_output.replace(/MATCH:/g, '<label class="loki_important">MATCH:</label>');
          this.loki_output = this.loki_output.replace(/DESCRIPTION:/g, '<label class="loki_important">DESCRIPTION:</label>');
          this.loki_output = this.loki_output.replace(/MATCHES:/g, '<label class="loki_important">MATCHES:</label>');
          this.loki_output = this.loki_output.replace(/DESC:/g, '<label class="loki_important">DESC:</label>');
          this.loki_output = this.loki_output.replace(/NAME:/g, '<label class="loki_important">NAME:</label>');
          this.loki_output = this.loki_output.replace(/CMD:/g, '<label class="loki_important">CMD:</label>');
          this.loki_output = this.loki_output.replace(/PATH:/g, '<label class="loki_important">PATH:</label>');
          this.loki_output = this.loki_output.replace(/OWNER:/g, '<label class="loki_important">OWNER:</label>');
          this.loki_output = this.loki_output.replace(/COMMAND:/g, '<label class="loki_important">COMMAND:</label>');
          this.loki_output = this.loki_output.replace(/IP:/g, '<label class="loki_important">IP:</label>');
          this.loki_output = this.loki_output.replace(/PORT:/g, '<label class="loki_important">PORT:</label>');
          this.loki_output = this.loki_output.replace(/Listening process PID:/g, '<label class="loki_important">Listening process PID:</label>');

          // Extra Smart Highlighting
          this.loki_output = this.loki_output.replace(/metasploit/ig, '<label class="alert_style">Metasploit</label>');
          this.loki_output = this.loki_output.replace(/webshell/ig, '<label class="alert_style">Webshell</label>');
          this.loki_output = this.loki_output.replace(/meterpreter/ig, '<label class="alert_style">Meterpreter</label>');
          this.loki_output = this.loki_output.replace(/mimikatz/ig, '<label class="alert_style">Mimikatz</label>');
          this.loki_output = this.loki_output.replace(/powershell/ig, '<label class="alert_style">Powershell</label>');
          this.loki_output = this.loki_output.replace(/procdump/ig, '<label class="alert_style">ProcDump</label>');
          this.loki_output = this.loki_output.replace(/lsadump/ig, '<label class="alert_style">lsadump</label>');
          this.loki_output = this.loki_output.replace(/sysinternals/ig, '<label class="alert_style">Sysinternals</label>');
          this.loki_output = this.loki_output.replace(/power_pe_injection/ig, '<label class="alert_style">power_pe_injection</label>');

        }
      })
      .catch(e => {
        this.checkAuth();
      });
    },
    sanitize(string) {
      const map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#x27;',
          "/": '&#x2F;',
      };
      const reg = /[&<>"'/]/ig;
      return string.replace(reg, (match)=>(map[match]));
    }
  },
  beforeMount(){
    this.checkAuth();
  },
  mounted(){
    this.display_results();
  },
  data() {
    return {
          endpoint: "",
          ipaddress: "",
          alert: "",
          warn: "",
          notice: "",
          loki_output: "",
          show_spinner: true
      };
  }
};
</script>
