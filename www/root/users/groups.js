//js page object

var Page = {
    id: undefined,
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
        //find our action buttons
        $("#button_add").unbind("click").click(function(){
            Page.submitForm("&action=Add&"+ $("#form_add").serialize());
        });
        $(".button_update").unbind("click").click(function(){
            Page.submitForm("&action=Update&"+ $("#"+ $(this).attr("data-id")).serialize());
        });
        $(".button_cancel").unbind("click").click(function(){
            if(confirm("Are you sure?")) {
                Page.submitForm("&action=Cancel&"+ $("#"+ $(this).attr("data-id")).serialize());
            }
        });
    },
    
    /**
     * submitForm
     */
    submitForm: function(extras) {
        F.xhr("id="+ Page.id +"&data-section=all&"+ extras, undefined, "POST");
    }
}
