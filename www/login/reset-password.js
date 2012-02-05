//js page object

var Page = {
    /**
     * intializes with the document
     */
    init: function() {
        //attach events
        Page.attachEvents();
    },
    
    /**
     * attach page events
     */
    attachEvents: function() {
        //focus on password
        $("[name='password']").focus();
        
        //find our action buttons
        $("#button_set").unbind("click").click(function(){
            Page.submitForm();
        });
    },
    
    /**
     * submitForm
     */
    submitForm: function() {
        F.xhr("&action=SetPassword&data-section=form&"+ $("#form").serialize() +"&"+ F.input("s"), undefined, "POST");
    }
}
