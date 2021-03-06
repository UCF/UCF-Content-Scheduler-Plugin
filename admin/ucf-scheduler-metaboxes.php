<?php
/**
 * Handles the creation and management of metaboxes.
 **/
 if ( ! class_exists( 'UCF_Scheduler_Metaboxes' ) ) {
     class UCF_Scheduler_Metaboxes {
        /**
		 * Outputs the html needed for the schedule metabox
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function schedule_metabox_markup() {
			global $post;
			wp_nonce_field( 'ucf_scheduler_metabox', 'ucf_scheduler_metabox_nonce' );
			
			$tz = UCF_Scheduler_Util::get_timezone();
			$utc = new DateTimeZone( 'UTC' );

			$title = get_post_meta( $post->ID, 'ucf_scheduler_update_title', true );
            $start = get_post_meta( $post->ID, 'ucf_scheduler_start_datetime', true );
            $end = get_post_meta( $post->ID, 'ucf_scheduler_end_datetime', true );

            if ( $start ) {
				$start = new DateTime( $start, $utc );
				$start->setTimezone( $tz );
                $start_date = $start->format( 'Y-m-d' );
                $start_time = $start->format( 'H:i' );
            }

            if ( $end ) {
				$end = new DateTime( $end, $utc );
				$end->setTimezone( $tz );
                $end_date = $end->format( 'Y-m-d' );
                $end_time = $end->format( 'H:i' );
            }

            $scheduled_statuses = array(
                'update_unscheduled',
                'update_scheduled'
            );

            if ( in_array( $post->post_status, $scheduled_statuses ) ) :
        ?>
			<style> #duplicate-action, #delete-action, #minor-publishing-actions, #preview-action, .misc-pub-post-status, .misc-pub-visibility, .misc-pub-curtime {display:none;} </style>
			<div class="misc-pub-section ucf-scheduler-options">
				<fieldset>
					<div>
						<label for="ucf_scheduler_title">Update Title</label><br>
						<input type="text" id="ucf_scheduler_update_title" name="ucf_scheduler_update_title" value="<?php echo $title; ?>">
					</div>
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
					<button id="ucf_scheduler_update_now" type="button" class="submitscheduler scheduler button button-warning button-large pull-right" style="margin: 8px;">Publish Immediately</a>
				</fieldset>
			</div>
        <?php else : ?>
		    <button id="ucf_scheduler_create_update" type="button" class="submitscheduler scheduler button button-large pull-right" style="margin: 8px;">Create Scheduled Update</button>
        <?php endif;
		?>
			<div class="clear"></div>
		<?php
		}

		/**
		 * Saves post meta
		 * @author Jim Barnes
		 * @since 1.0.0
		 * 
		 * @param $postarr Array | The post array
		 * @return string|null | The post_status or null if nonce isn't set.
		 **/
		public static function save_meta_box( $postarr ) {
			$post_id = $postarr['ID'];
			$retval = null;

			if ( ! wp_verify_nonce( $postarr['ucf_scheduler_metabox_nonce'], 'ucf_scheduler_metabox' ) ) {
				return null;
			}

			$schedule_array = array();

			if ( isset( $postarr['ucf_scheduler_start_date'] ) && ( ! empty( $postarr['ucf_scheduler_start_date'] ) ) ) {
				$start_date = sanitize_text_field( $postarr['ucf_scheduler_start_date'] );
				$start_time = isset( $postarr['ucf_scheduler_start_time'] ) ? sanitize_text_field( $postarr['ucf_scheduler_start_time'] ) : '00:00';

				$schedule_array['start_date'] = $start_date;
				$schedule_array['start_time'] = $start_time;
			}

			if ( isset( $postarr['ucf_scheduler_end_date'] ) && ( ! empty( $postarr['ucf_scheduler_end_date'] ) ) ) {
				$end_date = sanitize_text_field( $postarr['ucf_scheduler_end_date'] );
				$end_time = isset( $postarr['ucf_scheduler_end_time'] ) ? sanitize_text_field( $postarr['ucf_scheduler_end_time'] ) : '00:00';

				$schedule_array['end_date'] = $end_date;
				$schedule_array['end_time'] = $end_time;
			}

			if ( isset( $postarr['ucf_scheduler_update_title'] ) && ( ! empty( $postarr['ucf_scheduler_update_title'] ) ) ) {
				update_post_meta( $post_id, 'ucf_scheduler_update_title', sanitize_text_field( $postarr['ucf_scheduler_update_title'] ) );
			}

			if ( ! empty( $schedule_array ) ) {
				$schedule = new UCF_Schedule( $post_id );
				$retval = $schedule->update_schedule( $schedule_array );
			} else {
				$schedule = new UCF_Schedule( $post_id );
				$schedule->remove_schedule();
				$retval = 'update_unscheduled';
			}

			return $retval;
		}
     }
 }

?>
