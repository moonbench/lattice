// Shared

/* Core functions */

// XHRs
var xmlhttp;
function new_req(){
    if( window.XMLHttpRequest ){
	return new XMLHttpRequest();
    }
    throw "No request object available";
}
function get( url, callback ){
    xmlhttp = new_req();

    xmlhttp.onreadystatechange = function(){
	if( xmlhttp.readyState != 4 || xmlhttp.status != 200 ) return;
	callback();
    }

    xmlhttp.open("GET", url, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send();
}
function post( url, params, callback, skip_formatting ){
    xmlhttp = new_req();

    xmlhttp.onreadystatechange = function(){
        if( xmlhttp.readyState != 4 || xmlhttp.status != 200 ) return;
        callback();
    }

    params = _.map(params, function(pair){ return pair[0] + "=" + pair[1]; }).join("&");

    xmlhttp.open("POST", url, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(params);
}


// DOM interaction helpers
function hasClass( object, class_name ){
    var classes = object.className.split(" ");
    return classes.indexOf(class_name) >= 0;
}

function toggleClass( object, class_name, preferred_on ) {
    var has_class = hasClass(object, class_name);
    if( (preferred_on == true && !has_class) || (preferred_on == undefined && !has_class) ){
	var classes = object.className.split(" ");
	classes.push(class_name);
	object.className = classes.join(" ");
    } else if(has_class && !preferred_on) {
	var classes = object.className.split(" ");
	classes.splice(classes.indexOf(class_name), 1);
	object.className = classes.join(" ");
    }
}

function closestParentByClassName(object, class_name){
    var node = object;
    for(var i = 0; i < 6; i++){
	if(node.className === undefined) break;
	if( hasClass( node, class_name )) return node;
	node = node.parentNode;
    }
    return null;
}

function is_on_page(object){
    if(!object) return false;

    var object_middle = object.offsetTop + (object.offsetHeight/2);
    var window_top = pageYOffset;
    var window_bottom = window_top + window.innerHeight;

    if(object_middle > window_bottom || object_middle < window_top) return false;
    return true;
}