//js page object

var Page = {
    timeLastKeyPressed: undefined,
    lastTimeoutID: undefined,
    
    /**
     * intializes with the document
     */
    init: function() {
        Page.attachEvents();
    },
    
    /**
     * attach page events
     */
    attachEvents: function() {
        //find our text filters
        $(".filters [type='text']").unbind("keyup").keyup(function() {
            var tmpDate = new Date();
            Page.timeLastKeyPressed = tmpDate.getTime();
            Page.keyBuffer();
        });
        
        //find our date filters
        $(".filters [name='date_from'], .filters [name='date_to']").unbind("keyup").unbind("change").change(function() {
            Page.filterResults();
        });
        
        //find our select filters
        $(".filters select").unbind("change").change(function() {
            Page.filterResults();
        });
        
        //paging
        $("a[data-page]").unbind("click").click(function() {
            Page.filterResults("&"+ $(this).attr("data-page"));
        });
        
        //find our action buttons
        $("#button_download").unbind("click").click(function() {
            location.href = F.engineNamespace +".html?"+ $(".filters").serialize() + "&action=Download";
        });
        $("#button_delete").unbind("click").click(function() {
            Page.filterResults("&action=Delete");
        });
    },
    
    /**
     * key buffer
     */
    keyBuffer: function() {
        //what time is it?
        var tmpNow = new Date();
        
        //how much time has passed?
        timePassed = (tmpNow - Page.timeLastKeyPressed) / 1000;
        
        //only run search if 250 milliseconds have passed?
        if(timePassed >= 0.25) {
            Page.filterResults();
        }
        else {
            //cancel the last timeout?
            if(Page.lastTimeoutID) {
                clearTimeout(Page.lastTimeoutID);
            }
            
            //call back in 50 milliseconds
            Page.lastTimeoutID = setTimeout(Page.keyBuffer, 50);
        }
    },
    
    /**
     * get new results
     */
    filterResults: function(extras) {
        if(extras == undefined) {
            extras = "";
        }
        F.xhr("data-section=all&"+ $(".filters").serialize() + extras);
    }
}
