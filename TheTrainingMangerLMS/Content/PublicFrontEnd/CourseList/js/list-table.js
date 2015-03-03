var list_table = (function($) {
return {
	_appendFilters: function(data) {
		var e = this.element;
		['s', 'cat', 'type'].forEach(function(name) {
			var temp = $(e).find(".table-nav-filter :input[name=" + name + "]").val();
			if (temp) {
				data[name] = temp;
			}
		});
		//jQuery('#course_list_table').find(":input[name=cat] option").filter(function(i, e) { return jQuery(e).prop("defaultSelected");}).val();
	},
	_appendOrderby: function(data) {
		var e = this.element;
		var orderby = $(e).find("input[name=orderby]").val();
		if (orderby) {
			data.orderby = orderby;
			data.order = $(e).find("input[name=order]").val();
		}
	},
	_appendPaging: function(data) {
		var e = this.element;
		var pagenum = $(e).find("input[name=pagenum]").val();
		data.paged = pagenum ? pagenum : '1';
	},
	initAjax: function(e) {
		this.element = e;
		var list_table = this; // just in case we have more than one list_table
		// add click handler to filter button
		$(e).find(".search-box input[type=submit]").click(function(event) {
			event.preventDefault();
			var data = {};
			list_table._appendFilters(data, true);
			list_table._appendOrderby(data);
			// paging is irrelevant
			list_table.update(data);
		});
		// add click handlers to orderby buttons
		$(e).find("thead, tfoot").find(".sorted,.sortable").find("a").click(function(event) {
			event.preventDefault();
			var data = {};
			// append new orderby, Xupdating hidden inputs after ajax completes
			data.orderby = $(this).closest(".sorted, .sortable").attr('id');
			$(e).find("input[name=orderby]").val(data.orderby);
			data.order = $(this).closest(".sorted, .sortable").hasClass("asc") ? "desc" : "asc";
			$(e).find("input[name=order]").val(data.order);
			// switch here and in footer
			$(this).closest(".sorted, .sortable").removeClass(data.order == "asc" ? "desc" : "asc").addClass(data.order == "asc" ? "asc" : "desc");
			list_table._appendFilters(data, false);
			list_table._appendPaging(data);
			list_table.update(data);
		});
		// add click handlers to paging interface
		$(e).find(".pagination-links a").click(function(event) {
			event.preventDefault();
			var that = $(this);
			if (that.hasClass("disabled")) {
				return;
			}
			var data = {};
			if (that.hasClass("first-page")) {
				data.paged = 1;
			} else if (that.hasClass("prev-page")) {
				data.paged = parseInt($(e).find("input[name=pagenum]").val()) - 1;
			} else if (that.hasClass("next-page")) {
				data.paged = parseInt($(e).find("input[name=pagenum]").val()) + 1;
			} else if (that.hasClass("last-page")) {
				data.paged = $(e).find(".top .tablenav-pages .total-pages").text();
			}
			list_table._appendFilters(data, false);
			list_table._appendOrderby(data);
			list_table.update(data);
		});
		// add select handlers to paging interface
		$(e).find(".pagination-links .current-page").change(function(event) {
			var data = {};
			data.paged = $(this).val();
			list_table._appendFilters(data, false);
			list_table._appendOrderby(data);
			list_table.update(data);
		});
		$(e).data('listTable', list_table);
	},
	update: function(data) {
		var list_table = this;
		console.log($.extend({ action: $(list_table.element).attr('id'), nonce: $(list_table.element).find("input[name='_ajax_nonce']").val() }, data));
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: $.extend({ action: $(list_table.element).attr('id'), nonce: $(list_table.element).find("input[name='_ajax_nonce']").val() }, data),
			success: function(response) {
				$('#the-list').html( response.rows );
				var e = list_table.element;
				// update paging interface
				$(e).find("input[name=pagenum]").val(response.pagenum);
				// update item text
				$(e).find(".tablenav .displaying-num").text(response.total_items_i18n);
				if (response.total_pages <= 1) {
					$(e).find(".pagination-links").hide();
					return;
				} else {
					$(e).find(".pagination-links").show();
				}
				// update select with totalpages first, then select val and update text in span
				// check for difference maybe to skip this
				$(e).find(".pagination-links .current-page").empty();
				for(var i = 1; i <= response.total_pages; i++) {
					$(e).find(".pagination-links .current-page").append('<option value="' + i + '">' + i + '</option>');
				}
				$(e).find(".pagination-links .current-page").val(response.pagenum);
				// update pagination links
				$(e).find(".pagination-links a").each(function(i, e) {
					if ($(e).hasClass('first-page') || $(e).hasClass('prev-page')) {
						$(e).toggleClass('disabled', response.pagenum == '1')
					} else if ($(e).hasClass('next-page') || $(e).hasClass('last-page')) {
						$(e).toggleClass('disabled', response.pagenum == response.total_pages);
					}
				});
			}
		});
	}
};
})(jQuery);
	//var temp = new list_table;
jQuery(document).ready((function($) {return function() {
	list_table.initAjax(document.getElementById('course_list_table'));
	//(new list_table).initAjax($('#course_list_table').element());
};})(jQuery));
