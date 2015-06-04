<?php

class CMA_Answer {
	
	protected $comment;
	
	
	public function __construct($comment) {
		$this->comment = (object)$comment;
	}
	
	
	public static function getById($commentId, $userId = null) {
		if ($comment = get_comment($commentId)) {
			if( empty($userId) OR $comment->user_id == $userId ) {
				return new self($comment);
			}
		}
	}
	
	
	public function getId() {
		return (isset($this->comment->comment_ID) ? $this->comment->comment_ID : null);
	}
	
	
	public function isApproved() {
		return ($this->comment->comment_approved == 1);
	}
	
	public function getThreadId() {
		return $this->comment->comment_post_ID;
	}
	
	public function getQuestionId() {
		return $this->getThreadId();
	}
	
	public function isVisible() {
    	return true;
    }
	
	
	public function getStatus() {
		return CMA::__($this->isApproved() ? 'approved' : 'pending');
	}
	
	public function getPermalink(array $query = array(), $backlink = false, $append = '') {
		$append .= '#answer-' . $this->getId();
		return $this->getThread()->getPermalink($query, $backlink, $append);
	}
	
	public function getPermalinkWithBacklink(array $query = array(), $append = '') {
		return $this->getPermalink($query, true, $append);
	}
	
	public function getAuthorId() {
		return $this->comment->user_id;
	}
	
	public function getCommentAuthor() {
		return $this->comment->comment_author;
	}
	
	public function getContent() {
		return $this->comment->comment_content;
	}
	
	public function getExcerpt() {
		return get_comment_excerpt($this->getId());
	}
	
	public function getDate() {
		return $this->comment->comment_date;
	}
	
	public function getCreationDate($format = '') {
		if( empty($format) )
		{
			$format = get_option('date_format') . ' ' . get_option('time_format');
		}
		return date_i18n($format, strtotime($this->getDate()));
	}
	
	
	public function getThread() {
		return CMA_AnswerThread::getInstance($this->getThreadId());
	}
	
	
	public function getQuestion() {
		return $this->getThread();
	}
	
}
