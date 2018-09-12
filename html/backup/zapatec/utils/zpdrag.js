/**
 * @fileoverview Zapatec Drag library. Used to drag elements. Requires utils.js.
 *
 * <pre>
 * Copyright (c) 2004-2007 by Zapatec, Inc.
 * http://www.zapatec.com
 * 1700 MLK Way, Berkeley, California,
 * 94709, U.S.A.
 * All rights reserved.
 * </pre>
 */

/* $Id: zpdrag.js 7599 2007-07-25 11:13:15Z roman $ */

/**
 * @constructor
 */
Zapatec.Drag = {};

// Emulate window.event in Mozilla for some events. Required for Zapatec.Drag.
Zapatec.Utils.emulateWindowEvent(['mousedown', 'mousemove', 'mouseup']);

/**
 * Holds id of an element that is currently dragged.
 * @private
 */
Zapatec.Drag.currentId = null;

/**
 * Starts dragging an element.
 *
 * <xmp>
 * To make an element draggable, just attach Zapatec.Drag.start function to its
 * mousedown event:
 *
 * <div id="myDiv"
 *  onmousedown="return Zapatec.Drag.start(window.event, this.id)">
 * </div>
 *
 * It is not mandatory to use mousedown event like shown above. You can make
 * the element draggable from any part of your code just by calling
 * Zapatec.Drag.start function, or attach it to other event.
 * </xmp>
 *
 * <pre>
 * Fires static Zapatec events:
 *
 * <b>dragStart</b> before dragging is started. Listener receives following
 * object:
 * {
 *   el: [object] element got by id passed to Zapatec.Drag.start function,
 *   event: [object] event object passed to Zapatec.Drag.start function
 * }
 *
 * <b>dragMove</b> on every mouse move while element is dragged. Listener
 * receives following object:
 * {
 *   el: [object] element got by id passed to Zapatec.Drag.start function,
 *   startLeft: [number] initial left offset,
 *   startTop: [number] initial top offset,
 *   prevLeft: [number] previous left offset,
 *   prevTop: [number] previous top offset,
 *   left: [number] current left offset,
 *   top: [number] current top offset,
 *   realLeft: [number] can be used to determine position or size of the element
 *    if its movement was not limited by vertical or step option,
 *   realTop: [number] can be used to determine position or size of the element
 *    if its movement was not limited by horizontal or step option,
 *   event: [object] event object
 * }
 *
 * <b>dragEnd</b> after element was dropped. Listener receives following object:
 * {
 *   el: [object] element got by id passed to Zapatec.Drag.start function,
 *   startLeft: [number] initial left offset,
 *   startTop: [number] initial top offset,
 *   left: [number] current left offset,
 *   top: [number] current top offset,
 *   realLeft: [number] can be used to determine position or size of the element
 *    if its movement was not limited by vertical or step option,
 *   realTop: [number] can be used to determine position or size of the element
 *    if its movement was not limited by horizontal or step option,
 *   event: [object] event object
 * }
 *
 * Offsets are in pixels and relative to offsetParent of the element.
 *
 * Additional arguments format:
 * {
 *   vertical: [boolean] if true, element moves only vertically,
 *   horizontal: [boolean] if true, element moves only horizontally,
 *   limitTop: [number] element doesn't go beyond this limit when moved up,
 *   limitBottom: [number] element doesn't go beyond this limit when moved down,
 *   limitLeft: [number] element doesn't go beyond this limit when moved to
 *    the left,
 *   limitRight: [number] element doesn't go beyond this limit when moved to
 *    the right,
 *   stepVertical: [number] vertical step in pixels - gives ability to move or
 *    resize element incrementally vertically,
 *   stepHorizontal: [number] horizontal step in pixels - gives ability to move
 *    or resize element incrementally horizontally,
 *   step: [number] sets both stepVertical and stepHorizontal to the same value,
 *   resize: [boolean] if true, instead of dragging element is resized
 * }
 *
 * limitTop, limitBottom, limitLeft and limitRight values are in pixels relative
 * to offsetParent of the element.
 * </pre>
 *
 * @param {object} oEv Event object from which to get mouse position. In most
 * cases this is window.event. Note that when Zapatec.Drag is used, window.event
 * exists in all browsers. When one of mousedown, mousemove or mouseup events
 * occurs, window.event contains that event object.
 * @param {object} sId Id of the element that is dragged
 * @param {object} oArg Optional. Additional arguments
 * @return Always true
 * @type boolean
 */
Zapatec.Drag.start = function(oEv, sId, oArg) {
	var oDrag = Zapatec.Drag;
	var oUtils = Zapatec.Utils;
	// Make sure we are not dragging anything
	if (oDrag.currentId) {
		return true;
	}
	var oEl = Zapatec.Widget.getElementById(sId);
	if (!oEl || oEl.zpDrag) {
		return true;
	}
	// Optional arguments
	if (!oArg) {
		oArg = {};
	}
	// Get mouse position
	var oPos = oUtils.getMousePos(oEv || window.event);
	// Notify all that element is dragged
	Zapatec.EventDriven.fireEvent('dragStart', {
		el: oEl,
		event: oEv
	});
	// Set properties
	// Flags indicating that this element is dragged or resized
	oEl.zpDrag = true;
	if (oArg.resize) {
		oEl.zpDragResize = true;
	}
	// Mousedown event position
	oEl.zpDragPageX = oPos.pageX;
	oEl.zpDragPageY = oPos.pageY;
	// Original element dimensions
	oEl.zpDragWidth = oEl.clientWidth;
	oEl.zpDragHeight = oEl.clientHeight;
	// Original element position
	// offsetLeft doesn't work properly in IE
	var sTag;
	var oOffsetParent = oEl.offsetParent;
	if (oOffsetParent) {
		sTag = oOffsetParent.tagName.toLowerCase();
	}
	if (sTag && sTag != 'body' && sTag != 'html') {
		oPos = oUtils.getElementOffset(oEl);
		var oPosParent = oUtils.getElementOffset(oOffsetParent);
		oEl.zpDragLeft = oPos.left - oPosParent.left;
		oEl.zpDragTop = oPos.top - oPosParent.top;
	} else {
		oEl.zpDragLeft = oEl.offsetLeft;
		oEl.zpDragTop = oEl.offsetTop;
	}
	oEl.zpDragRight = oEl.zpDragLeft + oEl.zpDragWidth;
	oEl.zpDragBottom = oEl.zpDragTop + oEl.zpDragHeight;
	// Previous element position
	oEl.zpDragPrevLeft = oEl.zpDragPrevRealLeft = oEl.zpDragLeft;
	oEl.zpDragPrevTop = oEl.zpDragPrevRealTop = oEl.zpDragTop;
	// Flag indicating that moving only vertically or horizontally
	oEl.zpDragV = oArg.vertical;
	oEl.zpDragH = oArg.horizontal;
	// Movement limits
	oEl.zpDragLimTop =
	 typeof oArg.limitTop == 'number' ? oArg.limitTop : -Infinity;
	oEl.zpDragLimBot =
	 typeof oArg.limitBottom == 'number' ? oArg.limitBottom : Infinity;
	oEl.zpDragLimLft =
	 typeof oArg.limitLeft == 'number' ? oArg.limitLeft : -Infinity;
	oEl.zpDragLimRgh =
	 typeof oArg.limitRight == 'number' ? oArg.limitRight : Infinity;
	// Step
	if (typeof oArg.step == 'number') {
		oEl.zpDragStepV = oEl.zpDragStepH = oArg.step;
	}
	if (typeof oArg.stepVertical == 'number') {
		oEl.zpDragStepV = oArg.stepVertical;
	}
	if (typeof oArg.stepHorizontal == 'number') {
		oEl.zpDragStepH = oArg.stepHorizontal;
	}
	// Save id of currently moved element
	oDrag.currentId = sId;
	// Set event listeners
	oUtils.addEvent(document, 'mousemove', oDrag.move);
	oUtils.addEvent(document, 'mouseup', oDrag.end);
	// Continue event
	return true;
};

/**
 * Moves element to the current mouse position. Gets called on document
 * mousemove event.
 *
 * @private
 * @param {object} oEv Event object
 * @return Always false
 * @type boolean
 */
Zapatec.Drag.move = function(oEv) {
	var oDrag = Zapatec.Drag;
	var oUtils = Zapatec.Utils;
	// Get event
	oEv || (oEv = window.event);
	// Make sure we are dragging anything
	if (!oDrag.currentId) {
		return oUtils.stopEvent(oEv);
	}
	var oEl = document.getElementById(oDrag.currentId);
	if (!(oEl && oEl.zpDrag)) {
		return oUtils.stopEvent(oEv);
	}
	var oSt = oEl.style;
	// Get mouse position
	var oPos = oUtils.getMousePos(oEv);
	// Calculate element position
	var oParam = {
		el: oEl,
		startLeft: oEl.zpDragLeft,
		startTop: oEl.zpDragTop,
		prevLeft: oEl.zpDragPrevLeft,
		prevTop: oEl.zpDragPrevTop,
		left: oEl.zpDragLeft,
		top: oEl.zpDragTop,
		realLeft: oEl.zpDragLeft,
		realTop: oEl.zpDragTop,
		event: oEv
	};
	var iOffset, iPos, iStep, iSize;
	// Horizontal offset
	iOffset = oPos.pageX - oEl.zpDragPageX;
	iStep = oEl.zpDragStepH;
	if (iStep) {
		iPos = oEl.zpDragLeft + Math.floor(iOffset / iStep) * iStep;
		oParam.realLeft = oEl.zpDragPrevRealLeft = oEl.zpDragLeft + iOffset;
	} else {
		oParam.realLeft = oEl.zpDragPrevRealLeft = iPos = oEl.zpDragLeft + iOffset;
	}
	// If it is not vertical
	if (!oEl.zpDragV) {
		// Check limits
		if (oEl.zpDragLimLft <= iPos && oEl.zpDragLimRgh >= iPos) {
			// left and right can't exist together
			if (oSt.right) {
				oSt.right = '';
			}
			if (oEl.zpDragResize) {
				if (iOffset > 0) {
					iSize = oEl.zpDragWidth + iOffset;
					if (iStep) {
						iSize = Math.floor(iSize / iStep) * iStep;
					}
					oSt.left = oEl.zpDragLeft + 'px';
				} else {
					iSize = oEl.zpDragWidth - iOffset;
					if (iStep) {
						iSize = Math.ceil(iSize / iStep) * iStep;
					}
					oSt.left = oEl.zpDragLeft - iSize + 'px';
				}
				oSt.width = iSize + 'px';
			} else {
				oSt.left = iPos + 'px';
			}
			oParam.left = iPos;
			oEl.zpDragPrevLeft = iPos;
		} else {
			oParam.left = oParam.prevLeft;
		}
	}
	// Vertical offset
	iOffset = oPos.pageY - oEl.zpDragPageY;
	iStep = oEl.zpDragStepV;
	if (iStep) {
		iPos = oEl.zpDragTop + Math.floor(iOffset / iStep) * iStep;
		oParam.realTop = oEl.zpDragPrevRealTop = oEl.zpDragTop + iOffset;
	} else {
		iPos = oParam.realTop = oEl.zpDragPrevRealTop = oEl.zpDragTop + iOffset;
	}
	// If it is not horizontal
	if (!oEl.zpDragH) {
		// Check limits
		if (oEl.zpDragLimTop <= iPos && oEl.zpDragLimBot >= iPos) {
			// top and bottom can't exist together
			if (oSt.bottom) {
				oSt.bottom = '';
			}
			if (oEl.zpDragResize) {
				if (iOffset > 0) {
					iSize = oEl.zpDragHeight + iOffset;
					if (iStep) {
						iSize = Math.floor(iSize / iStep) * iStep;
					}
					oSt.top = oEl.zpDragTop + 'px';
				} else {
					iSize = oEl.zpDragHeight - iOffset;
					if (iStep) {
						iSize = Math.ceil(iSize / iStep) * iStep;
					}
					oSt.top = oEl.zpDragBottom - iSize + 'px';
				}
				oSt.height = iSize + 'px';
			} else {
				oSt.top = iPos + 'px';
			}
			oParam.top = iPos;
			oEl.zpDragPrevTop = iPos;
		} else {
			oParam.top = oParam.prevTop;
		}
	}
	// Notify all that element was moved or resized
	Zapatec.EventDriven.fireEvent('dragMove', oParam);
	// Stop event
	return oUtils.stopEvent(oEv);
};

/**
 * Finishes dragging. Gets called on document mouseup event.
 *
 * @private
 * @param {object} oEv Event object
 * @return Always false
 * @type boolean
 */
Zapatec.Drag.end = function(oEv) {
	var oDrag = Zapatec.Drag;
	var oUtils = Zapatec.Utils;
	// Get event
	oEv || (oEv = window.event);
	// Make sure we are dragging anything
	if (!oDrag.currentId) {
		return oUtils.stopEvent(oEv);
	}
	var oEl = document.getElementById(oDrag.currentId);
	if (!(oEl && oEl.zpDrag)) {
		return oUtils.stopEvent(oEv);
	}
	// Remove event listeners
	oUtils.removeEvent(document, 'mousemove', oDrag.move);
	oUtils.removeEvent(document, 'mouseup', oDrag.end);
	// Get element position
	var oParam = {
		el: oEl,
		startLeft: oEl.zpDragLeft,
		startTop: oEl.zpDragTop,
		left: oEl.zpDragPrevLeft,
		top: oEl.zpDragPrevTop,
		realLeft: oEl.zpDragPrevRealLeft,
		realTop: oEl.zpDragPrevRealTop,
		event: oEv
	};
	// Remove properties
	oDrag.currentId = null;
	oEl.zpDrag = null;
	oEl.zpDragPageY = null;
	oEl.zpDragPageX = null;
	oEl.zpDragTop = null;
	oEl.zpDragLeft = null;
	oEl.zpDragPrevTop = null;
	oEl.zpDragPrevLeft = null;
	oEl.zpDragPrevRealTop = null;
	oEl.zpDragPrevRealLeft = null;
	oEl.zpDragV = null;
	oEl.zpDragH = null;
	oEl.zpDragLimTop = null;
	oEl.zpDragLimBot = null;
	oEl.zpDragLimLft = null;
	oEl.zpDragLimRgh = null;
	oEl.zpDragStepV = null;
	oEl.zpDragStepH = null;
	// Notify all that element was dropped
	Zapatec.EventDriven.fireEvent('dragEnd', oParam);
	// Stop event
	return oUtils.stopEvent(oEv);
};
