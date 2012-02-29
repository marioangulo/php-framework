//weblegs js footprint engine

var F = {
    engineNamespace: undefined,
    xhrCryBaby: false,
    
    setup: function() {
        //set page namespace
        var myLocation = window.location.pathname;
        myLocation = myLocation.substring(1, myLocation.length);
        myLocation = myLocation.replace(".html", "");
        if(myLocation == "") { myLocation = "index"; }
        F.engineNamespace = myLocation;
    },
    
    /**
     * the global init (first thing added to "$(document).ready(xyz)"
     */
    init: function() {
        //mark active tab
        $("[id='tab-"+ (F.engineNamespace == "" ? "index" : F.engineNamespace) +"']").attr("class", "active");
        
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
            var totalSecondsAgo = parseInt($(this).text()) ? parseInt($(this).text()) : 0;
            var depth = parseInt($(this).attr("data-time-ago")) ? parseInt($(this).attr("data-time-ago")) : 1;
            
            var timeAgo = new Array();
            timeAgo.push({value: Math.floor(totalSecondsAgo / 86400), type: "day"});
            timeAgo.push({value: Math.floor((totalSecondsAgo / 3600) - (timeAgo[0].value * 24)), type: "hour"});
            timeAgo.push({value: Math.floor((totalSecondsAgo / 60) - (timeAgo[0].value * 1440) - (timeAgo[1].value * 60)), type: "minute"});
            timeAgo.push({value: Math.floor(totalSecondsAgo % 60), type: "second"});
            
            var output = "";
            for(var i = 0 ; i < timeAgo.length ; i++) {
                if(timeAgo[i].value != 0) {
                    output += timeAgo[i].value +" "+ timeAgo[i].type + (timeAgo[i].value > 1 ? "s" : "") +", ";
                    depth--; if(depth <= 0) { break; }
                }
            }
            
            //is the output empty
            if(output == "") { output = "0 seconds, "; }
            
            //remove the last comma+space
            output = output.substr(0, output.length - 2);
            
            //place the new value
            $(this).text(output +" ago");
        });
        
        //call the Page.init if it exists
        if(typeof(Page) != "undefined") {
            Page.init();
        }
    },
    
    /**
     * our xhr shorthand method
     */
    xhr: function(argData, argCallBack, argType, argURL) {
        if(argData == undefined) { argData = ""; }
        if(argCallBack == undefined) { argCallBack = ""; }
        if(argType == undefined || argType == "") { argType = "GET"; }
        if(argURL == undefined || argURL == "") { argURL = F.engineNamespace +".html"; }
        
        //create ajax object
        var xhrInstance = {
            datatype: "json",
            type: argType,
            data: argData,
            url: argURL,
            
            //loading handler
            beforeSend: function(request) { $(".ajax-running").show(100); },
            
            //success handler
            success: function(jResponse, status, response) {
                //hide loading indicator
                $(".ajax-running").hide();
                
                //if we got logged out, redirect back to re-initiate login
                if(response.getResponseHeader("x-login-status")) {
                    if(response.getResponseHeader("x-login-status") == "logged_out" && F.engineNamespace != "login/index") {
                        location.href = location.href;
                    }
                }
                
                //process sections
                if(typeof(jResponse) == "object") {
                    if(jResponse["sections"]) {
                        F.processSections(jResponse["sections"]);
                    }
                    //do we have a callback?
                    if(argCallBack) {
                        if(typeof(argCallBack) == "function") {
                            argCallBack(jResponse, response);
                        }
                        else {
                            eval(argCallBack +"(jResponse, this)");
                        }
                    }
                }
            },
            
            //error handler
            error: function(response, text, error){
                //hide loading indicator
                $(".ajax-running").hide();
                
                //should we cry?
                if(F.xhrCryBaby) {
                    //notify of error
                    alert("F.xhr: There was an error.\n"+ error);
                    
                    //get response and show in pop-up
                    var errorWindow = window.open("", "Error", "status=no,scrollbars=yes,resizable=yes,width=640,Height=480");
                    if(errorWindow){
                        errorWindow.document.write(response.responseText);
                    }
                }
            }
        };
        
        //execute request
        $.ajax(xhrInstance);
    },
    
    /**
     * proccesses the xhr request sections
     */
    processSections: function(sections) {
        for(var section in sections) {
            $("[data-section='"+ section +"']").html(sections[section]);
        }
        
        if(typeof(Page) != "undefined") {
            if(typeof(Page.attachEvents) != "undefined") {
                Page.attachEvents();
            }
        }
    },
    
    /**
     * our shorthand method for querystring data
     */
    input: function(name) {
        var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
        var result = match && decodeURIComponent(match[1].replace(/\+/g, ' '));
        if(result) {
            return result;
        }
        return "";
    }
};

/**
 * setup engine before anything else happens
 */
F.setup();

/**
 * register our engine init method with jquery's ready event
 */
$(document).ready(F.init);
