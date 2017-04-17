<?php
/**
 * Scheduler Class
 * Allows for content changes to be scheduled
 **/
if ( ! class_exists( 'UCF_Scheduler' ) ) {
	class UCF_Scheduler_Statuses {
		/**
		 * Registers the post_status of `scheduled`
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function register_scheduled_post_status() {
			register_post_status( 'update_unscheduled', array(
				'label'                     => _x( 'Update Unschedule', 'post' ),
				'public'                    => false,
				'internal'                  => true,
				'private'                   => true,
				'exclude_from_search'       => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Unscheduled Update <span class="count">(%s)</span>', 'Unscheduled Updates <span class="count">(%s)</span>' )
			) );

			register_post_status( 'update_scheduled', array(
				'label'                     => _x( 'Update Scheduled', 'post' ),
				'public'                    => false,
				'internal'                  => true,
				'private'                   => true,
				'exclude_from_search'       => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Scheduled Update <span class="count">(%s)</span>', 'Scheduled Updates <span class="count">(%s)</span>' )
			) );
		}
	}
}

?>
