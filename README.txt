=== Recent Posts Widget With Thumbnails ===
Contributors: Hinjiriyo
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SBF76TCGTRNX2
Tags: aspect ratio, current post, excerpt, featured, featured images, first post image, height, image, images, listed posts, post date, post title, recent posts, sticky, thumb, thumbnail, thumbnails, thumbs, widget, widgets, width
Requires at least: 2.9
Tested up to: 4.2.2
Stable tag: 4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Small and fast plugin to display in the sidebar a list of linked titles and thumbnails of the most recent postings

== Description ==

No huge widget with hundreds of options. This plugin is based on the well-known WordPress default widget 'Recent Posts' and enhanced to display thumbnails of the posts.

The thumbnails will be built from the featured image of a post or of the first image in the post content. If there is neither a featured image nor a content image then you can define a default thumbnail.

The thumbnails appears left-aligned to the post titles. You can set the width and heigth of the thumbnails in the list.

The widget is available in english and german.

= Options you can set =

1. Title of the widget
2. Number of listed posts
3. Hide current post in list
4. Keep sticky posts on top of the list
5. Hide post title
6. Show post date
7. Show post excerpt
8. Excerpt length
9. Signs after excerpt
10. Show post thumbnail (featured image)
11. Registered thumbnail dimensions
12. Thumbnail width in px
13. Thumbnail height in px
14. Keep aspect ratio of thumbnails
15. Try to take the first post image as thumbnail
16. Only use the first post image as thumbnail
17. Use default thumbnail if no thumbnail is available
18. Default thumbnail URL

= What users said =

* [How to show recent posts in WordPress blog](http://mayifix.com/how-to-show-recent-posts-in-wordpress-blog.html) by Robin on June 28, 2015
* **Number 5** in [Best List of Free Recent Posts Widgets for WordPress](http://dotcave.com/wordpress/free-recent-posts-widgets-for-wordpress/) by jerry on November 29, 2014
* **Number 1** in [Nützlich: Die 10 interessantesten Widgets für Ihre WordPress-Webseite](http://www.drweb.de/magazin/nuetzlich-die-10-interessantesten-widgets-fuer-ihre-wordpress-webseite-50307/) by Andreas Hecht on October 13, 2014
* **Number 1** in [25 Most Useful WordPress Widgets for Your Site](http://www.wpbeginner.com/showcase/25-most-useful-wordpress-widgets-for-your-site/) by Editorial Staff on September 18, 2014

= Useful hints for developers: Hooks and CSS =

**Supported Hooks:** The plugin considers the output of actions hooked on:

1. widget_title
2. widget_posts_args
3. excerpt_more
4. excerpt_length

**CSS Selectors:** To design the list and its items you can use these CSS selectors:

The elements which contain the posts list:
`.recent-posts-widget-with-thumbnails`

The lists which contain the list items:
`.recent-posts-widget-with-thumbnails ul`

All list items in the list:
`.recent-posts-widget-with-thumbnails ul li`

All list items of sticky posts in the list:
`.recent-posts-widget-with-thumbnails ul li.rpwwt-sticky`

All links in the list; every link contains the image and the post title:
`.recent-posts-widget-with-thumbnails ul li a`

All images in the list (use that to set the margins around images):
`.recent-posts-widget-with-thumbnails ul li a img`

All post titles in the list:
`.recent-posts-widget-with-thumbnails ul li a span.rpwwt-post-title`

All post dates in the list:
`.recent-posts-widget-with-thumbnails ul li div.rpwwt-post-date`

All post excerpts in the list: 
`.recent-posts-widget-with-thumbnails ul li div.rpwwt-post-excerpt`

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'Recent Posts Widget With Thumbnails'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard
5. Go to 'Appereance' => 'Widgets' and select 'Recent Posts Widget With Thumbnails'

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `recent-posts-widget-with-thumbnails.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard
6. Go to 'Appereance' => 'Widgets' and select 'Recent Posts Widget With Thumbnails'

= Using FTP =

1. Download `recent-posts-widget-with-thumbnails.zip`
2. Extract the `recent-posts-widget-with-thumbnails` directory to your computer
3. Upload the `recent-posts-widget-with-thumbnails` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard
5. Go to 'Appereance' => 'Widgets' and select 'Recent Posts Widget With Thumbnails'

== Frequently Asked Questions ==

= What are the requirements for this plugin? =

The WordPress version should be at least 2.9 to use featured images.

The theme should support `wp_head()` in the HTML header section to print the CSS code for a beautiful alignment of the thumbnails.

= Can I set a default thumbnail? =

Yes.

= Can I set the width and height of the thumbnail? =

Yes. You can enter the desired width and height of the thumbnails or select one of the sizes as set in 'Settings' > 'Media'.

= Can I change the alignment of the thumbnails in the list? =

This feature will come in a future version of the plugin. Set the alignment in the CSS of your theme instead.

= Where can I set the CSS of the list? =

This feature will come in a future version of the plugin. Set the CSS in the style.css of your theme instead.

= Can the plugin take the first image of a post as thumbnail image? =

Yes. It works with images previously uploaded into the media library. You can select to prefer the first image to the featured image or to use the first image only.

= Which languages does the plugin support? =

Actually these languages are supported:

* English
* German

Further translations are welcome. If you want to give in your translation please leave a notice in the [plugin's support forum](http://wordpress.org/support/plugin/recent-posts-widget-with-thumbnails).

= Where is the *.pot file for translating the plugin in any language? =

If you want to contribute a translation of the plugin in your language it would be great! You would find the *.pot file in the 'languages' directory of this plugin. If you would send the *.po file to me I would include it in the next release of the plugin.

== Screenshots ==

1. The first screenshot shows the widget in the sidebar with a title, clickable images and links, in german.
2. The second screenshot shows the german translated widget on the Widget Management Page in the backend.

== Changelog ==

= 4.0 =
* Added category option: widget only lists posts of a selected category, else lists posts of all categories
* Added sticky posts option: widget shows sticky posts on top of the list, else lists them in normal order
* Added hide current post option: widget does not list the post where the user is currently on, else lists it
* Added CSS class names for easy designing of the list and its list items; see Description for details
* Added style sheet for Widget page in the backend
* Fixed missing custom image sizes in frontend
* Formatted the code more readable
* Updated *.pot file and german translation
* Updated screenshots
* Revised readme.txt

= 3.0 =
* Added default image sizes dropdown menu
* Added options to print out excerpts
* Refactored: HTML output moved into include files
* Slight improvements for security and performance
* Updated *.pot file and german translation
* Revised readme.txt

= 2.3.3 =
* Fixed error message on trial to open the CSS file
* Tested successfully with WordPress 4.2.2

= 2.3.2 =
* Fixed bug of wrong path to public.css file
* Changed HTML class names, now they start with 'rpwwt-'

= 2.3.1 =
* Set CSS for the list style to prevent dots in some themes
* Added span element with class "post-title" around the title
* Tested successfully with WordPress 4.2

= 2.3 =
* Added option to keep aspect ratios of the original images
* Added option to hide the post title in the list
* Moved inline CSS to external file
* Revised *.pot file and german translation

= 2.2.2 =
* Successfully tested with WordPress 4.1
* Fixed bug which threw a warning in debug mode when accessing options

= 2.2.1 =
* Fixed bug which prevented to find the first content image
* Slightly revised algorithm for detecting the first image in post content

= 2.2 =
Revised algorithm to detect the first image in post content.

= 2.1.1 =
Successfully tested with WordPress 4.0

= 2.1 =
* Improve uninstall routine
* Tested successfully with WordPress 3.9.2

= 2.0 =
* Added option to set width and height of the thumbnails
* Added option to prefer first content image to featured image
* Added option to use only first content image as thumbnail
* Added option to set a default thumbnail
* Added function to delete plugin's settings in the database if the plugin is deleted
* Improved code for more robustness
* Updated *.pot file and german translation

= 1.0 =
* The plugin was released.

== Upgrade Notice ==

= 4.0 =
Added options: sticky posts, current post, category filter; revised code

= 3.0 =
Added options: image sizes and excerpt

= 2.3.3 =
Fixed error message on trial to open the CSS file

= 2.3.2 =
Fixed CSS bug

= 2.3.1 =
Slight CSS improvements, tested successfully with WordPress 4.2

= 2.3 =
Refactored. Please update the settings of the widget after upgrading the plugin

= 2.2.2 =
Successfully tested with WordPress 4.1, fixed a minor bug

= 2.2.1 =
Bugfixed and improved algorithm for detecting the first image in post contents

= 2.2 =
Revised algorithm for detecting the first image in post content

= 2.1.1 =
Successfully tested with WordPress 4.0

= 2.1 =
Improved uninstall routine, tested with WordPress 3.9.2

= 2.0 =
More options and improved code

= 1.0 =
First release.
