//##########################################################################################

//--> Setup :: Footprint
	F = {};
	F.PageNamespace = undefined;
	F.XHRCryBaby = false;
//<-- End Setup :: RootSearch

//##########################################################################################

//--> Begin Function :: Init
	F.Init = function() {
		//set page namespace
		var myLocation = window.location.toString();
		myLocation = myLocation.replace("http://", "");
		myLocation = myLocation.replace("https://", "");
		F.PageNamespace = myLocation.substring(myLocation.indexOf("/") + 1, myLocation.lastIndexOf("."));
		
		//mark active tab
		$("[id='tab-"+ F.PageNamespace +"']").attr("class", "active");
		
		//setup data-confirm functionality
		$("[data-confirm]").click(function() {
			return(confirm($(this).attr("data-confirm")));
		});
		
		//tab drop down bindings
		$("body").bind("click", function (e) {
			$('.dropdown-toggle, .menu').parent("li").removeClass("open");
		});
		$(".dropdown-toggle, .menu").click(function (e) {
			var $li = $(this).parent("li").toggleClass('open');
			return false;
		});
		
		//setup the date pickers
		$("[data-date-picker]").datepicker();
		
		//setup the time ago feature
		$("[data-time-ago]").each(function() {
			var TotalSecondsAgo = parseInt($(this).text()) ? parseInt($(this).text()) : 0;
			var Depth = parseInt($(this).attr("data-time-ago")) ? parseInt($(this).attr("data-time-ago")) : 1;
			
			var TimeAgo = new Array();
			TimeAgo.push({value: Math.floor(TotalSecondsAgo / 86400), type: "day"});
			TimeAgo.push({value: Math.floor((TotalSecondsAgo / 3600) - (TimeAgo[0].value * 24)), type: "hour"});
			TimeAgo.push({value: Math.floor((TotalSecondsAgo / 60) - (TimeAgo[0].value * 1440) - (TimeAgo[1].value * 60)), type: "minute"});
			TimeAgo.push({value: Math.floor(TotalSecondsAgo % 60), type: "second"});
			
			var Output = "";
			for(var i = 0 ; i < TimeAgo.length ; i++) {
				if(TimeAgo[i].value != 0) {
					Output += TimeAgo[i].value +" "+ TimeAgo[i].type + (TimeAgo[i].value > 1 ? "s" : "") +", ";
					Depth--; if(Depth <= 0) { break; }
				}
			}
			
			//is the output empty
			if(Output == "") { Output = "0 seconds, "; }
			
			//remove the last comma+space
			Output = Output.substr(0, Output.length - 2);
			
			//place the new value
			$(this).text(Output +" ago");
		});
	};
//<-- End Function :: Init

//##########################################################################################

//--> Begin Function :: XHR
	F.XHR = function(Data, CallBack, Type, URL) {
		if(Data == undefined) { Data = ""; }
		if(CallBack == undefined) { CallBack = ""; }
		if(Type == undefined || Type == "") { Type = "GET"; }
		if(URL == undefined || URL == "") { URL = F.PageNamespace +".html"; }
		
		//create ajax object
		var XHR = new Object();
		XHR.dataType = "json";
		XHR.type = Type;
		XHR.data = Data;
		XHR.url = URL;
		
		//loading handler
		XHR.beforeSend = function(request) { $("[class='ajax-running']").show(100); };
		
		//success handler
		XHR.success = function(JResponse, status, response) {
			//hide loading indicator
			$("[class='ajax-running']").hide();
			
			//process sections
			if(typeof(JResponse) == "object") {
				if(JResponse["sections"]) {
					F.ProcessSections(JResponse["sections"]);
				}
				//do we have a callback?
				if(CallBack) {
					if(typeof(CallBack) == "function") {
						CallBack(JResponse, this);
					}
					else {
						eval(CallBack +"(JResponse, this)");
					}
				}
			}
		};
		
		//error handler
		XHR.error = function(response, text, error){
			//hide loading indicator
			$("[class='ajax-running']").hide();
			
			//should we cry?
			if(F.XHRCryBaby) {
				//notify of error
				alert("F.XHR: There was an error.\n"+ error);
				
				//get response and show in pop-up
				var ErrorWindow = window.open("", "Error", "status=no,scrollbars=yes,resizable=yes,width=640,Height=480");
				if(ErrorWindow){
					ErrorWindow.document.write(response.responseText);
				}
			}
		};
		
		//execute request
		$.ajax(XHR);
	};
//<-- End Function :: XHR

//##########################################################################################

//--> Begin Function :: ProcessSections
	F.ProcessSections = function(Sections) {
		for(var Section in Sections) {
			$("[data-section='"+ Section +"']").html(Sections[Section]);
		}
		
		if(typeof(P) != "undefined") {
			if(typeof(P.AttachEvents) != "undefined") {
				P.AttachEvents();
			}
		}
	};
//<-- End Function :: ProcessSections

//##########################################################################################

//--> Begin Function :: Input
	F.Input = function(name) {
    	var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
		return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
	};
//<-- End Function :: Input

//##########################################################################################

	//start when ready
	$(document).ready(F.Init);
	
//##########################################################################################