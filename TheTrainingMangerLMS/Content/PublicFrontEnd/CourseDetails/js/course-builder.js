"use strict";
// TODO: move this to ttp.courseBuilder
var skip = false;
// These extensions can be defined in document ready
(function ($, undefined) {
    $.widget("ttp.tabs", $.ui.tabs, {
    	_create: function() {
    		this._super();
            this.element.find("> ul").removeClass("ui-tabs-nav").removeClass("ui-widget-header");
            this.element.find("> ul > li").removeClass("ui-corner-top");
            this.element.find("> ul > li:first-child").addClass("ui-corner-left");
            this.element.find("> ul > li:last-child").addClass("ui-corner-right");
    		// bug fix due to cascaded tab buttons on create
    		if (this.options.heightStyle == 'fill') {
    			var height = this.element.height() - this.element.find("> ul").outerHeight();
    			// TODO: - padding.top - padding-bottom
    			this.element.find("> .ui-tabs-panel").css('height', height);
    		}
    	}
    });

	ttp.courseBuilder = {
		main: {
			'courseView': function(data) {
				ttp.doTemplate('course-view', data,
					function(text) {
						jQuery("#ttp-lms-acb-main").children().hide();
						jQuery("#course-view").html(text).show();
						// for the time being
						jQuery('#course-view').find(":input[name=edit]").click(function(e) {
							e.stopPropagation();
							var course_id = TheTrainingMangerLMS.course_id;
							ttp.doAjax('get_course', {'course_id': course_id},
								function(response) {
									var course = jQuery.parseJSON(response.data.course);
									ttp.courseBuilder.main.courseEdit({course: course});
								}
							);
						})
					},
					function(err) {
						console.log(err);
						alert("An error has occurred; see console for details.");
					});
			},
			'courseEdit' : function(data) {
				var course = data.course;
				$("#edit-course-form")
				.find('input[name=ID]').val(course.ID).data("course", course).end()
				.find('input[name=title]').val(course.title).prop("defaultValue", course.title).end();
				$('#edit-course').scrollTop(0);
				$("#ttp-lms-acb-main").children().hide();
				$("#edit-course").show();
				jQuery('#ttp-lms-acb-container').data('ttp-layout').hide('west');
			},
			'lessonView': function(data) {
				ttp.doTemplate('lesson-view', data, 
					function(text) {
						jQuery("#ttp-lms-acb-main").children().hide();
						jQuery('#lesson-view').html(text).show();
					},
					function(err) {
						console.log(err);
						alert("An error has occurred; see console for details.");
					});
			},
			'lessonEdit': function(data) {
				var lesson = data.lesson;
				$("#edit-lesson-form")
				.find('input[name=ID]').val(lesson.ID).data("lesson", lesson).end()
				.find('input[name=title]').val(lesson.title).prop("defaultValue", lesson.title).end()
				.find('textarea[name=description]').val(lesson.description).prop("defaultValue", lesson.description).end()
				.find('textarea[name=content]').val(lesson.content).prop("defaultValue", lesson.content).end()
				.find('input[name=estimated_time]').val(lesson.estimated_time).prop("defaultValue", lesson.estimated_time);
				$("#edit-lesson").scrollTop(0);
				$("#ttp-lms-acb-main").children().hide();
				$("#edit-lesson").show();
				jQuery('#ttp-lms-acb-container').data('ttp-layout').hide('west');
			},
			'topicView': function(data) {
				ttp.doTemplate('topic-view', data, 
					function(text) {
						jQuery("#ttp-lms-acb-main").children().hide();
						jQuery('#topic-view').html(text).show();
					},
					function(err) {
						console.log(err);
						alert("An error has occurred; see console for details.");
					});
			},
			'topicEdit': function(data) {
				var topic = data.topic;
				$("#edit-topic-form")
				.find('input[name=ID]').val(topic.ID).data('topic', topic).end()
				.find('input[name=title]').val(topic.title).prop("defaultValue", topic.title).end();
				switch(topic.content_type ) {
					case 'Html' : $("#edit-topic-form").find('textarea[name=content]').val(topic.content).prop("defaultValue", topic.content).end()
									.find('.topic-edit-content').children().hide().end().find('.topic-type-html').show();
								  break;
				};
				$("#edit-topic").scrollTop(0);
				$("#ttp-lms-acb-main").children().hide();
				$("#edit-topic").show();
				jQuery('#ttp-lms-acb-container').data('ttp-layout').hide('west');
			}
		}
	};
})(jQuery);

jQuery(document).ready((function($) {return function() {
	// set-up layout(s)
	var layout = $('#ttp-lms-acb-container').layout({
        closable: false,
        resizable: false,
        slidable: false,
        livePaneResizing: false,
        north__spacing_open: 2,
        south__spacing_open: 2,
        west__spacing_open: 7,

        west__closable: true,
        west__slidable: false,
        west__resizable: true,
        west__livePaneResizing: true,
        west__minSize: 0.15,
        west__size:    0.25,
        west__maxSize: 0.4
    });
    $('#ttp-lms-acb-container').data('ttp-layout', layout);
    $('#ttp-lms-acb-container > .ui-layout-resizer-west').append('<span class="ui-icon ui-icon-grip-dotted-vertical resizer-bar-west-icon"></span><span class="ui-icon ui-icon-grip-dotted-vertical resizer-bar-west-icon"></span>').addClass("ui-state-default");
    layout.resizeAll();
    $('#ttp-lms-acb-container > .ui-layout-resizer-west').hover(function() { $(this).addClass("ui-state-hover"); $(this).removeClass("ui-state-default"); }, function() { $(this).addClass("ui-state-default"); $(this).removeClass("ui-state-hover"); });

	// add jQuery widgets
		// add tabs
	$( "#ttp-lms-acb-tab" ).tabs({
		/*create: function( e, ui ) {
             $(this).find("> ul").removeClass("ui-tabs-nav").removeClass("ui-widget-header");
             $(this).find("> ul > li").removeClass("ui-corner-top");
             $(this).find("> ul > li:first-child").addClass("ui-corner-left");
             $(this).find("> ul > li:last-child").addClass("ui-corner-right");
        },*/
		heightStyle: "fill"
	});
	$('#syllabus-wrapper').layout({
		closable: false,
		resizable: false,
		slidable: false,
		livePaneResizing: false,
		south__spacing_open: 2
	});
function set_up_syllabus_lessons() {
		// add accordions and icon menu events but only if it has not been set-up previously.
    	var ami = $("#syllabus > div").filter(function(i, e) { return !$(e).is(":data(ui-accordion)"); })
            .accordion({
            active: false,
            header: "h3",
            collapsible: true,
            heightStyle: "content",
            beforeActivate: function(event, ui) {
                if (skip) {
                    skip = false;
                    return false;
                }
                return true;
            }
        })
    	.hover(function() {
	    		$(this).find('h3').css('paddingRight', $(this).find("h3 .icon-header-menu").outerWidth());
	    		$(this).find("h3 .icon-header-menu").css('display', 'block')
	    	},
    		function() {
	    		$(this).find("h3 .icon-header-menu").css('display', 'none');
	    		$(this).find('h3').css('paddingRight', '0px');
	    	}
    	)
        .find("h3 .accordion-menu-icon")
		.hover(function() {
	            $(this).addClass('accordion-menu-icon-hover');
	        },
	        function() {
	            $(this).removeClass('accordion-menu-icon-hover');
	        }
        )
		.filter(function(i, e) { return $(e).data('action') == "view";})
		.click(function(e) {
			e.stopPropagation();
			var lesson_id = $(this).closest(".syllabus-lesson").data('id');
			ttp.doAjax('get_lesson', {'lesson_id': lesson_id},
				function(response) {
					var lesson = $.parseJSON(response.data.lesson);
					ttp.courseBuilder.main.lessonView({lesson: lesson});
				}
			);
        }).end()
		.filter(function(i, e) { return $(e).data('action') == "delete";})
		.click(function(e) {
			e.stopPropagation();
			var lesson_el = $(this).closest(".syllabus-lesson");
			var lesson_id = lesson_el.data('id');
			$("#dialog-confirm").confirm("option", "title", "Remove lesson...")
				.confirm("option", "content", "Are you sure you want to delete this lesson?")
				.confirm("option", "response", '')
				.confirm("option", "close", function() {
					if ($(this).confirm("option", "response") == 'Ok') {
						ttp.doAjax('remove_lesson', {'course_id': TheTrainingMangerLMS.course_id, 'lesson_id': lesson_id}, function() {
							lesson_el.hide();
							// get topic elements
							var topics_el = lesson_el.find(".syllabus-lesson-topics li").detach();
							// get unassigned, check to see if it has a ul, if not, create it, appendTo it
							var unassigned = $("#syllabus .unassigned-topics > .syllabus-lesson-topics");
		            		if (unassigned.find("ul > .no-topics").length) {
		            			unassigned.find("ul > .no-topics").remove();
	    	        		}
							// add topics to unassigned
							unassigned.find("ul").append(topics_el);//.sortable("refresh");
							// remove lesson
							lesson_el.remove();
						});
					}
				}).confirm("open");
		}).end()
		.filter(function(i, e) { return $(e).data('action') == "edit";})
		.click(function(e) {
			e.stopPropagation();
			var lesson_id = $(this).closest(".syllabus-lesson").data('id');
			ttp.doAjax('get_lesson', {'lesson_id': lesson_id},
				function(response) {
					var lesson = $.parseJSON(response.data.lesson);
					ttp.courseBuilder.main.lessonEdit({lesson: lesson});
				}
			);
		}).end()
		.filter(function(i,e) { return $(e).data('action') == "add-topic"})
		.click(function(e) {
			e.stopPropagation();
			var lesson_id = $(this).closest(".syllabus-lesson").data('id');
			$('#dialog-add-topic') // jQuery preferred identing
				.find(':input[name=type]')
					.prop('selectedIndex', 0)
					.end()
				.find(':input[name=title]')
					.val("")
					.end() // consider putting this on dialog ok
				.find(':input[name=lesson_id]')
					.val(lesson_id)
					.end()
				.dialog("open");
		}).end().end()
		.find(".syllabus-lesson-topics > ul")
		// check if there is a hover option for accordion that will allow it to auto open for certain sortable's or draggable's or dropable's
			.sortable({
				axis: "y",
				handle: "span.topic-title",
	            items: "li",
	            connectWith: "#syllabus > .syllabus-lesson > .syllabus-lesson-topics > ul",
	            containment: "#syllabus",
	            sort: function(event, ui) {
	            	//console.log("You are sorting!");
	            	// FIXME: Check for scroll limits of containment and scroll accordingly
	            },
	            update: function(event, ui) {
	            	if (ui.sender === null) {
	            		// ui needs a receiver field; since it doesn't, check to see if it still has the item
	            		if ($(this).has(ui.item).length && !$(ui.item).closest(".syllabus-lesson").is(".unassigned-topics")) {
	            			var topic_order = $(this).find("li").map(function(i, e) { return $(e).data("id"); }).get().join(',');
	            			var lesson_id = $(this).closest(".syllabus-lesson").data("id");
	            			ttp.doAjax('update_topics_order', {'lesson_id': lesson_id, 'topic_order': topic_order }, function(response) {}, function() {});
	            		} else {
	            			// Update No Topics added
	            			if (!$(this).find("> li").length) {
	            				$(this).html('<div class="no-topics">No topics have been added.</div>');
	            			}
	            		}
	            	} else {
	            		var is_from_unassigned = false, is_to_unassigned = false;
	            		if ((ui.sender !== null) && $(ui.sender).closest(".syllabus-lesson").is(".unassigned-topics")) {
	            			is_from_unassigned = true;
	            		} else if ($(ui.item).closest(".syllabus-lesson").is(".unassigned-topics")) {
	            			is_to_unassigned = true;
	            		}
	            		if ($(this).find("> .no-topics").length) {
	            			$(this).find("> .no-topics").remove();
	            		}
	            		var destination_lesson_id = is_to_unassigned ? -1 : $(this).closest(".syllabus-lesson").data("id");
	            		var source_lesson_id = is_from_unassigned ? -1 : $(ui.sender).closest(".syllabus-lesson").data("id");
	            		var topic_id = $(ui.item).data("id");
	            		// TODO: Consider hiding <div>No topics added on hover if possible
	            		var index = $(ui.item).index();
	            		//move(topic_id, source_lesson_id, destination_lesson_id, index)
	            		ttp.doAjax('move_topic', {'topic_id': topic_id, 'source_lesson_id': source_lesson_id,
	            			'destination_lesson_id': destination_lesson_id, 'index': index,
	            			'course_id': TheTrainingMangerLMS.course_id});
	            	}
	            },
	            stop: function(event, ui) {
	            	$(ui.item).css('left', '');
	            }
			})
			.end()
		.find("div li").each(function(i,e) { set_up_syllabus_topic($(e)); });
	};
function set_up_syllabus_topic(e$) {
		e$.hover(
			function() {
				$(this).addClass('ui-state-hover');
	    		$(this).css('paddingRight', $(this).find(".icon-header-menu").outerWidth());
	    		$(this).find(".icon-header-menu").css('display', 'block')
			}, 
			function() {
				$(this).removeClass('ui-state-hover');
	    		$(this).find(".icon-header-menu").css('display', 'none');
	    		$(this).css('paddingRight', '0px');
			}
		).find(".accordion-menu-icon")
		.hover(function() {
	            $(this).addClass('accordion-menu-icon-hover');
	        },
	        function() {
	            $(this).removeClass('accordion-menu-icon-hover');
	        }
        )
		.filter(function(i, e) { return $(e).data('action') == "view";})
		.click(function(e) {
			e.stopPropagation();
			var topic_id = $(this).closest("li").data('id');
			ttp.doAjax('get_topic', {'topic_id': topic_id},
				function(response) {
					var topic = $.parseJSON(response.data.topic);
					ttp.courseBuilder.main.topicView({topic: topic});
				},
				function(err) {
					console.log(err);
					alert("An error has occurred; see console for details.");
				}
			);
        }).end()
		.filter(function(i, e) { return $(e).data('action') == "delete";})
		.click(function(e) {
			e.stopPropagation();
			var topic_el = $(this).closest("li");
			var lesson_el = $(this).closest(".syllabus-lesson");
			var topic_id = topic_el.data('id');
			var lesson_id = lesson_el.data('id');
			$("#dialog-confirm").confirm("option", "title", "Delete topic...")
				.confirm("option", "content", "Are you sure you want to delete this topic?")
				.confirm("option", "response", '')
				.confirm("option", "close", function() {
					if ($(this).confirm("option", "response") == 'Ok') {
						ttp.doAjax('delete_topic', {'course_id': TheTrainingMangerLMS.course_id, 'lesson_id': lesson_id, 'topic_id': topic_id}, function() {
							var topics_el = topic_el.parent();
							topic_el.remove();
							if (!topics_el.find("> li").length) {
								topics_el.html('<div class="no-topics">No topics have been added.</div>');
							}
							topics_el.sortable("refresh");
						});
					}
				}).confirm("open");
		}).end()
		.filter(function(i, e) { return $(e).data('action') == "edit"; })
		.click(function(e) {
			e.stopPropagation();
			var topic_id = $(this).closest("li").data('id');
			ttp.doAjax('get_topic', {'topic_id': topic_id},
				function(response) {
					var topic = $.parseJSON(response.data.topic);
					ttp.courseBuilder.main.topicEdit({topic: topic});
				}
			);
		});
	};
    set_up_syllabus_lessons();
        $("#syllabus").sortable({
            axis: "y",
            handle: "h3",
            items: "> div:not(.unassigned-topics)",
            start: function(event, ui) {
                		$("#syllabus > div > h3").each(function(i, e) {
    							//if ($(e).tooltip("instance")) $(e).tooltip("disable");
    					});
            		},
            stop: function(event, ui) {
                		// IE doesn't register the blur when sorting
                		// so trigger focusout handlers to remove
                		// .ui-state-focus
                		ui.item.children("h3").triggerHandler("focusout");
                		skip = true;
                		$("#syllabus > div > h3").each(function(i,e) {
    							//if($(e).tooltip("instance")) $(e).tooltip("enable");
    					});
            		}
        });
		// add sortable to topics (connect-with, limit to syllabus-lessons)
	// add tooltips as needed (make this function), call after resizing or size changes
	// add events
		// add hover for all icons: check
		// add click for all icons: delete edit add-topic
		// add click for add Lesson: check
		$("#insert-lesson").click(function(e) {
			$('#dialog-add-lesson-selector').val("New");
			$('#dialog-add-lesson-selector').change();
			$('#dialog-add-lesson').find(":input[name=title]").val("");
			$('#dialog-add-lesson').dialog("open");
		});
		$("#edit-course-form").find(":input[name=submit]").click(function(e) {
			var updates = jQuery("#edit-course-form").find(":input:not(:button):not([type=hidden])").filter(function(i,e) { return jQuery(e).val() != jQuery(e).prop('defaultValue'); }).get().reduce(function(pValue, e) { pValue[jQuery(e).attr('name')] = jQuery(e).val(); return pValue; }, {});
			if (Object.keys(updates).length) {
				var course_id = jQuery("#edit-course-form").find(":input[name=ID]").val();
				ttp.doAjax('update_course', {course_id: course_id, updates: updates},
					function(response) {
						if (response.success == 'true') {
							jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
							course = $.parseJSON(response.data.course);
							// generate view with returned lesson
							ttp.courseBuilder.main.courseView({course: course});
						} else {
							// TODO: we know some fields failed to validate
							// mark input blocks accordingly
						}
					}
				);
			} else {
				jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
				var course = $('#edit-course-form').find(':input[name=ID]').data('course');
				ttp.courseBuilder.main.courseView({course: course});
			}
		}).end()
		.find(":input[name=cancel]").click(function(e) {
			var updates = jQuery("#edit-course-form").find(":input:not(:button):not([type=hidden])").filter(function(i,e) { return jQuery(e).val() != jQuery(e).prop('defaultValue'); }).get().reduce(function(pValue, e) { pValue[jQuery(e).attr('name')] = jQuery(e).val(); return pValue; }, {});
			if (Object.keys(updates).length) {
				$("#dialog-confirm").confirm("option", "title", "Discard changes...")
					.confirm("option", "content", "You have made changes.  Are you sure you want to discard these changes?")
					.confirm("option", "response", '')
					.confirm("option", "close", function() {
						if ($(this).confirm("option", "response") == 'Ok') {
							jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
							// go to topic view
							var course = $('#edit-course-form').find(':input[name=ID]').data('course');
							ttp.courseBuilder.main.courseView({course: course});
						}
					}).confirm("open");
			} else {
				jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
				var course = $('#edit-course-form').find(':input[name=ID]').data('course');
				ttp.courseBuilder.main.courseView({course: course});
			}
		});
		// add function for submit Topic Edit
		$("#edit-topic-form").find(":input[name=submit]").click(function(e) {
			e.stopPropagation();
			// var updates = form.getChanges();
			// for now do it in a hackish fashion until we build something better
			var updates = jQuery("#edit-topic-form").find(":input:not(:button):not([type=hidden])").filter(function(i,e) { return jQuery(e).val() != jQuery(e).prop('defaultValue'); }).get().reduce(function(pValue, e) { pValue[jQuery(e).attr('name')] = jQuery(e).val(); return pValue; }, {});
			if (Object.keys(updates).length) {
				var topic_id = jQuery("#edit-topic-form").find(":input[name=ID]").val();
				ttp.doAjax('update_topic', {topic_id: topic_id, updates: updates},
					function(response) {
						if (response.success == 'true') {
							jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
							topic = $.parseJSON(response.data.topic);
							// update title in lesson accordion topic list
							$("#syllabus > .syllabus-lesson ul > li").find("[data-id=" + topic.ID + "]").find(".topic-title").text(topic.title != '' ? topic.title : 'Untitled');
							// generate view with returned lesson
							ttp.courseBuilder.main.topicView({topic: topic});
						} else {
							// TODO: we know some fields failed to validate
							// mark input blocks accordingly
						}
					}
				);
			} else {
				jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
				var topic = $('#edit-topic-form').find(':input[name=ID]').data('topic');
				ttp.courseBuilder.main.topicView({topic: topic});
			}
		}).end()
		// add function for cancel Topic Edit
		.find(":input[name=cancel]").click(function(e) {
			var updates = jQuery("#edit-topic-form").find(":input:not(:button):not([type=hidden])").filter(function(i,e) { return jQuery(e).val() != jQuery(e).prop('defaultValue'); }).get().reduce(function(pValue, e) { pValue[jQuery(e).attr('name')] = jQuery(e).val(); return pValue; }, {});
			if (Object.keys(updates).length) {
				$("#dialog-confirm").confirm("option", "title", "Discard changes...")
					.confirm("option", "content", "You have made changes.  Are you sure you want to discard these changes?")
					.confirm("option", "response", '')
					.confirm("option", "close", function() {
						if ($(this).confirm("option", "response") == 'Ok') {
							jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
							// go to topic view
							var topic = $('#edit-topic-form').find(':input[name=ID]').data('topic');
							ttp.courseBuilder.main.topicView({topic: topic});
						}
					}).confirm("open");
			} else {
				jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
				var topic = $('#edit-topic-form').find(':input[name=ID]').data('topic');
				ttp.courseBuilder.main.topicView({topic: topic});
			}
		});
		// add function for submit Lesson Edit
		$("#edit-lesson-form").find(":input[name=submit]").click(function(e) {
			e.stopPropagation();
			// var updates = form.getChanges();
			// for now do it in a hackish fashion until we build something better
			var updates = jQuery("#edit-lesson-form").find(":input:not(:button):not([type=hidden])").filter(function(i,e) { return jQuery(e).val() != jQuery(e).prop('defaultValue'); }).get().reduce(function(pValue, e) { pValue[jQuery(e).attr('name')] = jQuery(e).val(); return pValue; }, {});
			if (Object.keys(updates).length) {
				var lesson_id = jQuery("#edit-lesson-form").find(":input[name=ID]").val();
				ttp.doAjax('update_lesson', {lesson_id: lesson_id, updates: updates},
					function(response) {
						if (response.success == 'true') {
							jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
							lesson = $.parseJSON(response.data.lesson);
							// update title in accordion
							$("#syllabus > .syllabus-lesson:not(.unassigned-topics)[data-id=" + lesson.ID + "]").find("> h3 > .lesson-title").text(lesson.title != '' ? lesson.title : 'Untitled');
							// generate view with returned lesson
							ttp.courseBuilder.main.lessonView({lesson: lesson});
						} else {
							// TODO: we know some fields failed to validate
							// mark input blocks accordingly
						}
					}
				);
			} else {
				jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
				var lesson = $('#edit-lesson-form').find(':input[name=ID]').data('lesson');
				ttp.courseBuilder.main.lessonView({lesson: lesson});
			}
		}).end()
		// add function for cancel Lesson Edit
		.find(":input[name=cancel]").click(function(e) {
			var updates = jQuery("#edit-lesson-form").find(":input:not(:button):not([type=hidden])").filter(function(i,e) { return jQuery(e).val() != jQuery(e).prop('defaultValue'); }).get().reduce(function(pValue, e) { pValue[jQuery(e).attr('name')] = jQuery(e).val(); return pValue; }, {});
			if (Object.keys(updates).length) {
				$("#dialog-confirm").confirm("option", "title", "Discard changes...")
					.confirm("option", "content", "You have made changes.  Are you sure you want to discard these changes?")
					.confirm("option", "response", '')
					.confirm("option", "close", function() {
						if ($(this).confirm("option", "response") == 'Ok') {
							jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
							// go to lesson view
							var lesson = $('#edit-lesson-form').find(':input[name=ID]').data('lesson');
							ttp.courseBuilder.main.lessonView({lesson: lesson});
						}
					}).confirm("open");
			} else {
				jQuery('#ttp-lms-acb-container').data('ttp-layout').show('west');
				var lesson = $('#edit-lesson-form').find(':input[name=ID]').data('lesson');
				ttp.courseBuilder.main.lessonView({lesson: lesson});
			}
		});
		// add update for lesson sortable
	$("#syllabus").sortable("option", "update", function(event, ui) {
		// get new order of lessons and update
		var lesson_order = $("#syllabus > .syllabus-lesson:not(.unassigned-topics)").map(function(i, e) { return $(e).data("id"); }).get();
		ttp.doAjax('update_lessons_order', {'course_id': TheTrainingMangerLMS.course_id, 'lesson_order': lesson_order.join()}, function() {}, function() {});
	});
	// add dialogs
	$('<div></div').attr('id', 'dialog-confirm').hide().appendTo('body')
    .confirm({
        resizable: true,
        width: 340,
        modal: true,
        buttons: {
            Ok: function () {
            	$(this).confirm("option", "response", "Ok");
                $(this).confirm("close");
            },
            Cancel: function () {
            	$(this).confirm("option", "response", "Cancel");
                $(this).confirm("close");
            }
        },
        autoOpen: false,
    });
    // add supporting form dialogs
    $('#dialog-add-topic').dialog({
    	resizable: false,
    	width: 548,
    	height: 340,
    	buttons: {
    		Ok: function() {
    			var type = $('#dialog-add-topic-type-selector').val(); // Check this
    			var title = $('#dialog-add-topic input[name=title]').val();
    			var lesson_id = $('#dialog-add-topic input[name=lesson_id]').val();
    			ttp.doAjax('new_topic', {'course_id': TheTrainingMangerLMS.course_id, 'lesson_id': lesson_id, 'topic_title' : title, 'topic_content_type': type },
    				function(response) {
   						$('#dialog-add-topic').dialog("close");
    					if (response.success == 'true') {
    						var topic = $.parseJSON(response.data.topic);
    						// add Topic to lesson's accordion
							var topics_el = $("#syllabus > .syllabus-lesson:not(.unassigned-topics)[data-id=" + lesson_id + "]").find(".syllabus-lesson-topics");
							if (topics_el.find("ul > .no-topics").length) {
								topics_el.find("ul").empty();
							}
							ttp.doTemplate("syllabus-lesson-topic", { topic: topic }, 
								function(text) { 
									var li = $(text).appendTo(topics_el.find("ul"));
									set_up_syllabus_topic(li);
									topics_el.find("ul").sortable("refresh");
									topics_el.end().accordion("option", "active", 0);
			    					ttp.courseBuilder.main.topicEdit({'topic': topic});
								}, 
								function(err) { console.log(err); alert("An error occurred; see console for details."); }
							);
	    				}
    				},
    				function() {
    					$('#dialog-add-topic').dialog("close");
    				}
    			);
    		},
    		Cancel: function() {
    			$(this).dialog("close");
    		}
    	},
    	open: function() {
    		// reset elements
    	},
    	title: "Add Topic",
    	autoOpen: false
    });
	$('#dialog-add-lesson-content').data('layout', $('#dialog-add-lesson-content').layout({
		closable: false,
		resizable: false,
		slidable: false,
		livePaneResizing: false,
		north__spacing_open: 0
	}));
    $('#dialog-add-lesson').dialog({
    	resizable: true,
    	width: 548,
    	height: 340,
    	buttons: {
    		Ok: function() {
				var selected = $('#dialog-add-lesson-selector').find("option:selected").text();
				if (selected == "New") {
	    			// get title
	    			var title = $('#dialog-new-lesson-pane input[name=title]').val();
	    			// do appropriate ajax call
	    			retval = ttp.doAjax('new_lesson', {'course_id': TheTrainingMangerLMS.course_id, 'lesson_title': title},
	    				function(response) {
	    					$('#dialog-add-lesson').dialog("close");
	    					if (response.success == 'true') {
	    						var lesson = $.parseJSON(response.data.lesson);
	    						// add the lesson to the syllabus-lessons
	    						setTimeout(function() {
	    							ttp.doTemplate("syllabus-lesson", {'lesson': lesson}, 
	    								function(text){
			    							$('#syllabus > .unassigned-topics').before(text);
			    							//$('#syllabus > .syllabus-lesson:not(.unassigned-topics)').last().
			    							set_up_syllabus_lessons();
		   						        	$("#syllabus").sortable("refresh");

			    							ttp.courseBuilder.main.lessonEdit({'lesson': lesson});
	    								}, 
	    								function(err) {
	    									console.log(err);
	    									alert('An error occurred; check console for details.');
	    								}
	    							);
								}, 0);
	    					}
	    				},
	    				function() {
	    					$('#dialog-add-lesson').dialog("close");
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
			$('#dialog-add-lesson-selector').selectmenu({
				width: 'auto',
				change: function(e, ui) {
					$($(this).selectmenu().data("ui-selectmenu").element).change();
				}
			});
    		$('#dialog-add-lesson-selector').selectmenu("refresh");
    		$('#dialog-add-lesson-content').data('layout').resizeAll();
		},
    	title: "Insert Lesson",
    	autoOpen: false,
    });
	$('#dialog-add-lesson-selector').change(function(e) {
		var selected = $(e.target).find("option:selected").text();
		if (selected == "New") {
			$('#dialog-import-lesson-pane').hide();
			$('#dialog-new-lesson-pane').show();
		} else {
			$('#dialog-new-lesson-pane').hide();
			$('#dialog-import-lesson-pane').show();
			// Load lesson list, show progress bar if necessary
			// update list in pane
		}
	});
	$('#course-view').find(":input[name=edit]").click(function(e) {
		e.stopPropagation();
		var course_id = TheTrainingMangerLMS.course_id;
		ttp.doAjax('get_course', {'course_id': course_id},
			function(response) {
				var course = $.parseJSON(response.data.course);
				ttp.courseBuilder.main.courseEdit({course: course});
			}
		);
	})

	// remove save button from dfw
	$('#wp-fullscreen-save').remove();

    // set course_id as a global variable
    TheTrainingMangerLMS.course_id = $("#ttp-lms-acb-container").data("id");
    $("#ttp-lms-acb-container").css('visibility', '');
    // TODO: trigger course_view_screen load
};})(jQuery));