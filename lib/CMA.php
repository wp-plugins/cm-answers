<?php
require_once CMA_PATH . "/licensing_api.php";
include_once CMA_PATH . '/lib/models/AnswerThread.php';
include_once CMA_PATH . '/lib/controllers/BaseController.php';

class CMA
{
	
	const TEXT_DOMAIN = 'cm-answers';

    public static function init()
    {
    	
    	$licensingApi = new CMA_free_Cminds_Licensing_API('CM Answers', CMA_AnswerThread::ADMIN_MENU, 'CM Answers', CMA_PLUGIN_FILE,
    		array('release-notes' => 'http://answers.cminds.com/release-notes/'), '', array('CM Answers'));
    	
        CMA_AnswerThread::init();
        CMA_BuddyPress::init();
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


    /**
     * Get localized string.
     *
     * @param string $msg
     * @return string
     */
    public static function __($msg)
    {
        return __($msg, self::TEXT_DOMAIN);
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
    

    public static function getReferer() {
    	global $wp_query;
    	
    	$isEditPage = function($url) { return false; };
    	$isTheSameHost = function($a, $b) {
    		return parse_url($a, PHP_URL_HOST) == parse_url($b, PHP_URL_HOST);
    	};
    	
    	$canUseReferer = (!empty($_SERVER['HTTP_REFERER'])
    			AND $isTheSameHost($_SERVER['HTTP_REFERER'], site_url())
    			AND !$isEditPage($_SERVER['HTTP_REFERER']));
    	$canUseCurrentPost = (is_single() AND !empty($wp_query->post) AND $wp_query->post->post_type == CMA_Thread::POST_TYPE
    			AND $isEditPage($_GET));
    	
    	if (!empty($_GET['backlink'])) { // GET backlink param
    		return base64_decode(urldecode($_GET['backlink']));
    	}
    	else if (!empty($_POST['backlink'])) { // POST backlink param
    		return $_POST['backlink'];
    	}
    	else if ($canUseReferer) { // HTTP referer
    		return $_SERVER['HTTP_REFERER'];
    	}
    	else if ($canUseCurrentPost) { // Question permalink
    		return get_permalink($wp_query->post->ID);
    	} else { // CMA index page
    		return get_post_type_archive_link(CMA_Thread::POST_TYPE);
    	}
    }
    
    
}