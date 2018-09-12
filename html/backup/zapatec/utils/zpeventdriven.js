/**
 * @fileoverview Zapatec EventDriven library. Base EventDriven class. Contains
 * functions to handle events and basic methods for event-driven class.
 *
 * <pre>
 * Copyright (c) 2004-2007 by Zapatec, Inc.
 * http://www.zapatec.com
 * 1700 MLK Way, Berkeley, California,
 * 94709, U.S.A.
 * All rights reserved.
 * </pre>
 */

/* $Id: zpeventdriven.js 7323 2007-06-01 21:05:51Z alex $ */

if (typeof Zapatec == 'undefined') {
	/**
	 * @ignore Namespace definition.
	 */
	Zapatec = function() {};
}

/**
 * Base event-driven class.
 * @constructor
 */
Zapatec.EventDriven = function() {};

/**
 * Initializes object.
 * @private
 */
Zapatec.EventDriven.prototype.init = function() {
	// Holds events of this object
	this.events = {};
};

/**
 * Adds event listener to the end of list.
 *
 * <pre>
 * If multiple identical event listeners are registered on the same event, the
 * duplicate instances are discarded. They do not cause the event listener to be
 * called twice, and since the duplicates are discarded, they do not need to be
 * removed manually with the removeEventListener method.
 *
 * Synopsis:
 *
 * oEventDriven.addEventListener('eventName', fEventListener);
 *
 * There is also static method doing the same with global events:
 *
 * Zapatec.EventDriven.addEventListener('globalEventName', fEventListener);
 * </pre>
 *
 * @param {string} sEv Event name
 * @param {function} fLsnr Event listener
 */
Zapatec.EventDriven.prototype.addEventListener = function(sEv, fLsnr) {
	if (typeof fLsnr != "function") {
		return false;
	}
	var oE = this.events;
	if (!oE[sEv]) {
		oE[sEv] = {
			listeners: []
		};
	} else {
		this.removeEventListener(sEv, fLsnr);
	}
	oE[sEv].listeners.push(fLsnr);
};

/**
 * Adds event listener to the beginning of list. Note that there is no guarantee
 * that it will be always first in the list. It will become second once this
 * method is called again. Never rely on that!
 *
 * <pre>
 * If multiple identical event listeners are registered on the same event, the
 * duplicate instances are discarded. They do not cause the event listener to be
 * called twice, and since the duplicates are discarded, they do not need to be
 * removed manually with the removeEventListener method.
 *
 * Synopsis:
 *
 * oEventDriven.unshiftEventListener('eventName', fEventListener);
 *
 * There is also static method doing the same with global events:
 *
 * Zapatec.EventDriven.unshiftEventListener('globalEventName', fEventListener);
 * </pre>
 *
 * @param {string} sEv Event name
 * @param {function} fLsnr Event listener
 */
Zapatec.EventDriven.prototype.unshiftEventListener = function(sEv, fLsnr) {
	if (typeof fLsnr != "function") {
		return false;
	}
	var oE = this.events;
	if (!oE[sEv]) {
		oE[sEv] = {
			listeners: []
		};
	} else {
		this.removeEventListener(sEv, fLsnr);
	}
	oE[sEv].listeners.unshift(fLsnr);
};

/**
 * Removes event listener.
 *
 * <pre>
 * Synopsis:
 *
 * oEventDriven.removeEventListener('eventName', fEventListener);
 *
 * There is also static method doing the same with global events:
 *
 * Zapatec.EventDriven.removeEventListener('globalEventName', fEventListener);
 * </pre>
 *
 * @param {string} sEv Event name
 * @param {function} fLsnr Event listener
 * @return Number of listeners removed
 * @type number
 */
Zapatec.EventDriven.prototype.removeEventListener = function(sEv, fLsnr) {
	var oE = this.events;
	if (!oE[sEv]) {
		return 0;
	}
	var aL = oE[sEv].listeners;
	var iRemoved = 0;
	for (var iL = aL.length - 1; iL >= 0; iL--) {
		if (aL[iL] == fLsnr) {
			aL.splice(iL, 1);
			iRemoved++;
		}
	}
	return iRemoved;
};

/**
 * Returns array of listeners for the specified event.
 *
 * <pre>
 * Synopsis:
 *
 * oEventDriven.getEventListeners('eventName');
 *
 * There is also static method doing the same with global events:
 *
 * Zapatec.EventDriven.getEventListeners('globalEventName');
 * </pre>
 *
 * @param {string} sEv Event name
 * @return Array of function references
 * @type object
 */
Zapatec.EventDriven.prototype.getEventListeners = function(sEv) {
	var oE = this.events;
	if (!oE[sEv]) {
		return [];
	}
	return oE[sEv].listeners;
};

/**
 * Checks if the event listener is attached to the event.
 *
 * <pre>
 * Synopsis:
 *
 * oEventDriven.isEventListener('eventName', fEventListener);
 *
 * There is also static method doing the same with global events:
 *
 * Zapatec.EventDriven.isEventListener('globalEventName', fEventListener);
 * </pre>
 *
 * @param {string} sEv Event name
 * @param {function} fLsnr Event listener
 * @return True if event listener is attached to the event
 * @type boolean
 */
Zapatec.EventDriven.prototype.isEventListener = function(sEv, fLsnr) {
	var oE = this.events;
	if (!oE[sEv]) {
		return false;
	}
	var aL = oE[sEv].listeners;
	for (var iL = aL.length - 1; iL >= 0; iL--) {
		if (aL[iL] == fLsnr) {
			return true;
		}
	}
	return false;
};

/**
 * Checks if the event exists.
 *
 * <pre>
 * Synopsis:
 *
 * oEventDriven.isEvent('eventName');
 *
 * There is also static method doing the same with global events:
 *
 * Zapatec.EventDriven.isEvent('globalEventName');
 * </pre>
 *
 * @param {string} sEv Event name
 * @return Exists
 * @type boolean
 */
Zapatec.EventDriven.prototype.isEvent = function(sEv) {
	if (this.events[sEv]) {
		return true;
	}
	return false;
};

/**
 * Removes all listeners for the event.
 *
 * <pre>
 * Synopsis:
 *
 * oEventDriven.removeEvent('eventName');
 *
 * There is also static method doing the same with global events:
 *
 * Zapatec.EventDriven.removeEvent('globalEventName');
 * </pre>
 *
 * @param {string} sEv Event name
 */
Zapatec.EventDriven.prototype.removeEvent = function(sEv) {
	var oE = this.events;
	if (oE[sEv]) {
		var undef;
		oE[sEv] = undef;
	}
};

/**
 * Fires event. Takes in one mandatory argument sEv and optionally any number of
 * other arguments that should be passed to the listeners.
 *
 * <pre>
 * Synopsis:
 *
 * oEventDriven.fireEvent('eventName');
 *
 * There is also static method doing the same with global events:
 *
 * Zapatec.EventDriven.fireEvent('globalEventName');
 * </pre>
 *
 * @param {string} sEv Event name
 */
Zapatec.EventDriven.prototype.fireEvent = function(sEv) {
	var oE = this.events;
	if (!oE[sEv]) {
		return;
	}
	// Duplicate array because it may be modified from within the listeners
	var aL = oE[sEv].listeners.slice();
	var iLs = aL.length;
	var aArgs;
	for (var iL = 0; iLs--; iL++) {
		// Remove first argument
		aArgs = [].slice.call(arguments, 1);
		// Call in scope of this object
		aL[iL].apply(this, aArgs);
	}
};

/**
 * Holds global events.
 * @private
 */
Zapatec.EventDriven.events = {};

/**
 * Adds event listener to global event to the end of list.
 *
 * <pre>
 * If multiple identical event listeners are registered on the same event, the
 * duplicate instances are discarded. They do not cause the event listener to be
 * called twice, and since the duplicates are discarded, they do not need to be
 * removed manually with the removeEventListener method.
 * </pre>
 *
 * @param {string} sEv Event name
 * @param {function} fLsnr Event listener
 */
Zapatec.EventDriven.addEventListener = function(sEv, fLsnr) {
	if (typeof fLsnr != "function") {
		return false;
	}
	var oED = Zapatec.EventDriven;
	var oE = oED.events;
	if (!oE[sEv]) {
		oE[sEv] = {
			listeners: []
		};
	} else {
		oED.removeEventListener(sEv, fLsnr);
	}
	oE[sEv].listeners.push(fLsnr);
};

/**
 * Adds event listener to global event to the beginning of list. Note that there
 * is no guarantee that it will be always first in the list. It will become
 * second once this method is called again. Never rely on that!
 *
 * <pre>
 * If multiple identical event listeners are registered on the same event, the
 * duplicate instances are discarded. They do not cause the event listener to be
 * called twice, and since the duplicates are discarded, they do not need to be
 * removed manually with the removeEventListener method.
 * </pre>
 *
 * @param {string} sEv Event name
 * @param {function} fLsnr Event listener
 */
Zapatec.EventDriven.unshiftEventListener = function(sEv, fLsnr) {
	if (typeof fLsnr != "function") {
		return false;
	}
	var oED = Zapatec.EventDriven;
	var oE = oED.events;
	if (!oE[sEv]) {
		oE[sEv] = {
			listeners: []
		};
	} else {
		oED.removeEventListener(sEv, fLsnr);
	}
	oE[sEv].listeners.unshift(fLsnr);
};

/**
 * Removes event listener from global event.
 *
 * @param {string} sEv Event name
 * @param {function} fLsnr Event listener
 * @return number of listeners removed
 * @type number
 */
Zapatec.EventDriven.removeEventListener = function(sEv, fLsnr) {
	var oE = Zapatec.EventDriven.events;
	if (!oE[sEv]) {
		return 0;
	}
	var aL = oE[sEv].listeners;
	var iRemoved = 0;
	for (var iL = aL.length - 1; iL >= 0; iL--) {
		if (aL[iL] == fLsnr) {
			aL.splice(iL, 1);
			iRemoved++;
		}
	}
	return iRemoved;
};

/**
 * Returns array of listeners for the specified global event.
 *
 * @param {string} sEv Event name
 * @return Array of function references
 * @type object
 */
Zapatec.EventDriven.getEventListeners = function(sEv) {
	var oE = Zapatec.EventDriven.events;
	if (!oE[sEv]) {
		return [];
	}
	return oE[sEv].listeners;
};

/**
 * Checks if the event listener is attached to the global event.
 *
 * @param {string} sEv Event name
 * @param {function} fLsnr Event listener
 * @return True if event listener is attached to the event
 * @type boolean
 */
Zapatec.EventDriven.isEventListener = function(sEv, fLsnr) {
	var oE = Zapatec.EventDriven.events;
	if (!oE[sEv]) {
		return false;
	}
	var aL = oE[sEv].listeners;
	for (var iL = aL.length - 1; iL >= 0; iL--) {
		if (aL[iL] == fLsnr) {
			return true;
		}
	}
	return false;
};

/**
 * Checks if the global event exists.
 *
 * @param {string} sEv Event name
 * @return Exists
 * @type boolean
 */
Zapatec.EventDriven.isEvent = function(sEv) {
	if (Zapatec.EventDriven.events[sEv]) {
		return true;
	}
	return false;
};

/**
 * Removes all listeners for the global event.
 *
 * @param {string} sEv Event name
 */
Zapatec.EventDriven.removeEvent = function(sEv) {
	var oE = Zapatec.EventDriven.events;
	if (oE[sEv]) {
		var undef;
		oE[sEv] = undef;
	}
};

/**
 * Fires global event. Takes in one mandatory argument sEv and optionally any
 * number of other arguments that should be passed to the listeners.
 *
 * @param {string} sEv Event name
 */
Zapatec.EventDriven.fireEvent = function(sEv) {
	var oE = Zapatec.EventDriven.events;
	if (!oE[sEv]) {
		return;
	}
	// Duplicate array because it may be modified from within the listeners
	var aL = oE[sEv].listeners.slice();
	var iLs = aL.length;
	var aArgs;
	for (var iL = 0; iLs--; iL++) {
		// Remove first argument
		aArgs = [].slice.call(arguments, 1);
		// Call listener
		aL[iL].apply(aL[iL], aArgs);
	}
};
