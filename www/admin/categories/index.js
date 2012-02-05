//js page object

var Page = {
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
        //find our select filters
        $(".filters select").unbind("change").change(function() {
            Page.filterResults();
        });
        
        //paging
        $("a[data-page]").unbind("click").click(function() {
            Page.filterResults("&"+ $(this).attr("data-page"));
        });
    },
    
    /**
     * get new results
     */
    filterResults: function(extras) {
        if(extras == undefined) {
            extras = "";
        }
        F.xhr("data-section=results&"+ $(".filters").serialize() + extras);
    }
}
