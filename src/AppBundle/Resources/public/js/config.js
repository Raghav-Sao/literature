cardApp.config(function($routeProvider) {
    $routeProvider
    .when("/", {
        templateUrl : "bundles/app/view/card.templt.html"
    })
    .when("/red", {
        templateUrl : "red.htm"
    })
    .when("/green", {
        templateUrl : "green.htm"
    })
    .when("/blue", {
        templateUrl : "blue.htm"
    });
});
// alert("kk