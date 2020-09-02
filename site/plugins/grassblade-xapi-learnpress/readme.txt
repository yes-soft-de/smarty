=== Experience API for LearnPress by GrassBlade ===
Contributors: liveaspankaj
Donate link: 
Tags: GrassBlade, xAPI, Experience API, Tin Can, LearnPress, Articulate, Storyline, Captivate, iSpring
Requires at least: 4.0
Tested up to: 5.4.2
Stable tag: trunk
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin enables the Experience API (xAPI / Tin Can), SCORM 1.2, SCORM 2004 and SCORM Dispatch on the LearnPress LMS by integrating with GrassBlade xAPI Companion plugin.

== Description ==

This plugin enables the Experience API (xAPI / Tin Can), SCORM 1.2, SCORM 2004 and SCORM Dispatch support on the [LearnPress LMS](https://thimpress.com/learnpress-lms-pricing/?ref=liveaspankaj&campaign=wordpress_plugin_page) by integrating with [GrassBlade xAPI Companion](https://www.nextsoftwaresolutions.com/grassblade-xapi-companion/) plugin. 

Which authoring tools are supported:

* H5P 
* Articulate Storyline
* Articulate Rise
* Articulate Studio
* Articulate 360
* Adobe Captivate
* Lectora Inspire
* Lectora Publisher
* Lectora Online
* iSpring Suite
* Adapt Authoring Tool
* iSpring Pro
* DominKnow Claro
* and more not listed here


Videos Supported with [advanced video tracking](https://www.nextsoftwaresolutions.com/kb/advanced-video-tracking/): 

* YouTube
* Vimeo
* MP4 (self hosted or URL)
* MP3 (self hosted or URL)


What do you need? 

1. [LearnPress LMS Plugin](https://thimpress.com/learnpress-lms-pricing/?ref=liveaspankaj&campaign=wordpress_plugin_page)
1. [GrassBlade xAPI Companion](https://www.nextsoftwaresolutions.com/grassblade-xapi-companion/) plugin
1. [GrassBlade Cloud LRS](https://www.nextsoftwaresolutions.com/grassblade-lrs-experience-api/) (or GrassBlade LRS)

The LRS, also known as the Learning Record Store, is optional if you are using content without any tracking. 

What features do you get with this integration?

* Upload and host your xAPI, SCORM 1.2, SCORM 2004 and SCORM Dispach packages on your site.
* You can host content from several authoring tools
* Restrict progress till xAPI Content is completed
* Completion of LearnPress Lessons based on xAPI Content
* Quiz based xAPI Content can be used for LearnPress Lesson completion (but NOT LearnPress Quizzes at the moment)
* Award Certificates based on completion of xAPI Content

What features are currently NOT supported by this integration?

* Completion of LearnPress Quiz based on xAPI Content is currently not supported


GrassBlade xAPI Companion works with:

* [LearnDash LMS](https://learndash.idevaffiliate.com/idevaffiliate.php?id=112&tid1=wordpress_plugin_page)
* [WP Courseware LMS](https://flyplugins.com/wp-courseware/?fly=707&campaign=wordpress_plugin_page)
* [LifterLMS](https://shareasale.com/r.cfm?b=1175128&u=982246&m=62106&urllink=&afftrack=wordpress_plugin_page)
* [LearnPress LMS](https://thimpress.com/learnpress-lms-pricing/?ref=liveaspankaj&campaign=wordpress_plugin_page)


== Installation ==

This section describes how to install the plugin and get it working.

1. Please make sure you have installed the other required plugins first as listed on the Details tab. 
1. Upload the plugin files to the `/wp-content/plugins/grassblade-xapi-learnpress` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Please follow the documentation of GrassBlade xAPI Companion for reset of the setup,

== Frequently Asked Questions ==

= What is GrassBlade xAPI Companion plugin?  =

[GrassBlade xAPI Companion](https://www.nextsoftwaresolutions.com/grassblade-xapi-companion/) is a paid WordPress plugin that enables support for Experience API (xAPI)  based content on WordPress. 

It also provides best in industry Advanced Video Tracking feature, that works with YouTube, Vimeo and self-hosted MP4 videos. Tracking of MP3 audios is also supported. 

It can be used independently without any LMS. However, to add advanced features, it also has integrations with several LMSes. 


= What is LearnPress LMS? =

LearnPress LMS is a free WordPress plugin which allows you to use Learning Management System features right on WordPress. It is very simple to use yet quite powerful and feature-rich.


= What is GrassBlade Cloud LRS? =

[GrassBlade Cloud LRS](https://www.nextsoftwaresolutions.com/grassblade-lrs-experience-api/) is a cloud-based Learning Record Store (LRS). An LRS is a required component in any xAPI-based ecosystem. It works as a data store of all eLearning data, as well as a reporting and analysis platform.  There is an installable version which can be installed on any PHP/MySQL based server. 


== Screenshots ==

1. Articulate content added using GrassBlade xAPI Companion
2. Quiz Report for Articulate Quiz on GrassBlade Cloud LRS 
3. YouTube Video added for Advanced Video Tracking
4. Video Tracking Heatmap on GrassBlade Cloud LRS
5. User Report on LearnPress LMS.
6. LearnPress LMS course page showing Lesson Completed by Articulate Content completion


== Changelog ==

= 2.4 = 
* Added Add-ons page

= 2.3 = 
* Fixed: Hide Button and Auto Redirect Completion Tracking options not working on LearnPress v3.2.6.9+ 

= 2.2 = 
* Fixed: Fatal error when lesson is not attached to any course

= 2.1 =
* Styling: Make disabled Continue button look grey
* Fixed: Fatal error when lesson is not attached to any course

= 2.0 = 
* Added support for Advanced Completion Behaviour
 
= 1.0. = 
* New plugin added
