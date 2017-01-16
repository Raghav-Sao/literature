cardApp

    .directive('dirLeftDemoCard', function() {
        var template = '<div class="card" style="left:1em;top:-2.5em;"></div>' +
            '<div class="card" style="left:2em;top:-2.5em;"></div>' +
            '<div class="card" style="left:3em;top:-2.5em;"></div>'

        return {

            template
        }
    })
    .directive('dirRightDemoCard', function() {
        var template = '<div class="card" style="right:3em;top:-2.5em;"></div>' +
            '<div class="card" style="right:2em;top:-2.5em;"></div>' +
            '<div class="card" style="right:1em;top:-2.5em;"></div>'

        return {

            template
        }
    })

.directive('dirTopDemoCard', function() {
    var template = '<div class="card" style="left:-1em;top:0.5em;"></div>' +
        '<div class="card" style="left:0em;top:0.5em;"></div>' +
        '<div class="card" style="left:1em;top:0.5em;"></div>'

    return {

        template
    }
})


.directive('dirGetCard', function($compile) {
    var template = '<div class="card {[{clr}]}" style="left:{[{cls}]}em">' +
        '<div class="front">' +
        '<div class="index">{[{no}]}<br />{[{type}]}</div>' +
        '<div class="spotA1" ng-show="{[{arr}]}.indexOf(11)>=0"> {[{type}]}</div>' +
        '<div class="spotA2" ng-show="{[{arr}]}.indexOf(12)>=0"> {[{type}]}</div>' +
        '<div class="spotA3" ng-show="{[{arr}]}.indexOf(13)>=0"> {[{type}]}</div>' +
        '<div class="spotA4" ng-show="{[{arr}]}.indexOf(14)>=0"> {[{type}]}</div>' +
        '<div class="spotA5" ng-show="{[{arr}]}.indexOf(15)>=0"> {[{type}]}</div>' +
        '<div class="spotB1" ng-show="{[{arr}]}.indexOf(21)>=0"> {[{type}]}</div>' +
        '<div class="spotB2" ng-show="{[{arr}]}.indexOf(22)>=0"> {[{type}]}</div>' +
        '<div class="spotB3" ng-show="{[{arr}]}.indexOf(23)>=0"> {[{type}]}</div>' +
        '<div class="spotB4" ng-show="{[{arr}]}.indexOf(24)>=0"> {[{type}]}</div>' +
        '<div class="spotB5" ng-show="{[{arr}]}.indexOf(25)>=0"> {[{type}]}</div>' +
        '<div class="spotC1" ng-show="{[{arr}]}.indexOf(31)>=0"> {[{type}]}</div>' +
        '<div class="spotC2" ng-show="{[{arr}]}.indexOf(32)>=0"> {[{type}]}</div>' +
        '<div class="spotC3" ng-show="{[{arr}]}.indexOf(33)>=0"> {[{type}]}</div>' +
        '<div class="spotC4" ng-show="{[{arr}]}.indexOf(34)>=0"> {[{type}]}</div>' +
        '<div class="spotC5" ng-show="{[{arr}]}.indexOf(35)>=0"> {[{type}]}</div>' +
        '<img class="face" src="/bundles/app/img/king.gif" alt="" width="80" height="130"  ng-show="{[{arr}]}.indexOf(1)>=0"/>'+
        '<img class="face" src="/bundles/app/img/queen.gif" alt="" width="80" height="130" ng-show="{[{arr}]}.indexOf(2)>=0"/>'+
        '<img class="face" src="/bundles/app/img/jack.gif" alt="" width="80" height="130"  ng-show="{[{arr}]}.indexOf(3)>=0"/>'+
        // '<div class="spotA1">&clubs;</div>'+
        // '<div class="spotC5">&clubs;</div>'+
        '</div>' +
        '</div>'
    return {
        restrict: 'AE',
        replace: 'true',
        scope: {
            type: "@",
            no: "@",
            cls: "@",
            arr: "@",
            clr: "@"
        },
        link: function(scope, element, attr) {
            var content = $compile(template)(scope);
            element.replaceWith(content);
        }
    }
})
    .directive('dirCenterCard', function($compile) {
        var template =
        '<div class="front">' +
        '<div class="index">{[{no}]}<br />{[{type}]}</div>' +
        '<div class="spotA1" ng-show="{[{arr}]}.indexOf(11)>=0"> {[{type}]}</div>' +
        '<div class="spotA2" ng-show="{[{arr}]}.indexOf(12)>=0"> {[{type}]}</div>' +
        '<div class="spotA3" ng-show="{[{arr}]}.indexOf(13)>=0"> {[{type}]}</div>' +
        '<div class="spotA4" ng-show="{[{arr}]}.indexOf(14)>=0"> {[{type}]}</div>' +
        '<div class="spotA5" ng-show="{[{arr}]}.indexOf(15)>=0"> {[{type}]}</div>' +
        '<div class="spotB1" ng-show="{[{arr}]}.indexOf(21)>=0"> {[{type}]}</div>' +
        '<div class="spotB2" ng-show="{[{arr}]}.indexOf(22)>=0"> {[{type}]}</div>' +
        '<div class="spotB3" ng-show="{[{arr}]}.indexOf(23)>=0"> {[{type}]}</div>' +
        '<div class="spotB4" ng-show="{[{arr}]}.indexOf(24)>=0"> {[{type}]}</div>' +
        '<div class="spotB5" ng-show="{[{arr}]}.indexOf(25)>=0"> {[{type}]}</div>' +
        '<div class="spotC1" ng-show="{[{arr}]}.indexOf(31)>=0"> {[{type}]}</div>' +
        '<div class="spotC2" ng-show="{[{arr}]}.indexOf(32)>=0"> {[{type}]}</div>' +
        '<div class="spotC3" ng-show="{[{arr}]}.indexOf(33)>=0"> {[{type}]}</div>' +
        '<div class="spotC4" ng-show="{[{arr}]}.indexOf(34)>=0"> {[{type}]}</div>' +
        '<div class="spotC5" ng-show="{[{arr}]}.indexOf(35)>=0"> {[{type}]}</div>' +
        '<img class="face" src="/bundles/app/img/king.gif" alt="" width="80" height="130"  ng-show="{[{arr}]}.indexOf(1)>=0"/>'+
        '<img class="face" src="/bundles/app/img/queen.gif" alt="" width="80" height="130" ng-show="{[{arr}]}.indexOf(2)>=0"/>'+
        '<img class="face" src="/bundles/app/img/jack.gif" alt="" width="80" height="130"  ng-show="{[{arr}]}.indexOf(3)>=0"/>'+
        // '<div class="spotA1">&clubs;</div>'+
        // '<div class="spotC5">&clubs;</div>'+
        '</div>' 
    return {
        restrict: 'AE',
        replace: 'true',
        scope: {
            type: "@",
            no  : "@",
            cls : "@",
            arr : "@",
            clr : "@"
        },
        link: function(scope, element, attr) {
            var content = $compile(template)(scope);
            element.replaceWith(content);
        }
    }
});
