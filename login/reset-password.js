//##########################################################################################

//--> Setup :: Page
	P = {};
//<-- End Setup :: Page

//##########################################################################################

//--> Begin Function :: Init
	P.Init = function() {
		//find the edit buttons
		$("[name='password']")focus();
	};
//<-- End Function :: Init

//##########################################################################################
	
	//start when ready
	$(document).ready(P.Init);
	
//##########################################################################################