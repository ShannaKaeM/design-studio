=== SureMembers ===
Contributors: brainstormforce
Requires at least: 5.9
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.10.9
License: GPL-2.0-only
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple yet powerful way to add content restriction to your website.

== Description ==

SureMembers is an integration based content restriction solution. SureMembers easily integrated with SureCart, SureTrigger and provide simple yet powerful restriction mechanism.

== Changelog ==

= 1.10.9 - May 28, 2025 =
* Fix: PHP error while accessing filter_content_meta_values() with 1 passed parameter, in case of checking access group's drip content.

= 1.10.8 - May 27, 2025 =
* Improvement: SureDash compatibility added for spaces, groups, courses restrictions.
* Improvement: Added Localization support to SureMembers for German and Spanish.
* Improvement: Introducing a new filter 'suremembers_restrict_login_redirect_url' to modify the redirect URL for the login page.
* Fix: PHP notice "_load_textdomain_just_in_time was called incorrectly" after WordPress 6.8 update.
* Fix: Resolved the errors "Call to a member function get_customer_id() on bool" and "Uncaught TypeError: array_search(): Argument #2 ($haystack) must be of type array, null given" in the WooCommerce integration.

= 1.10.7 - February 18, 2025 =
* Fix: Resolved the warning "You attempted to edit an item that doesn't exist. Perhaps it was deleted?" when editing any post/page.

= 1.10.6 - February 17, 2025 =
* Fix: PHP notice "_load_textdomain_just_in_time was called incorrectly".
* Fix: Extra "a" letter removed from email notification template.
* Fix: The expiration date was not updated if the user was revoked access and then granted access again after a few days.
* Fix: Fixed the issue where restricted content was accessible via the API.

= 1.10.5 - November 28, 2024 =
* New: Added the suremembers_after_restricted_message_content action to display custom content after the restricted message section.
* Improvement: Improved the code base to help achieve good compatibility with SureDash portal.

= 1.10.4 - November 21, 2024 =
* Improvement: Updated the licensing library to fix error warnings related to PHP 8.2 version.
* Fix: Fixed an error stopping orders from being permanently deleted.
* Fix: The custom logo was not getting displayed on the wp-login page while using WordPress 6.7 version.
* Fix: Fixed the issue with character encoding issue for email templates.

= 1.10.3 - June 25, 2024 =
* Fix: In downloads section, all of the uploaded media files were not getting displayed due to pagination issue.
* Fix: Fixed the logic to redirect and display the restriction message when the download file is viewed in non-logged in view.

= 1.10.2 - May 2, 2024 =
* New: Added a access group expiry field in the users import example CSV file.
* New: Introduced a filter `suremembers_restrict_login_redirect` to modify the redirect page URL for the checkout page of third-party plugins.
* Fix: Fixed the user role sync and Drip content restriction use cases.

= 1.10.1 - April 15, 2024 =
* Fix: The restricted message template was not getting displayed properly for the custom post type designed using Astra's Site Builder.
* Fix: The drip content restriction feature was not working with user role sync setting.
* Fix: Corrected the color picker display and alignment issue which was restricting to select the correct color in login customizer tab.
* Improvement: Corrected the UI issue with the files selected in the downloads section in Access Group.

= 1.10.0 - March 21, 2024 =
* New: Introduced the email notification feature for access groups.
* New: Introduced the import the users feature via CSV file.

= 1.9.4 - March, 05, 2024 =
* New: Showing the group ID on the access group list page for ease of use for the users.
* Fix: Fixed the conflict between SureMembers and LatePoint plugins.
* Fix: The single LearnDash courses were not getting restricted on the basis of rules selected.
* Fix: Fixed array_search function called with an invalid argument.
* Fix: Blog page was not getting restricted when it was set as post page.
* Fix: Show password button was not working in the mobile views.

= 1.9.3 - December 14, 2023 =
* New: Introduced new hooks to add extra meta-boxes to support third-party plugins.
* Improvement: Updated the license library to it's latest version.
* Fix: Resolved an issue where an empty value was causing errors in the strpos function.
* Fix: Addressed a problem with the opening position of the date-picker for the Expiration date option.
* Fix: Fixed the access expiration issue when the access is set to expire on the relative OR specific expiration dates.
* Fix: Access restriction was not working on the basis of URL conditions.

= 1.9.2 - October 9, 2023 =
* Fix: Fixed the login customizer redirect issue for showing 404 page in some cases.

= 1.9.1 - September 19, 2023 =
* Improvement: Added compatibility for adding access groups to users when registered via Gravity Forms.
* Fix: Fixed the issue that prevented clearing selected access groups in the WooCommerce product edit screen.
* Fix: Fixed JS error that occurred when selecting a specific date in the drip date.
* Fix: Updated BuddyBoss integration file load logic for better compatibility.

= 1.9.0 - September 5, 2023 =
* Feature: Easily change the URL of the WP login form page.
* Improvement: Added support for threaded access groups logic when using BuddyBoss components restriction with SureMembers.
* Improvement: Added a new filter to control featured image display for restricted posts.

= 1.8.1 - August 18, 2023 =
* Improvement: Optimized error layout and resolved BuddyBoss Theme issues in Login Popup.
* Improvement: Added the ability to set expiration settings based on users directly from the profile page.
* Improvement: Removed licensing popup and added licensing using API on the admin page.
* Fix: Restricted the usage of special characters when creating, updating, and deleting user accounts.
* Fix: Alert now appears when attempting to save a particular date and expiration date.
* Fix: Unauthorized Access Button URL strips all content from non-English URLs.
* Fix: Eliminate inactive or deleted access groups from the users list.
* Fix: Modal Content not visible properly on restricted pages.

= 1.8.0 - June 2, 2023 =
* Feature: Added a new feature that lets users choose a drip date to a specific date in the access group.
* Improvement: Added compatibility for restricting BuddyBoss pages using URL restriction.
* Improvement: Updated Download file's filename to inherit from the uploaded filename.
* Fix: Fixed unable to delete or archive access group issue.

= 1.7.2 - April 28, 2023 =
* Improvement: Added compatibility for new Elementor Containers to have support for block restriction option.

= 1.7.1 - April 24, 2023 =
* Improvement: Added compatibility for BuddyBoss user registration and access group.
* Improvement: Allow more than 10 downloads to be added to the access group.
* Fix: Fixed Jetpack notification shortcut bug in the SureMembers edit page.
* Fix: Changed Elementor widget access menu position to maintain uniformity.
* Fix: Bug fixed that was causing the screen to go blank when using the login styler settings page.
* Fix: Fixed the issue that was causing all downloads filename to be set as 'Download'.

= 1.7.0 - February 07, 2023 =
* Feature: Prevent login sharing for chosen user roles.
* Improvement: Updated admin icon SVG.
* Fix: Resolved archive page restricted issue when all singulars rule was selected.

= 1.6.1 - January 16, 2023 =
* Fix: Drip content was not working in the case of the Elementor template.
* Fix: Issue with PostX and Kadance theme when activated together.
* Fix: Elementor section content restriction was failing.

= 1.6.0 - December 27, 2022 =
* Feature: Access Group expiration option.
* Fix: Restricted menu item was not visible to site admins.
* Fix: Block display condition was not showing in widgets screen.
* Fix: Removed unwanted excerpt shown in the restricted message in Archive pages.

= 1.5.1 - December 8, 2022 =
* Fix: Category name was missing in specific taxonomy/exclude selection.
* Fix: Header was hidden on centralized rules engine popup.
* Fix: Login page’s custom logo redirects issue.
* Fix: Shadow was appearing on the transparent background login form.
* Fix: Drip restriction was failing after certain days.

= 1.5.0 - December 6, 2022 =
* Feature: Customize WordPress login page.
* Improvement: Filters to modify post loop.
* Improvement: SureMembers icon on admin bar for classic editor.
* Fix: BuddyBoss infinite loading.
* Fix: Warning on user's profile due to broken logo URL.
* Fix: Drip time getting reset on save.
* Fix: Code optimization and bug fixes.

= 1.4.0 - November 21, 2022 =
* Feature: BuddyBoss API Integration.
* Feature: Support for TutorLMS.
* Feature: WooCommerce coupons integration with access groups.
* Feature: Select default access groups for new registrations.
* Improvement: SureTriggers compatibility.
* Improvement: Customization option for hardcoded text labels.
* Fix: PHP 8.0+ compatibility.

= 1.3.1 - November 9, 2022 =
* Improvement: In content support for blog posts.
* Improvement: Better restriction logic for all other post types.
* Improvement: Centralized rules engine popup.
* Fix: Login popup UI issues with Divi and other themes.
* Fix: Redirection URLs were unrestricted.
* Fix: PHP 8.0+ compatibility.
* Fix: Compatibility with Mega Menu.

= 1.3.0 - October 21, 2022 =
* Feature: Secure digital downloads.
* Feature: Redirection rules.
* Fix: Infinite redirect loop.
* Fix: Administrator getting blocked using custom user role.
* Fix: Issues with dynamic Gutenberg block rendering.

= 1.2.0 - October 11, 2022 =
* Feature: Bulk edit access groups.
* Improvement: Better URL restriction UI.
* Improvement: URL restriction supports multiple regex rules.
* Improvement: Option to restrict content in search results.
* Improvement: Save access groups with empty rules.
* Improvement: Custom restriction template for pages with post loop.
* Fix: The elementor restriction widget is not working fine.
* Fix: Issues with dynamic Gutenberg block rendering.
* Fix: HTML entities not getting appropriately decoded.
* Fix: Code optimization and bug fixes.

= 1.1.0 - September 27, 2022 =
* Feature: Support for LearnDash.
* Feature: WooCommerce Integration.
* Feature: URL Restriction.
* Feature: Create custom user roles.
* Feature: Allow access group to User roles.
* Feature: Show content if a user is in / not in an access group.
* Feature: Login popup for unauthorized access.
* Improvement: Suggestions for redirect URL.
* Improvement: Search functionality for SureCart Integration
* Improvement: Admin bar rules update by ajax on the front end.

= 1.0.3 - September 6, 2022 =
* Improvement: Removed content restrictions for the administrator user role.
* Improvement: Added AJAX for smooth centralized access group management in the admin bar.
* Improvement: Added active state indication for the current page in the admin bar.
* Fix: Fixed page redirection issue when the protocol is missing in the redirection URL.
* Fix: Fixed PHP error in user access table when a user does not have access groups.

= 1.0.2 - September 2, 2022 =
* Fix: Code optimization.

= 1.0.1 - September 1, 2022 =
* Feature: Restrict child posts for hierarchical post types.
* Improvement: Performance with assets loading.
* Fix: Empty data populating in users table.

= 1.0.0 - August 26, 2022 =
* Initial release.
