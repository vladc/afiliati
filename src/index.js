var {Cc, Ci, Cu} = require("chrome");
Cu.import("resource://gre/modules/Services.jsm");
const {XMLHttpRequest} = require("sdk/net/xhr");
const {setTimeout} = require("sdk/timers");
var data = require("sdk/self").data;
var panel = require("sdk/panel").Panel({
	contentURL: data.url("alert.html"),
	contentScriptFile: data.url("alert.js"),
	position: {top: 0},
	height: 30,
	focus: false
});

var list = ["profitshare.ro/l/", "profitshare.ro/cl/", "campanii.emag.ro/click.php", "event.2parale.ro/events/click"];
function is_afiliat(url) {
	for (var i=list.length; i--;) {
		if(url.substring(0, list[i].length) === list[i]) {
			return true;
		}
	}
	return false;
}

function get_clean(url) {
	var request = new XMLHttpRequest();
	request.open('POST', 'https://afiliati.cdn-static.com/clean', false);
	request.send(url);
	if (request.status === 200) {
		return request.responseText;
	}
	return false;
}

exports.main = function(options, callbacks) {
	httpRequestObserver = {
		observe: function(subject, topic, data) {
			if (topic == "http-on-modify-request") {
				var httpChannel = subject.QueryInterface(Ci.nsIHttpChannel);
				var url = httpChannel.URI.host + httpChannel.URI.path;
				if(is_afiliat(url)) {
					var clean_url = get_clean(url);
					if(clean_url !== false) {
						panel.show();
						panel.port.emit("setUrl", httpChannel.URI.host);
						setTimeout(function() {
							panel.hide();
						}, 1500);
						httpChannel.redirectTo(Services.io.newURI(clean_url, null, null));
					}
				}
			}
		},
		register: function() {
			var observerService = Cc["@mozilla.org/observer-service;1"].getService(Ci.nsIObserverService);
			observerService.addObserver(this, "http-on-modify-request", false);
		},
		unregister: function() {
			var observerService = Cc["@mozilla.org/observer-service;1"].getService(Ci.nsIObserverService);
			observerService.removeObserver(this, "http-on-modify-request");
		}
	};
	httpRequestObserver.register();
};

exports.onUnload = function(reason) {
	httpRequestObserver.unregister();
};
