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
    }
}