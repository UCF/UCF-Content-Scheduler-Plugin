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
			register_post_status( 'pending_scheduled', array(
				'label'                     => _x( 'Pending Update Schedule', 'post' ),
				'public'                    => false,
				'internal'                  => true,
				'private'                   => true,
				'exclude_from_search'       => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Updates Pending Scheduling <span class="count">(%s)</span>', 'Updates Pending Scheduling <span class="count">(%s)</span>' )
			) );

			register_post_status( 'update_scheduled', array(
				'label'                     => _x( 'Update Scheduled', 'post' ),
				'public'                    => false,
				'internal'                  => true,
				'private'                   => true,
				'exclude_from_search'       => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Update Scheduled <span class="count">(%s)</span>', 'Update Scheduled <span class="count">(%s)</span>' )
			) );
		}
	}
}
