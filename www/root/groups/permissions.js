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
        $("#button_update").unbind("click").click(function(){
            Page.submitForm("&action=Update");
        });
        $("#button_delete").unbind("click").click(function(){
            if(confirm("Are you sure?")) {
                Page.submitForm("&action=DeleteAll");
            }
        });
    },
    
    /**
     * submitForm
     */
    submitForm: function(extras) {
        F.xhr("id="+ Page.id +"&data-section=record&"+ $("#form").serialize() + extras, undefined, "POST");
    }
}
