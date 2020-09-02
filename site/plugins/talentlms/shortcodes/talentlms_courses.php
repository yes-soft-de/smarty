<?php if(!isset($_GET['tlms-course'])): ?>

    <fieldset>
        <legend><?php _e('Options', 'talentlms'); ?></legend>
        <label><?php _e('All categories', 'talentlms');?></label>
        <input type="checkbox" class="ef-category" value="all" checked="true">

		<?php foreach ($categories as $key => $category): ?>
            <label><?php echo $category->name;?></label>
            <input type="checkbox" class="ef-category" value="<?php echo $category->id; ?>">
		<?php endforeach ?>
    </fieldset>



    <table id="tlms_courses_table" >
        <thead>
        <tr>
            <th><?php _e('Image', 'talentlms'); ?></th>
            <th><?php _e('Course', 'talentlms'); ?></th>
            <th><?php _e('Description', 'talentlms'); ?></th>
            <th><?php _e('Price', 'talentlms'); ?></th>
            <th><?php _e('Created On', 'talentlms'); ?></th>
            <th><?php _e('Last Updated On', 'talentlms'); ?></th>
            <th style="display:none;"><?php _e('categories_ID', 'talentlms'); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($courses as $course) : ?>
            <tr>
                <td><img src="<?php echo $course->big_avatar; ?>"/></td>
                <td><a href="?tlms-course=<?php echo $course->id; ?>"><?php echo $course->name; echo ($course->course_code) ? "(".$course->course_code.")":''; ?></a></td>
                <td><?php echo $course->description; ?></td>
                <td><?php echo $course->price; ?></td>
                <td><?php echo date(tlms_getDateFormat(true), $course->creation_date); ?></td>
                <td><?php echo date(tlms_getDateFormat(true), $course->last_update_on); ?></td>
                <td style="display:none;"><?php echo $course->category_id; ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>

    <script>
        jQuery(function(){
            jQuery("#tlms_courses_table").dataTable({
                "order": [[ 0, "asc" ]] ,
                "columns": [
                    { "orderable": false },
                    null,
                    { "orderable": false },
                    null,
                    { "orderable": true },
                    { "orderable": true },
                    { "bSearchable": true }
                ]
            });
        });

        jQuery('.ef-category').click(function () {
            var id = jQuery(this).val();
            var courseTable = jQuery('#tlms_courses_table').DataTable();
            if(id=='all'){
                courseTable.search('').columns().search('').draw();
            }else{
                courseTable.column(3).search(id, true, true).draw();
            }
            jQuery(this).siblings('input:checkbox').not(this).removeAttr('checked');
        });

    </script>


<?php else: ?>

	<?php $course = tlms_getCourse($_GET['tlms-course']); ?>

    <div class="tlms-course-header">
        <img src="<?php echo $course['big_avatar']; ?>" alt="<?php echo $course['name']; ?>" />
        <h2><?php echo $course['name']; ?></h2>
    </div>

    <h3><?php _e('Price', 'talentlms');?>:</h3>
    <p><?php echo ($course['price']) ? $course['price'] : '-'; ?></p>

    <h3><?php _e('Description', 'talentlms');?>:</h3>
    <p><?php echo $course['description']; ?></p>

<!--    <h3>--><?php //_e('Content', 'talentlms');?><!--:</h3>-->
<!--    <ul>-->
<!--	    --><?php //foreach ($course['units'] as $unit): ?>
<!--            <li>-->
<!--                <i class="--><?php //echo tlms_getUnitIconClass($unit['type']); ?><!--"></i>-->
<!--                <span>--><?php //echo $unit['name'];?><!--</span>-->
<!--            </li>-->
<!--	    --><?php //endforeach; ?>
<!---->
<!--    </ul>-->


<?php endif; ?>
