<?php
/**
 * Template for the Course Syllabus Displayed on individual course pages
 *
 * @author      LifterLMS
 * @package     LifterLMS/Templates
 * @since       1.0.0
 * @version     3.24.0
 */
defined( 'ABSPATH' ) || exit;
global $post;
$course   = new LLMS_Course( $post );
$sections = $course->get_sections();
?>

<div class="clear"></div>

<?php $request_uri = explode('/', $_SERVER['REQUEST_URI']);
	if ( in_array('prewelness', $request_uri, false) ): ?>
		<div class="llms-syllabus-wrapper">

			<?php if ( ! $sections ) : ?>

				<?php _e( 'This course does not have any sections.', 'lifterlms' ); ?>

			<?php else : ?>
				<ul class="nav nav-tabs mb-5" id="myTab" role="tablist">
						<?php foreach ( $sections as $section ) : ?>
								<?php if ( apply_filters( 'llms_display_outline_section_titles', true ) ) : ?>
									<li class="nav-item mx-auto">

                    <a class="nav-link" id="<?php echo get_the_title( $section->get( 'id' ) ); ?>-tab" data-toggle="tab" href="#<?php echo get_the_title( $section->get( 'id' ) ); ?>" role="tab" aria-controls="<?php echo get_the_title( $section->get( 'id' ) ); ?>" aria-selected="true">
	                    <?php
		                    $icon = get_the_title( $section->get( 'id' ) );
		                    switch ($icon) {
			                    case 'video' :
			                    	echo '<i class="fa fa-video-camera fa-fw"></i>';
			                    	break;
			                    case 'audio' :
				                    echo '<i class="fa fa-microphone fa-fw"></i>';
				                    break;
			                    case 'article' :
				                    echo '<i class="fa fa-file-text fa-fw"></i>';
				                    break;
			                    case 'about' :
				                    echo '<i class="fa fa-info-circle fa-fw"></i>';
				                    break;
		                    }
	                    ?>
	                    <?php echo get_the_title( $section->get( 'id' ) ); ?>
										</a>
									</li>
								<?php endif; ?>
						<?php endforeach; ?>
				</ul>
				<div class="tab-content" id="myTabContent">
			<?php
			 foreach ( $sections as $section ) :
						$lessons = $section->get_lessons(); ?>
					<?php if ( $lessons ) : ?>
				 <div class="tab-pane fade" id="<?php echo get_the_title( $section->get( 'id' ) ); ?>" role="tabpanel" aria-labelledby="<?php echo get_the_title( $section->get( 'id' ) ); ?>-tab">
						<?php foreach ( $lessons as $lesson ) : ?>


									<?php
										llms_get_template(
											'course/lesson-preview.php',
											array(
												'lesson'        => $lesson,
												'total_lessons' => count( $lessons ),
											)
										);
									?>




						<?php endforeach; ?>
				 </div>
					<?php else : ?>

						<?php _e( 'This section does not have any lessons.', 'lifterlms' ); ?>

					<?php endif; ?>

				<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="clear"></div>

		</div>

<?php else: ?>

		<div class="llms-syllabus-wrapper">

			<?php if ( ! $sections ) : ?>

				<?php _e( 'This course does not have any sections.', 'lifterlms' ); ?>

			<?php else : ?>

				<?php foreach ( $sections as $section ) : ?>

					<?php if ( apply_filters( 'llms_display_outline_section_titles', true ) ) : ?>
						<h3 class="llms-h3 llms-section-title"><?php echo get_the_title( $section->get( 'id' ) ); ?></h3>
					<?php endif; ?>

					<?php $lessons = $section->get_lessons(); ?>
					<?php if ( $lessons ) : ?>

						<?php foreach ( $lessons as $lesson ) : ?>

							<?php
							llms_get_template(
								'course/lesson-preview.php',
								array(
									'lesson'        => $lesson,
									'total_lessons' => count( $lessons ),
								)
							);
							?>

						<?php endforeach; ?>

					<?php else : ?>

						<?php _e( 'This section does not have any lessons.', 'lifterlms' ); ?>

					<?php endif; ?>

				<?php endforeach; ?>

			<?php endif; ?>

			<div class="clear"></div>

		</div>
<?php endif; ?>
