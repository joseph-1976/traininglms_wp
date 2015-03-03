(function() {
	var root = this;
	root.ttp = root.ttp || {};

	var ttp = root.ttp;

(function ($, undefined) {
    $.widget("ttp.confirm", $.ui.dialog, {
        options: {
            content: undefined,
            response: undefined,
        },
        open: function () {
            this.element.html(this.options.content);
            this._super();
        }
    });
})(jQuery);

ttp.doAjax = (function($, undefined) {
	var pending = undefined;
	return function(action, data, success_cb, failure_cb) {
	if (typeof pending === "undefined") {
		// if same action in some predefined window, ignore (debounce)
		// if same action outside of initial window, consider canceling and retrying request
		// if another action, consider queueing action or ???
		pending = { action: action, timestamp: Math.round(Date.now()/1000) };
	} else {
		return pending;
	}
    jQuery('body').css('cursor', 'wait');
	jQuery.ajax({
		type: "POST",
		url: ajaxurl,
		data: {
			action: TheTrainingMangerLMS.action,
			method: action,
			data: data,
			nonce: TheTrainingMangerLMS.nonce
		},
		success: function( response ) {
		    jQuery('body').css('cursor', 'default');
			if (typeof success_cb !== "undefined") {
				success_cb( response );
			}
			pending = undefined;
		},
		error: function(jqXHR, textStatus, errorThrown) {
		    jQuery('body').css('cursor', 'default');
			if (textStatus !== 'error') {
				// have them try again or refresh the page
				jQuery('#dialog-error').confirm("option", "title", "An error was encountered...")
				.confirm("option", "content", "<p>There was a problem completing the request.  You can try again or refresh this page.  If you continue having problems, please contact the administrator.</p>")
				.confirm("open");
			} else
				if (jqXHR.status == 400) { // Bad request
					jQuery('#dialog-error').confirm("option", "title", "Bad request...")
					.confirm("option", "content", "<p>The server received an erroneous request.  Please contact the server administrator.</p>")
					.confirm("open");
			} else
				if (jqXHR.status == 401) { // Unauthorized
					jQuery('#dialog-error').confirm("option", "title", "Unauthorized...")
					.confirm("option", "content", "<p>You are not authorized to perform this action.  If you feel that you have received this message in error, please contact the server administrator.</p>")
					.confirm("open");
			} else
				if (jqXHR.status == 420) { // Caught exception
					exception = jQuery.parseJSON(jqXHR.responseText).errors[0];
					jQuery('#dialog-error').confirm("option", "title", "Exception...")
					.confirm("option", "content", "<p>An exception of type " + exception.type + " has occured, with the following message: " + exception.msg + "</p>")
					.confirm("open");
			} else { // Other server error
				jQuery('#dialog-error').confirm("option", "title", "Server error...")
				.confirm("option", "content", "<p>A server error was encountered.  Please contact the administor.</p>")
				.confirm("open");
			}
			if (typeof failure_cb !== "undefined") {
				failure_cb();
			}
			pending = undefined;
		}
	});
	return true;
	};
})(jQuery);

ttp.doTemplate = (function(undefined) {
	var cache = {};
	return function(name, data, success_cb, failure_cb) {
		try {
			cache[name] = cache[name] || _.template( document.getElementById("tmpl-" + name).innerHTML );//wp.template(name);//new Function("data", moose);
			var text = cache[name](data);
			if (!_.isUndefined(success_cb)) {
				success_cb(text);
			}
			return text;
		} catch (err) {
			if (!_.isUndefined(failure_cb)) {
				failure_cb(err);
			}
			return false;
		}
	};
})();

}.call(this));

jQuery(document).ready((function($) {return function() {
	$('<div></div').attr('id', 'dialog-error').hide().appendTo('body')
    .confirm({
        resizable: true,
        width: 340,
        modal: true,
        buttons: {
            Ok: function () {
            	$(this).confirm("option", "response", "Ok");
                $(this).confirm("close");
            }
        },
        autoOpen: false,
    });
};})(jQuery));
