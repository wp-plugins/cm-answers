<?php
include_once CMA_PATH . '/lib/models/AnswerThread.php';
include_once CMA_PATH . '/lib/controllers/BaseController.php';

class CMA
{

    public static function init()
    {
        CMA_AnswerThread::init();
        if (get_option('cma_afterActivation', 0) == 1) {
            add_action('admin_notices', array(get_class(), 'showProMessages'));
        }
        add_action('init', array('CMA_BaseController', 'bootstrap'));
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

}
?>
