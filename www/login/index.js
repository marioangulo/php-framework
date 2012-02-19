//js page object

var Page = {
    returnURL: undefined,
    /**
     * intializes with the document
     */
    init: function() {
        //grab return url
        Page.returnURL = F.input("return") || "";
        
        //attach events
        Page.attachEvents();
    },
    
    /**
     * attach page events
     */
    attachEvents: function() {
        //focus on email
        $("[name='username']").focus();
        
        //find our password field
        $("[name='password']").unbind("keypress").keypress(function(e){
            if(e.which == 13){
                e.preventDefault();
                Page.submitForm();
            }
        });
        
        //find our action buttons
        $("#button_login").unbind("click").click(function(){
            Page.submitForm();
        });
    },
    
    /**
     * submitForm
     */
    submitForm: function() {
        F.xhr("&action=Login&data-section=form&"+ $("#form").serialize() +"&return="+ encodeURIComponent(Page.returnURL), function(jResponse){
            if(jResponse["data"]) {
                if(jResponse["data"]["redirect"]){ 
                    location.href = jResponse["data"]["redirect"];
                }
            }
        }, "POST");
    }
}
