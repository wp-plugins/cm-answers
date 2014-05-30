<?php
require_once CMA_PATH . "/licensing_api.php";
include_once CMA_PATH . '/lib/models/AnswerThread.php';
include_once CMA_PATH . '/lib/controllers/BaseController.php';

class CMA
{

    public static function init()
    {
    	
    	$licensingApi = new CMA_free_Cminds_Licensing_API('CM Answers', CMA_AnswerThread::ADMIN_MENU, 'CM Answers', CMA_PLUGIN_FILE,
    		array('release-notes' => 'http://answers.cminds.com/release-notes/'), '', array('CM Answers'));
    	
        CMA_AnswerThread::init();
        if (get_option('cma_afterActivation', 0) == 1) {
            add_action('admin_notices', array(get_class(), 'showProMessages'));
        }
        add_action('init', array('CMA_BaseController', 'bootstrap'));

        add_filter('bp_blogs_record_comment_post_types', array(get_class(), 'bp_record_my_custom_post_type_comments'));
    }

    public static function install()
    {
        update_option('cma_afterActivation', 1);
    }

    public static function uninstall()
    {

    }

    public static function showProMessages()
    {

        // Only show to admins
        if (current_user_can('manage_options')) {
            ?>
            <div id="message" class="updated fade">
                <p><strong>New !! A Pro version of CM Answers is <a href="http://answers.cminds.com/"  target="_blank">available here</a></strong></p>
            </div><?php
            delete_option('cma_afterActivation');
        }
    }

    /**
     * BuddyPress record custom post type comments
     * @param array $post_types
     * @return string
     */
    public static function bp_record_my_custom_post_type_comments($post_types)
    {
        $post_types[] = CMA_AnswerThread::POST_TYPE;
        return $post_types;
    }
}