<?php
include_once CMA_PATH.'/lib/models/AnswerThread.php';
include_once CMA_PATH . '/lib/controllers/BaseController.php';
class CMA {
        public static function init() {
            CMA_AnswerThread::init();
        add_action('init', array('CMA_BaseController', 'bootstrap'));
    }
    public static function install() {

    }
    public static function uninstall() {
        
    }
}

?>
