cardApp.controller('cardCtrl', ['$scope', function($scope){
	$scope.cB1 = true;
	$scope.cardList =["C4","D2","S5", "C4","D2","S5", "C4","D2","S5"];
	$scope.showCards = [];
	$scope.cls       = [-4,-3,-2,-1, 0, 1, 2, 3, 4];
	$scope.getNo = function(card) {
		return card.slice(-1);
	}
	$scope.getCardDetails = function(card) {
		cards = {
			"card1"  : [],
			"card2"  : [21, 23],
			"card3"  : [21, 23, 25],
			"card4"  : [11, 15, 31, 35],
			"card5"  : [11, 15, 23, 31, 35],
			"card6"  : [11, 13, 15, 31, 33, 35],
		 	"card7"  : [11, 13, 15, 22, 31, 33, 35],
			"card8"  : [11, 13, 15, 22, 24, 31, 33, 35],
			"card9"  : [11, 12, 14, 15, 23, 31, 32, 34, 35]
		}
		return cards[card];
	}
	$scope.carEncoding = function(card) {
		return "card" + card.slice(-1);
	}
	$scope.getCards = function(cardList) {
		var result = [];
		angular.forEach(cardList, function(cardValue, index){
			card = $scope.carEncoding(cardValue)
			var cardInfo = {
				"arr" : $scope.getCardDetails(card),
				"no"  : $scope.getNo(card),
				"type": "♣",
				"cls" : "left-" + $scope.cls[index],
				"card": cardValue
			}
			result.push(cardInfo);
		});
		return result;
	}
	$scope.showCards  = $scope.getCards($scope.cardList);
	$scope.getCardOptions = function(card) {
		console.log(card);
		$scope.optionCards = $scope.getCards([card]);
	}

}])