var bindEvents = function($) {
    var $createUpdateBtn = $('#ucf_scheduler_create_update'),
        $updateNowBtn = $('#ucf_scheduler_update_now');

    if ( $createUpdateBtn ) {
        $createUpdateBtn.click(createScheduledUpdate);
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
    var $ = jQuery,
        $spinner = $('.spinner');

    $spinner.css('visibility', 'hidden');

    if ( data.status === 'Success' && data.redirect_url ) {
        window.location = data.redirect_url;
    }
};

var updateLabels = function($) {
  var $post_status = $('#original_post_status'),
      post_status = $post_status.val(),
      $publishBtn = $('#publish');

  if (post_status === 'pending_scheduled' || post_status === 'update_scheduled') {
    $publishBtn.val('Update');
  }
};

if ( 'undefined' !== jQuery ) {
    jQuery(document).ready(function($) {
        bindEvents($);
        updateLabels($);
    });
}
