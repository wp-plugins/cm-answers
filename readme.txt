=== Plugin Name ===
Name: CM Answers
Contributors: CreativeMinds (http://www.cminds.com/)
Donate link: http://www.cminds.com/plugins
Tags:answers,forum,questions,comments,question and answer,Question and Answer Forum,support forum,q&a,list,stackoverflow,stack overflow,stackoverflow answers,splunkbase,bbPress,board,boards,bulletin,bulletin board,bulletin boards,discussion,discussion board,discussion forums,discussions,simple forum,forum plugin,forums,message,message board,messages,messaging,user messages,threads,user forum,wordpress forum,wp,resolved topics,user rankings,post rating,rate,rating,customer service,customer support,community,embed,embedded forum,discussion group,website forum,community,conversation,discussions,message,network,notification,social,thread,topic,group,groups,support
Requires at least: 3.2
Tested up to: 3.5
Stable tag: 1.9.3

Allow users to post questions and answers (Q&A) in a stackoverflow style forum which is easy to use, customize and install. w Social integration.

== Description ==

Free Question & Answer forum for WordPress that allows customization of the system look&feel.

**Use-Cases**

* Forum - Put a lightweight and easy to use Forum on your WordPress site/blog. 
* Support forum - Support your users while letting them vote and answer existing  topics
* Community - Add a forum to your site and allow your members to start their own conversations
* Questions & Answers - Users can answer questions sent in by other users.
* Customer Support - Support customers questions
* StackOverflow - Add StackOverflow like forum to your site

**Features**

* Includes views count 
* Includes answers count
* Admin can moderate question & answers and can receive email notifications
* Users can receive notifications once answering a question on followup
* Sorting option in question and answer page
* Templet can be easily customized 
* We will be happy to add more language files submitted by WP community members, Currently we support: Spanish, German, Polish, Russain (only regular version), French. 

**Demo & User Guide**

* Basic demo [Read Only mode](http://www.cminds.com/answers/).
* [User Guide](http://www.cminds.com/cm-answers-user-guide/)

**Pro Version**	

[Pro Version](http://www.cminds.com/downloads/cm-answers-pro/)
The Pro version adds a layer of powerful features to the CM Answers giving the admin better tools to customize the Answers system behavior, adding login support from social networks, adding shortcodes and support for categories and a lot more

* Ajax Support - Using shortcode to display Category now supports Ajax. View fits intself into current site templet [See example](http://jumpstartcto.com/cm-answers-ajax-example)
* Social Media Registration Integration - Integrates with Facebook &amp; Google+ &amp; LinkedIn [See Image](http://www.cminds.com/wp-content/uploads/edd/image1.png) [See another image](http://www.cminds.com/wp-content/uploads/edd/cm-answers-image2.png)
* Shortcodes - Generate questions list by using shortcode: cma-questions with additional parameters. For example limit=10 author=123 sort=hottest
* User Dashboard - Add "My Questions" and "My Answers" dashboards to user profile page by using shortcodes: cma-my-questions and cma-my-answers [See image](http://www.cminds.com/wp-content/uploads/edd/cm-answers-image3.png)
* Categories - Ability to add categories and display by using shortcode cat=catname
* Widgets -  Widget can display hottest questions, most viewed, recent and more [See image](http://www.cminds.com/wp-content/uploads/edd/cm-answers-image4.png)
* User Posting Meter - Ability to add near each user  number of questions and answers already posted [See image](http://www.cminds.com/wp-content/uploads/edd/cm-answers-image5.png)
* Show/Hide Views - Admin can hide or show number of views [See image](http://www.cminds.com/wp-content/uploads/edd/cm-answers-image6.png)
* Auto-approve questions and answers from users</strong>- Admin can define list of users which do not need moderation [See image](http://www.cminds.com/wp-content/uploads/edd/cm-answers-image7.png)
* Multisite - Supports multisite
* Gravatar - Ability to show Gravatar near user name and in user profile
* Order Answers - Show answers in ascending or descending order
* Attachment - Accept file attachment in question, limit by file type and size
* Localization Support - Forntend (user side) is localized
* View Count Control - Control how view count is done (by view or by session)
* Public User Profile - Automatically generate a public profile page containing the questions and answers user posted with link to his social media profile [See image](http://www.cminds.com/wp-content/uploads/edd/cm-answers-image8.png)
* Gratitude Message - Does not include Gratitude message in the footer.
* Tags - Tags are support. Admin can control the appearance of tags. Tags widget is also available
* Sticky Posts  -  Support sticky posts with admin defined background color
* Code Snippets Posts  - Support code snippets and background color
* Homepage - Support option to define cm answers as Site/Blog homepage

[Visit Pro Version Page](http://www.cminds.com/downloads/cm-answers-pro/)


**More About this Plugin**
	
You can find more information about CM Answers at [CreativeMinds Website](http://www.cminds.com/plugins/).


**More Plugins by CreativeMinds**

* [CM Super ToolTip Glossary](http://wordpress.org/extend/plugins/enhanced-tooltipglossary/) - Easily create Glossary, Encyclopedia or Dictionary of your terms and show tooltip in posts and pages while hovering. Many powerful features. 
* [CM Download manager](http://wordpress.org/extend/plugins/cm-download-manager) - Allow users to upload, manage, track and support documents or files in a directory listing structure for others to use and comment.
* [CM Answers](http://wordpress.org/extend/plugins/cm-answers/) - Allow users to post questions and answers (Q&A) in a stackoverflow style forum which is easy to use, customize and install. w Social integration.. 
* [CM Email Blacklist](http://wordpress.org/extend/plugins/cm-email-blacklist/) - Block users using blacklists domain from registering to your WordPress site.. 
* [CM Multi MailChimp List Manager](http://wordpress.org/extend/plugins/multi-mailchimp-list-manager/) - Allows users to subscribe/unsubscribe from multiple MailChimp lists. 


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Manage your CM Answers from Left Side Admin Menu

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
= 1.9.1 =
* CSS and style changes

= 1.8.3 =
* Added option to edit "Questions" listing title

= 1.8.2 =
* Fixed bug with not displaying last poster name for new threads
* Added option to disable sidebar or set its max-width

= 1.8.1 =
* Fixed bug with parsing error upon new thread/comment approval

= 1.8.0 =
* Added user guide

= 1.7.0 =
* Corrected daysAgo calculation, added hours/minutes/seconds
* Corrected translations

= 1.6.3 = 
* Added French language file


= 1.6.1 = 
* Fixed renderDaysAgo function
* Fixed pagination to work with permalink structure without trailing slash
* Fixed comment direct link

= 1.5.1 =
* Removed unused admin.js

= 1.4 =
* Datetimes are now formatted according to wordpress general settings
* Dates use date_i18n function to produce localized names
* Fixed escaping for notification titles and contents
* Fixed template

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

