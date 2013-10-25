<?php
include_once CMA_PATH . '/lib/models/AnswerThread.php';
class CMA_AnswerController extends CMA_BaseController
{
    const OPTION_ADD_ANSWERS_MENU = 'cma_add_to_nav_menu';

    public static function initialize()
    {
        add_filter('template_include', array(get_class(), 'overrideTemplate'));
        add_filter('manage_edit-' . CMA_AnswerThread::POST_TYPE . '_columns', array(get_class(), 'registerAdminColumns'));
        add_filter('manage_' . CMA_AnswerThread::POST_TYPE . '_posts_custom_column', array(get_class(), 'adminColumnDisplay'), 10, 2);
        do_action('CMA_custom_post_type_nav', CMA_AnswerThread::POST_TYPE);
        add_filter('CMA_admin_settings', array(get_class(), 'addAdminSettings'));
        add_action('parse_query', array(get_class(), 'processStatusChange'));
        add_filter('wp_nav_menu_items', array(get_class(), 'addMenuItem'), 1, 1);
        add_action('pre_get_posts', array(get_class(), 'registerCustomOrder'), 1, 1);
        add_action('CMA_login_form', array(get_class(), 'showLoginForm'));

        register_sidebar(array(
            'id' => 'cm-answers-sidebar',
            'name' => __('CM Answers Sidebar', 'cm-answers'),
            'description' => __('This sidebar is shown on CM Answers pages', 'cm-answers')
        ));
    }

    public static function addMenuItem($items)
    {
        $link = self::_loadView('answer/meta/menu-item', array('listUrl' => self::addAnswersMenu() ? get_post_type_archive_link(CMA_AnswerThread::POST_TYPE) : null));
        return $items . $link;
    }

    public static function showLoginForm()
    {
        echo self::_loadView('answer/widget/login');
    }

    public static function processStatusChange()
    {
        if(is_admin() && get_query_var('post_type') == CMA_AnswerThread::POST_TYPE && isset($_REQUEST['cma-action']))
        {
            switch($_REQUEST['cma-action'])
            {
                case 'approve':
                    $id = $_REQUEST['cma-id'];
                    if(is_numeric($id))
                    {
                        $thread = CMA_AnswerThread::getInstance($id);
                        $thread->approve();
                        add_action('admin_notices', create_function('$q', 'echo "<div class=\"updated\"><p>' . addslashes(__('Question', 'cm-answers') . ': ' . sprintf(__('"%s" has been succesfully approved'), $thread->getTitle())) . '</p></div>";'));
                    }
                    break;
                case 'trash':
                    $id = $_REQUEST['cma-id'];
                    if(is_numeric($id))
                    {
                        $thread = CMA_AnswerThread::getInstance($id);
                        $thread->trash();
                        add_action('admin_notices', create_function('$q', 'echo "<div class=\"updated\"><p>' . addslashes(__('Question', 'cm-answers') . ': ' . sprintf(__('"%s" has been succesfully moved to trash'), $thread->getTitle())) . '</p></div>";'));
                    }
                    break;
            }
        }
    }

    public static function registerCustomOrder($query)
    {
        if($query->query_vars['post_type'] == CMA_AnswerThread::POST_TYPE && $query->query_vars['widget'] !== true && !$query->is_single && !$query->is_404 && !$query->is_author && isset($_GET['sort']))
        {
            $query         = CMA_AnswerThread::customOrder($query, $_GET['sort']);
            $query->is_top = true;
        }
    }

    public static function overrideTemplate($template)
    {
        if(get_query_var('post_type') == CMA_AnswerThread::POST_TYPE)
        {
            if(self::_isPost()) self::processQueryVars();
            if(is_single() || is_404())
            {
                wp_enqueue_script('jquery');
                wp_enqueue_script('jquery-toast', CMA_URL . '/views/resources/toast/js/jquery.toastmessage.js', array('jquery'));
                wp_enqueue_style('jquery-toast-css', CMA_URL . '/views/resources/toast/resources/css/jquery.toastmessage.css');
                $template = self::locateTemplate(array(
                            'answer/single'
                                ), $template);
                if(!self::_isPost())
                {
                    self::_processViews();
                }
            }
            else
            {
                $template = self::locateTemplate(array(
                            'answer/index'
                                ), $template);
            }
            add_filter('body_class', array(get_class(), 'adjustBodyClass'), 20, 2);
        }
        return $template;
    }

    protected static function _processViews()
    {
        global $wp_query;
        $post   = $wp_query->post;
        $thread = CMA_AnswerThread::getInstance($post->ID);
        if($thread)
        {
        $thread->addView();
    }
        else
        {
            global $wp_query;
            $wp_query->is_404    = true;
            $wp_query->is_single = false;
            $wp_query->is_page   = false;

            $template = get_query_template('404');
            if(!empty($template)) include( $template );
            die();
        }
    }

    protected static function _processAddCommentToThread()
    {
        global $wp_query;
        $post      = $wp_query->post;
        $thread    = CMA_AnswerThread::getInstance($post->ID);
        $content   = $_POST['content'];
        $notify    = (bool) $_POST['thread_notify'];
        $resolved  = (bool) $_POST['thread_resolved'];
        $author_id = get_current_user_id();
        $error     = false;
        $messages  = array();
        try
        {
            $comment_id = $thread->addCommentToThread($content, $author_id, $notify, $resolved);
        }
        catch(Exception $e)
        {
            $messages = unserialize($e->getMessage());
            $error    = true;
        }
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        {

            header('Content-type: application/json');
            echo json_encode(array('success' => (int) (!$error), 'comment_id' => $comment_id, 'commentData' => CMA_AnswerThread::getCommentData($comment_id), 'message' => $messages));
            exit;
        }
        else
        {
            if($error)
            {
                foreach((array) $messages as $message)
                {
                    self::_addMessage(self::MESSAGE_ERROR, $message);
                }
            }
            else
            {
                $autoApprove = CMA_AnswerThread::isAnswerAutoApproved();
                if($autoApprove)
                {
                    $msg = __('Your answer has been succesfully added.', 'cm-answers');
                    self::_addMessage(self::MESSAGE_SUCCESS, $msg);
                    wp_redirect(get_permalink($post->ID) . '/#comment-' . $comment_id, 303);
                }
                else
                {
                    $msg = __('Thank you for your answer, it has been held for moderation.', 'cm-answers');
                    self::_addMessage(self::MESSAGE_SUCCESS, $msg);
                    wp_redirect(get_permalink($post->ID), 303);
                }
                exit;
            }
        }
    }

    protected static function _processAddThread()
    {
        global $wp_query;
        $post      = $wp_query->post;
        $title     = $_POST['thread_title'];
        $content   = $_POST['thread_comment'];
        $notify    = (bool) $_POST['thread_notify'];
        $author_id = get_current_user_id();
        $error     = false;
        $messages  = array();
        $data      = array(
            'title' => $title,
            'content' => $content,
            'notify' => $notify,
            'author_id' => $author_id
        );
        try
        {
            $thread    = CMA_AnswerThread::newThread($data);
            $thread_id = $thread->getId();
        }
        catch(Exception $e)
        {
            $messages = unserialize($e->getMessage());
            $error    = true;
        }
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        {

            header('Content-type: application/json');
            echo json_encode(array('success' => (int) (!$error), 'thread_id' => $thread_id, 'message' => $messages));
            exit;
        }
        else
        {
            if($error)
            {
                foreach((array) $messages as $message)
                {
                    self::_addMessage(self::MESSAGE_ERROR, $message);
                }
                wp_redirect(get_post_type_archive_link(CMA_AnswerThread::POST_TYPE), 303);
            }
            else
            {
                $autoApprove = CMA_AnswerThread::isQuestionAutoApproved();
                if($autoApprove)
                {
                    $msg = __('New question has been succesfully added.', 'cm-answers');
                    self::_addMessage(self::MESSAGE_SUCCESS, $msg);
                }
                else
                {
                    $msg = __('Thank you for your question, it has been held for moderation.', 'cm-answers');
                    self::_addMessage(self::MESSAGE_SUCCESS, $msg);
                }
                wp_redirect(get_post_type_archive_link(CMA_AnswerThread::POST_TYPE), 303);
            }

            exit;
        }
    }

    protected static function _processVote()
    {
        if(is_single())
        {
            global $wp_query;
            $post = $wp_query->post;
            if(!empty($post))
            {
                $thread  = CMA_AnswerThread::getInstance($post->ID);
                $comment = self::_getParam('cma-comment');
                if(!empty($comment))
                {
                    $response = array('success' => 0, 'message' => __('There was an error while processing your vote', 'cm-answers'));
                    $votes    = 0;
                    if(!is_user_logged_in())
                    {
                        $response['success'] = 0;
                        $response['message'] = __('You have to be logged-in to vote', 'cm-answers');
                    }
                    else
                    if($thread->isVotingAllowed($comment, get_current_user_id()))
                    {
                        $response['success'] = 1;
                        if(self::_getParam('cma-value') == 'up')
                        {
                            $response['message'] = $thread->voteUp($comment);
                        }
                        else $response['message'] = $thread->voteDown($comment);
                    } else
                    {
                        $response['message'] = __('You have already voted for this comment', 'cm-answers');
                    }
                    header('Content-type: application/json');
                    echo json_encode($response);
                    exit;
                }
            }
        }
    }

    public static function processQueryVars()
    {
        $action = self::_getParam('cma-action');
        if(!empty($action))
        {
            switch($action)
            {
                case 'add':
                    if(is_single()) self::_processAddCommentToThread();
                    else self::_processAddThread();
                    break;
                case 'vote':
                    self::_processVote();
                    break;
            }
        }
    }

    public static function adjustBodyClass($wp_classes, $extra_classes)
    {
        foreach($wp_classes as $key => $value)
        {
            if($value == 'singular') unset($wp_classes[$key]);
        }
        if(!CMA_AnswerThread::isSidebarEnabled() || !is_active_sidebar('cm-answers-sidebar')) $extra_classes[] = 'full-width';
        return array_merge($wp_classes, (array) $extra_classes);
    }

    public static function registerAdminColumns($columns)
    {
        $columns['author']   = __('Author', 'cm-answers');
        $columns['views']    = __('Views', 'cm-answers');
        $columns['status']   = __('Status', 'cm-answers');
        $columns['comments'] = __('Answers', 'cm-answers');
        return $columns;
    }

    public static function adminColumnDisplay($columnName, $id)
    {
        $thread = CMA_AnswerThread::getInstance($id);
        if(!$thread) return;
        switch($columnName)
        {
            case 'author':
                echo $thread->getAuthor()->display_name;
                break;
            case 'views':
                echo $thread->getViews();
                break;
            case 'status':
                echo $thread->getStatus();
                if(strtolower($thread->getStatus()) == strtolower(__('pending', 'cm-answers')))
                {
                    ?>
                    <a href="<?php echo add_query_arg(array('cma-action' => 'approve', 'cma-id' => $id)); ?>">(<?php _e('Approve', 'cm-answers'); ?>)</a>
                    <?php
                }
                break;
        }
    }

    public static function addAdminSettings($params = array())
    {
        if(self::_isPost())
        {
            CMA_AnswerThread::setQuestionAutoApproved(isset($_POST['questions_auto_approve']) && $_POST['questions_auto_approve'] == 1);
            CMA_AnswerThread::setAnswerAutoApproved(isset($_POST['answers_auto_approve']) && $_POST['answers_auto_approve'] == 1);
            CMA_AnswerThread::setRatingAllowed(isset($_POST['ratings']) && $_POST['ratings'] == 1);
            CMA_AnswerThread::setNegativeRatingAllowed(isset($_POST['negative_ratings']) && $_POST['negative_ratings'] == 1);
            CMA_AnswerThread::setNewQuestionNotification(stripslashes($_POST['notification_new_questions']));
            CMA_AnswerThread::setNewQuestionNotificationTitle(stripslashes($_POST['new_question_notification_title']));
            CMA_AnswerThread::setNewQuestionNotificationContent($_POST['new_question_notification_content']);
            CMA_AnswerThread::setNotificationTitle(stripslashes($_POST['notification_title']));
            CMA_AnswerThread::setNotificationContent(stripslashes($_POST['notification_content']));
            CMA_AnswerThread::setVotesMode((int) $_POST['votes_mode']);
            CMA_AnswerThread::setSidebarEnabled(isset($_POST['sidebar_enable']) && $_POST['sidebar_enable'] == 1);
            CMA_AnswerThread::setSidebarMaxWidth((int) $_POST['sidebar_max_width']);
            if(!empty($_POST['questions_title']))
            {
                update_option(CMA_AnswerThread::OPTION_QUESTIONS_TITLE, $_POST['questions_title']);
            }
            self::setAnswersMenu(isset($_POST['add_menu']) && $_POST['add_menu'] == 1);
        }
        $params['ratings']                        = CMA_AnswerThread::isRatingAllowed();
        $params['negativeRatings']                = CMA_AnswerThread::isNegativeRatingAllowed();
        $params['questionAutoApproved']           = CMA_AnswerThread::isQuestionAutoApproved();
        $params['answerAutoApproved']             = CMA_AnswerThread::isAnswerAutoApproved();
        $params['notificationNewQuestions']       = CMA_AnswerThread::getNewQuestionNotification();
        $params['newQuestionNotificationTitle']   = CMA_AnswerThread::getNewQuestionNotificationTitle();
        $params['newQuestionNotificationContent'] = CMA_AnswerThread::getNewQuestionNotificationContent();
        $params['notificationTitle']              = CMA_AnswerThread::getNotificationTitle();
        $params['notificationContent']            = CMA_AnswerThread::getNotificationContent();
        $params['votesMode']                      = CMA_AnswerThread::getVotesMode();
        $params['sidebarEnable']                  = CMA_AnswerThread::isSidebarEnabled();
        $params['sidebarMaxWidth']                = CMA_AnswerThread::getSidebarMaxWidth();
        $params['addMenu']                        = self::addAnswersMenu();
        $params['questions_title']                = CMA_AnswerThread::getQuestionsTitle();
        return $params;
    }

    public static function setAnswersMenu($value = false)
    {
        update_option(self::OPTION_ADD_ANSWERS_MENU, $value);
    }

    public static function addAnswersMenu()
    {
        return (bool) get_option(self::OPTION_ADD_ANSWERS_MENU);
    }

}
?>
