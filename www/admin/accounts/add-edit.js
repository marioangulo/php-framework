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
        $("#button_new").unbind("click").click(function(){
            Page.submitForm("&action=CreateNew");
        });
        $("#button_update").unbind("click").click(function(){
            Page.submitForm("&action=Update");
        });
        $("#button_delete").unbind("click").click(function(){
            if(confirm("Are you sure?")) {
                Page.submitForm("&action=Delete");
            }
        });
    },
    
    /**
     * submitForm
     */
    submitForm: function(extras) {
        F.xhr("id="+ Page.id +"&data-section=record&"+ $("#form").serialize() + extras, function(jResponse){
            if(jResponse["data"]) {
                if(jResponse["data"]["id"]) {
                    location.href = F.engineNamespace +".html?id="+ jResponse["data"]["id"];
                }
                if(jResponse["data"]["delete"]) {
                    location.href = "admin/accounts/index.html";
                }
            }
        }, "POST");
    }
}
