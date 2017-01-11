cardApp.controller('cardCtrl', ['$scope', function($scope){
	$scope.cB1 = true;
	$scope.cardList =["C4","D2","S5", "C4","D2","S5", "C4","D2","S5"];
	$scope.showCards = [];
	$scope.cls       = [-5.9,-4.9,-3.9,-2.9, -1.9, -0.9, 0.1, 1.1, 2.1];
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
		console.log(cards[card]);
		return cards[card];
	}

	$scope.carEncoding = function(card) {
		return "card" + card.slice(-1);
	}

	$scope.getCardCss = function(cardCount) {
		var totalWidth = cardCount + 3.8 -1;
		var result     = [];
		var cardCss    = totalWidth/2;
		cardCss        = -cardCss;
		result.push(cardCss);
		for(i=1; i< cardCount; i++) {
			cardCss += 1;
			result.push(cardCss);
		}
		console.log(result);

		return result;
	}

	$scope.getCardType = function(card) {
		console.log(card);
		card = card.slice(0,1);
		console.log(card);
		switch(card) {
		    case "C":
		        return "♣";
		        break;
		    case "D":
		        return "♦";
		        break;
		    case "H":
		        return "♥";
		        break;
		    case "S":
		        return "♠";
		        break;
		}
	}

	$scope.getClr = function(card) {
		card = card.slice(0,1);
		switch(card) {
		    case "C":
		        return "black";
		        break;
		    case "D":
		        return "red";
		        break;
		    case "H":
		        return "red";
		        break;
		    case "S":
		        return "black";
		        break;
		}
	}

	$scope.getCards = function(cardList) {
		var result = [];
		var cls    = $scope.getCardCss(cardList.length)
		angular.forEach(cardList, function(cardValue, index){
			card     = $scope.carEncoding(cardValue)
			var cardInfo = {
				"arr"  : $scope.getCardDetails(card),
				"no"   : $scope.getNo(card),
				"type" : $scope.getCardType(cardValue),
				"cls"  : cls[index],
				"card" : cardValue,
				"clr"  : $scope.getClr(cardValue)
			}
			result.push(cardInfo);
		});
		return result;
	}
	$scope.showCards  = $scope.getCards($scope.cardList);
	$scope.getCardOptions = function(card) {
		console.log(card);
		$scope.optionCards = [];
		$scope.optionCards = $scope.getCards([card]);
	}
}])