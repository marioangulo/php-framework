//##########################################################################################

//--> Setup :: GlobalSearch
	GlobalSearch = {};
	GlobalSearch.Input = undefined;
	GlobalSearch.SelectedIndex = undefined;
	GlobalSearch.TimeLastKeyPressed = undefined;
	GlobalSearch.LastTimeoutID = undefined;
//<-- End Setup :: GlobalSearch

//##########################################################################################

//--> Begin Function :: Init
	GlobalSearch.Init = function() {
		$(".topbar form").submit(function(){
			return false;
		});
		
		//find the delete button
		GlobalSearch.Input = $("#global_search").get(0);
		if(GlobalSearch.Input) {
			GlobalSearch.Input.onblur = function(Event) {
				if(GlobalSearch.Input.value == "") {
					//don't run search and clear the current results
					$(".global-search-results").html("");
				}
			};
			GlobalSearch.Input.onkeydown = function(Event) {
				if(GlobalSearch.Input.value == "") {
					//don't run search and clear the current results
					$(".global-search-results").html("");
				}
			};
			GlobalSearch.Input.onkeyup = function(Event) {
				if(GlobalSearch.Input.value == "") {
					//don't run search and clear the current results
					$(".global-search-results").html("");
				}
				else {
					//as long as it's not "enter|esc|up|down"
					if(Event.keyCode != 13 && Event.keyCode != 27 && Event.keyCode != 37 && Event.keyCode != 38 && Event.keyCode != 39 && Event.keyCode != 40) {
						var TmpDate = new Date();
						GlobalSearch.TimeLastKeyPressed = TmpDate.getTime();
						GlobalSearch.KeyBuffer();
					}
				}
				
				//key events
					//on enter key
					if(Event.keyCode == 13){
						//find the selected link and follow the href
						var selectedLink = $(".global-search-results [data-row] a:eq("+ GlobalSearch.SelectedIndex +")").get(0);
						if(selectedLink) {
							location.href = selectedLink.getAttribute("href");
						}
						
						return false;
					}
					//on esc key
					if(Event.keyCode == 27){
						//clear search and clear the current results
						GlobalSearch.Input.value = "";
						GlobalSearch.Input.blur();
						$(".global-search-results").html("");
					}
					//on up key
					if(Event.keyCode == 38){
						if(GlobalSearch.SelectedIndex != undefined) {
							//can we go up?
							if(GlobalSearch.SelectedIndex == 0) {
								//go around
								var arrResults = $(".global-search-results [data-row]").get();
								//change index
								GlobalSearch.SelectedIndex = arrResults.length - 1;
								//highlight row
								GlobalSearch.SelectResult();
							}
							else {
								//change index
								GlobalSearch.SelectedIndex -= 1;
								//highlight row
								GlobalSearch.SelectResult();
							}
						}
						else {
							//find the first link and focus on it
							var firstLink = $(".global-search-results [data-row]").get(0);
							if(firstLink) {
								//change index
								GlobalSearch.SelectedIndex = 0;
								//highlight row
								GlobalSearch.SelectResult();
							}
						}
						
						//hack the cursor to the end
						GlobalSearch.Input.value = GlobalSearch.Input.value;
						return false;
					}
					//on down key
					if(Event.keyCode == 40){
						if(GlobalSearch.SelectedIndex != undefined) {
							//can we go down?
							var arrResults = $(".global-search-results [data-row]").get();
							if(GlobalSearch.SelectedIndex + 1 == arrResults.length) {
								//change index
								GlobalSearch.SelectedIndex = 0;
								//highlight row
								GlobalSearch.SelectResult();
							}
							else {
								//change index
								GlobalSearch.SelectedIndex += 1;
								//highlight row
								GlobalSearch.SelectResult();
							}
						}
						else {
							//find the first link and focus on it
							var firstLink = $(".global-search-results [data-row]").get(0);
							if(firstLink) {
								//change index
								GlobalSearch.SelectedIndex = 0;
								//highlight row
								GlobalSearch.SelectResult();
							}
						}
						
						//hack the cursor to the end
						GlobalSearch.Input.value = GlobalSearch.Input.value;
						return false;
					}
				//end key events
			};
		}
	};
//<-- End Function :: Init

//##########################################################################################

//--> Begin Function :: KeyBuffer
	GlobalSearch.KeyBuffer = function() {
		//what time is it?
		var TmpNow = new Date();
		
		//how much time has passed?
		TimePassed = (TmpNow - GlobalSearch.TimeLastKeyPressed) / 1000;
		
		//only run search if 250 milliseconds have passed?
		if(TimePassed >= 0.25) {
			F.XHR("data-section=global_search_results&q="+ GlobalSearch.Input.value, undefined, "GET", "root/search/results.html");
		}
		else {
			//cancel the last timeout?
			if(GlobalSearch.LastTimeoutID) {
				clearTimeout(GlobalSearch.LastTimeoutID);
			}
			
			//call back in 100 milliseconds
			GlobalSearch.LastTimeoutID = setTimeout(GlobalSearch.KeyBuffer, 50);
		}
	};
//<-- End Function :: KeyBuffer

//##########################################################################################

//--> Begin Function :: SelectResult
	GlobalSearch.SelectResult = function() {
		//reset all rows
		$(".global-search-results [data-row]").attr("class", "unselected");
		//highlight this
		$(".global-search-results [data-row]:eq("+ GlobalSearch.SelectedIndex +")").attr("class", "selected");
	};
//<-- End Function :: SelectResult

//##########################################################################################
	
	//start when ready
	$(document).ready(GlobalSearch.Init);
	
//##########################################################################################