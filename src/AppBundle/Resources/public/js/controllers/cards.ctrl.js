cardApp.controller('cardCtrl', ['$scope', 'getApiDataService', function($scope, getApiDataService){
	$scope.cB1          = true;
	$scope.cardList     = [];
	$scope.showCards    = [];
	$scope.cls          = [-5.9,-4.9,-3.9,-2.9, -1.9, -0.9, 0.1, 1.1, 2.1];
	$scope.selectedCard = null;
	$scope.selectedUser = null;
	$scope.gameInfo		= {};
	var pusher;
	

	$scope.getCardNo = function(card) {
		return card.slice(1);
	}
	$scope.getCardType = function(card) {
		return card.splice(0,1);
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
			"card9"  : [11, 12, 14, 15, 23, 31, 32, 34, 35],
			"card10" : [11, 12, 14, 15, 22, 24, 31, 32, 34, 35],
			"card11" : [3, 11, 35],
			"card12" : [2, 11, 35],
			"card13" : [1, 11, 35]
		}
		return cards[card];
	}

	$scope.carEncoding = function(card) {
		return "card" + card.slice(1);
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

		return result;
	}

	$scope.getCardType = function(card) {
		card = card.slice(0,1);
		switch(card) {
		    case "c":
		        return "♣";
		        break;
		    case "d":
		        return "♦";
		        break;
		    case "h":
		        return "♥";
		        break;
		    case "s":
		        return "♠";
		        break;
		}
	}

	$scope.getClr = function(card) {
		card = card.slice(0,1);
		switch(card) {
		    case "c":
		        return "black";
		        break;
		    case "d":
		        return "red";
		        break;
		    case "h":
		        return "red";
		        break;
		    case "s":
		        return "black";
		        break;
		}
	}

	$scope.isSameRange = function(cardNo, value){
		value = value.slice(1);
		if(cardNo < 7 && value >=1 && value <= 6)
			return true;
		if(cardNo < 8 && value >=8 && value <= 13)
			return true;
		return false;
	}

	$scope.getOtherRangeCard = function(card, cardList){
		var cardType = card.slice(0,1);
		var cardNo   = parseInt($scope.getCardNo(card));
		var start,end
		if(cardNo < 7){
			start = 1;
			end   = 6;
		}
		else {
			start = 8;
			end   = 13;
		}
		var otherCard = [];
		console.log(cardNo, cardList);
		var allType = [];
		angular.forEach(cardList, function(value, index){
			if(cardType == value.slice(0,1))
				allType.push(parseInt(value.slice(1)));
		});
		console.log(allType);
		for(i=start; i <= end; i++ ) {
			var card = "";
			if(i != cardNo && (allType.indexOf(i) == -1)) {
				console.log(i, allType.indexOf(i) == -1, "dfds\n");
				card = cardType + i;
				otherCard.push(card);		

			}
		}
		console.log(otherCard);
		return otherCard;
	}

	$scope.getCards = function(cardList) {
		var result = [];
		var cls    = $scope.getCardCss(cardList.length)
		angular.forEach(cardList, function(cardValue, index){
			card     = $scope.carEncoding(cardValue)
			var cardInfo = {
				"arr"  : $scope.getCardDetails(card),
				"no"   : $scope.getCardNo(cardValue),
				"type" : $scope.getCardType(cardValue),
				"cls"  : cls[index],
				"card" : cardValue,
				"clr"  : $scope.getClr(cardValue)
			}
			result.push(cardInfo);
		});
		return result;
	}

	// $scope.showCards  = $scope.getCards($scope.cardList);
	
	$scope.getCardOptions = function(card) {
		$scope.optionCards = [];
		$scope.optionCards = $scope.getCards($scope.getOtherRangeCard(card, $scope.cardList));
	}

	$scope.setectCenterCard = function(card) {
		console.log(card);
		$scope.selectedCard = card;
	}
	$scope.setectUser = function(user) {
		console.log(card);
		$scope.selectedUser = user;
	}
	$scope.getCurrentGame = function() {
		var url   = "/game";
		getApiDataService.makeGetDataReq(url, "", function(response, isSuccess) {
            if (isSuccess) {
                $scope.cardList  = response.response.user.cards;
				$scope.showCards = $scope.getCards(response.response.user.cards);
				$scope.initPusher();
				$scope.setPusher(response.response.game.id, "GAME_JOIN_ACTION");
				$scope.setGameDetails(response.response);

            } else {
               alert("Please Try Again Later!")
            }
        });
	}
	var url = "/game/start"
	getApiDataService.makePostDataReq(url, "", function(response, isSuccess) {
		console.log(response);
        if (isSuccess) {
        	$scope.cardList  = response.response.user.cards;
			$scope.showCards = $scope.getCards(response.response.user.cards);
			$scope.initPusher();
			$scope.setPusher(response.response.game.id, "GAME_JOIN_ACTION");
			$scope.setGameDetails(response.response);
        } else {
           $scope.getCurrentGame();
        }
    });

    $scope.setPusher = function(channel, event) {
	    var channel = pusher.subscribe(channel);
	    channel.bind(event, function(data) {
	      alert("joining action");
	      console.log(data);
	    });
	}

	$scope.initPusher = function() {
	    Pusher.logToConsole = true;
	    pusher = new Pusher('0a4e78670f01ad56c33a', {
	      encrypted: true
	    });
	}

	$scope.setGameDetails = function(gameData) {
		$scope.gameInfo.team0    = gameData.game.team0;
		$scope.gameInfo.team1    = gameData.game.team1;
		$scope.gameInfo.myTeam   = null;
		$scope.gameInfo.nextTurn = gameData.game.nextTurn;
		$scope.gameInfo.points   = gameData.game.points;

		if($scope.gameInfo.team0.indexOf(gameData.user.id) >= 0) {
			$scope.gameInfo.myTeam  = "team0";
			$scope.gameInfo.oppTeam = "team1";
		} else if($scope.gameInfo.team1.indexOf(gameData.user.id) >= 0) {
			$scope.gameInfo.myTeam  = "team1";
			$scope.gameInfo.oppTeam = "team0";
		}

		try {
			$scope.gameInfo.u1 = $scope.gameData[$scope.gameInfo.myTeam][0];
			$scope.gameInfo.u2 = $scope.gameData[$scope.gameInfo.myTeam][1];
		} catch(Err) {

		}
		try {
			$scope.gameInfo.u3 = $scope.gameData[$scope.gameInfo.oppTeam][0];
			$scope.gameInfo.u4 = $scope.gameData[$scope.gameInfo.oppTeam][1];
		} catch(Err) {

		}
	}
}])