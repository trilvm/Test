Zapatec.demo=function() {
	this.init();
};

Zapatec.demo.prototype.init=function() {
	this.demoTab = null;
	this.htmlTab = null;
	this.cssTab = null;
};

//--------------------------------------------------------------------------
/*
Function called on startup
*/
Zapatec.demo.prototype.run=function() {
    this.need2Change2demo = '';
    this.demo_load();
    this.zptree = new Zapatec.Tree({
        'tree': "tree",
        'expandOnLabelClick': true,
        'expandOnLabel': true,
        'theme': 'demos',
        'highlightSelectedNode': true,
        'createWholeDOM': true,
        'eventListeners': {
            'select': function() {
                if (this.data.attributes != undefined && this.data.attributes.demo != undefined) {
						if (zpDemo.strPath2Demo != undefined)
							// if path2Demo defined then ALWAYS use
							zpDemo.strPath = zpDemo.strPath2Demo
						else
						{

                    try {
                        if (this.config.parentNode.config.parentNode.data.attributes.widget != undefined) {
                            zpDemo.strPath = '../../' + this.config.parentNode.config.parentNode.data.attributes.widget + '/demo/';
                        } else {
                            zpDemo.strPath = '../demo/';
                        }
                    } catch (e) {
                        zpDemo.strPath = '../demo/';
                    }
						}
                    zpDemo.changeDemo(this.data.attributes.demo, 
                                      this.data.attributes.themes, 
                                      this.data.attributes.demo,
                                      this.data.label);
                }
            },
            "loadDataEnd": function(){
           		if(this.data && this.data.label && this.data.label.indexOf("Demos") >= 0 && this.oldSource && zpDemo.need2Change2demo){
	            	setTimeout(function(){
		        		zpDemo.changeDemoById(zpDemo.need2Change2demo);
						zpDemo.need2Change2demo = null;
					}, 0);
				}
            }
        }
    }
    );

    this.tab_build();
}

Zapatec.demo.prototype.changeDemoById = function(id){
	if(!this.zptree){
		return;
	}

	var treeNode = this.zptree.find(function(node){
		return node.data.attributes && node.data.attributes.demo == id;
	});

	if(treeNode){
		treeNode.sync();
	}
}

/*
Function called when a demo is clicked on in the tree
*/
Zapatec.demo.prototype.changeDemo=function(url, css, demo, title) {
   // are tabs build yet?
	if (!this.demoTab)
		return;

	this.objTabs.changeTab("demo");
	var _this=this
    hash_prefix = '';
	if (this.strPath != undefined)
		url=this.strPath + url
        if (this.strPath.indexOf('zp') != -1 && this.withwidgets) {
            hash_prefix = this.strPath.substring(
                            this.strPath.indexOf('zp'),
                            this.strPath.indexOf('/', this.strPath.indexOf('zp'))
                            );
        }
	if (this.idAlternate)
		document.getElementById(this.idAlternate).style.display = "none";
	document.getElementById("tabs_content").style.display = "block";
	this.demoTab.container.getContainer().style.height = "700px";
	if(Zapatec.is_opera){
		this.demoTab.config.showLoadingIndicator = false; // very strange glitch :-/
	}
	this.demoTab.setPaneContent(url, "html/url");
	this.demoTab.container.fireWhenReady(function(pane) {
		var height = 0;
		var doc = this.iframeDocument;
		if (!doc) {return null;}
		try{
			if (doc.compatMode && doc.compatMode == 'CSS1Compat') {
				height = doc.documentElement.scrollHeight || doc.documentElement.offsetHeight;
			} 
		} catch(e){}

		if(!height){
			height = doc.body.scrollHeight || doc.documentElement.scrollHeight;
		}
	            
		if(height < 700){
			height = 700;
		}

		this.getContainer().style.height = (height + 30) + "px";
	});

	var this_htmlTab=this.htmlTab
	Zapatec.Transport.fetch({
		url : url + (Zapatec.is_opera ? "?" : ""),
		onLoad : function(response) {
			this_htmlTab.value = response.responseText;
		},
		onError : function(error) {
			alert(error.errorDescription);
		}
	});
	this.cssTab.value = "";
	var this_cssTab=this.cssTab
	function fetchCSS(theme) {
		Zapatec.Transport.fetch({
			url : (!_this.strPath ? '../themes/' : _this.strPath + '../themes/') + theme + '.css',
			onLoad : function(response) {
				this_cssTab.value += "/* " + theme + " */\n\n" + response.responseText + "\n";
			},
			onError : function(error) {
				alert(error.errorDescription);
			}
		});
	}
	for (var i = 0; i < css.length; ++i) {
		if (css[i] == 'none') {
			this_cssTab.value = "There is no theme for this demo";
		} else {
			fetchCSS(css[i]);
		}
	}
	document.title = "AJAX " + this.strWidget + " - " + title;
    if (hash_prefix != '') {
        hash = hash_prefix + '/' + demo;
    } else {
        hash = demo;
    }
    location.href = location.href.substring(0, location.href.length - location.hash.length) + '#' + hash;
}
		
/*
Function called when a demo is clicked on in the tree
*/
Zapatec.demo.prototype.changeDemoByName=function(demo) {
    this.need2Change2demo = demo;
}

//--------------------------------------------------------------------------
//--------------------------------------------------------------------------

// Load first demo, done after tree and tabs loaded
Zapatec.demo.prototype.demo_load=function() {
    var hash = "";
    if (hash = window.location.hash.slice(1)) {
        this.changeDemoByName(hash);
    } else {
        if (this.zpdefDemo) {
            location.href += "#" + this.zpdefDemo;
            this.changeDemoByName(this.zpdefDemo);
        } else { // no anchor, disable tab pane, enable alternate
            document.getElementById("tabs_content").style.display = "none";
            if (this.idAlternate)
                document.getElementById(this.idAlternate).style.display = "block";
        }
    }
}

Zapatec.demo.prototype.tree_load=function() {
			this.zptree = new Zapatec.Tree("zpDemoList", {'expandOnLabel': true, theme: 'demos'});
}

//--------------------------------------------------------------------------


Zapatec.demo.prototype.tab_build=function() 
{
    var tabs = {
        tabs : [
            {
                id : "demo",
                linkInnerHTML : "<span style='text-decoration : underline; border : none; padding : 0px;'>D</span>emo",
                accessKey : "d",
                title : "demo",
                tabType : "iframe"
            },
            {
                id : "html",
                linkInnerHTML : "HTM<span style='text-decoration : underline; border : none; padding : 0px;'>L</span>",
                accessKey : "l",
                title : "HTML part of the demo.",
                content : document.getElementById("html_source"),
                tabType : "div"
            },
            {
                id : "css",
                linkInnerHTML : "CS<span style='text-decoration : underline; border : none; padding : 0px;'>S</span>",
                accessKey : "s",
                title : "CSS part of the demo.",
                content : document.getElementById("css_source"),
                tabType : "div"
            }
        ]
    };

    var self = this;

    // Create a new demo|css|html tabs widget instance
    this.objTabs = new Zapatec.Tabs({
        tabBar : "tabBar",
        tabs : "tabs",
        source: tabs,
        sourceType : "json",
        themePath : Zapatec.zapatecPath + '/dhelp/themes/',
        changeUrl : false,
        theme: 'default',
        ignoreUrl: true,
        onTabChange: function(oCfg, numberOfTries){
        	if(oCfg.newTabId == 'demo'){
        		if(!self.demoTab){
        			var selfFunction = arguments.callee;
        			numberOfTries = ++numberOfTries || 1;

        			if(numberOfTries < 10){
	        			setTimeout(function(){selfFunction(oCfg, numberOfTries)}, 100);
	        		} else {
						self.demoTab.container.style.height = 700 + "px";
	        		}

	        		return;
        		}

				self.demoTab.container.fireWhenReady(function(pane) {
					var height = 0;
					var doc = this.iframeDocument;
					if (!doc) {return null;}
					if (doc.compatMode && doc.compatMode == 'CSS1Compat') {
						height = doc.documentElement.scrollHeight || doc.documentElement.offsetHeight;
					} else {
						height = doc.body.scrollHeight || doc.documentElement.scrollHeight;
					}
	            
					if(height < 700){
						height = 700;
					}

					this.getContainer().style.height = (height + 30) + "px";
				});
        	}
        }
    });
    
    this.demoTab = this.objTabs.tabs["demo"];
    this.htmlTab = document.getElementById("html_source");
    this.cssTab = document.getElementById("css_source");

    var hash = "";
    this.demoTab.container.getContainer().style.width = "100%";
    this.demoTab.container.getContainer().style.height = "700px";
}
