//js note object

var Notes = {
    container: undefined,
    dataField: undefined,
    pivotTable: undefined,
    pivotID: undefined,
    
    /**
     * intializes with the document
     */
    init: function() {
        //find notes widgets
        Notes.container = $("[data-section='notes']").get(0);
        if(Notes.container) {
            Notes.pivotTable = Notes.container.getAttribute("data-notes-pivot");
            Notes.pivotID = Notes.container.getAttribute("data-notes-id");
            F.xhr("fk_pivot_table="+ Notes.pivotTable +"&fk_pivot_id="+ Notes.pivotID +"&data-section=notes", "Notes.ajaxCallBack", "GET", "admin/notes/init.html");
        }
    },
    
    /**
     * our own xhr callback
     */
    ajaxCallBack: function(jResponse, xhr) {
        Notes.attachEvents();
    },
    
    /**
     * detach|attach events events when the xhr changes the dom
     */
    attachEvents: function() {
        $("a#save_button").unbind("click").click(function() {
            var tmpNotes = $("textarea[name='note_data']").val();
            if(tmpNotes != "") {
                F.xhr("action=Add+Note&data="+ encodeURIComponent(tmpNotes) +"&fk_pivot_table="+ Notes.pivotTable +"&fk_pivot_id="+ Notes.pivotID +"&data-section=notes", "Notes.ajaxCallBack", "POST", "admin/notes/init.html");
            }
        });
    }
};

/**
 * register our notes init method with jquery's ready event
 */
$(document).ready(Notes.init);
