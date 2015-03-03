<?php
// all ttp-lms content should be wrapped in an I.D.ed container
// this can be done at the template level or with the content
// wrapper construct.
?>
<div id="<?php echo ttp_lms_post_prefix('container'); ?>">
	<div style="width: auto; height: auto;">
		<form id="edit-course-form">
			<input type="hidden" name="ID" value="<?php echo $course->ID(); ?>"/>
			<div class="input-block">
				<label for="title">Name</label>
				<input id="title" name="title" type="text" size="50" value="<?php echo esc_html($course->getTitle()); ?>"/>
			</div>
			<div class="input-block">
				<label for="default_event_price">Default Event Price</label>
				<input id="default_event_price" type="text" name="default_event_price" size="8" value="<?php echo $course->getDefaultEventPrice(); ?>"/>
			</div>
			<div class="input-block">
				<label for="short_description">Short Description</label>
				<?php wp_editor(esc_html($course->getShortDescription()), "short_description", array('textarea_name' => 'short_description', 'textarea_rows' => 5, 'media_buttons' => false, 'dfw' => true)); ?>
			</div>
			<div class="input-block">
				<label for="description">Description</label>
				<?php wp_editor(esc_html($course->getDescription()), "description", array('textarea_name' => 'description', 'textarea_rows' => 10, 'media_buttons' => false, 'dfw' => true)); ?>
			</div>
			<div class="input-block">
				<label for="objectives">Objectives</label>
				<?php wp_editor(esc_html($course->getObjectives()), "objectives", array('textarea_name' => 'objectives', 'textarea_rows' => 10, 'media_buttons' => false, 'dfw' => true)); ?>
			</div>
			<div class="input-block">
				<label for="syllabus">Syllabus</label>
				<?php wp_editor(esc_html($course->getSyllabus()), "syllabus", array('textarea_name' => 'syllabus', 'textarea_rows' => 10, 'media_buttons' => false, 'dfw' => true)); ?>
			</div>
			<div class="button-block" style="float:right; padding: 2em;">
				<input type="button" name="submit" value="Submit"/>
				<input type="button" name="cancel" value="Cancel"/>
			</div>
		</form>
	</div>
</div>