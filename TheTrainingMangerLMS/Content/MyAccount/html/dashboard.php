<?php
?>
<div id="ttp-lms-ad-container" style="width: 100%; height: 100%;">
<div class="wrap"><h2>Courses<a class="add-new-h2" id="add-new-course">Add Course</a></h2>
<?php
	$course_list_table->prepare_items();
	$course_list_table->display();
?>
</div>
<div id="dialog-add-course" style="display: none;">
	<div id="dialog-add-course-content" style="width: 100%; height: 100%;">
		<div class="ui-layout-north" style="background: none;">
			<select id="dialog-add-course-selector" style="float: right;">
				 <option selected="selected">New</option>
				 <option>Copy</option>
			</select>
		</div>
		<div class="ui-layout-center" style="background: none;">
			<div id="dialog-new-course-pane">
				<label for="title">Course Name</label>
				<input type="text" name="title" size=50 value=""/>
			</div>
			<div id="dialog-clone-course-pane" style="display: none;">
				List of courses here.
			</div>
		</div>
	</div>
</div>
</div>
