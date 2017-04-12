<?php
/**
 * A custom class for handling the flow logic of updating posts.
 **/
if ( ! class_exists( 'UCF_Schedule' ) ) {
    class UCF_Schedule {
        public
            $original,
            $shadow;

        /**
         * Created the UCF_Schedule Object
         *
         * @author Jim Barnes
         * @since 1.0.0
         *
         * @param $post_id int | The $post_id
         * @return UCF_Schedule Object
         **/
        public function __construct( $post_id ) {
            $post = get_post( $post_id, ARRAY_A );

            if ( is_wp_error( $post ) ) {
                throw new Exception(
                    'You must provide a valid post_id'
                );
            } else {
                if ( $post['post_status'] === 'pending_scheduled'
                  || $post['post_status'] === 'update_scheduled' ) {

                    $this->shadow = $post;
                    $original_id = $post['post_parent'];
                    $this->original = get_post( $original_id, ARRAY_A );
                } else {
                    $this->original = $post;
                }
            }
        }

        /**
         * Creates the shadow copy of the original post.
         *
         * @author Jim Barnes
         * @since 1.0.0
         *
         * @return int | The ID of the new post.
         **/
        public function create_shadow_post() {
            $original_id = $this->original['ID'];
            unset( $this->original['ID'] );
            $this->original['post_parent'] = $original_id;
            $this->original['post_status'] = 'pending_scheduled';

            $retval = wp_insert_post( $this->original );

            $metadata = $this->format_metadata( $original_id );
            $this->update_metadata( $original_id, $metadata );

            return $retval;
        }

        /**
         * Updates the schedule of the post.
         * 
         * @author Jim Barnes
         * @since 1.0.0
         * 
         * @param $schedule Array | An array of the start and end dates and times
		 * @return int|WP_Error | Returns $post_id if update was successful, WP_Error if not.
         **/
        public function update_schedule( $schedule ) {
            $metadata = $this->format_schedule( $schedule );

			if ( $this->verify_unique_schedule( $metadata ) ) {
				$this->update_metadata( $this->shadow['ID'], $metadata );
				$this->shadow['post_status'] = 'update_scheduled';
				$retval = wp_update_post( $this->shadow );
			} else {
				$retval = new WP_Error(
					"not-unique-schedule",
					__( 'The provided schedule overlaps with an already scheduled update for this post.', 'ucf_scheduler' )
				);
			}

			return $retval;
        }

		/**
		 * Removes the schedule and reverts the post_status
		 * 
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public function remove_schedule() {
			$retval = $this->shadow['ID'];

			if ( $this->shadow['post_status'] === 'update_scheduled' ) {
				delete_post_meta( $this->shadow['ID'], 'ucf_scheduler_start_datetime' );
				delete_post_meta( $this->shadow['ID'], 'ucf_scheduler_end_datetime' );
				$this->shadow['post_status'] = 'pending_scheduled';
				$retval = wp_update_post( $this->shadow );
			}

			return $retval;
		}

        /**
         * Updates the original post with the content from the shadow
         * 
         * @author Jim Barnes
         * @since 1.0.0
         * 
         * @param $delete_update bool | If true, will delete the shadow post
         * @return int | The ID of the original post.
         **/
        public function update_original_post( $delete_update=False ) {
			$shadow_id = $this->shadow['ID'];
			$original = $this->original;

			$end_date = get_post_meta( $shadow_id, 'ucf_scheduler_end_datetime', True );

			if ( $end_date ) {
				$end_date = new DateTime( $end_date );
				$original['post_parent'] = $retval;
				$schedule = new UCF_Schedule( $original );
				$schedule->create_shadow_post();
				$start_date = array(
					'start_date' => $end_date->format( 'Y-m-d' ),
					'start_time' => $end_date->format( 'H:i:s' )
				);

				$schedule->update_schedule( $start_date );
			}

            $this->shadow['ID'] = $this->original['ID'];
            $this->shadow['post_parent'] = $this->original['post_parent'];
            $this->shadow['post_name'] = $this->original['post_name'];
            $this->shadow['post_status'] = $this->original['post_status'];

            $retval = wp_update_post( $this->shadow );

			if ( ! $end_date ) {
				wp_delete_post( $shadow_id );
			}

            return $retval;
        }

        /**
         * Formats the schedule information in DateTime objects.
         *
         * @author Jim Barnes
         * @since 1.0.0
         * 
         * @param $schedule Array | The array of the start and end dates and times
         * @return Array | Returns a start and end DateTime object (or null if start of end is not provided)
         **/
        private function format_schedule( $schedule ) {
            $start_date = $schedule['start_date'];
            $start_time = isset( $schedule['start_time'] ) ? $schedule['start_time'] : '00:00';

			$start_date_time = new DateTime( $start_date . ' ' . $start_time );

            $end_date = isset( $schedule['end_date'] ) ? $schedule['end_date'] : null;
            $end_time = isset( $schedule['end_time'] ) ? $schedule['end_time'] : '00:00';

            $retval = array(
                'ucf_scheduler_start_datetime' => $start_date_time->format( 'Y-m-d H:i:s' )
            );

            if ( $end_date ) {
				$end_date_time = new DateTime( $end_date . ' ' . $end_time );

                $retval['ucf_scheduler_end_datetime'] = $end_date_time->format( 'Y-m-d H:i:s' );
            } else {
				$retval['ucf_scheduler_end_datetime'] = null;
			}

            return $retval;
        }

        /**
         * Formats the post metadata
         * 
         * @author Jim Barnes
         * @since 1.0.0
         * 
         * @param $post_id int | The ID of the post
         * @return Array | The formatted metadata of the post.
         **/
        private function format_metadata( $post_id ) {
            $retval = array();

            $postmeta = get_post_meta( $post_id );

            foreach( $postmeta as $key => $val ) {
                if ( count( $val ) === 1 ) {
                    $retval[$key] = $val[0];
                } else {
                    $retval[$key] = $val;
                }
            }

            return $retval;
        }

        /**
         * Updates a post's metadata
         *
         * @author Jim Barnes
         * @since 1.0.0
         * 
         * @param $post_id int | The ID id of the post
         * @param $metadata Array | The metadata array
         **/
        private function update_metadata( $post_id, $metadata ) {
            foreach( $metadata as $key => $val ) {
                $unique = add_post_meta( $post_id, $key, $val, True );

                if ( ! $unique ) {
                    update_post_meta( $post_id, $key, $val );
                }
            }
        }

		private function verify_unique_schedule( $schedule ) {
			$post_parent = $this->original['ID'];

			$args = array(
				'post_type'      => $this->original['post_type'],
				'post_parent'    => $post_parent,
				'post_status'    => 'update_scheduled',
				'posts_per_page' => -1,
				'post__not_in'   => array( $this->shadow['ID'] ),
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => 'ucf_scheduler_start_datetime',
						'value'   => $schedule['ucf_scheduler_start_datetime'],
						'compare' => '<=',
						'type'    => 'DATETIME'
					),
					array(
						'key'     => 'ucf_scheduler_end_datetime',
						'value'   => $schedule['ucf_scheduler_end_datetime'],
						'compare' => '>=',
						'type'    => 'DATETIME'
					)
				)
			);

			$count = count( get_posts( $args ) );

			if ( $count > 0 ) {
				return false;
			}

			return true;
		}
    }
}
