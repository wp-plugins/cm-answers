<?php
include_once CMA_PATH . '/lib/models/PostType.php';

class CMA_AnswerThread extends CMA_PostType
{
    /**
     * Post type name
     */
    const POST_TYPE = 'cma_thread';
    /**
     * Rewrite slug
     */
    const REWRITE_SLUG = 'answers';
    const ADMIN_MENU = 'CMA_answers_menu';
    const OPTION_QUESTIONS_TITLE = 'cma_questions_title';
    const OPTION_QUESTION_AUTO_APPROVE = 'cma_question_auto_approve';
    const OPTION_ANSWER_AUTO_APPROVE = 'cma_answer_auto_approve';
    const OPTION_RATING_ALLOWED = 'cma_rating_allowed';
    const OPTION_NEGATIVE_RATING_ALLOWED = 'cma_negative_rating_allowed';
    const OPTION_VOTES_MODE = 'cma_votes_mode';
    const OPTION_SIDEBAR_ENABLED = 'cma_sidebar_enabled';
    const OPTION_SIDEBAR_MAX_WIDTH = 'cma_sidebar_max_width';
    const VOTES_MODE_COUNT = 1;
    const VOTES_MODE_HIGHEST = 2;
    const OPTION_NEW_QUESTION_NOTIFICATION = 'cma_new_question_notification';
    const OPTION_NEW_QUESTION_NOTIFICATION_TITLE = 'cma_new_question_notification_title';
    const OPTION_NEW_QUESTION_NOTIFICATION_CONTENT = 'cma_new_question_notification_content';
    const DEFAULT_NEW_QUESTION_NOTIFICATION_TITLE = '[[blogname]] A new question has been asked by [author]';
    const DEFAULT_NEW_QUESTION_NOTIFICATION_CONTENT = 'A new question has been asked by [author]:
Title: [question_title]
Approval status: [question_status]

Click to see: [question_link]';
    const OPTION_THREAD_NOTIFICATION = 'cma_thread_notification';
    const OPTION_THREAD_NOTIFICATION_TITLE = 'cma_thread_notification_title';
    const DEFAULT_THREAD_NOTIFICATION = 'Someone has posted a new answer on the topic you subscribed to

Topic: [question_title]
Click to see: [comment_link]';
    const DEFAULT_THREAD_NOTIFICATION_TITLE = '[[blogname]] Someone has posted a new answer on the topic you subscribed to';

    /**
     * @var CMA_AnswerThread[] singletones cache
     */
    protected static $instances = array();
    /**
     * @var array meta keys mapping
     */
    protected static $_meta = array(
        'lastPoster'         => '_last_poster',
        'views'              => '_views',
        'listeners'          => '_listeners',
        'resolved'           => '_resolved',
        'highestRatedAnswer' => '_highest_rated_answer',
        'votes'              => '_votes'
    );
    protected static $_commentMeta = array(
        'rating'     => '_rating',
        'usersRated' => '_users_rated'
    );

    /**
     * Initialize model
     */
    public static function init()
    {
        $post_type_args = array(
            'has_archive'  => TRUE,
//            'menu_position' => 4,
            'show_in_menu' => self::ADMIN_MENU,
            'rewrite'      => array(
                'slug'       => self::REWRITE_SLUG,
                'with_front' => FALSE,
            ),
            'supports'     => array('title', 'editor'),
            'hierarchical' => false
        );
        $plural = self::getQuestionsTitle();
        self::registerPostType(self::POST_TYPE, __('Question', 'cm-answers'), $plural, 'CM Answers', $post_type_args);

        add_filter('CMA_admin_parent_menu', create_function('$q', 'return "' . self::ADMIN_MENU . '";'));
        add_action('admin_menu', array(get_class(), 'registerAdminMenu'));

        /**
         * TODO: initiate widgets
         */
    }

    /**
     * @static
     * @param int $id
     * @return CMA_AnswerThread
     */
    public static function getInstance($id = 0)
    {
        if( !$id )
        {
            return NULL;
        }
        if( !isset(self::$instances[$id]) || !self::$instances[$id] instanceof self )
        {
            self::$instances[$id] = new self($id);
        }
        if( self::$instances[$id]->post->post_type != self::POST_TYPE )
        {
            return NULL;
        }
        return self::$instances[$id];
    }

    public static function getQuestionsTitle()
    {
        return get_option(self::OPTION_QUESTIONS_TITLE, 'Questions');
    }

    public static function registerAdminMenu()
    {
        $current_user = wp_get_current_user();

        if( user_can($current_user, 'manage_options') )
        {

            $page = add_menu_page(__('Questions', 'cm-answers'), 'CM Answers', 'edit_posts', self::ADMIN_MENU, create_function('$q', 'return;'));
            add_submenu_page(self::ADMIN_MENU, __('Answers', 'cm-answers'), __('Answers', 'cm-answers'), 'edit_posts', 'edit-comments.php?post_type=' . self::POST_TYPE);
            add_submenu_page(self::ADMIN_MENU, __('Add New', 'cm-answers'), __('Add New', 'cm-answers'), 'edit_posts', 'post-new.php?post_type=' . self::POST_TYPE);
        }
    }

    /**
     * Get content of answer
     * @return string
     */
    public function getContent()
    {
        return $this->post->post_content;
    }
    
    
	public function getLightContent() {
    	return self::lightContent($this->getContent());
    }
    
    
    public static function lightContent($content) {
    	return preg_replace('/[\s\n\r\t]+/', ' ', strip_tags($content));
    }

    /**
     * Set content of question
     * @param string $_description
     * @param bool $save Save immediately?
     * @return CMA_AnswerThread
     */
    public function seContent($_content, $save = false)
    {
        $this->post->post_content = nl2br($_description);
        if( $save ) $this->savePost();
        return $this;
    }

    /**
     * Set status
     * @param string $_status
     * @param bool $save Save immediately?
     * @return CMA_AnswerThread
     */
    public function setStatus($_status, $save = false)
    {
        $this->post->post_status = $_status;
        if( $save ) $this->savePost();
        return $this;
    }

    public function getStatus()
    {
        $status = $this->post->post_status;
        if( $status == 'draft' ) return __('pending', 'cm-answers');
        elseif( $status == 'publish' ) return __('approved', 'cm-answers');
    }

    /**
     * Get author ID
     * @return int Author ID
     */
    public function getAuthorId()
    {
        return $this->post->post_author;
    }

    /**
     * Get author
     * @return WP_User
     */
    public function getAuthor()
    {
        return get_userdata($this->getAuthorId());
    }

    /**
     * Set author
     * @param int $_author
     * @param bool $save Save immediately?
     * @return CMA_AnswerThread
     */
    public function setAuthor($_author, $save = false)
    {
        $this->post->post_author = $_author;
        if( $save ) $this->savePost();
        return $this;
    }

    public function getLastPoster()
    {
        $lastPoster = $this->getPostMeta(self::$_meta['lastPoster']);
        if( empty($lastPoster) ) $lastPoster = $this->getAuthorId();
        return $lastPoster;
    }

    public function getLastPosterName()
    {
        $userdata = get_userdata($this->getLastPoster());
        return $userdata->display_name;
    }

    public function setLastPoster($lastPoster)
    {
        $this->savePostMeta(array(self::$_meta['lastPoster'] => $lastPoster));
        return $this;
    }

    public function getViews()
    {
        return (int) $this->getPostMeta(self::$_meta['views']);
    }

    public function addView()
    {
        $views = $this->getViews();
        $this->savePostMeta(array(self::$_meta['views'] => $views + 1));
        return $this;
    }

    public function getTitle()
    {
        $title = parent::getTitle();
        if( $this->isResolved() ) $title = '[' . __('RESOLVED', 'cm-answers') . '] ' . $title;
        return $title;
    }

    public function getVotes()
    {
        if( self::getVotesMode() == self::VOTES_MODE_COUNT ) return (int) $this->getPostMeta(self::$_meta['votes']);
        else return $this->getHighestRatedAnswer();
    }

    public function addVote()
    {
        $votes = $this->getVotes();
        $this->savePostMeta(array(self::$_meta['votes'] => $votes + 1));
        $this->refreshHighestRatedAnswer();
        return $this;
    }

    public function getHighestRatedAnswer()
    {
        return (int) $this->getPostMeta(self::$_meta['highestRatedAnswer']);
    }

    public function refreshHighestRatedAnswer()
    {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT MAX(m.meta_value*1) FROM {$wpdb->commentmeta} m JOIN {$wpdb->comments} c ON c.comment_ID=m.comment_id AND m.meta_key='%s' AND c.comment_post_ID='%d'", self::$_commentMeta['rating'], $this->getId());
        $highest = (int) $wpdb->get_var($sql);
        $this->savePostMeta(array(self::$_meta['highestRatedAnswer'] => $highest));
        return $this;
    }

    public function isResolved()
    {
        return $this->getPostMeta(self::$_meta['resolved']) == 1;
    }

    public function setResolved($value = true)
    {
        $this->savePostMeta(array(self::$_meta['resolved'] => (int) $value));
        return $this;
    }

    public function getListeners()
    {
        return (array) $this->getPostMeta(self::$_meta['listeners']);
    }

    public function addListener($userId)
    {
        $listeners = $this->getListeners();
        $listeners[] = $userId;
        $listeners = array_unique($listeners);
        $this->savePostMeta(array(self::$_meta['listeners'] => $listeners));
        return $this;
    }

    public function getUnixUpdated($gmt = false)
    {
        return get_post_modified_time('G', $gmt, $this->getPost());
    }

    /**
     * Get when item was updated
     * @param string $format
     * @return string
     */
    public function getUpdated($format = '')
    {
        if( empty($format) ) $format = get_option('date_format');
        return date_i18n($format, strtotime($this->post->post_modified));
    }

    public function getCreationDate($format = '')
    {
        if( empty($format) ) $format = get_option('date_format') . ' ' . get_option('time_format');
        return date_i18n($format, strtotime($this->post->post_date));
    }

    public function setUpdated($date = null)
    {
        if( empty($date) ) $date = current_time('mysql');
        $this->post->post_modified = $date;
        $this->savePost();
        return $this;
    }

    public function getNumberOfAnswers()
    {
        $answers = get_comment_count($this->getId());
        if( $answers && is_array($answers) )
        {
            return $answers['approved'];
        }
        return 0;
    }

    public function getAnswers($sort = 'newest')
    {
        if( $sort == 'newest' )
        {
            $args = array(
                'post_id' => $this->getId(),
                'status'  => 'approve',
                'order'   => 'DESC',
//                 'fields'  => 'ids'
            );
            $rawComments = get_comments($args);
        }
        elseif( $sort == 'votes' )
        {
            global $wpdb;
            $sql = $wpdb->prepare("SELECT c.comment_ID
            	FROM {$wpdb->comments} c
            	LEFT JOIN {$wpdb->commentmeta} cm ON c.comment_ID=cm.comment_id AND cm.meta_key=%s
            	WHERE c.comment_post_ID=%d AND c.comment_approved
            	ORDER BY cm.meta_value*1 DESC",
            	self::$_commentMeta['rating'],
            	$this->getId()
            );
            $rawComments = $wpdb->get_results($sql);
        }
        $comments = array();
        if( !empty($rawComments) )
        {
            foreach($rawComments as $row)
            {
                $comments[] = $this->getCommentData($row->comment_ID);
            }
        }
        return $comments;
    }

    public function isEditAllowed($userId)
    {
        return (user_can($userId, 'manage_options') || $this->getAuthorId() == $userId);
    }

    public static function newThread($data = array())
    {
        if( self::isQuestionAutoApproved() ) $status = 'publish';
        else $status = 'draft';
        $title = trim(wp_kses($data['title'], array()));
        $content = trim(wp_kses($data['content'], array(
            'a'      => array(
                'href'  => array(),
                'title' => array()
            ),
            'em'     => array(),
            'strong' => array(),
            'b'      => array(),
            'pre'    => array()
        )));
        if( empty($title) ) $errors[] = __('Title cannot be empty', 'cm-answers');
        if( empty($content) ) $errors[] = __('Content cannot be empty', 'cm-answers');

        if( !empty($errors) )
        {
            throw new Exception(serialize($errors));
        }
        $id = wp_insert_post(array(
            'post_status'  => $status,
            'post_type'    => self::POST_TYPE,
            'post_title'   => $title,
            'post_content' => $content,
            'post_name'    => sanitize_title_with_dashes($title),
            'post_author'  => get_current_user_id(),
        ));
        if( $id instanceof WP_Error )
        {
            return $id->get_error_message();
        }
        else
        {
            $instance = self::getInstance($id);
            $instance->setUpdated()
                    ->setResolved(false)
                    ->setLastPoster(get_current_user_id());
            if( $data['notify'] == 1 ) $instance->addListener(get_current_user_id());
            $instance->savePostMeta(array(self::$_meta['votes'] => 0));
            $instance->savePostMeta(array(self::$_meta['highestRatedAnswer'] => 0));
            $instance->savePost();
            if( !self::isQuestionAutoApproved() ) $instance->notifyModerator();
            $instance->notifyAboutNewQuestion();
            do_action('cma_question_post_after', $instance);
            return $instance;
        }
    }

    public function notifyModerator()
    {
        $link = get_permalink($this->getId());
        $author = $this->getAuthor()->display_name;
        $email = $this->getAuthor()->user_email;
        $title = $this->getTitle();
        $content = $this->getContent();

        $approveLink = admin_url('edit.php?post_status=draft&post_type=' . self::POST_TYPE . '&cma-action=approve&cma-id=' . $this->getId());
        $trashLink = admin_url('edit.php?post_status=draft&post_type=' . self::POST_TYPE . '&cma-action=trash&cma-id=' . $this->getId());
        $pendingLink = admin_url('edit.php?post_status=draft&post_type=' . self::POST_TYPE);

        $emailTitle = '[' . get_bloginfo('name') . '] ' . __('Please moderate', 'cm-answers') . ' : "' . $title . '"';
        $emailContent = __("A new question has been asked and is waiting for your approval", 'cm-answers') . " {$link}

" . __('Author', 'cm-answers') . " : {$author}
" . __('E-mail', 'cm-answers') . " : {$email}
" . __('Title', 'cm-answers') . "  : {$title}
" . __('Content', 'cm-answers') . ":
{$content}


" . __('Approve it', 'cm-answers') . ": {$approveLink}
" . __('Trash it', 'cm-answers') . ": {$trashLink}
" . __('Please visit the questions moderation panel', 'cm-answers') . ":
{$pendingLink}
";
        @wp_mail(get_option('admin_email'), $emailTitle, $emailContent);
    }

    public function notifyAboutNewQuestion()
    {
        $receivers = self::getNewQuestionNotification(false);
        if( !empty($receivers) )
        {
            $author = $this->getAuthor()->display_name;
            $questionTitle = $this->getTitle();
            $questionLink = get_permalink($this->getId());
            $questionStatus = $this->getStatus();
            $blogname = get_bloginfo('name');
            $title = self::getNewQuestionNotificationTitle();
            $content = self::getNewQuestionNotificationContent();
            $title = str_replace('[blogname]', $blogname, $title);
            $title = str_replace('[author]', $author, $title);
            $title = str_replace('[question_title]', $questionTitle, $title);
            $title = str_replace('[question_status]', $questionStatus, $title);
            $title = str_replace('[question_link]', $questionLink, $title);
            $content = str_replace('[blogname]', $blogname, $content);
            $content = str_replace('[author]', $author, $content);
            $content = str_replace('[question_title]', $questionTitle, $content);
            $content = str_replace('[question_status]', $questionStatus, $content);
            $content = str_replace('[question_link]', $questionLink, $content);
            foreach($receivers as $receiver)
            {
                if( is_email($receiver) )
                {
                    @wp_mail($receiver, $title, $content);
                }
            }
        }
    }

    public function delete()
    {
        return wp_delete_post($this->getId(), true) !== false;
    }

    public function approve()
    {
        $this->setStatus('publish', true);
    }

    public function trash()
    {
        $this->setStatus('trash', true);
    }

    public static function getCommentData($comment_id)
    {
        $comment = get_comment($comment_id);
        $retVal = array(
            'id'         => $comment_id,
            'content'    => $comment->comment_content,
            'author'     => get_comment_author($comment_id),
            'date'       => get_comment_date(get_option('date_format') . ' ' . get_option('time_format'), $comment_id),
            'daysAgo'    => self::renderDaysAgo(get_comment_date('G', $comment_id)),
            'rating'     => (int) get_comment_meta($comment_id, self::$_commentMeta['rating'], true),
            'status'     => $comment->comment_approved == 1 ? 'approved' : 'pending',
            'questionId' => $comment->comment_post_ID
        );
        return $retVal;
    }

    public function addCommentToThread($content, $author_id, $notify = false, $resolved = false)
    {
        $user = get_userdata($author_id);
        $content = trim(wp_kses($content, array(
            'a'      => array(
                'href'  => array(),
                'title' => array()
            ),
            'em'     => array(),
            'strong' => array(),
            'b'      => array(),
            'pre'    => array()
        )));
        if( empty($content) ) $errors[] = __('Content cannot be empty', 'cm-answers');
        if( !empty($errors) )
        {
            throw new Exception(serialize($errors));
        }
        if( self::isAnswerAutoApproved() ) $approved = 1;
        else $approved = 0;

        $data = array(
            'comment_post_ID'      => $this->getId(),
            'comment_author'       => $user->display_name,
            'comment_author_email' => $user->user_email,
            'comment_author_IP'    => $_SERVER['REMOTE_ADDR'],
            'user_id'              => $author_id,
            'comment_parent'       => 0,
            'comment_content'      => apply_filters('comment_text', $content),
            'comment_approved'     => $approved,
            'comment_date'         => current_time('mysql')
        );
        $comment_id = wp_insert_comment($data);

        $this->updateThreadMetadata($comment_id, $author_id, $notify, $resolved);
        update_comment_meta($comment_id, self::$_commentMeta['rating'], 0);
        if( $approved !== 1 )
        {
            wp_notify_moderator($comment_id);
        }
        return $comment_id;
    }

    protected function _notifyOnFollow($lastCommentId)
    {
        $listeners = $this->getListeners();
        if( !empty($listeners) )
        {
            $message = get_option(self::OPTION_THREAD_NOTIFICATION, self::DEFAULT_THREAD_NOTIFICATION);
            $title = get_option(self::OPTION_THREAD_NOTIFICATION_TITLE, self::DEFAULT_THREAD_NOTIFICATION_TITLE);

            $postTitle = $this->getTitle();
            $commentLink = get_permalink($this->getId()) . '/#comment-' . $lastCommentId;
            $blogname = get_bloginfo('name');
            $title = str_replace('[blogname]', $blogname, $title);
            $title = str_replace('[question_title]', $postTitle, $title);
            $title = str_replace('[comment_link]', $commentLink, $title);
            $message = str_replace('[blogname]', $blogname, $message);
            $message = str_replace('[question_title]', $postTitle, $message);
            $message = str_replace('[comment_link]', $commentLink, $message);
            foreach($listeners as $user_id)
            {
                $user = get_userdata($user_id);
                if( !empty($user->user_email) )
                {
                    wp_mail($user->user_email, $title, $message);
                }
            }
        }
    }

    public function updateThreadMetadata($comment_id, $author_id, $notify, $resolved)
    {
        if( $notify )
        {
            $this->addListener($author_id);
        }
        $this->setResolved($resolved)
                ->setLastPoster($author_id)
                ->setUpdated()
                ->savePost();
        $this->_notifyOnFollow($comment_id);
    }

    public function getVoters($comment_id)
    {
        return (array) get_comment_meta($comment_id, self::$_commentMeta['usersRated'], true);
    }

    public function addVoter($comment_id, $user_id)
    {
        $voters = $this->getVoters($comment_id);
        $voters[] = $user_id;
        $voters = array_unique($voters);
        update_comment_meta($comment_id, self::$_commentMeta['usersRated'], $voters);
        return $this;
    }

    public function isVotingAllowed($comment_id, $user_id)
    {
        return !in_array($user_id, $this->getVoters($comment_id));
    }

    public function voteUp($comment_id)
    {
        $currentRating = (int) get_comment_meta($comment_id, self::$_commentMeta['rating'], true);
        update_comment_meta($comment_id, self::$_commentMeta['rating'], $currentRating + 1);
        $this->addVoter($comment_id, get_current_user_id())->addVote();
        return $currentRating + 1;
    }

    public function voteDown($comment_id)
    {
        $currentRating = (int) get_comment_meta($comment_id, self::$_commentMeta['rating'], true);
        update_comment_meta($comment_id, self::$_commentMeta['rating'], $currentRating - 1);

        $this->addVoter($comment_id, get_current_user_id())->addVote();
        return $currentRating - 1;
    }

    public function getUnixDate($gmt = false)
    {
        return get_post_time('G', $gmt, $this->getPost());
    }

    public static function renderDaysAgo($date, $gmt = false)
    {
        if( !is_numeric($date) ) $date = strtotime($date);
        $current = current_time('timestamp', $gmt);
        $seconds_ago = floor($current - $date);
        if( $seconds_ago < 0 ) return __('some time ago', 'cm-answers');
        else
        {
            if( $seconds_ago < 60 )
            {
                return sprintf(_n('1 second ago', '%d seconds ago', $seconds_ago, 'cm-answers'), $seconds_ago);
            }
            else
            {
                $minutes_ago = floor($seconds_ago / 60);
                if( $minutes_ago < 60 )
                {
                    return sprintf(_n('1 minute ago', '%d minutes ago', $minutes_ago, 'cm-answers'), $minutes_ago);
                }
                else
                {
                    $hours_ago = floor($minutes_ago / 60);
                    if( $hours_ago < 24 )
                    {
                        return sprintf(_n('1 hour ago', '%d hours ago', $hours_ago, 'cm-answers'), $hours_ago);
                    }
                    else
                    {
                        $days_ago = floor($hours_ago / 24);
                        if( $days_ago < 7 )
                        {
                            return sprintf(_n('1 day ago', '%d days ago', $days_ago, 'cm-answers'), $days_ago);
                        }
                        else
                        {
                            $weeks_ago = floor($days_ago / 7);
                            if( $weeks_ago < 4 )
                            {
                                return sprintf(_n('1 week ago', '%d weeks ago', $weeks_ago, 'cm-answers'), $weeks_ago);
                            }
                            else
                            {
                                $months_ago = floor($weeks_ago / 4);
                                if( $months_ago < 12 )
                                {
                                    return sprintf(_n('1 month ago', '%d months ago', $months_ago, 'cm-answers'), $months_ago);
                                }
                                else
                                {
                                    $years_ago = floor($months_ago / 12);
                                    return sprintf(_n('1 year ago', '%d years ago', $years_ago, 'cm-answers'), $years_ago);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function setQuestionAutoApproved($value = true)
    {
        update_option(self::OPTION_QUESTION_AUTO_APPROVE, (int) $value);
    }

    public static function setAnswerAutoApproved($value = true)
    {
        update_option(self::OPTION_ANSWER_AUTO_APPROVE, (int) $value);
    }

    public static function isQuestionAutoApproved()
    {
        return (bool) get_option(self::OPTION_QUESTION_AUTO_APPROVE);
    }

    public static function isAnswerAutoApproved()
    {
        return (bool) get_option(self::OPTION_ANSWER_AUTO_APPROVE);
    }

    public static function isRatingAllowed()
    {
        $allowed = get_option(self::OPTION_RATING_ALLOWED, 1);
        return (bool) $allowed;
    }

    public static function isNegativeRatingAllowed()
    {
        $allowed = get_option(self::OPTION_NEGATIVE_RATING_ALLOWED, 1);
        return (bool) $allowed;
    }

    public static function setRatingAllowed($value = true)
    {
        update_option(self::OPTION_RATING_ALLOWED, (int) $value);
    }

    public static function setNegativeRatingAllowed($value = true)
    {
        update_option(self::OPTION_NEGATIVE_RATING_ALLOWED, (int) $value);
    }

    public static function getVotesMode()
    {
        return get_option(self::OPTION_VOTES_MODE, self::VOTES_MODE_COUNT);
    }

    public static function setVotesMode($mode)
    {
        update_option(self::OPTION_VOTES_MODE, $mode);
    }

    public static function isSidebarEnabled()
    {
        $allowed = get_option(self::OPTION_SIDEBAR_ENABLED, 1);
        return (bool) $allowed;
    }

    public static function setSidebarEnabled($value = true)
    {
        update_option(self::OPTION_SIDEBAR_ENABLED, (int) $value);
    }

    public static function getSidebarMaxWidth()
    {
        $width = get_option(self::OPTION_SIDEBAR_MAX_WIDTH, 0);
        return (int) $width;
    }

    public static function setSidebarMaxWidth($value = 0)
    {
        update_option(self::OPTION_SIDEBAR_MAX_WIDTH, (int) $value);
    }

    public static function getNotificationTitle()
    {
        return get_option(self::OPTION_THREAD_NOTIFICATION_TITLE, self::DEFAULT_THREAD_NOTIFICATION_TITLE);
    }

    public static function getNewQuestionNotification($asString = true)
    {
        $receivers = get_option(self::OPTION_NEW_QUESTION_NOTIFICATION, array());
        if( $asString ) return implode(', ', $receivers);
        else return $receivers;
    }

    public static function setNewQuestionNotification($receivers)
    {
        if( !is_array($receivers) )
        {
            $receiversArr = explode(',', $receivers);
            array_walk($receiversArr, 'trim');
            $receivers = $receiversArr;
        }
        update_option(self::OPTION_NEW_QUESTION_NOTIFICATION, $receivers);
    }

    public static function getNotificationContent()
    {
        return get_option(self::OPTION_THREAD_NOTIFICATION, self::DEFAULT_THREAD_NOTIFICATION);
    }

    public static function setNotificationTitle($title)
    {
        update_option(self::OPTION_THREAD_NOTIFICATION_TITLE, $title);
    }

    public static function setNotificationContent($content)
    {
        update_option(self::OPTION_THREAD_NOTIFICATION, $content);
    }

    public static function getNewQuestionNotificationContent()
    {
        return get_option(self::OPTION_NEW_QUESTION_NOTIFICATION_CONTENT, self::DEFAULT_NEW_QUESTION_NOTIFICATION_CONTENT);
    }

    public static function getNewQuestionNotificationTitle()
    {
        return get_option(self::OPTION_NEW_QUESTION_NOTIFICATION_TITLE, self::DEFAULT_NEW_QUESTION_NOTIFICATION_TITLE);
    }

    public static function setNewQuestionNotificationTitle($title)
    {
        update_option(self::OPTION_NEW_QUESTION_NOTIFICATION_TITLE, $title);
    }

    public static function setNewQuestionNotificationContent($content)
    {
        update_option(self::OPTION_NEW_QUESTION_NOTIFICATION_CONTENT, $content);
    }

    public static function customOrder(WP_Query $query, $orderby)
    {
        switch($orderby)
        {
            case 'hottest':
                $query->set('orderby', 'modified');
                $query->set('order', 'DESC');
                break;
            case 'votes':
                if( self::getVotesMode() == self::VOTES_MODE_COUNT ) $query->set('meta_key', self::$_meta['votes']);
                else $query->set('meta_key', self::$_meta['highestRatedAnswer']);
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'views':
                $query->set('meta_key', self::$_meta['views']);
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'newest':
            default:
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
                break;
        }
        return $query;
    }

    public static function getQuestionsByUser($user_id, $limit = -1)
    {
        $args = array(
            'author'      => $user_id,
            'post_type'   => self::POST_TYPE,
            'post_status' => array('publish', 'draft'),
            'fields'      => 'ids',
            'orderby'     => 'date',
            'order'       => 'DESC'
        );
        $args['posts_per_page'] = $limit;
        $q = new WP_Query($args);
        $questions = array();
        foreach($q->get_posts() as $id)
        {
            $questions[] = self::getInstance($id);
        }
        return $questions;
    }

    public static function getAnswersByUser($user_id, $limit = -1)
    {
        $args = array(
            'user_id'   => $user_id,
            'post_type' => self::POST_TYPE,
        );
        if( $limit > 1 ) $args['number'] = $limit;
        $rawComments = get_comments($args);
        $comments = array();
        foreach($rawComments as $comment)
        {
            $comments[] = self::getCommentData($comment->comment_ID);
        }
        return $comments;
    }
    
    
    public function isVisible() {
    	return true;
    }
    
    
    public function getPermalink(array $query = array(), $backlink = false, $append = '') {
    	$result = get_permalink($this->getId()) . $append;
    	return add_query_arg($query, $result);
    }
    
	public function isPublished() {
    	return ($this->post->post_status == 'publish');
    }

}

class CMA_Thread extends CMA_AnswerThread {}
