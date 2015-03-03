jQuery(document).ready((function($) {return function() {
    $('#dialog-add-course').dialog({
        resizable: true,
        modal: true,
        width: 400,
        height: 300,
        buttons: {
            "Publish": function() {
                // get title
                var title = $('#dialog-new-course-pane input[name=title]').val();
                // do appropriate ajax call
                retval = ttp.doAjax('create_course', {'type': 'live', 'title': title},
                    function(response) {
                        if (response.success == 'true') {
                            $('#dialog-add-course').dialog("close");
                            // we should have the course_id
                            window.location = TheTrainingMangerLMS.edit_url + response.data.course.ID;
                        } else {
                            jQuery('#dialog-error').confirm("option", "title", "Bad title...")
                            .confirm("option", "content", "<p>" + response.data.message + "</p>")
                            .confirm("open");
                        }
                    },
                    function() {
                        $('#dialog-add-course').dialog("close");
                    }
                );
                // the actions will be responsible for closing the dialog on success or failure
            },
            Cancel: function() {
                $(this).dialog("close");
            }
        },
        open: function() {
        },
        title: "Add Course...",
        autoOpen: false,
    });
    $('#add-new-course').click(function(e) {
        $('#dialog-add-course').dialog("open");
    });
    $('#dialog-publish-course').dialog({
        resizable: true,
        modal: true,
        width: 420,
        height: 320,
        buttons: {
            Ok: function() {
                // make sure checklist items have been checked
                if (!$(this).find("input[name=review]").prop("checked")) {
                    $("#dialog-error").confirm("option", "title", "Checklist...")
                    .confirm("option", "content", "<p>Please review and check the checklist items accordingly.</p>")
                    .confirm("open");
                    return;
                }
                $(this).dialog("close");
                var course_id = $(this).data('course-id');
                var dataIndex = $(this).data('dataIndex');
                ttp.doAjax('publish_course', {'course_id': course_id}, function(response) {
                    if (response.success == 'true') {
                        $('#course_list_table').DataTable().cell(dataIndex, 4).data("publish");
                        // Get node for last child, get select, modify Publish option to "Unpublish"
                        var $td = $($('#course_list_table').DataTable().cell(dataIndex, 5).node());
                        $td.find("li").filter(function(i, e) { return $(e).text() === 'Publish'; }).text("Unpublish");
                        // refresh dropdownmenu (automatic)
                    } else {
                        jQuery('#dialog-error').confirm("option", "title", "Publish failed...")
                        .confirm("option", "content", "<p>" + response.data.message + "</p>")
                        .confirm("open");
                    }
                });
            },
            Cancel: function() {
                $(this).dialog("close");
            },
        },
        open: function() {
            $(this).find("input[name=review]").prop("checked", false);
        },
        autoOpen: false
    });
    $('#dialog-publish-course button').click(function(e) {
        var course_id = $('#dialog-publish-course').data('course-id');
        window.open(TheTrainingMangerLMS.site_url + "/?p=" + course_id);
    });
    jQuery.fn.dataTableExt.afnFiltering.push(
        function( oSettings, aData, iDataIndex ) {
            var filters = {};
            filters['isFeatured'] = $('#table-filters').find("input[name=featured]").prop("checked");
            filters['isNotFeatured'] = $('#table-filters').find("input[name='not-featured']").prop("checked");
            filters['isScheduled'] = $('#table-filters').find("input[name=scheduled]").prop("checked");
            filters['isNotScheduled'] = $('#table-filters').find("input[name='not-scheduled']").prop("checked");
            filters['statusDraft'] = $('#table-filters').find("input[name='draft']").prop("checked");
            filters['statusPublish'] = $('#table-filters').find("input[name='publish']").prop("checked");
            if (!filters.isFeatured && (aData[1] === 'true')) return false;
            if (!filters.isNotFeatured && (aData[1] === 'false')) return false;
            if (!filters.isScheduled && (aData[3] != 0)) return false;
            if (!filters.isNotScheduled && (aData[3] == 0)) return false;
            if (!filters.statusDraft && (aData[4] === 'draft')) return false;
            if (!filters.statusPublish && (aData[4] === 'publish')) return false;
            return true;
        }
    );
    // FIXME: change this so we get the data via our own ajax functionality, alter it and set it
	$('#course_list_table').dataTable( {
        "ajax": {
        	'url': ajaxurl,
        	'data': {
				action: TheTrainingMangerLMS.action,
				method: 'course_list',
                data: {'tc_id': TheTrainingMangerLMS.training_company },
				nonce: TheTrainingMangerLMS.nonce
        	},
            /*'dataSrc': function(response) {
                for(var i = 0; i < response.data.length; i++) {
                    //response.data[i][0] = '<span class="title">' + response.data[i][0] + '</span>';
                    var select = '<select data-id="' + response.data[i][response.data[i].length - 1] + '"><option value="options" selected>Options</option><option value="edit">Edit</option>';
                    select += '<option value="status">' + (response.data[i][response.data[i].length - 2] == 'draft' ? 'Publish' : 'Unpublish') + '</option>';
                    select += '</select>';
                    response.data[i][response.data[i].length - 1] = select;
                }
                return response.data;
            },*/
        	'type': 'POST'
        },
        "columnDefs": [
            {
                "targets": 0,
                "render": function ( data, type, row, meta ) {
                    if (type === 'display') {
                        return '<span class="title">' + data + '</span>';
                    }
                    return data;
                }
            },
            {
                "targets": 1,
                "render": function ( data, type, row, meta ) {
                    if (type === 'display') {
                        return '<span class="fa ' + (data === 'true' ? 'fa-check-square-o' : 'fa-square-o') + ' fa-lg"></span>';
                    }
                    return data;
                }
            },
            {
                "targets": 4,
                "render": function ( data, type, row, meta ) {
                    if (type === 'display') {
                        return data.charAt(0).toUpperCase() + data.slice(1);
                    }
                    return data;
                }
            },
            {
                "targets": 5,
                "searchable": false,
                "orderable": false,
                "render": function ( data, type, row, meta ) {
                    if (type === 'display') {
                        var dropdown = '<div data-id="' + data + '"><span>Actions</span><ul><li>Edit</li><li>' + (row[row.length - 2] == 'draft' ? 'Publish' : 'Unpublish') + '</li><li>New Event</li><li>View Details</li></ul></div>'
                        return dropdown;
                    }
                    return data;
                }
            }
        ],
        "deferRender": true,
        "createdRow": function(row, data, dataIndex) {
            $(row).find("td:last-child > div").data("row", data).dropdownmenu({width: "auto", header: "> span:first-child", select: 
                function(event, ui) {
                    var text = ui.item.text();
                    var dropdown = ui.item.closest(":data(ui-dropdownmenu)");
                    var data = dropdown.data("row");
                    if (text === 'View Details') {
                        //window.location.href = TheTrainingMangerLMS.site_url + "/?p=" + data[5];
                        window.open(TheTrainingMangerLMS.site_url + "/?p=" + data[5]);
                    } else if (text === 'Publish') {
                        $("#dialog-publish-course").data('course-id', data[5]).data('dataIndex', dataIndex).dialog("option", "title", "Publish: " + data[0]).dialog("open");
/*                        $("#dialog-confirm").confirm("option", "title", "Publish: " + data[0])
                            .confirm("option", "content", "Publishing a course makes it viewable to the public on the front-end of your site. For this reason, we recommend double-checking the following information to make sure you're not forgetting anything.")
                            .confirm("option", "response", '')
                            .confirm("option", "close", function() {
                                if ($(this).confirm("option", "response") == 'Ok') {
                                    ttp.doAjax('publish_course', {'course_id': data[5]}, function(response) {
                                        if (response.success == 'true') {
                                            $('#course_list_table').DataTable().cell(dataIndex, 4).data("publish");
                                            // Get node for last child, get select, modify Publish option to "Unpublish"
                                            var $td = $($('#course_list_table').DataTable().cell(dataIndex, 5).node());
                                            $td.find("li").filter(function(i, e) { return $(e).text() === 'Publish'; }).text("Unpublish");
                                            // refresh dropdownmenu (automatic)
                                        } else {
                                            jQuery('#dialog-error').confirm("option", "title", "Publish failed...")
                                            .confirm("option", "content", "<p>" + response.data.message + "</p>")
                                            .confirm("open");
                                        }
                                    });
                                }
                            }).confirm("open");*/
                    } else if (text === 'Unpublish') {
                        if (data[3] == 0) {
                            $("#dialog-confirm")
                                .confirm("option", "title", "Unpublish...")
                                .confirm("option", "content", "<p>The course will no longer be available for sign up.</p><p>Are you sure you want to do this?</p>")
                                .confirm("option", "response", '')
                                .confirm("option", "close", function() {
                                    if ($(this).confirm("option", "response") == 'Ok') {
                                        ttp.doAjax('unpublish_course', {'course_id': data[5]}, function(response) {
                                            if (response.success == 'true') {
                                                $('#course_list_table').DataTable().cell(dataIndex, 4).data("draft");
                                                // Get node for last child, get select, modify Publish option to "Unpublish"
                                                var $td = $($('#course_list_table').DataTable().cell(dataIndex, 5).node());
                                                $td.find("li").filter(function(i, e) { return $(e).text() === 'Unpublish'; }).text("Publish");
                                                // refresh dropdownmenu (automatic)
                                            } else {
                                                jQuery('#dialog-error').confirm("option", "title", "Unpublish failed...")
                                                .confirm("option", "content", "<p>" + response.data.message + "</p>")
                                                .confirm("open");
                                            }
                                        });
                                    }
                                })
                                .confirm("open");
                        } else {
                            $("#dialog-error")
                                .confirm("option", "title", "Unpublish...")
                                .confirm("option", "content", "<p>This course still has Live Events that are actively accepting new students.</p><p>To unpublish this Course, the related Live Events must first be un-published.</p>")
                                .confirm("open");
                        }
                    } else if (text == 'Edit') {
                        window.location.href = TheTrainingMangerLMS.edit_url + '/course/' + data[5];
                    }
                }});
            $(row).find("td:nth-child(2)").addClass("featured");
            $(row).find("td:nth-child(3)").addClass("completed");
            $(row).find("td:nth-child(4)").addClass("upcoming");
            $(row).find("td:nth-child(5)").addClass("status");
            //console.log("Row Callback");
        }
    } );
    $('#table-filters').find("input[name=featured],input[name='not-featured'],input[name=scheduled],input[name='not-scheduled'],input[name=draft],input[name=publish]").change(function() { $('#course_list_table').DataTable().draw(); });

    $('.date-input .month-input')
        .mask('99', { placeholder: " ", 
            completed: function() {
                var month = parseInt(this.val(), 10);
                if (isNaN(month) || !((month >= 1) && (month <= 12))) {
                    // highlight temporary red(this)
                } else {
                    this.parent().find(".day-input").focus();
                }
            }
        });
    $('.date-input .day-input')
        .mask('99', { placeholder:" ",
            completed: function() {
                var day = parseInt(this.val(), 10);
                if (isNaN(day) || !((day >= 1) && (day <= 31))) {
                    // highlight temporary red(this)
                } else {
                    this.parent().find(".year-input").focus();
                }
            }
        });
    $('.date-input .year-input')
        .mask('9999', { placeholder: " ",
            completed: function() {
                var year = parseInt(this.val(), 10);
                var currentYear = (new Date()).getFullYear();
                if (isNaN(year) || !((year >= currentYear) && (year <= currentYear + 1))) {
                    // highlight temporary red(this)
                    console.log("Redrum!");
                    return;
                }
                // do more strigent validation
                var dateText = this.parent().find(".month-input").val() + '/' + this.parent().find(".day-input").val() + '/' + this.val();
                // set Date to datepicker, and read it back, verify match
                var datePicker = this.parent().find('.datepicker');
                datePicker.datepicker("setDate", dateText);
                var date = datePicker.datepicker("getDate");
                console.log(date.toDateString());
                // Date must be greater than or equal to Now or previous date
                // focus to time on success
            }
        });
    $('.date-input > .datepicker').datepicker({
        showOn: 'button', 
        buttonText: '<span class="fa fa-calendar fa-lg"></span>', 
        minDate: 0,
        maxDate: "+1y",
        onClose: function(dateText, datePicker) {
            var parent = $(this).parent();
            if (dateText === "") {
                parent.find(".month-input,.day-input,.year-input").val("");
            } else {
                parent.find(".year-input").val(datePicker.currentYear);
                parent.find(".month-input").val(pad(datePicker.currentMonth + 1, 2));
                parent.find(".day-input").val(pad(datePicker.currentDay, 2));
            }
            function pad(num, size) {
                var s = num+"";
                while (s.length < size) s = "0" + s;
                return s;
            }
        }
    });
};})(jQuery));	
