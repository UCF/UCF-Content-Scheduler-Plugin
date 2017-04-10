<?php
/**
 * Handles the creation and management of metaboxes.
 **/
 if ( ! class_exists( 'UCF_Scheduler_Metaboxes' ) ) {
     class UCF_Scheduler_Metaboxes {
         /**
		 * Adds a `Schedule` metabox
		 * of configured custom post types.
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function add_schedule_metabox() {
			$enabled_post_types = array_keys( UCF_Scheduler_Options::get_option_or_default( 'ucf_scheduler_enabled_post_types' ) );

			add_meta_box(
				'ucf_scheduler_metabox',
				'Schedule Update',
				array( 'UCF_Scheduler_Metaboxes', 'schedule_metabox_markup' ),
				$enabled_post_types,
				'side',
				'high'
			);
		}

        /**
		 * Outputs the html needed for the schedule metabox
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function schedule_metabox_markup() {
			global $post;
			wp_nonce_field( 'ucf_scheduler_metabox', 'ucf_scheduler_metabox_nonce' );
			
            $start = get_post_meta( $post->ID, 'ucf_scheduler_start_datetime', true );
            $end = get_post_meta( $post->ID, 'ucf_scheduler_end_datetime', true );

            if ( $start ) {
				$start = new DateTime( $start );
                $start_date = $start->format( 'Y-m-d' );
                $start_time = $start->format( 'H:i' );
            }

            if ( $end ) {
				$end = new DateTime( $end );
                $end_date = $end->format( 'Y-m-d' );
                $end_time = $end->format( 'H:i' );
            }

            $scheduled_statuses = array(
                'pending_scheduled',
                'update_scheduled'
            );

            if ( in_array( $post->post_status, $scheduled_statuses ) ) :
        ?>
			<style> #duplicate-action, #delete-action, #minor-publishing-actions, #misc-publishing-actions, #preview-action {display:none;} </style>
            <fieldset>
                <div>
                    <label for="ucf_scheduler_start_date">Start Date and Time: </label>
                    <input type="date" id="ucf_scheduler_start_date" name="ucf_scheduler_start_date" value="<?php echo $start_date ? $start_date : ''; ?>">
                    <input type="time" id="ucf_scheduler_start_time" name="ucf_scheduler_start_time" value="<?php echo $start_time ? $start_time : ''; ?>">
                </div>
                <div>
                    <label for="ucf_scheduler_end_date">End Date and Time: </label>
                    <input type="date" id="ucf_scheduler_end_date" name="ucf_scheduler_end_date" value="<?php echo $end_date ? $end_date : ''; ?>">
                    <input type="time" id="ucf_scheduler_end_time" name="ucf_scheduler_end_time" value="<?php echo $end_time ? $end_time : ''; ?>">
                </div>
                <button id="ucf_scheduler_update_schedule" type="button" class="submitscheduler scheduler button button-large pull-right" style="margin: 8px;">Update Schedule</a>
                <?php $display = ( $post->post_status === 'update_scheduled' ) ? 'inline-block' : 'none'; ?>
                <button id="ucf_scheduler_update_now" type="button" class="submitscheduler scheduler button button-warning button-large pull-right" style="display: <?php echo $display; ?>; margin: 8px;">Update Immediately</a>
            </fieldset>
        <?php else : ?>
		    <button id="ucf_scheduler_create_update" type="button" class="submitscheduler scheduler button button-large pull-right" style="margin: 8px;">Create Scheduled Update</button>
        <?php endif;
		?>
			<div class="clear"></div>
		<?php
		}
     }
 }
