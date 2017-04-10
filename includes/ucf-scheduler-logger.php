<?php
/**
 * Handles logging
 **/
if ( ! class_exists( 'UCF_Scheduler_Log' ) ) {
	class UCF_Scheduler_Log {
		/**
		 * Writes an entry to the error_log
		 * 
		 * @author Jim Barnes
		 * @since 1.0.0
		 * 
		 * @param $log string|Array | The log to write to the error log.
		 **/
		public static function write( $log ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}
