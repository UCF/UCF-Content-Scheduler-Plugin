<?php
/**
 * Administrative screens and actions
 **/
if ( ! class_exists( 'UCF_Scheduler_Admin' ) ) {
    class UCF_Scheduler_Admin {
        /**
		 * Enqueues the admin scripts
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function enqueue_admin_assets() {
			wp_enqueue_script( 'ucf_scheduler_script', UCF_SCHEDULER__SCRIPT_URL . '/ucf-scheduler-admin.js' );
		}

        /**
		 * Prevents `update_scheduled` posts from appearing in the 
		 * `All` list in the edit.php screen.
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 *
		 * @param $where string | The current `where` statement
		 * @param $q WP_Query | The current WP_Query object
		 * @return $where string | The modified `where` statement
		 **/
		public static function remove_scheduled_from_all( $where, $q ) {
			if ( is_admin()
				 && $q->is_main_query()
				 && ! filter_input( INPUT_GET, 'post_status' )
				 && ( $screen = get_current_screen() ) instanceof \WP_Screen
				 && 'edit' === $screen->base ) {
					 global $wpdb;

					$where .= $wpdb->prepare(
						" AND {$wpdb->posts}.post_status NOT IN ( '%s', '%s' ) ",
						'update_scheduled',
						'update_unscheduled'
					);
				 }

				 return $where;
		}

		/**
		 * Hook that supports workflow by preventing scheduled
		 * posts from being published outside of the cron job.
		 * Ref: 
		 * @author Jim Barnes
		 * @since 1.0.0
		 * 
		 * @param $data Array | An array of slashed post data
		 * @param $postarr Array | An array of sanitized, but otherwise unmodified post data
		 **/
		public static function status_workflow( $data, $postarr ) {
			
			$statuses = unserialize( UCF_SCHEDULER__STATUSES );
			$enabled_posttypes = array_keys( UCF_Scheduler_Options::get_option_or_default( 'enabled_post_types' ) );

			// Only follow this process if post type is enabled for publishing.
			if ( in_array( $data['post_type'], $enabled_posttypes ) ) {
				// Rules for existing posts
				if ( isset( $postarr['original_post_status'] ) ) {
					if ( in_array( $postarr['original_post_status'], $statuses ) ) {
						$data['post_status'] = UCF_Scheduler_Metaboxes::save_meta_box( $postarr );
					}

					if ( 'publish' === $data['post_status'] && in_array( $postarr['original_post_status'], $statuses ) ) {
						$data['post_status'] = $postarr['original_post_status'];
					}
				}
			}

			return $data;
		}

		/**
		 * Function that adds the `ucf_scheduler_release` column.
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 * 
		 * @param $columns Array | Array of available columns
		 * 
		 * @return Array | The modified array of columns.
		 **/
		public static function manage_columns( $columns ) {
			$post_status = isset( $_GET['post_status'] ) ? $_GET['post_status'] : null;

			$statuses = array(
				'update_unscheduled',
				'update_scheduled'
			);

			if ( ! ( $post_status && in_array( $post_status, $statuses ) ) ) {
				return $columns;
			}

			$new = array();

			foreach( $columns as $key => $val ) {
				$new[$key] = $val;

				if ( 'title' === $key ) {
					$new['ucf_scheduler_title'] = __( 'Update Title', 'ucf_scheduler' );
					$new['ucf_scheduler_release'] = __( 'Release Date', 'ucf_scheduler' );
					$new['ucf_scheduler_type'] = __( 'Update Type', 'ucf_scheduler' );
				}
			}

			return $new;
		}

		/**
		 * Function that adds additional meta to the columns in wp-admin.
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 * 
		 * @param $column string | The name of the column
		 * @param $post_id int | The post_id
		 **/
		public static function manage_posts_custom_column( $column, $post_id ) {
			$schedule = new UCF_Schedule( $post_id );
			$tz = UCF_Scheduler_Util::get_timezone();

			if ( 'ucf_scheduler_title' === $column ) {
				echo $schedule->update_title;
			}

			if ( 'ucf_scheduler_release' === $column ) {
				if ( $schedule->start_datetime ) {
					$date = $schedule->start_datetime;
					$date->setTimezone( $tz );
					echo $date->format( 'D, M j, Y - g:i a' );
				}
			}

			if ( 'ucf_scheduler_type' === $column ) {
				if ( $schedule->is_permanent() ) {
					echo 'Permament Update';
				} else {
					echo 'Temporary Update';
				}
			}
		}
    }
}

?>
