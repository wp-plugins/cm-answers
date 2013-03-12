=== Plugin Name ===
Name: CM Answers
Contributors: CreativeMinds (http://www.cminds.com/)
Donate link: http://www.cminds.com/plugins
Tags: answers, forum, questions, comments, question and answer, forum, q&a, list, stackoverflow, splunkbase
Requires at least: 3.2
Tested up to: 3.5
Stable tag: 1.1

Allow users to post questions and answers (Q&A) in stackoverflow style

== Description ==

Free Question & Answer forum for WordPress that allows customization of the system look&feel.

* Includes views count

* Includes answers count

* Admin can moderate question & answers and can receive email notifications

* Users can receive notifications once answering a question on followup

* Sorting option in question and answer page

* Templet can be easily customized 

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
The template file used to display the 
Cm-Answers  is located in wp-content/plugins/cm-answers/views/frontend To modify it, do two things:

    Create a folder called CMA inside of your currently active theme’s directory.
    Copy All php files including folders to the new folder created in 1 in the same structure.

Once you have created the CMA directory and copied the files over, 
you can make any change you wish to the files and your changes will be reflected in the way cm-answers looks.

You may decide also to copy only part of the files, only if a file can be found in your template directory, then it will have a priority. Otherwise, the default from plugin directory will be used.

Remmeber for check when new versions are released for changes in the files stracture

== Screenshots ==

1. Setting page.
2. Setting notification section.
3. Answer Page.
4. Questions Page.

== Changelog ==
= 1.1 =
* Renamed main list from "Answers" to "Questions"
* fixed bug when sorting answers by votes didn't show answers without any votes (will work only for answers added after upgrade)
* Added validation for question (it's not possible to add empty one now)
* Minor fix in styling
* Added link to answers from admin menu

= 1.0 =
* Initial release

