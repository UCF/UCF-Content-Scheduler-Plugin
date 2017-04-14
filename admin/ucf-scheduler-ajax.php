<?php
/**
 * Ajax actions
 **/
if ( ! class_exists( 'UCF_Scheduler_Ajax' ) ) {
    class UCF_Scheduler_Ajax {
        /**
		 * Handles creating the scheduled post and redirecting the user.
		 *
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function create_update_admin_action() {
			$post_id = (int) $_POST['post_id'];

			$schedule = new UCF_Schedule( $post_id );
            $shadow_id = $schedule->create_shadow_post();

			$params = array(
				'post'   => $shadow_id,
				'action' => 'edit'
			);

			$param_string = http_build_query( $params );

			$new_admin_url = admin_url( 'post.php?' . $param_string );

			$response = array(
				'status'       => 'Success',
				'redirect_url' => $new_admin_url
			);

			wp_send_json( $response );
		}

        /**
         * Handles migrating the update
         *
         * @author Jim Barnes
         * @since 1.0.0
         **/
        public static function update_original_admin_action() {
            $post_id = (int) $_POST['post_id'];

            $schedule = new UCF_Schedule( $post_id );
            $original_id = $schedule->update_original_post();

            $params = array(
                'post'    => $original_id,
                'action'  => 'edit'
            );

            $param_string = http_build_query( $params );

            $new_admin_url = admin_url( 'post.php?' . $param_string );

            $response = array(
                'status'       => 'Success',
                'redirect_url' => $new_admin_url
            );

            wp_send_json( $response );
        }
    }
}
