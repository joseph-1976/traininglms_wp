<?php
// all ttp-lms content should be wrapped in an I.D.ed container
// this can be done at the template level or with the content
// wrapper construct.
?>
<div id="<?php echo ttp_lms_post_prefix('container'); ?>">
    <div id="<?php echo ttp_lms_post_prefix('sidebar'); ?>">
        <div class="sidebar-content">
            <div class="sidebar-header">Your Dashboard</div>
            <div class="sidebar-items">
                <div class="sidebar-item page-focus">Our Courses</div>
                <div class="sidebar-item">Our Live Events</div>
            </div>
            <div class="sidebar-header">Narrow Your Results</div>
            <div id="table-filters" class="sidebar-items">
                <div class="sidebar-item">
                    <div class="filterable-header"><span class="fa fa-angle-double-down fa-lg"></span>Featured</div>
                    <div class="filterable-items">
                        <div class="filterable-item"><input type="checkbox" checked="true" name="featured">Featured</input></div>
                        <div class="filterable-item"><input type="checkbox" checked="true" name="not-featured">Not Featured</input></div>
                    </div>
                </div>
                <div class="sidebar-item">
                    <div class="filterable-header"><span class="fa fa-angle-double-down fa-lg"></span>Scheduled</div>
                    <div class="filterable-items">
                        <div class="filterable-item"><input type="checkbox" checked="true" name="scheduled">Scheduled</input></div>
                        <div class="filterable-item"><input type="checkbox" checked="true" name="not-scheduled">Not Scheduled</input></div>
                    </div>
                </div>
                <div class="sidebar-item">
                    <div class="filterable-header"><span class="fa fa-angle-double-down fa-lg"></span>Status</div>
                    <div class="filterable-items">
                        <div class="filterable-item"><input type="checkbox" checked="true" name="publish">Published</input></div>
                        <div class="filterable-item"><input type="checkbox" checked="true" name="draft">Draft</input></div>
                    </div>
                </div>
                <div class="sidebar-item">
<div class="date-input">
    <input class="month-input" type="text" size="2" maxlength="2" placeholder="mm"></input>
    <span>/</span>
    <input class="day-input" type="text" size="2" maxlength="2" placeholder="dd"></input>
    <span>/</span>
    <input class="year-input" type="text" size="4" maxlength="4" placeholder="yyyy"></input>
    <input class="datepicker" type="text" size="8" style="display: none;"></input>
</div>
                </div>
            </div>
        </div>
    </div>
    <div id="<?php echo ttp_lms_post_prefix('cl-content'); ?>">
        <div class="tc-header">
            <div class="tc-header-title"><span>TC's Courses</span></div>
            <div class="tc-header-button"><input id="add-new-course" type="button" value="Create New Course"></input></div>
        </div>
<table id="course_list_table" class="display" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Name</th>
            <th>Featured</th>
            <th>Completed</th>
            <th>Upcoming</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <th>Name</th>
            <th>Featured</th>
            <th>Completed</th>
            <th>Upcoming</th>
            <th>Status</th>
            <th></th>
        </tr>
    </tfoot>
</table>
</div> <!-- TTP LMS Content -->
<div id="dialog-add-course" style="display: none;">
    <div id="dialog-add-course-content" style="width: 100%; height: 100%;">
            <div id="dialog-new-course-pane">
                <label for="title">Course Name</label>
                <input type="text" name="title" size=55 value=""/>
            </div>
    </div>
</div>
<div id="dialog-publish-course" style="display: none;">
    <div id="dialog-publish-course-content" style="width: 100%; height: 100%;">
        <p class="ui-state-highlight" style="padding: 10px;">Publishing a course makes it viewable to the public on the front-end of your site. For this reason, we recommend double-checking the following information to make sure you're not forgetting anything.</p>
        <div>
            <span style="font-size: larger;">Course Publishing Checklist</span>
            <hr style="width: 100%;"></hr>
            <div>
                <input name="review" type="checkbox" value="reviewed">All course descriptions, details, syllabus items, etc...</input>
                <button>View Course Details</button>
            </div>
        </div>
    </div>    
</div>
</div> <!-- TTP LMS Container -->
