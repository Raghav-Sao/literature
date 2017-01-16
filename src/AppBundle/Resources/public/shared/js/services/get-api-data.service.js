cardApp.service('getApiDataService', ['$http', '$log',
    function($http, $log) {
        this.makeGetDataReq = function(url, param, returnData) {
            $http({
                url: url,
                params: param,
                method: 'GET'
            }).then(function(response) {
                returnData(response.data, true);
            }, function(error) {
                returnData(error.data, false);
            });
        };
        this.makePostDataReq = function(url, param, returnData) {
            $http({
                url: url,
                data: param,
                method: 'POST'
            }).then(function(response) {
                returnData(response.data, true);
            }, function(error) {
                returnData(error.data , false);
            });
        };
        this.makePutDataReq = function(url, param, returnData) {
            $http({
                url: url,
                data: param,
                method: 'Patch'
            }).then(function(response) {
                returnData(response.data);
            }, function(error) {
                swal("Please Try Again", "", "error");
                $log.error(error.data);
            });
        };

        this.makeDeleteDataReq = function(url, param, returnData) {
            $http.delete(url).then(function(response) {
                returnData(response.data);
            }, function(error) {
                swal("Please Try Again", "", "error");
                $log.error(error.data);
            });
        };
}]);