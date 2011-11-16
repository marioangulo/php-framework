//##########################################################################################

//--> Setup :: Page
	P = {};
	P.InputSearch = undefined;
	P.TimeLastKeyPressed = undefined;
	P.LastTimeoutID = undefined;
//<-- End Setup :: Page

//##########################################################################################

//--> Begin Function :: Init
	P.Init = function() {
		//find the search box
		$("#username_search").unbind("keyup").keyup(function() {
			var TmpDate = new Date();
			P.TimeLastKeyPressed = TmpDate.getTime();
			P.KeyBuffer();
		});
	};
//<-- End Function :: Init

//##########################################################################################

//--> Begin Function :: KeyBuffer
	P.KeyBuffer = function() {
		//what time is it?
		var TmpNow = new Date();
		
		//how much time has passed?
		TimePassed = (TmpNow - P.TimeLastKeyPressed) / 1000;
		
		//only run search if 250 milliseconds have passed?
		if(TimePassed >= 0.25) {
			F.XHR("data-section=user_search&search="+ $("#username_search").val());
		}
		else {
			//cancel the last timeout?
			if(P.LastTimeoutID) {
				clearTimeout(P.LastTimeoutID);
			}
			
			//call back in 50 milliseconds
			P.LastTimeoutID = setTimeout(P.KeyBuffer, 50);
		}
	};
//<-- End Function :: KeyBuffer

//##########################################################################################
	
	//start when ready
	$(document).ready(P.Init);
	
//##########################################################################################