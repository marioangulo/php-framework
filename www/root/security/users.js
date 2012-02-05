//js page object

var Page = {
    id: undefined,
    inputSearch: undefined,
    timeLastKeyPressed: undefined,
    lastTimeoutID: undefined,
    
    /**
     * intializes with the document
     */
    init: function() {
        Page.id = F.input("id");
        Page.attachEvents();
    },
    
    /**
     * attach page events
     */
    attachEvents: function() {
        //find our search field
        $("#username_search").unbind("keyup").keyup(function() {
            var tmpDate = new Date();
            Page.timeLastKeyPressed = tmpDate.getTime();
            Page.keyBuffer();
        });
        
        //find our action buttons
        $("#button_add").unbind("click").click(function(){
            Page.submitForm("&action=Add&"+ $("#form_add").serialize());
        });
        $(".button_update").unbind("click").click(function(){
            Page.submitForm("&action=Update&"+ $("#"+ $(this).attr("data-id")).serialize());
        });
        $(".button_delete").unbind("click").click(function(){
            if(confirm("Are you sure?")) {
                Page.submitForm("&action=Delete&"+ $("#"+ $(this).attr("data-id")).serialize());
            }
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
    },
    
    /**
     * submitForm
     */
    submitForm: function(extras) {
        F.xhr("id="+ Page.id +"&data-section=all&"+ extras, undefined, "POST");
    }
};
