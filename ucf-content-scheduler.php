<?php
/*
Plugin Name: UCF Content Scheduler
Description: Allows for the scheduling of content replacement.
Version: 1.0.0
Author: Jim Barnes
Tags: post, schedule
License: GPL3
*/
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'UCF_SCHEDULER__PLUGIN_FILE', __FILE__ );
define( 'UCF_SCHEDULER__PLUGIN_URL', plugins_url( basename( dirname( __FILE__ ) ) ) );
define( 'UCF_SCHEDULER__STATIC_URL', UCF_SCHEDULER__PLUGIN_URL . '/static' );
define( 'UCF_SCHEDULER__SCRIPT_URL', UCF_SCHEDULER__STATIC_URL . '/js' );

include_once 'includes/ucf-scheduler-options.php';
include_once 'includes/ucf-scheduler-statuses.php';
include_once 'includes/class-ucf-schedule.php';
include_once 'admin/ucf-scheduler-admin.php';
include_once 'admin/ucf-scheduler-metaboxes.php';
include_once 'admin/ucf-scheduler-ajax.php';

if ( ! function_exists( 'ucf_scheduler_plugin_activated' ) ) {
	/**
	 * Triggered when the plugin is activated.
	 * @author Jim Barnes
	 * @since 1.0.0
	 **/
	function ucf_scheduler_plugin_activated() {
		UCF_Scheduler_Options::add_options();
	}

	register_activation_hook( UCF_SCHEDULER__PLUGIN_FILE, 'ucf_scheduler_plugin_activated' );
}

if ( ! function_exists( 'ucf_scheduler_plugin_deactivated' ) ) {
	/**
	 * Triggered when the plugin is deactivated.
	 * @author Jim Barnes
	 * @since 1.0.0
	 **/
	function ucf_scheduler_plugin_deactivated() {
		UCF_Scheduler_Options::delete_options();
	}

	register_deactivation_hook( UCF_SCHEDULER__PLUGIN_FILE, 'ucf_scheduler_plugin_deactivated' );
}

if ( ! function_exists( 'ucf_scheduler_init' ) ) {
	/**
	 * Triggered when plugins are all loaded.
	 *
	 * Primary hooks and functions called should be
	 * called here.
	 * @author Jim Barnes
	 * @since 1.0.0
	 **/
	function ucf_scheduler_init() {
		// Initiate the Plugin Settings
		add_action( 'admin_init', array( 'UCF_Scheduler_Options', 'settings_init' ) );
		// Add the options page.
		add_action( 'admin_menu', array( 'UCF_Scheduler_Options', 'add_options_page' ) );
		// Add `scheduled` post_status
		add_action( 'init', array( 'UCF_Scheduler_Statuses', 'register_scheduled_post_status' ), 10, 0 );
		// Remove `update_scheduled` from `All` list
		add_action( 'posts_where', array( 'UCF_Scheduler_Admin', 'remove_scheduled_from_all' ), 10, 2 );
		// Add admin scripts
		add_action( 'admin_enqueue_scripts', array( 'UCF_Scheduler_Admin', 'enqueue_admin_assets' ), 10, 0 );
		// Add `Schedule` publish action button
		add_action( 'add_meta_boxes', array( 'UCF_Scheduler_Metaboxes', 'add_schedule_metabox' ), 10, 0 );
		// Add `schedule` admin action
		add_action( 'wp_ajax_create_update', array( 'UCF_Scheduler_Ajax', 'create_update_admin_action' ), 10, 0 );
		// Add `update_schedule` admin action
		add_action( 'wp_ajax_update_schedule', array( 'UCF_Scheduler_Ajax', 'update_schedule_admin_action' ), 10, 0 );
		// Add `update_original` admin action
		add_action( 'wp_ajax_update_now', array( 'UCF_Scheduler_Ajax', 'update_original_admin_action' ), 10, 0 );
	}

	add_action( 'plugins_loaded', 'ucf_scheduler_init', 10, 0 );
}
