//js page object

var Page = {
    /**
     * intializes with the document
     */
    init: function() {
        //focus on first field
        $("input[name='name']").get(0).focus();
        
        //attach events
        Page.attachEvents();
    },
    
    /**
     * attach page events
     */
    attachEvents: function() {
        //focus on email
        
        //find our action buttons
        $("#button_send").unbind("click").click(function(){
            Page.submitForm();
        });
    },
    
    /**
     * submitForm
     */
    submitForm: function() {
        F.xhr("&action=SendMessage&data-section=form&"+ $("#form").serialize(), undefined, "POST");
    }
}
