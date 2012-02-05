//js admin search object

var adminSearch = {
    input: undefined,
    selectedIndex: undefined,
    timeLastKeyPressed: undefined,
    lastTimeoutID: undefined,
    
    /**
     * intializes with the document
     */
    init: function() {
        $(".navbar-fixed-top form").submit(function(){
            return false;
        });
        
        //find our dom queue
        adminSearch.input = $("#global_search").get(0);
        if(adminSearch.input) {
            adminSearch.input.onblur = function(event) {
                if(adminSearch.input.value == "") {
                    //don't run search and clear the current results
                    $(".global-search-results").html("");
                }
            };
            adminSearch.input.onkeydown = function(event) {
                if(adminSearch.input.value == "") {
                    //don't run search and clear the current results
                    $(".global-search-results").html("");
                }
            };
            adminSearch.input.onkeyup = function(event) {
                if(adminSearch.input.value == "") {
                    //don't run search and clear the current results
                    $(".global-search-results").html("");
                }
                else {
                    //as long as it's not "enter|esc|up|down"
                    if(event.keyCode != 13 && event.keyCode != 27 && event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40) {
                        var tmpDate = new Date();
                        adminSearch.timeLastKeyPressed = tmpDate.getTime();
                        adminSearch.keyBuffer();
                    }
                }
                
                //key events
                    //on enter key
                    if(event.keyCode == 13){
                        //find the selected link and follow the href
                        var selectedLink = $(".global-search-results [data-row] a:eq("+ adminSearch.selectedIndex +")").get(0);
                        if(selectedLink) {
                            location.href = selectedLink.getAttribute("href");
                        }
                        
                        return false;
                    }
                    //on esc key
                    if(event.keyCode == 27){
                        //clear search and clear the current results
                        adminSearch.input.value = "";
                        adminSearch.input.blur();
                        $(".global-search-results").html("");
                    }
                    //on up key
                    if(event.keyCode == 38){
                        if(adminSearch.selectedIndex != undefined) {
                            //can we go up?
                            if(adminSearch.selectedIndex == 0) {
                                //go around
                                var arrResults = $(".global-search-results [data-row]").get();
                                //change index
                                adminSearch.selectedIndex = arrResults.length - 1;
                                //highlight row
                                adminSearch.selectResult();
                            }
                            else {
                                //change index
                                adminSearch.selectedIndex -= 1;
                                //highlight row
                                adminSearch.selectResult();
                            }
                        }
                        else {
                            //find the first link and focus on it
                            var firstLink = $(".global-search-results [data-row]").get(0);
                            if(firstLink) {
                                //change index
                                adminSearch.selectedIndex = 0;
                                //highlight row
                                adminSearch.selectResult();
                            }
                        }
                        
                        //hack the cursor to the end
                        adminSearch.input.value = adminSearch.input.value;
                        return false;
                    }
                    //on down key
                    if(event.keyCode == 40){
                        if(adminSearch.selectedIndex != undefined) {
                            //can we go down?
                            var arrResults = $(".global-search-results [data-row]").get();
                            if(adminSearch.selectedIndex + 1 == arrResults.length) {
                                //change index
                                adminSearch.selectedIndex = 0;
                                //highlight row
                                adminSearch.selectResult();
                            }
                            else {
                                //change index
                                adminSearch.selectedIndex += 1;
                                //highlight row
                                adminSearch.selectResult();
                            }
                        }
                        else {
                            //find the first link and focus on it
                            var firstLink = $(".global-search-results [data-row]").get(0);
                            if(firstLink) {
                                //change index
                                adminSearch.selectedIndex = 0;
                                //highlight row
                                adminSearch.selectResult();
                            }
                        }
                        
                        //hack the cursor to the end
                        adminSearch.input.value = adminSearch.input.value;
                        return false;
                    }
                //end key events
            };
        }
    },
    
    /**
     * performs keyboard/typing input buffering (for efficiency)
     */
     keyBuffer: function() {
        //what time is it?
        var tmpNow = new Date();
        
        //how much time has passed?
        timePassed = (tmpNow - adminSearch.timeLastKeyPressed) / 1000;
        
        //only run search if 250 milliseconds have passed?
        if(timePassed >= 0.25) {
            F.xhr("data-section=global_search_results&q="+ adminSearch.input.value, undefined, "GET", "admin/search/results.html");
        }
        else {
            //cancel the last timeout?
            if(adminSearch.lastTimeoutID) {
                clearTimeout(adminSearch.lastTimeoutID);
            }
            
            //call back in 100 milliseconds
            adminSearch.lastTimeoutID = setTimeout(adminSearch.keyBuffer, 50);
        }
    },
    
    /**
     * selects a specific search result
     */
    selectResult: function() {
        //reset all rows
        $(".global-search-results [data-row]").attr("class", "");
        //highlight this
        $(".global-search-results [data-row]:eq("+ adminSearch.selectedIndex +")").attr("class", "active");
    }
};

/**
 * register our admin search init method with jquery's ready event
 */
$(document).ready(adminSearch.init);
