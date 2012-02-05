//js root search object

var rootSearch = {
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
        rootSearch.input = $("#global_search").get(0);
        if(rootSearch.input) {
            rootSearch.input.onblur = function(event) {
                if(rootSearch.input.value == "") {
                    //don't run search and clear the current results
                    $(".global-search-results").html("");
                }
            };
            rootSearch.input.onkeydown = function(event) {
                if(rootSearch.input.value == "") {
                    //don't run search and clear the current results
                    $(".global-search-results").html("");
                }
            };
            rootSearch.input.onkeyup = function(event) {
                if(rootSearch.input.value == "") {
                    //don't run search and clear the current results
                    $(".global-search-results").html("");
                }
                else {
                    //as long as it's not "enter|esc|up|down"
                    if(event.keyCode != 13 && event.keyCode != 27 && event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40) {
                        var tmpDate = new Date();
                        rootSearch.timeLastKeyPressed = tmpDate.getTime();
                        rootSearch.keyBuffer();
                    }
                }
                
                //key events
                    //on enter key
                    if(event.keyCode == 13){
                        //find the selected link and follow the href
                        var selectedLink = $(".global-search-results [data-row] a:eq("+ rootSearch.selectedIndex +")").get(0);
                        if(selectedLink) {
                            location.href = selectedLink.getAttribute("href");
                        }
                        
                        return false;
                    }
                    //on esc key
                    if(event.keyCode == 27){
                        //clear search and clear the current results
                        rootSearch.input.value = "";
                        rootSearch.input.blur();
                        $(".global-search-results").html("");
                    }
                    //on up key
                    if(event.keyCode == 38){
                        if(rootSearch.selectedIndex != undefined) {
                            //can we go up?
                            if(rootSearch.selectedIndex == 0) {
                                //go around
                                var arrResults = $(".global-search-results [data-row]").get();
                                //change index
                                rootSearch.selectedIndex = arrResults.length - 1;
                                //highlight row
                                rootSearch.selectResult();
                            }
                            else {
                                //change index
                                rootSearch.selectedIndex -= 1;
                                //highlight row
                                rootSearch.selectResult();
                            }
                        }
                        else {
                            //find the first link and focus on it
                            var firstLink = $(".global-search-results [data-row]").get(0);
                            if(firstLink) {
                                //change index
                                rootSearch.selectedIndex = 0;
                                //highlight row
                                rootSearch.selectResult();
                            }
                        }
                        
                        //hack the cursor to the end
                        rootSearch.input.value = rootSearch.input.value;
                        return false;
                    }
                    //on down key
                    if(event.keyCode == 40){
                        if(rootSearch.selectedIndex != undefined) {
                            //can we go down?
                            var arrResults = $(".global-search-results [data-row]").get();
                            if(rootSearch.selectedIndex + 1 == arrResults.length) {
                                //change index
                                rootSearch.selectedIndex = 0;
                                //highlight row
                                rootSearch.selectResult();
                            }
                            else {
                                //change index
                                rootSearch.selectedIndex += 1;
                                //highlight row
                                rootSearch.selectResult();
                            }
                        }
                        else {
                            //find the first link and focus on it
                            var firstLink = $(".global-search-results [data-row]").get(0);
                            if(firstLink) {
                                //change index
                                rootSearch.selectedIndex = 0;
                                //highlight row
                                rootSearch.selectResult();
                            }
                        }
                        
                        //hack the cursor to the end
                        rootSearch.input.value = rootSearch.input.value;
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
        timePassed = (tmpNow - rootSearch.timeLastKeyPressed) / 1000;
        
        //only run search if 250 milliseconds have passed?
        if(timePassed >= 0.25) {
            F.xhr("data-section=global_search_results&q="+ rootSearch.input.value, undefined, "GET", "root/search/results.html");
        }
        else {
            //cancel the last timeout?
            if(rootSearch.lastTimeoutID) {
                clearTimeout(rootSearch.lastTimeoutID);
            }
            
            //call back in 100 milliseconds
            rootSearch.lastTimeoutID = setTimeout(rootSearch.keyBuffer, 50);
        }
    },
    
    /**
     * selects a specific search result
     */
    selectResult: function() {
        //reset all rows
        $(".global-search-results [data-row]").attr("class", "");
        //highlight this
        $(".global-search-results [data-row]:eq("+ rootSearch.selectedIndex +")").attr("class", "active");
    }
};

/**
 * register our root search init method with jquery's ready event
 */
$(document).ready(rootSearch.init);
