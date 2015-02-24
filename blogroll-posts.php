<?php
/*
Plugin Name: Blogroll Posts
Plugin URI: http://www.stormyfrog.com/wordpress/blogroll-posts/
Description: Allows you to integrate the latest post titles from your blogroll into your site.
Version: 1.1.3
Author: Andrew Buckman
Author URI: http://abuckman.com/
License: GPL2

**************************************************************************

Copyright (c) 2010 Andrew Buckman

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************

*/

if (!class_exists('blogroll_posts')) {
	class blogroll_posts {

		function blogroll_posts() {
			$this->version = "1.1.3";
		}

		function get_settings() {
			# Set up defaults
			$settings = array(
				# Error reporting (debug mode)
				'errors' => false,
				# How frequently to update cache? [hourly|twicedaily|daily]
				'cache_update' => 'hourly',
				# Category Limiting (csv or empty)
				'categories' => '',
				# The number of posts you want cached per blog
				'max_posts' => 0,
				# The number of posts you want to output
				'max_output' => 2,
				# Do you want [random] posts, the [newest] posts, or an even [split] between the two
				'type' => 'newest',
				# The max number of posts per blog
				'max_each' => 1,
				# Sort order of output [random|newest|oldest]
				'sort_order' => 'newest',

				# the HTML to print before the list
				'before_list' => '<ul>',
				# the code to print out for each blog. Meta tags available:
				# [planned] %date:FORMAT% (ie: %date:F j, Y%)
				# '%blogurl%', '%blogtitle%', '%blogdesc%', # blog values from feed
				# '%permalink%', '%title%', '%author%', '%date%', # post values from feed
				# '%linkurl%', '%linkname%', '%linkdesc%', '%feedurl%' # wp values
				'html_template' => '<li><a href="%permalink%" title="%title%">%title%</a><br /><span class="byline">by <a href="%blogurl%">%author%</a> on %date:F j, Y%</span></li>',
				'fallback_template' => '<li><a href="%linkurl%" title="%linkname%">%linkname%</a><br /><span class="desc">%linkdesc%</span></li>',
				# the HTML to print after the list
				'after_list' => '</ul>',

				# Grouping: field to match for groups (date will disregard time, none disabled grouping)
				'group_by' => 'none',
				# Sort groups, posts inside group will sort by sort_order
				'group_sort' => 'newest',
				# If group_sort is ascending or descending, this is the field to sort on
				'group_sort_field' => 'linkname',
				# Limit output to a specified number of groups, use max_each to control the number of posts per group
				'max_groups' => 0,
				# HTML template to print before the group
				'before_group' => '<li><a href="%linkurl%" title="%linkname%">%linkname%</a><br /><span class="desc">%linkdesc%</span><h4>Recent posts</h4><ul>',
				# HTML template to print after the group
				'after_group' => '</ul></li>',

				# Last Update Timestamp
				'last_updated' => 0
			);
			if (get_option('blogrollPosts_settings'))
				$settings = array_merge($settings, get_option('blogrollPosts_settings'));
			return $settings;
		}

		function get_cache() {
			# Set up default
			$cache = array(
				'feeds' => array(),
				'posts' => array()
			);
			# Merge cached data
			if (get_option('blogrollPosts_cache'))
				$cache = array_merge($cache, get_option('blogrollPosts_cache'));
			# echo 'cache:'; print_r($cache);
			return $cache;
		}

		function get_rss( $rssurl ) {
			if (!function_exists('MagpieRSS')) {
				include_once (ABSPATH . WPINC . '/rss.php');
			}
			# return rss file as parsed object
			return fetch_rss($rssurl);
		}

		function discover_rss( $url ) {
			# fetch website url and parse for RSS feed link
			if (!$url) return false;
			#!! add curl option if http not enabled
			$html = file_get_contents($url);
			if (!$html) return false;
			#!! parse html for <link rel="alternate" type="application/rss+xml" title="[!Comments]" href="FEED" />
			if ( preg_match_all('/<link(.*?)\s*\/?>/s', $html, $metalinks) ) {
				# Loop through meta link tags
				foreach ($metalinks[1] as $ml) {
					# Look for an rss entry that is not comment related
					if (preg_match('/rel="alternate"/', $ml) && !preg_match('/title=".*Comments.*"/', $ml)) {
						# Grab the href attribute and return the rss feed url
						if (preg_match('/href="(.*?)"/', $ml, $hrefs)) {
							return $hrefs[1];
						}
					}
				}
			}
			return false;
		}

		function get_redirected_url( $url, $max_redirects = 10 ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_HEADER, true );
			curl_setopt( $ch, CURLOPT_NOBODY, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_URL, $url );

			for ( $i = 0; $i < $max_redirects; $i++ ) {
				$data = curl_exec($ch);
				$info = curl_getinfo($ch);
				$http_code = $info['http_code'];
				if ( $http_code == 301 || $http_code == 302 || $http_code == 303 ) {
					list($header) = explode("\r\n\r\n", $data, 2);
					$matches = array();
					preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
					$url = trim(array_pop($matches));
					$url_parsed = parse_url($url);
					if (isset($url_parsed['host'])) {
						curl_setopt($ch, CURLOPT_URL, $url);
					} else {
						# Redirect w/o new location, fail
						curl_close($ch);
						return false;
					}
				} elseif ( $http_code == 200 ) {
					$matches = array();
					preg_match('/(<meta http-equiv=)(.*?)(refresh)(.*?)(url=)(.*?)[\'|"]\s*>/', strtolower($data), $matches);
					$url = trim(array_pop($matches));
					$url_parsed = parse_url($url);
					if (isset($url_parsed['host'])) {
						curl_setopt($ch, CURLOPT_URL, $url);
					} else {
						# Final Answer
						curl_close($ch);
						return $info['url'];
					}
				} else {
					# Failure Code / Timeout
					curl_close($ch);
					return false;
				}
			}

			# Max Redirects Reached, fail
			curl_close($ch);
			return false;
		}

		function get_latest( $feedurl, $settings = array() ) {
			$settings = array_merge($this->get_settings(), $settings);
			if (!($rss = $this->get_rss($feedurl))) {
				# echo 'feed pull failed for ' . $feedurl;
				return 'fail';
			}
			# cut array down to max number of posts per blog
			#!! should we sort by pubdate first?
			if ($settings['max_posts'] > 0) $posts = array_slice($rss->items, 0, $settings['max_posts']);
			else $posts = $rss->items;

			# build data for cache
			$tocache = array();
			foreach ( $posts as $post ) {
				if (!isset($post['pubdate'])) {
					# try dc:date
					if (is_array($post['dc']) && array_key_exists('date', $post['dc']))
						$post['pubdate'] = $post['dc']['date'];

					if (!isset($post['pubdate'])) {
						# try published
						if (isset($post['published'])) $post['pubdate'] = $post['published'];
					}
				}
				$tocache[] = array(
					'bloglink' => $rss->channel['link'],
					'blogtitle' => $rss->channel['title'],
					'blogdesc' => $rss->channel['description'],
					'link' => $post['link'],
					'title' => $post['title'],
					'author' => $post['dc']['creator'],
					'pubdate' => $post['pubdate'],
					'timestamp' => strtotime($post['pubdate'])
				);
			}
			return $tocache;
		}

		function reset_rss_links() {
			$cache = $this->get_cache();
			$cache['feeds'] = array();
			update_option('blogrollPosts_cache', $cache);
			return $this->cache_blogroll( array(), 'links-only' );
		}

		function cache_blogroll( $settings = array(), $mode = 'all' ) {
			$settings = array_merge($this->get_settings(), $settings);
			$cache = $this->get_cache();

			# verify categories specified, cowardly refuse to cache otherwise
			if ( !$settings['categories'] || trim($settings['categories']) == '' ) return false;

			# get current list of blogroll links in matching categories
			$blogroll = get_bookmarks( array(
				'category' => $settings['categories']
			) );

			# combine blogroll links & cache[feeds], if feed in both, use cache
			$blogfeeds = array();
			foreach ( $blogroll as $blog ) {
				$blogfeeds[$blog->link_url] = '';
			}
			$cache['feeds'] = array_merge($blogfeeds, $cache['feeds']);
			#if ($settings['errors']) print_r($cache['feeds']);

			# get feed urls and latest posts (only update those from current blogroll)
			foreach ( $blogroll as $blog ) {
				if ( $cache['feeds'][$blog->link_url] ) {
					# RSS feed already cached
					$feedurl = $cache['feeds'][$blog->link_url];
				} elseif ( $blog->link_rss ) {
					# Manually entered RSS feed
					$feedurl = $blog->link_rss;
					$cache['feeds'][$blog->link_url] = $feedurl;
				} else {
					# Attempt to discover rss feed
					$feedurl = $this->discover_rss($blog->link_url);

					if ( $feedurl ) {
						# Test link for redirects
						$feedurl = $this->get_redirected_url($feedurl);
						if ( $feedurl ) $cache['feeds'][$blog->link_url] = $feedurl;
					}
				}

				if ( ( $mode != 'links-only' ) && $feedurl ) {
					# fetch latest post(s)
					$feedposts = $this->get_latest($feedurl, $settings);
					if ( $feedposts=='fail' ) {
						# Failed to fetch RSS feed, check for redirecting url
						# Do not overwrite possibly valid posts from before with a fail notice.
						$redir = $this->get_redirected_url($feedurl);
						if ( $redir!=$feedurl ) {
							# Feed URL is redirecting, check for posts again
							$feedposts = $this->get_latest($redir, $settings);
							if ( $feedposts=='fail' ) {
								# Give up, do not save redirect
							} else {
								# Redir was good, save redirect and cache new posts
								$cache['feeds'][$blog->link_url] = $redir;
								$cache['posts'][$blog->link_url] = $feedposts;
							}
						}
					} else {
						# Cache new posts
						if ( is_array($feedposts) && count($feedposts) > 0 )
							$cache['posts'][$blog->link_url] = $feedposts;
					}
					unset($feedposts);
				}
			}

			# save updated cache data
			update_option('blogrollPosts_cache', $cache);

			if ( $mode != 'links-only' ) {
				# update last_update timestamp
				$upd = get_option('blogrollPosts_settings');
				$upd['last_updated'] = time();
				update_option('blogrollPosts_settings', $upd);
			}

			return true;
		}

		/* sorting functions for blogroll posts */
		function sort_by_date( $a, $b ) {
			if ($a['timestamp'] == $b['timestamp']) return 0;
			return ($a['timestamp'] > $b['timestamp']) ? -1 : 1;
		}
		function sort_by_date_oldest( $a, $b ) {
			if ($a['timestamp'] == $b['timestamp']) return 0;
			return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
		}
		function sort_by_title( $a, $b ) {
			if ($a['title'] == $b['title']) return 0;
			return ($a['title'] > $b['title']) ? -1 : 1;
		}
		function sort_group_by_date( $a, $b ) {
			if ($a[0]['timestamp'] == $b[0]['timestamp']) return 0;
			return ($a[0]['timestamp'] > $b[0]['timestamp']) ? -1 : 1;
		}
		function sort_group_by_date_oldest( $a, $b ) {
			if ($a[0]['timestamp'] == $b[0]['timestamp']) return 0;
			return ($a[0]['timestamp'] < $b[0]['timestamp']) ? -1 : 1;
		}
		function sort_group_by_title( $a, $b ) {
			if ($a[0]['title'] == $b[0]['title']) return 0;
			return ($a[0]['title'] > $b[0]['title']) ? -1 : 1;
		}
		function sort_group_asc( &$oarray, $p ) {
			# $p should be the field
		    usort($oarray, create_function('$a,$b', 'if ($a[0][\'' . $p . '\']== $b[0][\'' . $p .'\']) return 0; return ($a[0][\'' . $p . '\'] < $b[0][\'' . $p .'\']) ? -1 : 1;'));
		}
		function sort_group_desc( &$oarray, $p ) {
			# $p should be the field
		    usort($oarray, create_function('$a,$b', 'if ($a[0][\'' . $p . '\']== $b[0][\'' . $p .'\']) return 0; return ($a[0][\'' . $p . '\'] > $b[0][\'' . $p .'\']) ? -1 : 1;'));
		}

		/* print out the blogroll */
		function print_blogroll( $settings = array(), $outputResults = true ) {
			$settings = array_merge($this->get_settings(), $settings);
			$cache = $this->get_cache();

			$max = $settings['max_output'];

			# Compile cached links from selected categories
			$blogroll = get_bookmarks( array(
				'category' => $settings['categories']
			) );

			$posts = array();

			# build eligible posts
			foreach ($blogroll as $blog) {
				if (is_array($cache['posts']) && array_key_exists($blog->link_url, $cache['posts'])) {
					$blogarr = array(
						'link_url' => $blog->link_url,
						'link_name' => $blog->link_name,
						'link_description' => $blog->link_description
					);
					# add posts from this blog to output queue, limit by max_each - always use newest posts from a blog
					if ($settings['max_each'] > 0) $cache['posts'][$blog->link_url] = array_slice($cache['posts'][$blog->link_url], 0, $settings['max_each']);
					foreach ($cache['posts'][$blog->link_url] as $blogpost) {
						$posts[] = array_merge($blogarr, $blogpost);
					}
				} # else blog not cached, won't output, sorry
			}

			if (count($posts)==0) {
				# Trigger Failsafe, output max_output links from matching blogroll categories
				# ----------------------------------------------
				if ($settings['errors']) echo '<p>Sorry, no posts found in cache.</p>';
				if ($settings['max_output']>0) $blogroll = array_slice($blogroll, 0, $settings['max_output']);
				if ($settings['sort_order']=='random') shuffle($blogroll);
				$output = stripslashes($settings['before_list']);
				foreach ($blogroll as $blog) {
					# output post
					$t = stripslashes($settings['fallback_template']);
					$t = str_replace(
						# Failsafe has no RSS data to use, only internal WP values
						array(
							'%linkurl%', '%linkname%', '%linkdesc%', '%feedurl%' # wp values
							),
						array(
							$blog->link_url, $blog->link_name, $blog->link_description, $blog->link_rss
							),
						$t
					);
					$output .= $t;
				}
				$output .= stripslashes($settings['after_list']);
				if (!$outputResults) return $output;
				else echo $output;
				return false;
			}

			# Set up posts to output based on settings
			# settings: type {random|newest|split}, max_output {#}, sort_order {random|newest|oldest}
			switch ($settings['type']) {
				case 'random':
					shuffle($posts);
					break;
				case 'split':
					# need half of each
					$tempPosts = array();
					if ($settings['max_output'] < 2) {
						usort($posts, array(&$this, 'sort_by_date'));
						break;
					}
					$maxRandom = floor($settings['max_output'] / 2);
					$maxNewest = $settings['max_output'] - $maxRandom;
					# build newest and remove from posts to avoid duplicates
					usort($posts, array(&$this, 'sort_by_date'));
					$tempPosts = array_splice($posts, 0, $maxNewest);
					# build random
					shuffle($posts);
					$posts = array_slice($posts, 0, $maxRandom);
					# combine
					$posts = array_merge($tempPosts, $posts);
					unset($tempPosts);
					break;
				case 'newest':
				default:
					usort($posts, array(&$this, 'sort_by_date'));
			}

			# Snip to max_output
			if ($settings['max_output']>0) $posts = array_slice($posts, 0, $settings['max_output']);

			# Sort final posts
			switch ($settings['sort_order']) {
				case 'random':
					shuffle($posts);
					break;
				case 'alpha':
					usort($posts, array(&$this, 'sort_by_title'));
					break;
				case 'oldest':
					usort($posts, array(&$this, 'sort_by_date_oldest'));
					break;
				case 'newest':
				default:
					usort($posts, array(&$this, 'sort_by_date'));
			}

			# Group posts
			if ($settings['group_by'] && $settings['group_by']!='none') {
				# convert setting to array key
				$xref = array(
					'blogurl'	=> 'bloglink', # ? probably doesn't make sense
					'blogtitle'	=> 'blogtitle', # ? probably doesn't make sense
					'blogdesc'	=> 'blogdesc', # ? doesn't make sense
					'permalink'	=> 'link', # ? doesn't make sense
					'title'		=> 'title', # ? doesn't make sense
					'author'	=> 'author',
					'date'		=> 'pubdate',
					'linkurl'	=> 'link_url',
					'linkname'	=> 'link_name', # ? probably doesn't make sense
					'linkdesc'	=> 'link_description', # ? doesn't make sense
					'feedurl'	=> 'link_rss' # ? doesn't make sense
				);
				$grpkey = $xref[$settings['group_by']];

				# group posts
				$groups = array();
				foreach ($posts as $post) {
					$groups[$post[$grpkey]][] = $post;
				}

				# Sort groups
				switch ($settings['group_sort']) {
					case 'random':
						shuffle($groups);
						break;
					case 'asc':
					case 'ascending':
						$this->sort_group_asc($groups, $xref[$settings['group_sort_field']]);
						break;
					case 'desc':
					case 'descending':
						$this->sort_group_desc($groups, $xref[$settings['group_sort_field']]);
						break;
					case 'oldest':
						usort($groups, array(&$this, 'sort_group_by_date_oldest'));
						break;
					case 'newest':
						usort($groups, array(&$this, 'sort_group_by_date'));
						break;
				}

				# Snip to max_groups
				if ($settings['max_groups']>0) $groups = array_slice($groups, 0, $settings['max_groups']);

			} else {
				$groups = array('none' => $posts);
				$settings['before_group'] = '';
				$settings['after_group'] = '';
			}
			unset($posts);
			# ----------------------------------------------

			# Output Resulting Post List
			$output = $this->parse_replacements($settings['before_list'], $group[0]);
			foreach ($groups as $group) {
				# Output before_group template
				$output .= $this->parse_replacements($settings['before_group'], $group[0]);

				# Loop through group's posts
				foreach ($group as $post) {
					$output .= $this->parse_replacements($settings['html_template'], $post);
				}

				# Output after_group template
				$output .= $this->parse_replacements($settings['after_group'], $group[0]);
			}
			$output .= $this->parse_replacements($settings['after_list'], $group[0]);

			if (!$outputResults) return $output;
			else echo $output;
			return true;
		}

		function parse_replacements( $strfmt, &$post ) {
			$strfmt = stripslashes($strfmt);

			# Calculate custom date formats
			if (preg_match('/%date:(.*)%/', $strfmt, $dateOptions)) {
				$strfmt = str_replace($dateOptions[0], date($dateOptions[1], $post['timestamp']), $strfmt);
			}

			# Verify replacement fields and swap in fallback if needed
			if (!$post['author']) $post['author'] = $post['link_name'];
			if (!$post['bloglink']) $post['bloglink'] = $post['link_url'];

			# Replace fields
			$strfmt = str_replace(
				array(
					'%blogurl%', '%blogtitle%', '%blogdesc%', # blog values from feed
					'%permalink%', '%title%', '%author%', '%date%', # post values from feed
					'%linkurl%', '%linkname%', '%linkdesc%', '%feedurl%' # wp values
					),
				array(
					$post['bloglink'], $post['blogtitle'], $post['blogdesc'],
					$post['link'], $post['title'], $post['author'], $post['pubdate'],
					$post['link_url'], $post['link_name'], $post['link_description'], $post['link_rss']
					),
				$strfmt
			);
			return $strfmt;
		}

		function setup_widget() {
			if (!function_exists('wp_register_sidebar_widget')) return;
			function widget_blogrollPosts($args) {
				extract($args);
				$options = get_option('widget_blogrollPosts');
				$title = $options['title'];
				echo $before_widget . $before_title . $title . $after_title;
				get_blogrollPosts();
				echo $after_widget;
			}
			function widget_blogrollPosts_control() {
				$options = get_option('widget_blogrollPosts');
				if ( $_POST['blogrollPosts-submit'] ) {
					$options['title'] = strip_tags(stripslashes($_POST['blogrollPosts-title']));
					update_option('widget_blogrollPosts', $options);
				}
				$title = htmlspecialchars($options['title'], ENT_QUOTES);
				$settingspage = trailingslashit(get_option('siteurl')).'wp-admin/options-general.php?page='.basename(__FILE__);
				echo
				'<p><label for="blogrollPosts-title">Title:<input class="widefat" name="blogrollPosts-title" type="text" value="'.$title.'" /></label></p>'.
				'<p>To control the other settings, please visit the <a href="'.$settingspage.'">Blogroll Posts Settings page</a>.</p>'.
				'<input type="hidden" id="blogrollPosts-submit" name="blogrollPosts-submit" value="1" />';
			}
			wp_register_sidebar_widget('blogroll-posts-1', 'blogroll-posts', 'widget_blogrollPosts');
			wp_register_widget_control('blogroll-posts-1', 'blogroll-posts', 'widget_blogrollPosts_control');
		}

		/* settings page */
		function setup_settings_page() {
			if (function_exists('add_options_page')) {
				add_options_page('Blogroll Posts Settings', 'Blogroll Posts', 'manage_options', basename(__FILE__), array(&$this, 'print_settings_page'));
				// add_action('admin_init', array(&$this, 'register_settings'));
			}
		}
		function register_settings() {
			register_setting('sfbrp-settings', 'blogrollPosts_settings');
		}
		function print_settings_page() {
			$settings = $this->get_settings();
			if (isset($_POST['save_blogrollPosts_settings'])) {
				# Check if we need to reschedule updates
				$reschedule = ($settings['cache_update']==$_POST['blogrollPosts_cache_update']) ? false : true;

				# Save new settings
				# echo 'Old: <code>'; print_r($settings); echo '</code>';
				foreach ($settings as $name => $value) {
					if ($name!='last_updated')
						$settings[$name] = $_POST['blogrollPosts_'.$name];
				}
				# echo 'New: <code>'; print_r($settings); echo '</code>';
				update_option('blogrollPosts_settings', $settings);

				# Reschedule update if needed
				if ($reschedule) $this->reschedule_updates();

				# Done!
				echo '<div class="updated"><p>Blogroll Posts settings saved!</p></div>';

			} elseif (isset($_POST['blogrollPosts_UpdateCache'])) {
				# Trigger update of RSS posts manually
				$this->cache_blogroll();
				echo '<div class="updated"><p>Cache updated!</p></div>';

			} elseif (isset($_POST['blogrollPosts_ResetRSSLinks'])) {
				# Reset cached RSS links and fetch again
				$this->reset_rss_links();
				echo '<div class="updated"><p>RSS links reset.</p></div>';

			} elseif (isset($_POST['blogrollPosts_ClearCache'])) {
				# Empty cache to remove any extraneous data (?and trigger new update?)
				$this->empty_cache();
				#$this->cache_blogroll();
				echo '<div class="updated"><p>Cache cleared! You may want to manually update the cache now.</p></div>';

			} elseif (isset($_POST['blogrollPosts_ResetSchedule'])) {
				$this->reschedule_updates();
				echo '<div class="updated"><p>Scheduled updates reset!</p></div>';

			} elseif (isset($_POST['blogrollPosts_ResetPlugin'])) {
				# Reset Plugin
				delete_option('blogrollPosts_settings');
				delete_option('blogrollPosts_cache');
				echo '<div class="updated"><p>Blogroll Posts plugin settings and cache reset to default!</p></div>';
			}

			# Pull settings again to account for updates before displaying form
			$settings = $this->get_settings();
			include("blogroll-posts-settings.php");
		}

		function cache_size() {
			$cache = $this->get_cache();
			$cnt = 0;
			foreach ($cache['posts'] as $cp) $cnt += count($cp);
			return $cnt;
		}

		function output_cache() {
			echo '<pre style="font-size:9px">';
			print_r($this->get_cache());
			echo '</pre>';
		}

		function empty_cache() {
			# Set up default
			$cache = array(
				'feeds' => array(),
				'posts' => array()
			);
			# Overwrite cached data
			update_option('blogrollPosts_cache', $cache);
		}

		/* settings link for plugin page */
		function add_actions( $links ) {
			array_unshift($links, '<a href="options-general.php?page=blogroll-posts.php">' . __('Settings') . '</a>');
			return $links;
		}

		/* scheduler functions via wp_cron */
		function update_feeds() {
			$this->cache_blogroll();
		}
		function reschedule_updates() {
			$settings = $this->get_settings();

			# Remove old schedule
			wp_clear_scheduled_hook('blogrollPosts_scheduled_update');

			# Set up new schedule, first run in 1hr (no scheduling for manual)
			if ($settings['cache_update']!='manual')
				wp_schedule_event(time()+3600, $settings['cache_update'], 'blogrollPosts_scheduled_update');
		}
		function activation() {
			$settings = $this->get_settings();

			# Populate initial cache, will only work if categories have been previously configured
			$this->cache_blogroll();

			# Set up scheduling, first batch in 1hr
			wp_schedule_event(time()+3600, $settings['cache_update'], 'blogrollPosts_scheduled_update');
		}
		function deactivation() {
			# wipe out cache, no need to waste space
			# but save settings in the event plugin is reactivated later
			update_option('blogrollPosts_cache');

			# clear scheduled update process
			wp_clear_scheduled_hook('blogrollPosts_scheduled_update');
		}

	} /* end class */
} /* end if !class_exists */

$blogrollPosts = new blogroll_posts();
add_action( 'admin_menu', array(&$blogrollPosts, 'setup_settings_page') );
add_action( 'plugins_loaded', array(&$blogrollPosts, 'setup_widget') );
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$blogrollPosts, 'add_actions') );
register_activation_hook( __FILE__, array( &$blogrollPosts, 'activation' ) );
register_deactivation_hook( __FILE__, array( &$blogrollPosts, 'deactivation' ) );
add_action( 'blogrollPosts_scheduled_update', array( &$blogrollPosts, 'update_feeds' ) ); #, priority, num args

function get_blogrollPosts( $settings = array(), $output = true ) {
	global $blogrollPosts;
	$blogrollPosts->print_blogroll($settings, $output);
}
