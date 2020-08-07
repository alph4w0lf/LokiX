export default {
    methods: {
        // Checks if the current session is authenticated
        checkAuth(){
            const vm = this;
            this.axios.interceptors.response.use(function (response) {
                return response;
            }, function (error) {
                if (401 === error.response.status) {
                    vm.$router.push('login');
                } else {
                    return Promise.reject(error);
                }
            });
            this.axios.defaults.withCredentials = true;
            this.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            this.axios.get(this.PLATFORM_URL+'/api/state');
          },
        // Display upper right notification panel
        notifyVue(mmmsg, ttype, icon) {
            this.$notify({
                message: mmmsg,
                icon: icon,
                horizontalAlign: 'right',
                verticalAlign: 'top',
                type: ttype
            });
        }
    },
    data(){
        return {
            PLATFORM_URL: document.location.origin + "/be"
        }
    }
}

