jQuery(document).ready((function($) {return function() {
    $('#dialog-add-course').dialog({
    	resizable: true,
    	width: 548,
    	height: 340,
    	buttons: {
    		Ok: function() {
				var selected = $('#dialog-add-course-selector').find("option:selected").text();
				if (selected == "New") {
	    			// get title
	    			var title = $('#dialog-new-course-pane input[name=title]').val();
	    			// do appropriate ajax call
	    			retval = ttp.doAjax('new_course', {'course_type': 'online', 'course_title': title},
	    				function(response) {
	    					if (response.success == 'true') {
	    						$('#dialog-add-course').dialog("close");
	    						// we should have the course_id
	    						window.location = TheTrainingMangerLMS.edit_url + response.data.course_id;
	    					}
	    				},
	    				function() {
	    					$('#dialog-add-course').dialog("close");
	    				}
	    			);
	    			// the actions will be responsible for closing the dialog on success or failure
				}
    		},
    		Cancel: function() {
    			$(this).dialog("close");
    		}
    	},
    	open: function() {
			$('#dialog-add-course-content').layout({
				closable: false,
				resizable: false,
				slidable: false,
				livePaneResizing: false,
				north__spacing_open: 0
			});
		},
    	title: "Add Course...",
    	autoOpen: false,
    });
	$('#dialog-add-course-selector').change(function(e) {
		var selected = $(e.target).find("option:selected").text();
		if (selected == "New") {
			$('#dialog-clone-course-pane').hide();
			$('#dialog-new-course-pane').show();
		} else {
			$('#dialog-new-course-pane').hide();
			$('#dialog-clone-course-pane').show();
			// Load course list, show progress bar if necessary
			// update list in pane
		}
	});
	$('#add-new-course').click(function(e) {
		$('#dialog-add-course').dialog("open");
	});
};})(jQuery));