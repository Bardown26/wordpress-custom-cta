/**
 * Created by andrewatieh on 7/26/15.
 */
jQuery(function($) {
    if ($('.attached-posts-wrap').length){

    }else {
        document.getElementsByClassName("attached-posts-wrap").innerHTML = "Paragraph changed!";
        $('.cmb-td').append('<div>You have not created any Calls-To-Action yet. <a href="/wp-admin/edit.php?post_type=custom_cta"> Create Some!</a></div>');
    }
});