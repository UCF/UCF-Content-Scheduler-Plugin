var bindEvents = function($) {
    var $createUpdateBtn = $('#ucf_scheduler_create_update'),
        $updateScheduleBtn = $('#ucf_scheduler_update_schedule'),
        $updateNowBtn = $('#ucf_scheduler_update_now');

    if ( $createUpdateBtn ) {
        $createUpdateBtn.click(createScheduledUpdate);
    }

    if ( $updateScheduleBtn ) {
        $updateScheduleBtn.click(updateSchedule);
    }

    if ( $updateNowBtn ) {
        $updateNowBtn.click(updateImmediately);
    }
};

var createScheduledUpdate = function(event) {
    var $ = jQuery;

    event.preventDefault();

    var post_id   = $('#post_ID').val();

    var $posting = $.post(ajaxurl, {
        action: 'create_update',
        post_id: post_id
    });

    $posting.done(handleUpdatePostBack);
};

var updateSchedule = function(event) {
    var $ = jQuery;

    event.preventDefault();

    var post_id    = $('#post_ID').val(),
        start_date = $('#ucf_scheduler_start_date').val(),
        start_time = $('#ucf_scheduler_start_time').val(),
        end_date   = $('#ucf_scheduler_end_date').val(),
        end_time   = $('#ucf_scheduler_end_time').val();

    var $posting = $.post(ajaxurl, {
        action: 'update_schedule',
        post_id: post_id,
        start_date: start_date,
        start_time: start_time,
        end_date: end_date,
        end_time: end_time
    });

    $posting.done(handleUpdatePostBack);
};

var updateImmediately = function(event) {
    var $ = jQuery;

    event.preventDefault();

    var post_id = $('#post_ID').val();

    var $posting = $.post(ajaxurl, {
        action: 'update_now',
        post_id: post_id
    });

    $posting.done(handleUpdatePostBack);
};

var handleUpdatePostBack = function(data) {
    console.log(data);

    if ( data.status === 'Success' && data.redirect_url ) {
        window.location = data.redirect_url;
    }
};

if ( 'undefined' !== jQuery ) {
    jQuery(document).ready(function($) {
        bindEvents($);
    });
}