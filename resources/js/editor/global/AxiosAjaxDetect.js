class AxiosAjaxDetect {

    init (startCb, endCb) {
        let count = 0;

        window.axios.interceptors.request.use(
            function(config) {
                count++;
                if(count === 1) startCb();
                return config;
            },
            function (error) {
                return Promise.reject(error);
            }
        );

        window.axios.interceptors.response.use(
            function(response) {
                count--;
                if (count === 0) { endCb(); }
                return response;

            },
            function (error) {

                count--;

                if (count === 0) {
                    endCb();
                }

                return Promise.reject(error);
            }
        );
    }
}

export default new AxiosAjaxDetect;
