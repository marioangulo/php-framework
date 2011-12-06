//js page object

var Page = {
    inputSearch: undefined,
    timeLastKeyPressed: undefined,
    lastTimeoutID: undefined,
    
    /**
     * intializes with the document
     */
    init: function() {
        $("#username_search").unbind("keyup").keyup(function() {
            var tmpDate = new Date();
            Page.timeLastKeyPressed = tmpDate.getTime();
            Page.keyBuffer();
        });
    },
    
    /**
     * performs keyboard/typing input buffering (for efficiency)
     */
    keyBuffer: function() {
        //what time is it?
        var tmpNow = new Date();
        
        //how much time has passed?
        timePassed = (tmpNow - Page.timeLastKeyPressed) / 1000;
        
        //only run search if 250 milliseconds have passed?
        if(timePassed >= 0.25) {
            F.xhr("data-section=user_search&search="+ $("#username_search").val());
        }
        else {
            //cancel the last timeout?
            if(Page.lastTimeoutID) {
                clearTimeout(Page.lastTimeoutID);
            }
            
            //call back in 50 milliseconds
            Page.lastTimeoutID = setTimeout(Page.keyBuffer, 50);
        }
    }
};
