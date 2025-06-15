=== SureDash ===

Contributors: brainstormforce
Tags: dashboard, customer dashboard, customer, user dashboard
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0-rc.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a WordPress plugin that will create a unified login and dashboard experience for your customers, for any plugins.

== Description ==

This is a WordPress plugin that will create a unified login and dashboard experience for your customers, for any plugins.

<a href="https://zipwp.org/plugins/suredash/" target="_blank" rel="">Try the live demo of SureDash.</a>
<a href="https://try.new/suredash" target="_blank" rel="">Try the live demo with dummy data.</a>

== Installation ==

1. Upload the SureDash folder to the /wp-content/plugins/ directory
2. Activate the SureDash plugin through the ‘Plugins’ menu in WordPress

== External services ==

This plugin connects to external APIs to provide specific functionalities. Below are the details about each service, including what data is sent, why it is sent, and links to their respective terms of service and privacy policies.

= Google reCAPTCHA =
* Purpose: This service is used to verify users and protect your website from spam and abuse.
* Data Sent: The plugin sends the user's reCAPTCHA response token and site-specific keys to the Google reCAPTCHA API for verification.
* When: Data is sent during form submissions or login attempts where reCAPTCHA is enabled.
* Service Links:
    1. [Google reCAPTCHA Terms of Service](https://cloud.google.com/security/products/recaptcha)
    2. [Google Privacy Policy](https://policies.google.com/privacy?)

= Facebook Graph API =
* Purpose: This service is used for social login functionality, allowing users to log in to your site via their Facebook accounts.
* Data Sent: The plugin sends app credentials (App ID and App Secret) and receives an OAuth access token. User data such as name, email, and profile picture may be retrieved if granted permission.
* When: Data is sent during user login attempts through Facebook.
* Service Links:
    1. [Facebook Terms of Service](https://www.facebook.com/terms.php/)
    2. [Facebook Privacy Policy](https://www.facebook.com/policies_center)

= Google Maps API (if applicable) =
* Purpose: This service is used to fetch and display map-related data (e.g., geolocation, markers).
* Data Sent: The plugin sends location-related data to the Google Maps API for rendering maps or retrieving location details.
* When: Data is sent during specific interactions like location searches or map loading.
* Service Links:
    1. [Google Maps Terms of Service](https://cloud.google.com/maps-platform/terms)
    2. [Google Privacy Policy](https://policies.google.com/privacy?)

== Changelog ==

= 1.0.0-rc.3 - Tuesday, 27th May 2025 =
* New: Brand new admin dashboard with better UI/UX and with lot of new features, improvements.
* New: "Enable Feeds" community setting to showcase your latest posts.
* New: "SureDash User" role introduced for the community.
* New: Tightening the SureMembers <> SureDash Integration.
* New: Lesson URL is now cleaner than previous query sd-lesson parameter based URL.
* New: Feeds option for getting the latest updates from community portal.
* New: Layout width-style options like Normal, Full Width with Boxed, Unboxed styles.
* Improved codebase with best coding practices.
* Improvement: Admin bar enabled for admin user with SureDash quick menu links.
* Improvement: Straight forward & clutter free 4 space types: Single Post/Page, Posts/Discussions, Course, Link.
* Improvement: Design options like logo setting, colors, layouts are moved to site portal-template editor.
* Improvement: Code refactor for better performance & maintainability.
* Improvement: Post submission popup improved, which gives extra room for the user to add content.
* Improvement: Refined settings, options & features for better user experience.
* Refactor: Flows of creation spaces, space groups, content posts generation has been simplified.
* Refactor: Getting rid of "Content" & "Topic" CPTs & completely relying on single CPT "Community Posts", which reduces the complexity of the plugin & ease for users to create community posts.
* Refactor: Removed custom dependency from dashboard app & relying on force-ui.
* Fix: Portal Editor - User profile looks middle in the editor screen.
* Fix: After submitting a post, the post creation modal is not closing.
* Fix: Notification Block - 'px' unit added twice for notification drawer position size.
* Fix: Resolved a CSS conflict with Spectra that was causing Design Library elements to be hidden.

= 0.0.7 - Friday, 18th April 2025 =
* Fix: The home grid layout is narrow when the portal is set as the homepage.
* Fix: Some UI glitches were fixed with an inline comment box.
* Fix: The portal template parts have links which open the frontend in the editor.
* Fix: The console error is related to the Giphy API URL targeting 'http' instead of 'https'.
* Fix: User Profile & Bookmark links are visible for non-logged-in users under the search quick links.

= 0.0.6 - Thursday, 17th April 2025 =
* New: Introducing Identity, Navigation, Notification, Profile, Search, Title, Content blocks for designing the portal.
* New: Introducing Classic & Modern layouts for the portal through the block patterns.
* New: Introducing "Portal" template part for designing the entire portal.
* New: Ability to reply via inline commenting using the minimal comment box.
* Improved codebase with best coding practices.
* Improvement: Added Giphy integration for the comment section.
* Improvement: Introduce a new filter "suredash_post_restriction" to adjust the restriction of the post.
* Improvement: Animation improvements and bug fixes for inline comment box under posts.
* Improvement: The inline comments now show the comment liked by Author and the logged in user's latest comment.
* Improvement: Updated the centre position of active space in the side navigation for better UI/UX.
* Improvement: Bricks compatibility: Post/Page built with Bricks will be displayed on the portal.
* Improvement: The course thumbnail is now aligned with the homepage grid instead of the featured image for better visual consistency and user experience.
* Improvement: Ensured compatibility with WordPress 6.8.
* Fix: Presto Player compatibility: Media blocks are not working properly in the portal when FSE theme is active.
* Fix: Presto Player compatibility: Video space Playlist selection points to community_content posts instead of PP's media library posts.
* Fix: Divi compatibility: There was some HTML markup printed on the frontend while builder post/page/template loaded.
* Fix: If custom Home Page sets, it results in a blank content area on the frontend.
* Fix: Marking as Read not working properly & status remains same even after refreshing the page.
* Fix: If a page designed with Spectra & set as portal homepage, their dynamic styling is not applied on the frontend.
* Fix: Prefetching space on hover of the side navigation gets tracked in the search's Recently Viewed section.

= 0.0.5 - Wednesday, 12nd March 2025 =
* New: Showcasing comment liked users names on hover in a tooltip format for better user experience.
* Improvement: The single page container size can be managed through the Global Container Type setting.
* Improvement: Added a login menu item to the user profile dropdown if a user is not logged in.
* Fix: Incorrect login URL showing on topic feeds for the non-logged-in users.
* Fix: There were some UI glitches on the user-view page while accessing cover photo field.
* Fix: In user view, on a existing profile photo new uploaded photo doesn't appear in the field.
* Fix: User view missed with user's respective posts & comments.
* Fix: On space creation the default space group field shows unselected.
* Fix: There was a jerk in space icon search.
* Fix: The long titled portal name was not displaying properly on the frontend.

= 0.0.4 - Monday, 10th February 2025 =
* Improved codebase for improved security.
* Improved codebase with improved best coding practices.
* Improvement: Improved compatibility for the Bricks Builder Theme.
* Fix: Bricks compatibility: Editing SureDash CPT posts with Bricks opens portal dashboard.
* Fix: There is weird aside space appearing to portal container when ACSS and Bricks work together.
* Fix: Latest feed/topic posts should be displayed first on the frontend.
* Fix: After creating a space group, newly added spaces immediately disappear from the dashboard area.
* Fix: Once a Space Group is created, a placeholder Space is automatically added under it.
* Fix: Beaver Builder & Divi Compatibility: There were some PHP errors on the frontend while using the Beaver Builder or Divi Builder.
* Fix: PHP Error - "Uncaught TypeError: SureDashboard\\Admin\\Editor::register_block_category(): Argument #2 ($post) must be of type object, string given".
* Fix: The admin setup language is directly updated after the update, conflict with German language.
* Fix: Timestamp was not displaying correctly when the timezone was set to something other than UTC.
* Fix: HTML tags were displaying in the notification panel.
* Fix: Deleted comment notifications showing under the notification panel.

= 0.0.3 - Thursday, 28th January 2025 =
* New: Introducing translations for the Swedish, Polish, Hebrew, French, Dutch and Dutch (Belgium) languages.
* Improved codebase for improved security best practices.
* Improvement: Displaying premium plugin incompatibility admin notice on all dashboard screens instead of the only plugins page.
* Fix: Draft spaces are getting displayed on the frontend after the 0.0.2 update.
* Fix: Breakdance compatibility: The entire portal dashboard appearing under Breakdance admin pages.
* Fix: Oxygen & Breakdance compatibility: Some portal spaces loading their content twice on the frontend.
* Fix: After the update of 0.0.2 admin setup translated to the Spanish language.

The full changelog is available [here](https://suredash.com/whats-new/).
