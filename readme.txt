=== Plugin Name ===
Name: CM Answers
Contributors: CreativeMinds (http://www.cminds.com/)
Donate link: http://www.cminds.com/plugins
Tags: answers, forum, questions, comments, question and answer, Question and Answer Forum, support forum, q&a, list, stackoverflow, stack overflow, stackoverflow answers, splunkbase, bbPress, board, boards, bulletin, bulletin board, bulletin boards, discussion, discussion board, discussion forums, discussions, simple forum, forum plugin, forums, message, message board, messages, messaging, user messages, threads, user forum, wordpress forum, wp, resolved topics, user rankings, post rating, rate, rating, customer service, customer support
Requires at least: 3.2
Tested up to: 3.5
Stable tag: 1.2

Allow users to post questions and answers (Q&A) in stackoverflow style with Multilingual/Localization Support

== Description ==

Free Question & Answer forum for WordPress that allows customization of the system look&feel.

Use-Cases
* Forum - Put a lightweight and easy to use Forum on your WordPress site/blog. 
* Support forum - Support your users while letting them vote and answer existing  topics
* Community - Add a forum to your site and allow your members to start their own conversations
* Questions & Answers - Users can answer questions sent in by other users. 
* Customer Support - Support customers questions
* StackOverflow - Add StackOverflow like forum to your site

Features
* Includes views count 
* Includes answers count
* Admin can moderate question & answers and can receive email notifications
* Users can receive notifications once answering a question on followup
* Sorting option in question and answer page
* Templet can be easily customized 
* We will be happy to add more language files submitted by WP community members, Currently we support: Spanish, German, Polish. 

**Demo**

* Basic demo [Read Only mode](http://www.cminds.com/answers/).


**More About this Plugin**
	
You can find more information about CM Answers at [CreativeMinds Website](http://www.cminds.com/plugins/).


**More Plugins by CreativeMinds**

* [CM Enhanced ToolTip Glossary](http://wordpress.org/extend/plugins/enhanced-tooltipglossary/) - Parses posts for defined glossary terms and adds links to the static glossary page containing the definition and a tooltip with the definition. 

* [CM Multi MailChimp List Manager](http://wordpress.org/extend/plugins/multi-mailchimp-list-manager/) - Allows users to subscribe/unsubscribe from multiple MailChimp lists. 

* [CM Invitation Codes](http://wordpress.org/extend/plugins/cm-invitation-codes/) - Allows more control over site registration by adding managed groups of invitation codes. 

* [CM Email Blacklist](http://wordpress.org/extend/plugins/cm-email-blacklist/) - Block users from blacklists domain from registering to your WordPress site.


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Manage your CM Download Manager from Left Side Admin Menu

Note: You must have a call to wp_head() in your template in order for the JS plugin files to work properly.  If your theme does not support this you will need to link to these files manually in your theme (not recommended).

== Frequently Asked Questions ==

= How can I customize look&feel? =
In your template create a directory "CMA". Inside you can place a structure similar to the one inside "cm-answers/views/frontend/". If the file can be found in your template directory, then it will have a priority. Otherwise, the default from plugin directory will be used.


== Screenshots ==

1. Setting page.
2. Setting notification section.
3. Answer Page.
4. Questions Page.


== Changelog ==
= 1.2 =
* Added localizations: Spanish, German and Polish

= 1.1 =
* Renamed main list from "Answers" to "Questions"
* fixed bug when sorting answers by votes didn't show answers without any votes (will work only for answers added after upgrade)
* Added validation for question (it's not possible to add empty one now)
* Minor fix in styling
* Added link to answers from admin menu

= 1.0 =
* Initial release

