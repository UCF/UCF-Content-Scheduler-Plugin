<?php
/**
 * General utility classes
 **/
if ( ! class_exists( 'UCF_Scheduler_Util' ) ) {
	class UCF_Scheduler_Util {
		/**
		 * Returns the current gmt_offset
		 *
		 **/
		public static function get_timezone() {
			if ( $timezone = get_option( 'timezone_string' ) ) {
				return new DateTimeZone( $timezone );
			}

			if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) {
				return new DateTimeZone( 'UTC' );
			}

			$utc_offset *= 3600;

			if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
				return new DateTimeZone( $timezone );
			}

			$is_dst = date( 'I' );

			foreach( timezone_abbreviations_list() as $abbr ) {
				foreach( $abbr as $city ) {
					if ( $city['dst'] === $is_dst && $city['offset'] === $utc_offset ) {
						return new DateTimeZone( $city['timezone_id'] );
					}
				}
			}

			return new DateTimeZone( 'UTC' );
		}
	}
}

?>
