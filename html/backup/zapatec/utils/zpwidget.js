/**
 * @fileoverview Zapatec Widget library. Base Widget class.
 *
 * <pre>
 * Copyright (c) 2004-2007 by Zapatec, Inc.
 * http://www.zapatec.com
 * 1700 MLK Way, Berkeley, California,
 * 94709, U.S.A.
 * All rights reserved.
 * </pre>
 */

/* $Id: zpwidget.js 7634 2007-07-31 13:44:21Z alex $ */

if (typeof Zapatec == 'undefined') {
	/**
	 * @ignore Namespace definition.
	 */
	Zapatec = function() {};
}

/**
 * Base widget class.
 *
 * <pre>
 * Defines following config options:
 *
 * <b>theme</b> [string] Theme name that will be used to display the widget.
 * Corresponding CSS file will be picked and added into the HTML document
 * automatically. Case insensitive. Default: "default".
 * May also contain relative or absolute URL of themes directory.
 * E.g. "../themes/default.css" or "http://my.web.host/themes/default.css".
 *
 * Special theme "auto" is used to detect theme automatically depending from OS.
 * It is replaced automatically with one of the following values:
 *
 * <i>winvista</i> for Windows Vista
 * <i>winxp</i> for Windows XP
 * <i>win2k</i> for other Windows
 * <i>macosx</i> for Mac OSX
 * <i>default</i> for other OS
 *
 * Make sure all these themes exist before using "auto" theme for the widget.
 *
 * <b>themePath</b> [string] Relative or absolute URL to themes directory.
 * Trailing slash is required. Default: path to child widget's file +
 * "../themes/". You may also include path into "theme" option instead of using
 * "themePath" option.
 *
 * <b>asyncTheme</b> [boolean] Load theme asynchronously. This means that script
 * execution will not be suspended until theme is loaded. Theme will be applied
 * once it is loaded. Default: false.
 *
 * <b>source</b> Depends on "sourceType" option. Possible sources:
 * -----------------------------------------------------------------------------
 * sourceType     | source
 * ---------------|-------------------------------------------------------------
 * 1) "html"      | [object or string] HTMLElement or its id.
 * 2) "html/text" | [string] HTML fragment.
 * 3) "html/url"  | [string] URL of HTML fragment.
 * 4) "json"      | [object or string] JSON object or string (http://json.org).
 * 5) "json/url"  | [string] URL of JSON data source.
 * 6) "xml"       | [object or string] XMLDocument object or XML string.
 * 7) "xml/url"   | [string] URL of XML data source.
 * -----------------------------------------------------------------------------
 *
 * <b>sourceType</b> [string] Used together with "source" option to specify how
 * source should be processed. Possible source types:
 * "html", "html/text", "html/url", "json", "json/url", "xml", "xml/url".
 * JSON format is described at http://www.json.org.
 *
 * <b>callbackSource</b> [function] May be used instead of "source" and
 * "sourceType" config options to get source depending on passed arguments.
 * Receives object with passed arguments. Must return following object:
 * {
 *   source: [object or string] see table above for possible sources,
 *   sourceType: [string] see table above for possible source types
 * }
 *
 * <b>asyncSource</b> [boolean] Load source asynchronously. This means that
 * script execution will not be suspended until source is loaded. Source will be
 * processed once it is loaded. Default: true.
 *
 * <b>reliableSource</b> [boolean] Used together with "json" or "json/url"
 * sourceType to skip JSON format verification. It saves a lot of time for large
 * data sets. Default: true.
 *
 * <b>callbackConvertSource</b> [function] May be used to convert input data
 * passed with "source" config option from custom format into internal format of
 * the widget. Must return converted source.
 *
 * <b>eventListeners</b> [object] Associative array with event listeners:
 * {
 *   [string] event name: [function or object] event listener or array of event
 *    listeners,
 *   ...
 * }
 *
 * Defines internal property <b>config</b>.
 * </pre>
 *
 * @constructor
 * @extends Zapatec.EventDriven
 * @param {object} oArg User configuration
 */
Zapatec.Widget = function(oArg) {
	// User configuration
	this.config = {};
	// Call constructor of superclass
	Zapatec.Widget.SUPERconstructor.call(this);
	// Initialize object
	this.init(oArg);
};

// Inherit EventDriven
Zapatec.inherit(Zapatec.Widget, Zapatec.EventDriven);

/**
 * Holds path to this file.
 * @private
 */
Zapatec.Widget.path = Zapatec.getPath('Zapatec.Widget');

/**
 * Initializes object.
 *
 * <pre>
 * Important: Before calling this method, define config options for the widget.
 * Initially "this.config" object should contain all config options with their
 * default values. Then values of config options will be changed with user
 * configuration in this method. Config options provided by user that were not
 * found in "this.config" object will be ignored.
 *
 * Defines internal property <b>id</b>.
 * </pre>
 *
 * @param {object} oArg User configuration
 */
Zapatec.Widget.prototype.init = function(oArg) {
	// Call parent method
	Zapatec.Widget.SUPERclass.init.call(this);
	// Add this widget to the list
	if (typeof this.id == 'undefined') {
		// Find id
		var iId = 0;
		while (Zapatec.Widget.all[iId]) {
			iId++;
		}
		this.id = iId;
		Zapatec.Widget.all[iId] = this;
	}
	// Configure
	this.configure(oArg);
	// Add custom event listeners
	this.addUserEventListeners();
	// Add standard event listeners
	this.addStandardEventListeners();
	// init language variables
	this.initLang();
	// Load theme
	this.loadTheme();
};

/**
 * Reconfigures the widget with new config options after it was initialized.
 * May be used to change look or behavior of the widget after it has loaded
 * the data. In the argument pass only values for changed config options.
 * There is no need to pass config options that were not changed.
 *
 * <pre>
 * Note: "eventListeners" config option is ignored by this method because it is
 * useful only on initialization. To add event listener after the widget was
 * initialized, use addEventListener method instead.
 * </pre>
 *
 * @param {object} oArg Changes to user configuration
 */
Zapatec.Widget.prototype.reconfigure = function(oArg) {
	// Configure
	this.configure(oArg);
	// Load theme
	this.loadTheme();
	// reinit language variables
	if(oArg.lang || oArg.langCountryCode || oArg.langEncoding){
		this.langStr = this.config.lang;

		if(this.config.langCountryCode && this.config.langCountryCode.length > 0){
			this.langStr += "_" + this.config.langCountryCode; 
		}

		if(this.config.langEncoding && this.config.langEncoding.length > 0){
			this.langStr += "-" + this.config.langEncoding; 
		}
	}

	if(
		this.config.lang && 
		this.config.lang.length > 0 &&
		!(
			Zapatec.Langs[this.config.langId] &&
			Zapatec.Langs[this.config.langId][this.langStr]
		)
	){
		Zapatec.Log({description:
			this.config.lang + (
				this.config.langCountryCode ? 
				" and country code " + this.config.langCountryCode : ""
			) + (
				this.config.langEncoding ? 
				" and encoding " + this.config.langEncoding : ""
			)
		});

		this.config.lang = null;
		this.config.langEncoding = null;
		this.langStr = null;
	}
};

/**
 * Configures widget.
 *
 * @param {object} oArg User configuration
 */
Zapatec.Widget.prototype.configure = function(oArg) {
	// Default configuration
	this.defineConfigOption('theme', 'default');
	var sPath = this.constructor.path;
	if (typeof sPath != 'undefined') {
		this.defineConfigOption('themePath', sPath + '../themes/');
	} else {
		this.defineConfigOption('themePath', '../themes/');
	}
	this.defineConfigOption('asyncTheme', false);
	this.defineConfigOption('source');
	this.defineConfigOption('sourceType');
	this.defineConfigOption('callbackSource');
	this.defineConfigOption('asyncSource', true);
	this.defineConfigOption('reliableSource', true);
	this.defineConfigOption('callbackConvertSource');
	this.defineConfigOption('eventListeners', {});
	this.defineConfigOption('langId');
	this.defineConfigOption('lang');
	this.defineConfigOption('langCountryCode');
	this.defineConfigOption('langEncoding');
	// Get user configuration
	if (oArg) {
		var oConfig = this.config;
		for (var sOption in oArg) {
			if (typeof oConfig[sOption] != 'undefined') {
				oConfig[sOption] = oArg[sOption];
			} else {
				Zapatec.Log({
					description: "Unknown config option: " + sOption
				});
			}
		}
	}
};

/**
 * Returns current configuration of the widget.
 *
 * @return Current configuration
 * @type object
 */
Zapatec.Widget.prototype.getConfiguration = function() {
	return this.config;
};

/**
 * Array to access any widget on the page by its id number.
 * @private
 */
Zapatec.Widget.all = [];

/**
 * Finds a widget by id.
 *
 * @param {number} Widget id
 * @return Widget or undefined if not found
 * @type object
 */
Zapatec.Widget.getWidgetById = function(iId) {
	return Zapatec.Widget.all[iId];
};

/**
 * Saves a property that must be set to null on window unload event. Should be
 * used for properties that can't be deleted by garbage collector in IE 6 due to
 * circular references.
 *
 * <pre>
 * Defines internal property <b>widgetCircularRefs</b>.
 * </pre>
 *
 * @param {object} oElement DOM object
 * @param {string} sProperty Property name
 */
Zapatec.Widget.prototype.addCircularRef = function(oElement, sProperty) {
	if (!this.widgetCircularRefs) {
		// Holds properties of DOM objects that must be set to null on window unload
		// event to prevent memory leaks in IE 6
		this.widgetCircularRefs = [];
	}
	this.widgetCircularRefs.push([oElement, sProperty]);
};

/**
 * Assigns a value to a custom property of DOM object. This property will be
 * set to null on window unload event. Use this function to create properties
 * that can't be deleted by garbage collector in IE 6 due to circular
 * references.
 *
 * @param {object} oElement DOM object
 * @param {string} sProperty Property name
 * @param {any} val Property value
 */
Zapatec.Widget.prototype.createProperty = function(oElement, sProperty, val) {
	oElement[sProperty] = val;
	this.addCircularRef(oElement, sProperty);
};

/**
 * Removes circular references previously defined with method
 * {@link Zapatec.Widget#addCircularRef} or
 * {@link Zapatec.Widget#createProperty} to prevent memory leaks in IE 6.
 * @private
 */
Zapatec.Widget.prototype.removeCircularRefs = function() {
	if (!this.widgetCircularRefs) {
		return;
	}
	for (var iRef = this.widgetCircularRefs.length - 1; iRef >= 0; iRef--) {
		var oRef = this.widgetCircularRefs[iRef];
		oRef[0][oRef[1]] = null;
		oRef[0] = null;
	}
};

/**
 * Deletes a reference to the object from the internal list and calls method
 * {@link Zapatec.Widget#removeCircularRefs}. This lets JavaScript garbage
 * collector to delete an object unless there are any external references to it.
 * Id of discarded object is reused. When you create new instance of Widget,
 * it obtains id of discarded object.
 */
Zapatec.Widget.prototype.discard = function() {
	Zapatec.Widget.all[this.id] = null;
	this.removeCircularRefs();
};

/**
 * Calls method {@link Zapatec.Widget#removeCircularRefs} for each instance of
 * Widget on the page. Should be called only on window uload event.
 * @private
 */
Zapatec.Widget.removeCircularRefs = function() {
	for (var iWidget = Zapatec.Widget.all.length - 1; iWidget >= 0; iWidget--) {
		var oWidget = Zapatec.Widget.all[iWidget];
		if (oWidget && oWidget.removeCircularRefs) {
			oWidget.removeCircularRefs();
		}
	}
};

// Remove circular references on window uload event to prevent memory leaks in
// IE 6
Zapatec.Utils.addEvent(window, 'unload', Zapatec.Widget.removeCircularRefs);

/**
 * Defines config option if it is not defined yet. Sets default value of new
 * config option. If default value is not specified, it is set to null.
 *
 * @param {string} sOption Config option name
 * @param {any} val Optional. Config option default value
 */
Zapatec.Widget.prototype.defineConfigOption = function(sOption, val) {
	if (typeof this.config[sOption] == 'undefined') {
		if (typeof val == 'undefined') {
			this.config[sOption] = null;
		} else {
			this.config[sOption] = val;
		}
	}
};

/**
 * Adds custom event listeners.
 */
Zapatec.Widget.prototype.addUserEventListeners = function() {
	var oListeners = this.config.eventListeners;
	var fListener, iListeners, iListener;
	for (var sEvent in oListeners) {
		if (oListeners.hasOwnProperty(sEvent)) {
			vListener = oListeners[sEvent];
			if (vListener instanceof Array) {
				iListeners = vListener.length;
				for (iListener = 0; iListener < iListeners; iListener++) {
					this.addEventListener(sEvent, vListener[iListener]);
				}
			} else {
				this.addEventListener(sEvent, vListener);
			}
		}
	}
};

/**
 * Adds standard event listeners.
 */
Zapatec.Widget.prototype.addStandardEventListeners = function() {
	this.addEventListener('loadThemeError', Zapatec.Widget.loadThemeError);
};

/**
 * Displays the reason why the theme was not loaded.
 *
 * @private
 * @param {object} oError Error received from Zapatec.Transport.loadCss
 */
Zapatec.Widget.loadThemeError = function(oError) {
	var sDescription = "Can't load theme.";
	if (oError && oError.errorDescription) {
		sDescription += ' ' + oError.errorDescription;
	}
	Zapatec.Log({
		description: sDescription
	});
};

/**
 * Loads specified theme.
 *
 * <pre>
 * Fires events:
 * <ul>
 * <li><i>loadThemeStart</i> before starting to load theme</li>
 * <li><i>loadThemeEnd</i> after theme is loaded or theme load failed</li>
 * <li><i>loadThemeError</i> after theme load failed. Passes one argument to the
 * listener: error object received from {@link Zapatec.Transport#loadCss}.</li>
 * </ul>
 *
 * Defines internal property <b>themeLoaded</b>.
 * </pre>
 */
Zapatec.Widget.prototype.loadTheme = function() {
	var oConfig = this.config;
	// Correct theme config option
	if (typeof oConfig.theme == 'string' && oConfig.theme.length) {
		// Remove path
		var iPos = oConfig.theme.lastIndexOf('/');
		if (iPos >= 0) {
			iPos++; // Go to first char of theme name
			oConfig.themePath = oConfig.theme.substring(0, iPos);
			oConfig.theme = oConfig.theme.substring(iPos);
		}
		// Remove file extension
		iPos = oConfig.theme.lastIndexOf('.');
		if (iPos >= 0) {
			oConfig.theme = oConfig.theme.substring(0, iPos);
		}
		// Make lower case
		oConfig.theme = oConfig.theme.toLowerCase();
		// Auto theme
		if (oConfig.theme == 'auto') {
			var sUserAgent = navigator.userAgent;
			if (sUserAgent.indexOf('Windows NT 6') != -1) {
				oConfig.theme = 'winvista';
			} else if (sUserAgent.indexOf('Windows NT 5') != -1) {
				oConfig.theme = 'winxp';
			} else if (sUserAgent.indexOf('Win') != -1) {
				oConfig.theme = 'win2k';
			} else if (sUserAgent.indexOf('Mac') != -1) {
				oConfig.theme = 'macosx';
			} else {
				oConfig.theme = 'default';
			}
		}
	} else {
		oConfig.theme = '';
	}
	// Load theme
	if(oConfig.theme){
		this.fireEvent('loadThemeStart');
		this.themeLoaded = false;
		var oWidget = this;
		var sUrl = oConfig.themePath + oConfig.theme + '.css';
		Zapatec.Transport.loadCss({
			// URL of theme file
			url: sUrl,
			// Suspend script execution until theme is loaded or error received
			async: oConfig.asyncTheme,
			// Onload event handler
			onLoad: function() {
				oWidget.fireEvent('loadThemeEnd');
				oWidget.themeLoaded = true;
			},
			onError: function(oError) {
				oWidget.fireEvent('loadThemeEnd');
				oWidget.fireEvent('loadThemeError', oError);
				oWidget.themeLoaded = true;
			}
		});
	}
}

/**
 * Forms class name from theme name and provided prefix and suffix.
 *
 * <pre>
 * Arguments object format:
 * {
 *   prefix: [string, optional] prefix,
 *   suffix: [string, optional] suffix
 * }
 * E.g. if this.config.theme == 'default' and following object provided
 * {
 *   prefix: 'zpWidget',
 *   suffix: 'Container'
 * },
 * class name will be 'zpWidgetDefaultContainer'.
 * </pre>
 *
 * @param oArg [object] Arguments object
 * @return Class name
 * @type string
 */
Zapatec.Widget.prototype.getClassName = function(oArg) {
	var aClassName = [];
	if (oArg && oArg.prefix) {
		aClassName.push(oArg.prefix);
	}
	var sTheme = this.config.theme;
	if (sTheme != '') {
		aClassName.push(sTheme.charAt(0).toUpperCase());
		aClassName.push(sTheme.substr(1));
	}
	if (oArg && oArg.suffix) {
		aClassName.push(oArg.suffix);
	}
	return aClassName.join('');
};

/**
 * Forms unique element id from widget id, unique counter and provided prefix
 * and suffix.
 *
 * <pre>
 * Arguments object format:
 * {
 *   prefix: [string, optional] prefix, default: 'zpWidget',
 *   suffix: [string, optional] suffix, default: '-'
 * }
 * E.g. if widget id is 0, unique counter is 1 and following object provided
 * {
 *   prefix: 'zpWidget',
 *   suffix: 'Item'
 * },
 * id will be 'zpWidget0Item1'.
 *
 * Defines internal property <b>widgetUniqueIdCounter</b>.
 * </pre>
 *
 * @param oArg [object] Arguments object
 * @return Element id
 * @type string
 */
Zapatec.Widget.prototype.formElementId = function(oArg) {
	var aId = [];
	if (oArg && oArg.prefix) {
		aId.push(oArg.prefix);
	} else {
		aId.push('zpWidget');
	}
	aId.push(this.id);
	if (oArg && oArg.suffix) {
		aId.push(oArg.suffix);
	} else {
		aId.push('-');
	}
	if (typeof this.widgetUniqueIdCounter == 'undefined') {
		this.widgetUniqueIdCounter = 0;
	} else {
		this.widgetUniqueIdCounter++;
	}
	aId.push(this.widgetUniqueIdCounter);
	return aId.join('');
};

/**
 * Shows widget using given effects and animation speed. You need to define
 * this.container to use this method.
 * @param {object} effects list of effects to apply
 * @param {number} animSpeed possible values - 1..100. Bigger value - more fast animation.
 * @param {function} onFinish Function to call on effect end.
 */
Zapatec.Widget.prototype.showContainer = function(effects, animSpeed, onFinish){
	return this.showHideContainer(effects, animSpeed, onFinish, true);
}

/**
 * Hides widget using given effects and animation speed. You need to define
 * this.container to use this method.
 * @param {object} effects list of effects to apply
 * @param {number} animSpeed possible values - 1..100. Bigger value - more fast animation.
 */
Zapatec.Widget.prototype.hideContainer = function(effects, animSpeed, onFinish){
	return this.showHideContainer(effects, animSpeed, onFinish, false);
}

/**
 * Show/hides widget using given effects and animation speed. You need to define
 * this.container to use this method.
 * @param {object} effects list of effects to apply
 * @param {number} animSpeed possible values - 1..100. Bigger value - more fast animation.
 * @param {boolean} show if true - show widget. Otherwise - hide.
 */
Zapatec.Widget.prototype.showHideContainer = function(effects, animSpeed, onFinish, show){
	if(this.container == null){
		return null;
	}

	if(effects && effects.length > 0 && typeof(Zapatec.Effects) == 'undefined'){
		var self = this;

		Zapatec.Transport.loadJS({
			url: Zapatec.zapatecPath + '../zpeffects/src/effects.js',
			onLoad: function() {
				self.showHideContainer(effects, animSpeed, onFinish, show);
			}
		});

		return false;
	}

	if(animSpeed == null && isNaN(parseInt(animSpeed))){
		animSpeed = 5;
	}

	if(!effects || effects.length == 0){
		if(show){
			this.container.style.display = this.originalContainerDisplay;
			this.originalContainerDisplay = null;
		} else {
			this.originalContainerDisplay = this.container.style.display;
			this.container.style.display = 'none';
		}

		if (onFinish) {
			onFinish();
		}
	} else {
		if(show){
			Zapatec.Effects.show(this.container, animSpeed, effects, onFinish);
		} else {
			Zapatec.Effects.hide(this.container, animSpeed, effects, onFinish);
		}
	}

	return true;
}

/**
 * Loads data from the specified source.
 *
 * <pre>
 * If source is URL, fires events:
 * <ul>
 * <li><i>fetchSourceStart</i> before fetching of source</li>
 * <li><i>fetchSourceError</i> if fetch failed. Passes one argument to the
 * listener: error object received from {@link Zapatec.Transport#fetch}.</li>
 * <li><i>fetchSourceEnd</i> after source is fetched or fetch failed</li>
 * </ul>
 *
 * Fires events:
 * <ul>
 * <li><i>loadDataStart</i> before parsing of data</li>
 * <li><i>loadDataEnd</i> after data are parsed or error occured during
 * fetch</li>
 * </ul>
 *
 * <i>fetchSourceError</i> is fired before <i>fetchSourceEnd</i> and
 * <i>loadDataEnd</i>.
 * </pre>
 *
 * @param {object} oArg Arguments object passed to callbackSource function
 */
Zapatec.Widget.prototype.loadData = function(oArg) {
	var oConfig = this.config;
	// Get source using callback function
	if (typeof oConfig.callbackSource == 'function') {
		var oSource = oConfig.callbackSource(oArg);
		if (oSource) {
			if (typeof oSource.source != 'undefined') {
				oConfig.source = oSource.source;
			}
			if (typeof oSource.sourceType != 'undefined') {
				oConfig.sourceType = oSource.sourceType;
			}
		}
	}
	// Process source
	var vSource = oConfig.source;
	if (typeof oConfig.callbackConvertSource == 'function') {
		vSource = oConfig.callbackConvertSource(vSource);
	}
	var sSourceType = oConfig.sourceType;
	if (vSource != null && sSourceType != null) {
		sSourceType = sSourceType.toLowerCase();
		if (sSourceType == 'html') {
			this.fireEvent('loadDataStart');
			this.loadDataHtml(Zapatec.Widget.getElementById(vSource));
			this.fireEvent('loadDataEnd');
		} else if (sSourceType == 'html/text') {
			this.fireEvent('loadDataStart');
			this.loadDataHtmlText(vSource);
			this.fireEvent('loadDataEnd');
		} else if (sSourceType == 'html/url') {
			this.fireEvent('fetchSourceStart');
			// Fetch source
			var oWidget = this;
			Zapatec.Transport.fetch({
				// URL of the source
				url: vSource,
				// Suspend script execution until source is loaded or error received
				async: oConfig.asyncSource,
				// Onload event handler
				onLoad: function(oRequest) {
					oWidget.fireEvent('fetchSourceEnd');
					oWidget.fireEvent('loadDataStart');
					oWidget.loadDataHtmlText(oRequest.responseText);
					oWidget.fireEvent('loadDataEnd');
				},
				// Onerror event handler
				onError: function(oError) {
					oWidget.fireEvent('fetchSourceError', oError);
					oWidget.fireEvent('fetchSourceEnd');
					oWidget.fireEvent('loadDataEnd');
				}
			});
		} else if (sSourceType == 'json') {
			this.fireEvent('loadDataStart');
			if (typeof vSource == 'object') {
				this.loadDataJson(vSource);
			} else if (oConfig.reliableSource) {
				this.loadDataJson(eval(['(', vSource, ')'].join('')));
			} else {
				this.loadDataJson(Zapatec.Transport.parseJson({
					strJson: vSource
				}));
			}
			this.fireEvent('loadDataEnd');
		} else if (sSourceType == 'json/url') {
			this.fireEvent('fetchSourceStart');
			// Fetch source
			var oWidget = this;
			Zapatec.Transport.fetchJsonObj({
				// URL of the source
				url: vSource,
				// Suspend script execution until source is loaded or error received
				async: oConfig.asyncSource,
				// Skip JSON format verification
				reliable: oConfig.reliableSource,
				// Onload event handler
				onLoad: function(oResult) {
					oWidget.fireEvent('fetchSourceEnd');
					oWidget.fireEvent('loadDataStart');
					oWidget.loadDataJson(oResult);
					oWidget.fireEvent('loadDataEnd');
				},
				// Onerror event handler
				onError: function(oError) {
					oWidget.fireEvent('fetchSourceError', oError);
					oWidget.fireEvent('fetchSourceEnd');
					oWidget.fireEvent('loadDataEnd');
				}
			});
		} else if (sSourceType == 'xml') {
			this.fireEvent('loadDataStart');
			if (typeof vSource == 'object') {
				this.loadDataXml(vSource);
			} else {
				this.loadDataXml(Zapatec.Transport.parseXml({
					strXml: vSource
				}));
			}
			this.fireEvent('loadDataEnd');
		} else if (sSourceType == 'xml/url') {
			this.fireEvent('fetchSourceStart');
			// Fetch source
			var oWidget = this;
			Zapatec.Transport.fetchXmlDoc({
				// URL of the source
				url: vSource,
				// Suspend script execution until source is loaded or error received
				async: oConfig.asyncSource,
				// Onload event handler
				onLoad: function(oResult) {
					oWidget.fireEvent('fetchSourceEnd');
					oWidget.fireEvent('loadDataStart');
					oWidget.loadDataXml(oResult);
					oWidget.fireEvent('loadDataEnd');
				},
				// Onerror event handler
				onError: function(oError) {
					oWidget.fireEvent('fetchSourceError', oError);
					oWidget.fireEvent('fetchSourceEnd');
					oWidget.fireEvent('loadDataEnd');
				}
			});
		}
	} else {
		this.fireEvent('loadDataStart');
		this.loadDataHtml(Zapatec.Widget.getElementById(vSource));
		this.fireEvent('loadDataEnd');
	}
};

/**
 * Loads data from the HTML source. Override this in child class.
 *
 * @param {object} oSource Source HTMLElement object
 */
Zapatec.Widget.prototype.loadDataHtml = function(oSource) {};

/**
 * Loads data from the HTML fragment source.
 *
 * @param {string} sSource Source HTML fragment
 */
Zapatec.Widget.prototype.loadDataHtmlText = function(sSource) {
	// Parse HTML fragment
	var oTempContainer = Zapatec.Transport.parseHtml(sSource);
	// Load data
	this.loadDataHtml(oTempContainer.firstChild);
};

/**
 * Loads data from the JSON source. Override this in child class.
 *
 * @param {object} oSource Source JSON object
 */
Zapatec.Widget.prototype.loadDataJson = function(oSource) {};

/**
 * Loads data from the XML source. Override this in child class.
 *
 * @param {object} oSource Source XMLDocument object
 */
Zapatec.Widget.prototype.loadDataXml = function(oSource) {};

/**
 * Loads data passed from other widget for example to view or edit them. Extend
 * this in child class.
 *
 * <pre>
 * Argument object format:
 * {
 *   widget: [object] Optional. Sender widget instance,
 *   data: [any] Data in format specific for each widget
 * }
 *
 * Saves passed widget in private property <i>dataSender</i> for later use in
 * {@link Zapatec.Widget#replyDataReturn}.
 *
 * Fires event:
 * <ul>
 * <li><i>receiveData</i>. Listener receives argument object passed to this
 * method.</li>
 * </ul>
 * </pre>
 *
 * @param {object} oArg Argument object
 */
Zapatec.Widget.prototype.receiveData = function(oArg) {
	if (!oArg) {
		oArg = {};
	}
	// Save reference
	this.dataSender = oArg.widget;
	this.fireEvent('receiveData', oArg);
};

/**
 * Prepares processed data to return them back to the sender in the same format
 * as they were received in {@link Zapatec.Widget#receiveData}. Extend this in
 * child class.
 *
 * @return Processed data in format specific for each widget.
 * @type any
 */
Zapatec.Widget.prototype.replyData = function() {
	return null;
};

/**
 * Cancels processing of the data received from the sender in
 * {@link Zapatec.Widget#receiveData}. Ususally just hides the widget (calls
 * hide method of the widget if it is defined).
 *
 * <pre>
 * Removes private property <i>dataSender</i>.
 *
 * Fires event:
 * <ul>
 * <li><i>replyDataCancel</i> before the widget is hidden</li>
 * </ul>
 * </pre>
 */
Zapatec.Widget.prototype.replyDataCancel = function() {
	this.fireEvent('replyDataCancel');
	if (typeof this.hide == 'function') {
		this.hide();
	}
	// Remove reference
	this.dataSender = null;
};

/**
 * Returns processed data back to the specified widget (not necessarily to the
 * same widget from which they were received in
 * {@link Zapatec.Widget#receiveData}). Passes data to
 * {@link Zapatec.Widget#acceptData} method of that widget. Then calls
 * {@link Zapatec.Widget#replyDataCancel} to hide this widget.
 *
 * <pre>
 * Argument object format:
 * {
 *   widget: [object] Optional. Receiver widget instance
 * }
 *
 * If receiver widget was not specified, uses widget passed to
 * {@link Zapatec.Widget#receiveData} and saved in private property
 * <i>dataSender</i>.
 *
 * Fires event:
 * <ul>
 * <li><i>replyDataReturn</i> before passing data to the specified widget.
 * Listener receives argument object passed to this method.</li>
 * </ul>
 * </pre>
 *
 * @param {object} oArg Argument object
 */
Zapatec.Widget.prototype.replyDataReturn = function(oArg) {
	if (!oArg) {
		oArg = {};
	}
	this.fireEvent('replyDataReturn', oArg);
	var oWidget = oArg.widget;
	if (!oWidget) {
		oWidget = this.dataSender;
	}
	if (!oWidget || typeof oWidget.acceptData != 'function') {
		return;
	}
	oWidget.acceptData({
		widget: this,
		data: this.replyData()
	});
	this.replyDataCancel();
};

/**
 * Receives data back from other widget previously passed to it using its
 * {@link Zapatec.Widget#receiveData} method. Extend this in child class.
 *
 * <pre>
 * Argument object format:
 * {
 *   widget: [object] Caller widget instance,
 *   data: [any] Data in format specific for each widget
 * }
 *
 * Fires event:
 * <ul>
 * <li><i>acceptData</i>. Listener receives argument object passed to this
 * method.</li>
 * </ul>
 * </pre>
 *
 * @param {object} oArg Argument object
 */
Zapatec.Widget.prototype.acceptData = function(oArg) {
	this.fireEvent('acceptData', oArg);
};

/**
 * Internal function to process language realted config options.
 * @private
 */
Zapatec.Widget.prototype.initLang = function(){
	// calculate hash key only once
	this.langStr = this.config.lang;

	if(this.config.langCountryCode && this.config.langCountryCode.length > 0){
		this.langStr += "_" + this.config.langCountryCode; 
	}

	if(this.config.langEncoding && this.config.langEncoding.length > 0){
		this.langStr += "-" + this.config.langEncoding; 
	}

	if(
		this.config.lang && 
		this.config.lang.length > 0 &&
		!(
			Zapatec.Langs[this.config.langId] &&
			Zapatec.Langs[this.config.langId][this.langStr]
		)
	){
		Zapatec.Log({
			description: "No language data found for language " + 
				this.config.lang + (
					this.config.langCountryCode ? 
					" and country code " + this.config.langCountryCode : ""
				) + (
					this.config.langEncoding ? 
					" and encoding " + this.config.langEncoding : ""
				)
		});

		this.config.lang = null;
		this.config.langCountryCode = null;
		this.config.langEncoding = null;
		this.langStr = null;
	}
};

/**
 * Get message for given key, make substitutions and return.
 * If more then 1 argument given - method will replace %1, .. %N with corresponding argument value
 * @param key {Object} String, object or anything else that can be treated as array key in Javascript. Required.
 * @param substitution1 {String} First substitution to the string. Optional.
 * ...
 * @param substitutionN {String} Last substitution to the string. Optional.
 */
Zapatec.Widget.prototype.getMessage = function(key){
	if(arguments.length == 0){
		return null;
	}

	if(
		!Zapatec.Langs[this.config.langId] || 
		!Zapatec.Langs[this.config.langId][this.langStr] ||
		!Zapatec.Langs[this.config.langId][this.langStr][key]
	){
//		Zapatec.Log({description: "No language data found"});
		return key;
	}

	var res = Zapatec.Langs[this.config.langId][this.langStr][key];

	if(arguments.length > 1 && typeof(res) == "string"){
		for(var ii = 1; ii < arguments.length; ii++){
			var re = new RegExp("(^|([^\\\\]))\%"+ii);

			res = res.replace(re, "$2" + arguments[ii]);
		}
	}

	return res;
};

/**
 * Finds a widget by id and calls specified method with specified arguments and
 * returns value from that method.
 *
 * @param {number} iWidgetId Widget id
 * @param {string} sMethod Method name
 * @param {any} any Any number of arguments
 * @return Value returned from the method
 * @type any
 */
Zapatec.Widget.callMethod = function(iWidgetId, sMethod) {
	// Get Widget object
	var oWidget = Zapatec.Widget.getWidgetById(iWidgetId);
	if (oWidget && typeof oWidget[sMethod] == 'function') {
		// Remove first two arguments
		var aArgs = [].slice.call(arguments, 2);
		// Call method
		return oWidget[sMethod].apply(oWidget, aArgs);
	}
};

/**
 * Converts element id to reference.
 *
 * @param {string} element Element id
 * @return Reference to element
 * @type object
 */
Zapatec.Widget.getElementById = function(element) {
	if (typeof element == 'string') {
		return document.getElementById(element);
	}
	return element;
};

/**
 * Returns style attribute of the specified element.
 *
 * @param {object} element Element
 * @return Style attribute value
 * @type string
 */
Zapatec.Widget.getStyle = function(element) {
	var style = element.getAttribute('style') || '';
	if (typeof style == 'string') {
		return style;
	}
	return style.cssText;
};
