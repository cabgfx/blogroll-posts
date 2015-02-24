=== Blogroll Posts ===
Contributors: Andrew Buckman
Donate link: http://www.stormyfrog.com/donate/
Tags: stats, statistics, google, analytics, google analytics, tracking, widget
Requires at least: 2.9
Tested up to: 2.9.2
Stable tag: trunk

Allows you to integrate the latest post titles from your blogroll in to your site.

== Description ==

Blogroll Posts checks links in your blogroll categories you select for RSS feeds to display the latest post(s) in your blogroll rather than just a list of websites.  Feed data is cached and automatically updated in the background to avoid slowing down your site.

= Features =

Blogroll Posts has the following features:

- Auto-discovers RSS feeds if not specified in link details
- Caches discovered feeds
- Caches latest posts using WordPress built-in cache
- Auto-updating on timed schedule using WordPress cron, avoiding lag on page load
- Customize output with template
- Fallback to standard blogroll display (with template)

For more information, visit the [Blogroll Posts plugin page](http://www.stormyfrog.com/wordpress/blogroll-posts/).

== Installation ==

1. Upload `blogroll-posts` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin in the Settings > Blogroll Posts menu screen
4. Include `get_blogrollPosts()` in your theme to output posts or 
   return output instead of echoing with `get_blogrollPosts(array(), true)`
   customize with `get_blogrollPosts($override_settings_array, $outputIfTrue)`

== Changelog ==

= 1.1.3 = April 9, 2010
* Check for redirected RSS feeds when caching RSS URL and when fetching posts fails
* Option to reset RSS feed link cache and re-discover links

= 1.1.2 = March 31, 2010
* Updated to match WordPress Coding Standards
  http://codex.wordpress.org/WordPress_Coding_Standards

= 1.1.1 = March 30, 2010
* Can now use template fields in before_list and after_list
* Added 'published' as recognized date field in RSS feed

= 1.1.0 = March 29, 2010
* Ability to group posts together
* Sort groups by specific field
* Alpha-sort

= 1.0.0 = March 28, 2010
* Initial release.