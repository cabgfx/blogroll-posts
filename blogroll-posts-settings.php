<div class="wrap">
	<h2>Blogroll Posts Settings</h2>
	<form method="post">
		<?php // settings_fields('sfbrp-settings'); ?>
		<fieldset id="cache_settings">
			<h3>Cache Settings</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="blogrollPosts_cache_update">Cache Update Frequency</label></th>
					<td>
						<select name="blogrollPosts_cache_update" id="blogrollPosts_cache_update">
							<option <?php if($settings['cache_update'] == 'manual') { echo 'selected="selected"'; } ?> value="manual">manual</option>
							<option <?php if($settings['cache_update'] == 'hourly') { echo 'selected="selected"'; } ?> value="hourly">hourly</option>
							<option <?php if($settings['cache_update'] == 'twicedaily') { echo 'selected="selected"'; } ?> value="twicedaily">twice daily</option>
							<option <?php if($settings['cache_update'] == 'daily') { echo 'selected="selected"'; } ?> value="daily">daily</option>
						</select>
						Next update: 
						<?php
							$nextupd = wp_next_scheduled('blogrollPosts_scheduled_update');
							if ($nextupd) echo date_i18n(get_option('date_format') . ' \a\t ' . get_option('time_format'), $nextupd + get_option('gmt_offset') * 3600, true);
							else echo '<code>Not scheduled</code>';
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="">Categories</label></th>
					<td>
						<input name="blogrollPosts_categories" type="text" id="blogrollPosts_categories" value="<?php echo $settings['categories']; ?>" size="40" /> 
						Comma separated list of IDs, no spaces. Categories <strong>must</strong> be cached to be shown in results.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="blogrollPosts_max_posts">Max Posts per Blog</label></th>
					<td>
						<select name="blogrollPosts_max_posts" id="blogrollPosts_max_posts">
							<option <?php if ($settings['max_posts'] == 0) { echo 'selected="selected"'; } ?> value="0">(unlimited)</option>
							<?php for ($i=1; $i<=20; $i++) { ?>
								<option <?php if ($settings['max_posts'] == $i) { echo 'selected="selected"'; } ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>	
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Cache Last Updated</th>
					<td><?php if ($settings['last_updated']) echo date_i18n(get_option('date_format') . ' \a\t ' . get_option('time_format'), $settings['last_updated'] + get_option('gmt_offset') * 3600, true); else echo 'Never'; ?></td>
				</tr>
			</table>
		</fieldset>

		<fieldset id="output_settings">
			<h3>Output Settings</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Display</th>
					<td>
						<select name="blogrollPosts_max_output" id="blogrollPosts_max_output">
							<option <?php if ($settings['max_output'] == 0) { echo 'selected="selected"'; } ?> value="0">(unlimited)</option>
							<?php for ($i=1; $i<=20; $i++) { ?>
								<option <?php if ($settings['max_output'] == $i) { echo 'selected="selected"'; } ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>	
							<?php } ?>
						</select>
						<select name="blogrollPosts_type" id="blogrollPosts_type">
							<option <?php if($settings['type'] == 'random') { echo 'selected="selected"'; } ?> value="random">random</option>
							<option <?php if($settings['type'] == 'newest') { echo 'selected="selected"'; } ?> value="newest">newest</option>
							<option <?php if($settings['type'] == 'split') { echo 'selected="selected"'; } ?> value="split">split 50/50</option>
						</select>
						posts with a limit of 
						<select name="blogrollPosts_max_each" id="blogrollPosts_max_each">
							<option <?php if ($settings['max_each'] == 0) { echo 'selected="selected"'; } ?> value="0">(unlimited)</option>	
							<?php for ($i=1; $i<=20; $i++) { ?>
								<option <?php if ($settings['max_each'] == $i) { echo 'selected="selected"'; } ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>	
							<?php } ?>
						</select>
						of the newest posts per blog.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="blogrollPosts_sort_order">Display Order</label></th>
					<td>
						<select name="blogrollPosts_sort_order" id="blogrollPosts_sort_order">
							<option <?php if($settings['sort_order'] == 'random') { echo 'selected="selected"'; } ?> value="random">random</option>
							<option <?php if($settings['sort_order'] == 'newest') { echo 'selected="selected"'; } ?> value="newest">newest first</option>
							<option <?php if($settings['sort_order'] == 'oldest') { echo 'selected="selected"'; } ?> value="oldest">oldest first</option>
							<option <?php if($settings['sort_order'] == 'alpha') { echo 'selected="selected"'; } ?> value="alpha">post title (alpha)</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="blogrollPosts_before_list">Before List</label>
						<br /><em style="font-size:11px"><a href="#blogrollPosts_metatags">metatag info</a> below</em>
					</th>
					<td><input name="blogrollPosts_before_list" type="text" id="blogrollPosts_before_list" value="<?=stripslashes($settings['before_list']); ?>" size="40" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="blogrollPosts_html_template">Output Template</label>
						<br /><em style="font-size:11px"><a href="#blogrollPosts_metatags">metatag info</a> below</em>
					</th>
					<td><textarea name="blogrollPosts_html_template" id="blogrollPosts_html_template" rows="5" cols="80" style="font-family:monospace;font-size:11px"><?=stripslashes($settings['html_template']); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="blogrollPosts_fallback_template">Fallback Template</label>
						<br /><em style="font-size:11px"><a href="#blogrollPosts_metatags">metatag info</a> below</em>
					</th>
					<td><textarea name="blogrollPosts_fallback_template" id="blogrollPosts_fallback_template" rows="5" cols="80" style="font-family:monospace;font-size:11px"><?=stripslashes($settings['fallback_template']); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="blogrollPosts_after_list">After List</label>
						<br /><em style="font-size:11px"><a href="#blogrollPosts_metatags">metatag info</a> below</em>
					</th>
					<td><input name="blogrollPosts_after_list" type="text" id="blogrollPosts_after_list" value="<?=stripslashes($settings['after_list']); ?>" size="40" /></td>
				</tr>
				
			</table>
		</fieldset>

		<fieldset id="group_settings">
			<h3>Group Settings (optional)</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Group Posts by</th>
					<td>
						<select name="blogrollPosts_group_by" id="blogrollPosts_group_by">
							<option <?php if($settings['group_by'] == 'none') { echo 'selected="selected"'; } ?> value="none">(no grouping)</option>
							<option <?php if($settings['group_by'] == 'linkurl') { echo 'selected="selected"'; } ?> value="linkurl">URL (from Blogroll)</option>
							<option <?php if($settings['group_by'] == 'date') { echo 'selected="selected"'; } ?> value="date">Date Post Published</option>
							<option <?php if($settings['group_by'] == 'author') { echo 'selected="selected"'; } ?> value="date">Post Author (from RSS)</option>
						</select>
						with a limit of 
						<select name="blogrollPosts_max_groups" id="blogrollPosts_max_groups">
							<option <?php if ($settings['max_groups'] == 0) { echo 'selected="selected"'; } ?> value="0">(unlimited)</option>	
							<?php for ($i=1; $i<=20; $i++) { ?>
								<option <?php if ($settings['max_groups'] == $i) { echo 'selected="selected"'; } ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>	
							<?php } ?>
						</select>
						groups in the output.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="blogrollPosts_before_group">Group Sorting</label>
					</th>
					<td>
						<select name="blogrollPosts_group_sort" id="blogrollPosts_group_sort">
							<option <?php if($settings['group_sort'] == 'random') { echo 'selected="selected"'; } ?> value="random">random</option>
							<option <?php if($settings['group_sort'] == 'newest') { echo 'selected="selected"'; } ?> value="newest">recently updated</option>
							<option <?php if($settings['group_sort'] == 'oldest') { echo 'selected="selected"'; } ?> value="oldest">least recent</option>
							<option <?php if($settings['group_sort'] == 'asc') { echo 'selected="selected"'; } ?> value="asc">ascending</option>
							<option <?php if($settings['group_sort'] == 'desc') { echo 'selected="selected"'; } ?> value="desc">descending</option>
						</select>
						on 
						<select name="blogrollPosts_group_sort_field" id="blogrollPosts_group_sort_field">
							<option <?php if($settings['group_sort_field'] == 'linkname') { echo 'selected="selected"'; } ?> value="linkname">Link Name (from Blogroll)</option>
							<option <?php if($settings['group_sort_field'] == 'linkurl') { echo 'selected="selected"'; } ?> value="linkurl">URL (from Blogroll)</option>
							<option <?php if($settings['group_sort_field'] == 'author') { echo 'selected="selected"'; } ?> value="author">Author (from RSS)</option>
							<option <?php if($settings['group_sort_field'] == 'blogtitle') { echo 'selected="selected"'; } ?> value="blogtitle">Blog Title (from RSS)</option>
							<option <?php if($settings['group_sort_field'] == 'blogurl') { echo 'selected="selected"'; } ?> value="blogurl">Blog URL (from RSS)</option>
						</select>
						(only used with ascending and descending)
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="blogrollPosts_before_group">Before Group</label>
						<br /><em style="font-size:11px"><a href="#blogrollPosts_metatags">metatag info</a> below</em>
					</th>
					<td><textarea name="blogrollPosts_before_group" id="blogrollPosts_before_group" rows="5" cols="80" style="font-family:monospace;font-size:11px"><?=stripslashes($settings['before_group']); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="blogrollPosts_after_group">After Group</label>
						<br /><em style="font-size:11px"><a href="#blogrollPosts_metatags">metatag info</a> below</em>
					</th>
					<td><textarea name="blogrollPosts_after_group" id="blogrollPosts_after_group" rows="5" cols="80" style="font-family:monospace;font-size:11px"><?=stripslashes($settings['after_group']); ?></textarea></td>
				</tr>
			</table>
		</fieldset>

		<fieldset id="debugging">
			<h3>Debugging</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="blogrollPosts_errors">Show Errors?</label></th>
					<td><input name="blogrollPosts_errors" type="checkbox" id="blogrollPosts_errors" <?php if($settings['errors']) { echo 'checked="checked"'; } ?> /></td>
				</tr>
			</table>
		</fieldset>

		<div class="submit">
			<input type="submit" name="save_blogrollPosts_settings" value="<?php _e('Save Settings') ?>" class="button-primary" />
		</div>
	</form>

	<hr />

	<h3 id="blogrollPosts_metatags">Output Metatags</h3>
	<p>
		You can use the metatags below in your template code to insert the data you'd like to output.
	</p>
	<ul>
		<li><code>%permalink%</code> Permalink to Post</li>
		<li><code>%title%</code> Post Title</li>
		<li><code>%author%</code> Author of Post</li>
		<li><code>%date%</code> Date Posted</li>
		<li><code>%blogurl%</code> Main Blog URL <em>from RSS Feed</em></li>
		<li><code>%blogtitle%</code> Title of Blog <em>from RSS Feed</em></li>
		<li><code>%blogdesc%</code> Blog Description <em>from RSS Feed</em></li>
		<li><code>%linkurl%</code> Blog URL <em>from YOUR Blogroll</em></li>
		<li><code>%linkname%</code> Blog Name <em>from YOUR Blogroll</em></li>
		<li><code>%linkdesc%</code> Blog Description <em>from YOUR Blogroll</em></li>
	</ul>

	<hr />

	<h3>Manual Actions</h3>
	<form method="post" class="actions" style="margin-bottom: 20px">
		<input type="submit" class="button-primary" name="blogrollPosts_UpdateCache" value="Update Now" />
		<input type="submit" class="button-secondary" name="blogrollPosts_ResetRSSLinks" value="Update RSS URLs" />
		<input type="submit" class="button-secondary" name="blogrollPosts_ResetSchedule" value="Reset Scheduled Updates" />
		<input type="submit" class="button-secondary" name="blogrollPosts_ClearCache" value="Clear Cache (<?=$this->cache_size();?> posts)" onclick="if ( confirm('You are about to delete all cached posts.  You should initiate a manual update after this completes.\n  \'Cancel\' to stop, \'OK\' to delete.') ) { return true;}return false;" />
		<input type="submit" class="button" name="blogrollPosts_ResetPlugin" value="Reset Plugin" onclick="if ( confirm('You are about to delete all settings and caches for this plugin.\n  \'Cancel\' to stop, \'OK\' to delete.') ) { return true;}return false;" />
	</form>

	<hr />
<div id="poststuff" class="meta-box-sortables"> 
	<script> 
		jQuery(document).ready(function($) {
			$('.postbox').children('h3, .handlediv').click(function(){
				$(this).siblings('.inside').toggle();
			});
		});
	</script>
	<div class="postbox">
		<div class="handlediv" title="Click to toggle"> <br/></div>
		<h3 style="cursor:pointer">View Current Cache</h3>
		<div class="inside" style="display:none">
			<?php $this->output_cache(); ?>
		</div>
	</div>
<?php /*
	<div class="postbox">
		<div class="handlediv" title="Click to toggle"> <br/></div>
		<h3 style="cursor:pointer">Cron Schedule Debugging</h3>
		<div class="inside">
			<pre><?php print_r ( _get_cron_array() ); ?></pre>
		</div>
	</div>
*/ ?>
</div>

</div>