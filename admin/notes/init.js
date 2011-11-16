//##########################################################################################

//--> Setup :: Page
	Notes = {};
	Notes.Container = undefined;
	Notes.DataField = undefined;
	Notes.PivotTable = undefined;
	Notes.PivotID = undefined;
//<-- End Setup :: Page

//##########################################################################################

//--> Begin Function :: Build
	Notes.Build = function() {
		//find notes widgets
		Notes.Container = $("[data-section='notes']").get(0);
		if(Notes.Container) {
			Notes.PivotTable = Notes.Container.getAttribute("data-notes-pivot");
			Notes.PivotID = Notes.Container.getAttribute("data-notes-id");
			F.XHR("fk_pivot_table="+ Notes.PivotTable +"&fk_pivot_id="+ Notes.PivotID +"&data-section=notes", "Notes.AjaxCallBack", "GET", "admin/notes/init.html");
		}
	};
//<-- End Function :: Build

//##########################################################################################

//--> Begin Function :: AjaxCallBack
	Notes.AjaxCallBack = function(JResponse, XHR) {
		Notes.AttachEvents();
	};
//<-- End Function :: AjaxCallBack

//##########################################################################################

//--> Begin Function :: AttachEvents
	Notes.AttachEvents = function() {
		$("a#save_button").unbind("click").click(function() {
			var tmpNotes = $("textarea[name='note_data']").val();
			if(tmpNotes != "") {
				F.XHR("action=Add+Note&data="+ encodeURIComponent(tmpNotes) +"&fk_pivot_table="+ Notes.PivotTable +"&fk_pivot_id="+ Notes.PivotID +"&data-section=notes", "Notes.AjaxCallBack", "POST", "admin/notes/init.html");
			}
		});
	};
//<-- End Function :: AttachEvents

//##########################################################################################
	
	//start when ready
	$(document).ready(Notes.Build);
	
//##########################################################################################