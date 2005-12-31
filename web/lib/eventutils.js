// from Todd Ditchendorf 

/** @constructor */
function EventUtils() {
	throw 'RuntimeException: EventUtils is a utility class with only static ' +
		' methods and may not be instantiated';
}		

/**
 *  @access static
 *  @param HTMLElement target
 *  @param string type
 *  @param Function callback
 *  @param boolean captures
 */
EventUtils.addEventListener = function (target,type,callback,captures) {
	if (target.addEventListener) {
		// EOMB
		target.addEventListener(type,callback,captures);
	} else if (target.attachEvent) {
		// IE
		target.attachEvent('on'+type,callback,captures);
	} else {
		// IE 5 Mac and some others
		target['on'+type] = callback;
	}
}			

/**
 *  @access static
 *  @param HTMLElement target
 *  @param string type
 *  @param Function callback
 *  @param boolean captures
 */
EventUtils.removeEventListener = function (target,type,callback,captures) {
	if (target.removeEventListener) {
		// EOMB
		target.removeEventListener(type,callback,captures);
	} else if (target.detachEvent) {
		// IE
		target.detachEvent('on'+type,callback,captures);
	} else {
		// IE 5 Mac and some others
		target['on'+type] = null;
	}
}			

/**
 *	@constructor
 *	@param Event (EOMB) | undefined (IE)
 *
 */
function Evt(evt) {
	var docEl 	 = document.documentElement;
	var body  	 = document.body;
	
	this._evt 	 = (evt) ? evt : 
				   (window.event) ? window.event : null;
	this._source = (evt.target) ? evt.target : 
				   (evt.srcElement) ? evt.srcElement : null;
	this._x		 = (evt.pageX) ? evt.pageX : 
				   (docEl.scrollLeft) ? (docEl.scrollLeft + evt.clientX) : 
				   (body.scrollLeft) ? (body.scrollLeft + evt.clientX) : evt.clientX;
	this._y 	 = (evt.pageY) ? evt.pageY : 
				   (docEl.scrollTop) ? (docEl.scrollTop + evt.clientY) :
				   (body.scrollTop) ? (body.scrollTop + evt.clientY) : evt.clientY;
}

/** @returns number */
Evt.prototype.getX = function () {
	return this._x;
};

/** @returns number */
Evt.prototype.getY = function () {
	return this._y;
};

/** @returns HTMLElement */
Evt.prototype.getSource = function () {
	return this._source;
};

/** 
 *	@returns void
 */
Evt.prototype.consume = function () {
	if (!this._evt) return;
	if (this._evt.stopPropagation) {
		this._evt.stopPropagation();
		this._evt.preventDefault();
	} else if (typeof this._evt.cancelBubble != undefined) {
		this._evt.cancelBubble = true;
		this._evt.returnValue  = false;
	} else {
		this._evt = null;
	}
};

