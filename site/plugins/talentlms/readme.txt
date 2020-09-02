=== TalentLMS WordPress plugin ===
Contributors: panagop, m13raptis, V., papagel75, simosnomikos
Tags: TalentLMS, elearning, lms, lcms, hcm, learning management system
Requires at least: 2.0
Tested up to: 5.4.1
Requires PHP: 5.2.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrates Talentlms with Wordpress. Promote your TalentLMS content through your WordPress site.

== Description ==

[TalentLMS](http://www.talentlms.com/ "TalentLMS super-easy, cloud-based learning platform") is a cloud-based, lean LMS with an emphasis on usability and easy course creation. With TalentLMS we wanted to create a better learning experience in every way that actually matters – and we are excited about this new offering. The product focuses on small but growing organizations. There are a number of obstacles that prohibit small organizations from using elearning. To be productive, small businesses need a number of tools and several related services such as setup and maintenance, course creation and the support of end-users. All these require ample time, resources and money. It comes as no surprise that most small organizations find elearning a non-viable pursuit and prefer on-job or informal training methods.

Read more about TalentLMS in:

* [TalentLMS - an Introduction](https://www.talentlms.com/blog/talentlms-an-introduction/ "TalentLMS - an Introduction")
* [TalentLMS - Get started in 5'](https://www.talentlms.com/blog/talentlms-get-started-in-5/ "TalentLMS - Get started in 5'")

## Plugin Features ##

* List your TalentLMS courses and their content in WordPress.
* Integrate your TalentLMS courses as WooCommerce products

== Installation ==

#To Install:#

1. Download TalentLMS WordPress plugin
1. Unzip the file into a folder on your hard drive
1. Upload `/talentlms/` folder to the `/wp-content/plugins/` folder on your site
1. Visit your WordPress _Administration -> Plugins_ and activate TalentLMS WordPress plugin

Alternatively you can automatically install TalentLMS WordPress plugin from the  WordPress Plugin Directory. 

#Usage:#

* Once you have activated the plugin, provide your TalentLMS Domain name and TalentLMS API key.
* You must update your permalinks to use "Custom Structure" or if your using WordPress 3.3 and above you can use the "Post name" option just as long as you have `/%postname%/` at the end of the url.
* Use the shortcodes:
	* `[talentlms-courses]` : to list your TalentLMS courses
* Use the widget:
	* Insert TalentLMS widget in any registered sidebar of your site.

== Frequently Asked Questions ==

If you have a question or any feedback you want to share send us an email at [support@talentlms.com](mailto:support@talentlms.com 'support')

== Screenshots ==
1. Course catalog `assets/screenshot-1.png`
2. Admin dashboard `assets/screenshot-2.png`
3. Integration pages `assets/screenshot-3.png`

== Changelog ==

= 6.6.9.2 =

* Fix double firstname whitespace sanitization

= 6.6.9.1 =

* Free courses upon order completion fix

= 6.6.9 =

* Payment gateway cancellation proccess fix

= 6.6.8 =

* Development functions prefixed.

= 6.6.7 =

* Metadata fix.

= 6.6.6 =

* A user should be able to purchase only one instance of a course.

= 6.6.5 =

* Drop plugin tables from the database when a user deletes the plugin

= 6.6.4 =

 * Upgrade script bugfix

= 6.6.3 =

* Assets versioning
* Upgrade script

= 6.6 =

* Replace all course pictures, to big avatar
* Course list, shows only active courses that are visible on catalog.
* Improve re-sync functionality.
* Improve users enrollment on courses.
* Improve thank you page in order details list, go to course page link addition.
* TalentLMS widget addition.
* Bugfix on woocommerce checkout page, when trying to create a customer account.
* Bugfix on plugin activation.

= 6.5 =

* Remove deprecated signup page
* Improve domain/api key validation

= 6.4 =

* Provision for WooCommerce customer reset password
* Course format fix
* Setting domain/api key issue

= 6.3 =

* Woocommerce integration handle non talentlms products
* Provision for WooCommerce customer change password
* Handle existing not talentlms customers
* Error logs

= 6.2 =

* Woocommerce integration handle non talentlms products
* Handling of deleted products


= 6.1 =

* Removed deprecated shortcode [talentlms-signup]

= 6.0 =

* New improved version
* Better TalentLMS WooCommerce integration
* Removed obsolete features

= 5.3.3 =

* Fixed WooCommerce error handling

= 5.3.2 =

* Fixed notices

= 5.3.1 =

* Fixed issue when unsetting woocommerce user integration

= 5.3.0 =

* Fixed synced content

= 5.2.9 =

* Fixed warning in integrations page

= 5.2.8 =

* Do not show hidden from catalog courses in [talentlms-courses] shortcode

= 5.2.7 =

* Shortcodes correctly appear in content

= 5.2.6 =

* Fixed WooCommerce integration user details

= 5.2.5 =

* Fixed WooCommerce integration selecting courses

= 5.2.4 =

* Fixed error handling

= 5.2.3 =

* Registering users bug fix

= 5.2.2 =

* Shortcodes correctly appear in content

= 5.2.1 =

* Bug fix in forgot credentials form

= 5.2 =

* WooCommerce assign user to course (compatibility issue)

= 5.1 =

* WooCommerce assign user to course

= 5.0 =

* Updated WooCommerce integration options

= 4.9 =

* Login integration option

= 4.8.2 =

* Bug fixes for WooCommerce integration

= 4.8 =

* Various bug fixes

= 4.7.1 =

* Login redirection issue

= 4.7 =

* Login fixes

= 4.6.3 =

* Synced users get fixed password, their login 
* Login redirects fixes (3)

= 4.6.2 =

* Login redirects fixes (2)

= 4.6.1 =

* Login redirects fixes

= 4.6 =

* Woocommerce integration, orders get autocompleted
* Login fixes

= 4.5.8 =

* Woocommerce integration, when admin marks order as complete user gets assigned to the course

= 4.5.7 =

* Lastest version of TalentLMS lib

= 4.5.6 =

* Fixed TalentLMS wp_login wp_logout for widget

= 4.5.5 =

* Fixed TalentLMS sync passwords issue
* Fixed TalentLMS wp_login wp_logout

= 4.5.4 =

* Updated CDNs for jquery boostrap datatables

= 4.5.3 =

* Course list does not show inactive courses

= 4.5.2 =

* Fixed issue about redirect to after TalentLMS logout

= 4.5.1 =

* Fixed issue about redirect to TalentLMS after login

= 4.5 =

* Fixed issue about TalentLMS user login (wordpress authentication)
* Signup with WooCommerce, also signs up with TalentLMS

= 4.4.6 =

* Fixed issue about empty custom fields

= 4.4.4 =

* WooCommerce integration login

= 4.4.4 =

* WooCommerce integration no active/hidden from catalog courses

= 4.4.3 =

* Fixed subscriber permissions issue  

= 4.4.2 =

* Minor bugfixes 

= 4.4.1 =

* Minor bugfixes 

= 4.4 =

* WooCommerce integration
* Minor bugfixes 

= 4.3 =

* When redirected (logged in) to TalentLMS, new option on logout from TaletLMS behavior

= 4.2.1 =

* Section unit types  

= 4.2 =

* New unit types integratd to plugin

= 4.0 =

* Various bug fixes
* Complete WordPress and TalentLMS signup integration
* Complete WordPress and TalentLMS login integration
* Redesigned course catalog and admin panel
* Subscriber panel in WordPress

= 3.18.1 =

* Avoid conflict with some themes

= 3.18 =

* Creating a TalentLMS user when a user signs up to WP full registration process

= 3.17 =

* Bugfix for option to create TalentLMS user when a user signs up to WP 

= 3.16 =

* Option to create TalentLMS user when a user signs up to WP
* WP subscriber/TalentLMS user, WP/TalentLMS Profile 

= 3.15 =

* jquery ui update

= 3.13 =

* New signup methods supported

= 3.12 =

* Edit TalentLMS CSS bug fixed

= 3.11 =

* Sync users bugs fixed
* Forgot login/pass bugs fixed

= 3.10 =

* New version of TalentLMSLib

= 3.9.1 =

* Signup issues fixed

= 3.9 =

* Forgot login/password checkboxes

= 3.8 =

* Forgot login/password bug fix

= 3.7 =

* More signup methods supported 

= 3.6 =

* Bugfix in custom fields 

= 3.5 =

* Bugfix in custom fields in signup page in case of multiple select custom fields

= 3.4 =

* Various typos fixed 

= 3.3 =

* Fixes issue with conflicts with other WP plugin due to query strings 

= 3.2 =

* New version of TalentLMS PHP library 

= 3.0 =

* TalentLMS users shortcode
* Sync TalentLMS content with WP 

= 2.2 =

* Building categories tree recursively. No longer depending to libxml PHP extension

= 2.1 =

* Plugin connects to TalentLMS domain map, if exists, instead of talentLMS domain
* Units in course, link to TalentLMS units (redirect without second login) 

= 2.0 =

* CSS additions

= 1.9.1 =

* When purchasing a course, does not redirect to PayPal

= 1.9 =

* Single course view, unit urls when user is logged in.

= 1.8 =

* Users page which lists instructors and instructor details
* Customizable template for users page
* Upon plugin installation courses, users and signup WordPress page with shortcodes are created.
* Error messages of each TalentLMS API call
* Clean CSS rules defined for each TalentLMS element
* New version of TalentLMS API PHP library

= 1.7 =

* Tree like representation of TalentLMS categories 

= 1.6 =

* Single course template options
* Courses list template options

= 1.5 =

* Course thumbnails static urls

= 1.4 =

* TalentLMS signup supports TalentLMS custom fields

= 1.3 = 

* Content to WordPress pages is inserted in its relative position to shortcodes.
* Show instructors' names in single course page
* Certification kai Certification duration removed from course page

= 1.2 =

* TalentLMS CSS editor in Wordpress Administration Panel
* TalentLMS Option for customizition of TalentLMS Plugins
	* Courses per page
	* Templates for course page
	* Action after signup
* Caching of TalentLMS data
* Login modal in single course page
* Users are prompted to buy TalentLMS course instead of just getting it

= 1.1 =

* Users can go to courses in TalentLMS

= 1.0 (Initial release) =

* Administration Panel for TalentLMS management
* Login to TalentLMS widget for Wordpress
* shortcode for signup page to TalentLMS
* shortcode for listing courses from TalentLMS

== Upgrade Notice ==

= 3.17.1 =

* Help tabs information

= 3.14 =

* Signup custom fields bug fix

= 3.1 =

* Various minor bugfixes

= 3.0 =

* Updated version of TalentLMS library
* Various minor/major bugfixes

= 2.0 =

* Users can buy categories

= 1.9.1 =

* When purchasing a course, does not redirect to PayPal fixed

= 1.8 =

Various security issues

= 1.6 =

Courses pagination does not include inactive or hidden from catalog courses

= 1.3 = 

Mishandling of inactive and not-for-catalog courses
Bottom pagination bug fix 

= 1.1 =

Login fixed
When logged in user first name/last name appear correctly
Course prices appear correctly
