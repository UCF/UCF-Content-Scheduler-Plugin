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
						'pending_scheduled'
					);
				 }

				 return $where;
		}

		/**
		 * Hook that supports workflow by preventing scheduled
		 * posts from being published outside of the cron job.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * 
		 * @param $new_status string | The new status of the post
		 * @param $old_status string | The old status of the post
		 * @param $post WP_Post object | The post object.
		 **/
		public static function prevent_publish( $new_status, $old_status, $post ) {
			$statuses = array(
				'pending_scheduled',
				'update_scheduled'
			);

			if ( $new_status === $old_status && in_array( $new_status, $statuses ) ) {
				return;
			}

			if ( $old_status === 'pending_scheduled' && $new_status === 'update_scheduled' ) {
				$post->post_status = $new_status;
			} else if ( in_array( $old_status, $statuses ) && 'publish' === $new_status ) {
				remove_action( 'post_save', array( 'UCF_Scheduler_Metaboxes', 'save_meta_box' ) );
				$post->post_status = $old_status;
				wp_update_post( $post, true );
				add_action( 'post_save', array( 'UCF_Scheduler_Metaboxes', 'save_meta_box' ), 10, 1 );
			}
		}
    }
}
