<?php
/**
 * The class that controls cron operations
 **/
if ( ! class_exists( 'UCF_Scheduler_Cron' ) ) {
	class UCF_Scheduler_Cron {
		/**
		 * Adds to the cron event on activation.
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function add_cron() {
			if ( ! wp_next_scheduled( 'ucf_scheduler_cron' ) ) {
				wp_schedule_event( time(), 'ucfscheduler', 'ucf_scheduler_cron' );			
			}
		}

		public static function delete_cron() {
			wp_clear_scheduled_hook( 'ucf_scheduler_cron' );
		}

		/**
		 * Checks for updates to be published
		 **/
		public static function init() {
			$now = date( 'Y-m-d H:i:s' );
			UCF_Scheduler_Log::write( 'Cron job ran at ' . $now  . '.' );

			$updates = self::check_for_updates();
			if ( $updates ) {
				self::update_posts( $updates );
			}
		}

		public static function check_for_updates() {
			$now = gmdate( 'Y-m-d H:i:s' );

			$args = array(
				'post_type'      => 'any',
				'posts_per_page' => -1,
				'post_status'    => 'update_scheduled',
				'meta_query'     => array(
					array(
						'key'       => 'ucf_scheduler_start_datetime',
						'value'     => $now,
						'compare'   => '<=',
						'type'      => 'DATETIME'
					)
				)
			);

			$updates = get_posts( $args );

			return $updates;
		}

		public static function update_posts( $posts ) {
			foreach( $posts as $post ) {
				$scheduler = new UCF_Schedule( $post->ID );
				$scheduler->update_original_post();
			}
		}

		/**
		 * Adds an `Every Five Minutes` schedule
		 * to WP_Cron
		 * @author Jim Barnes
		 * @since 1.0.0
		 * 
		 * @param $schedules Array | An array of existing schedules.
		 * @return Array | The modified array of schedules.
		 **/
		public static function register_interval( $schedules ) {
			$schedules['ucfscheduler'] = array(
				'interval' => 300,
				'display'  => 'UCF Scheduler'
			);

			return (array)$schedules;
		}
	}
}

?>
