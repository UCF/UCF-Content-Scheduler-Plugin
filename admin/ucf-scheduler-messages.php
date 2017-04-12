<?php
/**
 * Helper class for displaying error messages.
 **/

 if ( ! class_exists( 'UCF_Scheduler_Messages' ) ) {
	 class UCF_Scheduler_Messages {
		 
		/**
		 * Adds query variable when notice needs to be displayed.
		 * 
		 * @author Jim Barnes
		 * @since 1.0.0
		 * 
		 * @param $location string | The current location
		 * @return string | The updated location
		 **/
		public static function add_notice( $location ) {
			remove_filter( 'redirect_post_location', array( 'UCF_Scheduler_Message', 'add_notice' ), 99 );
			return add_query_arg( array( 'ucf_sch_msg' => 'ID' ), $location );
		}

		/**
		 * Displays an error for when a schedule is not unique.
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function admin_notices() {
			if ( ! isset( $_GET['ucf_sch_msg'] ) ) return;
		?>
			<div class="notice notice-error is-dismissible">
				<p>
					<?php _e( 'The schedule you\'ve specified overlaps with another scheduled update for this post.', 'ucf_scheduler' ); ?>
				</p>
			</div>
		<?php
		}
	 }
 }
