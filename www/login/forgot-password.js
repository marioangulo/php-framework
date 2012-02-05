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
        //focus on email
        $("[name='email']").focus();
        
        //find our action buttons
        $("#button_reset").unbind("click").click(function(){
            Page.submitForm();
        });
    },
    
    /**
     * submitForm
     */
    submitForm: function() {
        F.xhr("&action=ResetPassword&data-section=form&"+ $("#form").serialize(), undefined, "POST");
    }
}
