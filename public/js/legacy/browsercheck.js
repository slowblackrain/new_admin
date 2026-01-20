/**
*
*  IE check fucntion  
*
**/
 
var Browser = new function(){
 
	this.detectIE = function() {
		var ua = window.navigator.userAgent;
		
		var msie = ua.indexOf('MSIE');
		if( msie > 0 ) {
			var num = parseInt(ua.substring(msie + 6, ua.indexOf('.', msie)), 10);
			if( isNaN(num) ) num = 1;			
			return num;
		} 
		
		var trident = ua.indexOf('Trident/');
		if( trident > 0 ) {
			// if ie11 then version number
			var rv = ua.indexOf('rv:');
			return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);			
		}
			
	    var edge = ua.indexOf('Edge/');
	    if(edge > 0 ) {
	    	// if ie12+ then version number
	    	return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)),10);
	    }
	    // other browser
	    return false;
	};
 
};