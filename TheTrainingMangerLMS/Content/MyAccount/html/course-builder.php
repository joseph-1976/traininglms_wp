<?php
import('TheTrainingMangerLMS.Course');
import('TheTrainingMangerLMS.OnlineCourse');
import('TheTrainingMangerLMS.LiveCourse');

	if (!isset($course)) {
		ttp_lms_error("Course not set for Admin Course Builder Content.");
	}
?>
<div id="ttp-lms-acb-container" data-id="<?php echo $course->ID(); ?>" style="height: 600px; width: 100%; visibility: hidden;">
	<div class="ui-layout-west ttp-lms-acb-nav">
		<div id="ttp-lms-acb-tab">
	        <ul>
	            <li><a href="#syllabus-wrapper">Syllabus</a></li><!--
	            --><li><a href="#files">Files</a></li><!--
	            --><li><a href="#forum">Forum</a></li>
	        </ul>
	        <div id="syllabus-wrapper">
	        <div id="syllabus" class="ui-layout-center syllabus-lessons">
<?php
	$lessons = $course->getLessons();
	foreach($lessons as $lesson) {
?>		<div class="syllabus-lesson" data-id="<?php echo $lesson->ID(); ?>">
			<h3><span class="lesson-title"><?php echo $lesson->getTitle() != '' ? esc_html($lesson->getTitle()) : 'Untitled'; ?></span>
				<span class="icon-header-menu">
					<div class="accordion-menu-icon" title="Edit Lesson" data-action="edit">
						<span class="ui-icon ui-icon-pencil"></span>
					</div>
					<div class="accordion-menu-icon" title="Add Topic" data-action="add-topic">
						<span class="ui-icon ui-icon-plus"></span>
					</div>
					<div class="accordion-menu-icon" title="View Lesson" data-action="view">
						<span class="ui-icon ui-icon-search"></span>
					</div>
					<div class="accordion-menu-icon" title="Delete Lesson" data-action="delete">
						<span class="ui-icon ui-icon-trash"></span>
					</div>
				</span>
			</h3>
			<div class="syllabus-lesson-topics">
<?php
	$topics = $lesson->getTopics();
	if (count($topics)) {
?>
				<ul>
<?php
	foreach($topics as $topic) {
?>
					<li class="dashicons-before <?php 
					switch($topic->getContentType()) {
						case 'Html': echo "dashicons-media-code"; break; 
						case 'Video': echo "dashicons-media-video"; break;
						default: echo "dashicons-media-default";
					} 
					?>" data-id="<?php echo $topic->ID(); ?>"><span class="topic-title"><?php echo $topic->getTitle() != '' ? esc_html($topic->getTitle()) : 'Untitled'; ?></span>
						<span class="icon-header-menu">
							<div class="accordion-menu-icon" title="Edit Topic" data-action="edit">
								<span class="ui-icon ui-icon-pencil"></span>
							</div>
							<div class="accordion-menu-icon" title="Delete Topic" data-action="delete">
								<span class="ui-icon ui-icon-trash"></span>
							</div>
						</span>
					</li>
<?php
	} // topics
?>
				</ul>
<?php
	} else {
?>
		<ul><div class="no-topics">No topics have been added.</div></ul>
<?php
	}
?>	
			</div>
		</div>
<?php
	} // lessons
?>
		<div class="syllabus-lesson unassigned-topics">
			<h3>Unassigned Topics</h3>
			<div class="syllabus-lesson-topics">
<?php
	//$unassigned_topics = ...;
?>
				<ul>
<?php
	foreach($course->getUnassignedTopics() as $topic) {
?>
					<li class="dashicons-before <?php 
					switch($topic->getContentType()) {
						case 'Html': echo "dashicons-media-code"; break; 
						case 'Video': echo "dashicons-media-video"; break;
						default: echo "dashicons-media-default";
					} 
					?>" data-id="<?php echo $topic->ID(); ?>"><span class="topic-title"><?php echo $topic->getTitle() != '' ? esc_html($topic->getTitle()) : 'Untitled'; ?></span>
						<span class="icon-header-menu">
							<div class="accordion-menu-icon" title="Edit Topic" data-action="edit">
								<span class="ui-icon ui-icon-pencil"></span>
							</div>
							<div class="accordion-menu-icon" title="Delete Topic" data-action="delete">
								<span class="ui-icon ui-icon-trash"></span>
							</div>
						</span>
					</li>
<?php
	}
?>
				</ul>
			</div>
		</div>
	        </div><!-- syllabus-lessons -->
		<div class="ui-layout-south" style="padding: 0px; background: none;"><div id="insert-lesson" style="cursor: pointer;"><span class="ui-icon ui-icon-plus" style="display: inline-block;"></span><span style="display: inline-block;">Insert a Lesson</span></div></div>
</div><!-- wrapper -->
	        <div id="files"></div>
	        <div id="forum"></div>
	        <div id="notes"></div>
		</div>
	</div>
<div class="ui-layout-center" id="ttp-lms-acb-main">
	<div id="course-view" style="width: auto; height: auto;">
		<h3><?php echo $course->getTitle() != '' ? esc_html($course->getTitle()) : 'Untitled'; ?></h3>
		<input type="button" name="edit" value="Edit"/>
	</div>
	<div id="edit-course" style="width: auto; height: auto; display: none;">
		<form id="edit-course-form">
			<input type="hidden" name="ID" value="<?php echo $course->ID(); ?>"/>
			<div class="input-block">
				<label for="title">Name</label>
				<input id="course_edit_title" type="text" name="title" size="50" value=""/>
			</div>
			<div class="button-block" style="float:right; padding: 2em;">
				<input type="button" name="submit" value="Submit"/>
				<input type="button" name="cancel" value="Cancel"/>
			</div>
		</form>
	</div>
	<div id="topic-view" style="width: auto; height: auto; display: none;">
	</div>
	<div id="edit-topic" style="width: auto; height: auto; display: none;">
		<form id="edit-topic-form">
			<input type="hidden" name="ID" value=""/>
			<div class="input-block">
				<label for="title">Name</label>
				<input id="topic_edit_title" type="text" name="title" size="50" value=""/>
			</div>
			<div class="topic-edit-content">
				<div class="input-block topic-type-html">
					<label for="content">Content</label>
					<?php wp_editor("", "topic_edit_content", array('textarea_name' => 'content', 'textarea_rows' => 5, 'media_buttons' => false, 'dfw' => true)); ?>
				</div>
				<div class="input-block topic-type-video">
					<span>TBD</span>
				</div>
			</div>
			<div class="button-block" style="float:right; padding: 2em;">
				<input type="button" name="submit" value="Submit"/>
				<input type="button" name="cancel" value="Cancel"/>
			</div>
		</form>
	</div>
	<div id="lesson-view" style="width: auto; height: auto; display: none;">
	</div>
	<div id="edit-lesson" style="width: auto; height: auto; display: none;">
		<form id="edit-lesson-form">
			<input type="hidden" name="ID" value=""/>
			<div class="input-block">
				<label for="title">Name</label>
				<input id="lesson_edit_title" type="text" name="title" size="50" value=""/>
			</div>
			<div class="input-block">
				<label for="description">Description</label>
				<?php wp_editor("", "lesson_edit_description", array('textarea_name' => 'description', 'textarea_rows' => 5, 'media_buttons' => false, 'dfw' => true)); ?>
			</div>
			<div class="input-block">
				<label for="content">Content</label>
				<?php wp_editor("", "lesson_edit_content", array('textarea_name' => 'content', 'textarea_rows' => 10, 'media_buttons' => false, 'dfw' => true)); ?>
			</div>
			<div class="input-block">
				<label for="estimated_time">Estimated Time (hours)</label>
				<input id="lesson_edit_estimated_time" type="number" name="estimated_time" size="4" value=""/>
			</div>
			<div class="button-block" style="float:right; padding: 2em;">
				<input type="button" name="submit" value="Submit"/>
				<input type="button" name="cancel" value="Cancel"/>
			</div>
		</form>
	</div>
</div>
<div class="ui-layout-north" style="text-align: center;"><div id="switcher" style="float: left;"></div>Course Consumption Page Header<br><?php echo $course->getTitle() != '' ? esc_html($course->getTitle()) : 'Untitled'; ?></div>
<div class="ui-layout-south" style="text-align: center;">"Sticky" Footer</div>
<div id="dialog-add-lesson" style="display: none;">
	<div id="dialog-add-lesson-content" style="width: 100%; height: 100%;">
		<div class="ui-layout-north" style="background: none;">
			<select id="dialog-add-lesson-selector" style="float: right;">
				 <option selected="selected">New</option>
				 <option>Import</option>
			</select>
		</div>
		<div class="ui-layout-center" style="background: none;">
			<div id="dialog-new-lesson-pane">
				<label for="title">Lesson Name</label>
				<input type="text" name="title" size=50 value=""/>
			</div>
			<div id="dialog-import-lesson-pane" style="display: none;">
				List of lessons here.
			</div>
		</div>
	</div>
</div> <!-- dialog-add-lesson -->
<div id="dialog-add-topic" style="display: none;">
	<div id="dialog-add-topic-content" style="width: 100%; height: 100%;">
		<input type="hidden" name="lesson_id" val=""/>
		<label for="title">Topic Name</label>
		<input type="text" name="title" size="50" value=""/>
		<select id="dialog-add-topic-type-selector" name="type" style="float: right;">
<?php
	foreach(\TheTrainingMangerLMS\Topic::getContentTypes() as $type) {
?>
			<option><?php esc_html_e($type); ?></option>
<?php
	}
?>
		</select>
	</div>
</div> <!-- dialog-add-topic -->
</div> <!-- ttp-lms-acb-container -->
