<?php

/* Global Variables */

global $wp_registered_sidebars, $wp_registered_widgets, $wp_registered_widget_controls;

$wp_registered_sidebars = array();
$wp_registered_widgets = array();
$wp_registered_widget_controls = array();

/* Template tags & API functions */

function register_sidebars($number = 1, $args = array()) {
	global $wp_registered_sidebars;
	$number = (int) $number;

	if ( is_string($args) )
		parse_str($args, $args);

	for ( $i=1; $i <= $number; $i++ ) {
		$_args = $args;

		if ( $number > 1 ) {
			$_args['name'] = isset($args['name']) ? sprintf($args['name'], $i) : sprintf(__('Sidebar %d'), $i);
		} else {
			$_args['name'] = isset($args['name']) ? $args['name'] : __('Sidebar');
		}

		if (isset($args['id'])) {
			$_args['id'] = $args['id'];
		} else {
			$n = count($wp_registered_sidebars);
			do {
				$n++;
				$_args['id'] = "sidebar-$n";
			} while (isset($wp_registered_sidebars[$_args['id']]));
		}

		register_sidebar($_args);
	}
}

function register_sidebar($args = array()) {
	global $wp_registered_sidebars;

	if ( is_string($args) )
		parse_str($args, $args);

	$i = count($wp_registered_sidebars) + 1;

	$defaults = array(
		'name' => sprintf(__('Sidebar %d'), $i ),
		'id' => "sidebar-$i",
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => "</li>\n",
		'before_title' => '<h2 class="widgettitle">',
		'after_title' => "</h2>\n",
	);

	$sidebar = array_merge($defaults, $args);

	$wp_registered_sidebars[$sidebar['id']] = $sidebar;

	return $sidebar['id'];
}

function unregister_sidebar( $name ) {
	global $wp_registered_sidebars;

	if ( isset( $wp_registered_sidebars[$name] ) )
		unset( $wp_registered_sidebars[$name] );
}

function register_sidebar_widget($name, $output_callback, $classname = '') {
	// Compat
	if ( is_array($name) ) {
		if ( count($name) == 3 )
			$name = sprintf($name[0], $name[2]);
		else
			$name = $name[0];
	}

	$id = sanitize_title($name);
	$options = array();
	if ( !empty($classname) && is_string($classname) )
		$options['classname'] = $classname;
	$params = array_slice(func_get_args(), 2);
	$args = array($id, $name, $output_callback, $options);
	if ( !empty($params) )
		$args = array_merge($args, $params);

	call_user_func_array('wp_register_sidebar_widget', $args);
}

function wp_register_sidebar_widget($id, $name, $output_callback, $options = array()) {
	global $wp_registered_widgets;

	if ( empty($output_callback) ) {
		unset($wp_registered_widgets[$id]);
		return;
	}

	$defaults = array('classname' => $output_callback);
	$options = wp_parse_args($options, $defaults);
	$widget = array(
		'name' => $name,
		'id' => $id,
		'callback' => $output_callback,
		'params' => array_slice(func_get_args(), 4)
	);
	$widget = array_merge($widget, $options);

	if ( is_callable($output_callback) && ( !isset($wp_registered_widgets[$id]) || did_action( 'widgets_init' ) ) )
		$wp_registered_widgets[$id] = $widget;
}

function wp_widget_description( $id ) {
	if ( !is_scalar($id) )
		return;

	global $wp_registered_widgets;

	if ( isset($wp_registered_widgets[$id]['description']) )
		return wp_specialchars( $wp_registered_widgets[$id]['description'] );
}

function unregister_sidebar_widget($id) {
	return wp_unregister_sidebar_widget($id);
}

function wp_unregister_sidebar_widget($id) {
	wp_register_sidebar_widget($id, '', '');
	wp_unregister_widget_control($id);
}

function register_widget_control($name, $control_callback, $width = '', $height = '') {
	// Compat
	if ( is_array($name) ) {
		if ( count($name) == 3 )
			$name = sprintf($name[0], $name[2]);
		else
			$name = $name[0];
	}

	$id = sanitize_title($name);
	$options = array();
	if ( !empty($width) )
		$options['width'] = $width;
	if ( !empty($height) )
		$options['height'] = $height;
	$params = array_slice(func_get_args(), 4);
	$args = array($id, $name, $control_callback, $options);
	if ( !empty($params) )
		$args = array_merge($args, $params);

	call_user_func_array('wp_register_widget_control', $args);
}

/* $options: height, width, id_base
 *   height: never used
 *   width:  width of fully expanded control form.  Try hard to use the default width.
 *   id_base: for widgets which allow multiple instances (such as the text widget), an id_base must be provided.
 *            the widget id will ennd up looking like {$id_base}-{$unique_number}
 */
function wp_register_widget_control($id, $name, $control_callback, $options = array()) {
	global $wp_registered_widget_controls;

	if ( empty($control_callback) ) {
		unset($wp_registered_widget_controls[$id]);
		return;
	}

	if ( isset($wp_registered_widget_controls[$id]) && !did_action( 'widgets_init' ) )
		return;

	$defaults = array('width' => 250, 'height' => 200 ); // height is never used
	$options = wp_parse_args($options, $defaults);
	$options['width'] = (int) $options['width'];
	$options['height'] = (int) $options['height'];

	$widget = array(
		'name' => $name,
		'id' => $id,
		'callback' => $control_callback,
		'params' => array_slice(func_get_args(), 4)
	);
	$widget = array_merge($widget, $options);

	$wp_registered_widget_controls[$id] = $widget;
}

function unregister_widget_control($id) {
	return wp_unregister_widget_control($id);
}

function wp_unregister_widget_control($id) {
	return wp_register_widget_control($id, '', '');
}

function dynamic_sidebar($index = 1) {
	global $wp_registered_sidebars, $wp_registered_widgets;

	if ( is_int($index) ) {
		$index = "sidebar-$index";
	} else {
		$index = sanitize_title($index);
		foreach ( $wp_registered_sidebars as $key => $value ) {
			if ( sanitize_title($value['name']) == $index ) {
				$index = $key;
				break;
			}
		}
	}

	$sidebars_widgets = wp_get_sidebars_widgets();

	if ( empty($wp_registered_sidebars[$index]) || !array_key_exists($index, $sidebars_widgets) || !is_array($sidebars_widgets[$index]) || empty($sidebars_widgets[$index]) )
		return false;

	$sidebar = $wp_registered_sidebars[$index];

	$did_one = false;
	foreach ( $sidebars_widgets[$index] as $id ) {
		$callback = $wp_registered_widgets[$id]['callback'];

		$params = array_merge(array($sidebar), (array) $wp_registered_widgets[$id]['params']);

		// Substitute HTML id and class attributes into before_widget
		$classname_ = '';
		foreach ( (array) $wp_registered_widgets[$id]['classname'] as $cn ) {
			if ( is_string($cn) )
				$classname_ .= '_' . $cn;
			elseif ( is_object($cn) )
				$classname_ .= '_' . get_class($cn);
		}
		$classname_ = ltrim($classname_, '_');
		$params[0]['before_widget'] = sprintf($params[0]['before_widget'], $id, $classname_);

		if ( is_callable($callback) ) {
			call_user_func_array($callback, $params);
			$did_one = true;
		}
	}

	return $did_one;
}

/* @return mixed false if widget is not active or id of sidebar in which the widget is active
 */
function is_active_widget($callback) {
	global $wp_registered_widgets;

	$sidebars_widgets = wp_get_sidebars_widgets(false);

	if ( is_array($sidebars_widgets) ) foreach ( $sidebars_widgets as $sidebar => $widgets )
		if ( is_array($widgets) ) foreach ( $widgets as $widget )
			if ( $wp_registered_widgets[$widget]['callback'] == $callback )
				return $sidebar;

	return false;
}

function is_dynamic_sidebar() {
	global $wp_registered_widgets, $wp_registered_sidebars;
	$sidebars_widgets = get_option('sidebars_widgets');
	foreach ( $wp_registered_sidebars as $index => $sidebar ) {
		if ( count($sidebars_widgets[$index]) ) {
			foreach ( $sidebars_widgets[$index] as $widget )
				if ( array_key_exists($widget, $wp_registered_widgets) )
					return true;
		}
	}
	return false;
}

/* Internal Functions */

function wp_get_sidebars_widgets($update = true) {
	global $wp_registered_widgets, $wp_registered_sidebars;

	$sidebars_widgets = get_option('sidebars_widgets');
	$_sidebars_widgets = array();

	if ( !isset($sidebars_widgets['array_version']) )
		$sidebars_widgets['array_version'] = 1;

	switch ( $sidebars_widgets['array_version'] ) {
		case 1 :
			foreach ( $sidebars_widgets as $index => $sidebar )
			if ( is_array($sidebar) )
			foreach ( $sidebar as $i => $name ) {
				$id = strtolower($name);
				if ( isset($wp_registered_widgets[$id]) ) {
					$_sidebars_widgets[$index][$i] = $id;
					continue;
				}
				$id = sanitize_title($name);
				if ( isset($wp_registered_widgets[$id]) ) {
					$_sidebars_widgets[$index][$i] = $id;
					continue;
				}
				unset($_sidebars_widgets[$index][$i]);
			}
			$_sidebars_widgets['array_version'] = 2;
			$sidebars_widgets = $_sidebars_widgets;
			unset($_sidebars_widgets);

		case 2 :
			$sidebars = array_keys( $wp_registered_sidebars );
			if ( !empty( $sidebars ) ) {
				// Move the known-good ones first
				foreach ( $sidebars as $id ) {
					if ( array_key_exists( $id, $sidebars_widgets ) ) {
						$_sidebars_widgets[$id] = $sidebars_widgets[$id];
						unset($sidebars_widgets[$id], $sidebars[$id]);
					}
				}

				// Assign to each unmatched registered sidebar the first available orphan
				unset( $sidebars_widgets[ 'array_version' ] );
				while ( ( $sidebar = array_shift( $sidebars ) ) && $widgets = array_shift( $sidebars_widgets ) )
					$_sidebars_widgets[ $sidebar ] = $widgets;

				$_sidebars_widgets['array_version'] = 3;
				$sidebars_widgets = $_sidebars_widgets;
				unset($_sidebars_widgets);
			}

			if ( $update )
				update_option('sidebars_widgets', $sidebars_widgets);
	}

	unset($sidebars_widgets['array_version']);

	return $sidebars_widgets;
}

function wp_set_sidebars_widgets( $sidebars_widgets ) {
	update_option( 'sidebars_widgets', $sidebars_widgets );
}

function wp_get_widget_defaults() {
	global $wp_registered_sidebars;

	$defaults = array();

	foreach ( $wp_registered_sidebars as $index => $sidebar )
		$defaults[$index] = array();

	return $defaults;
}

/* Default Widgets */

function wp_widget_pages( $args ) {
	extract( $args );
	$options = get_option( 'widget_pages' );

	$title = empty( $options['title'] ) ? __( 'Pages' ) : $options['title'];
	$sortby = empty( $options['sortby'] ) ? 'menu_order' : $options['sortby'];
	$exclude = empty( $options['exclude'] ) ? '' : $options['exclude'];

	if ( $sortby == 'menu_order' ) {
		$sortby = 'menu_order, post_title';
	}

	$out = wp_list_pages( array('title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude) );

	if ( !empty( $out ) ) {
?>
	<?php echo $before_widget; ?>
		<?php echo $before_title . $title . $after_title; ?>
		<ul>
			<?php echo $out; ?>
		</ul>
	<?php echo $after_widget; ?>
<?php
	}
}

function wp_widget_pages_control() {
	$options = $newoptions = get_option('widget_pages');
	if ( $_POST['pages-submit'] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST['pages-title']));

		$sortby = stripslashes( $_POST['pages-sortby'] );

		if ( in_array( $sortby, array( 'post_title', 'menu_order', 'ID' ) ) ) {
			$newoptions['sortby'] = $sortby;
		} else {
			$newoptions['sortby'] = 'menu_order';
		}

		$newoptions['exclude'] = strip_tags( stripslashes( $_POST['pages-exclude'] ) );
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_pages', $options);
	}
	$title = attribute_escape($options['title']);
	$exclude = attribute_escape( $options['exclude'] );
?>
		<p><label for="pages-title"><?php _e('Title:'); ?> <input class="widefat" id="pages-title" name="pages-title" type="text" value="<?php echo $title; ?>" /></label></p>
		<p>
			<label for="pages-sortby"><?php _e( 'Sort by:' ); ?>
				<select name="pages-sortby" id="pages-sortby" class="widefat">
					<option value="post_title"<?php selected( $options['sortby'], 'post_title' ); ?>><?php _e('Page title'); ?></option>
					<option value="menu_order"<?php selected( $options['sortby'], 'menu_order' ); ?>><?php _e('Page order'); ?></option>
					<option value="ID"<?php selected( $options['sortby'], 'ID' ); ?>><?php _e( 'Page ID' ); ?></option>
				</select>
			</label>
		</p>
		<p>
			<label for="pages-exclude"><?php _e( 'Exclude:' ); ?> <input type="text" value="<?php echo $exclude; ?>" name="pages-exclude" id="pages-exclude" class="widefat" /></label>
			<br />
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>
		<input type="hidden" id="pages-submit" name="pages-submit" value="1" />
<?php
}

function wp_widget_links($args) {
	extract($args, EXTR_SKIP);
	wp_list_bookmarks(array(
		'title_before' => $before_title, 'title_after' => $after_title,
		'category_before' => $before_widget, 'category_after' => $after_widget,
		'show_images' => true, 'class' => 'linkcat widget'
	));
}

function wp_widget_search($args) {
	extract($args);
?>
		<?php echo $before_widget; ?>
			<form id="searchform" method="get" action="<?php bloginfo('home'); ?>">
			<div>
			<input type="text" name="s" id="s" size="15" /><br />
			<input type="submit" value="<?php echo attribute_escape(__('Search')); ?>" />
			</div>
			</form>
		<?php echo $after_widget; ?>
<?php
}

function wp_widget_archives($args) {
	extract($args);
	$options = get_option('widget_archives');
	$c = $options['count'] ? '1' : '0';
	$d = $options['dropdown'] ? '1' : '0';
	$title = empty($options['title']) ? __('Archives') : $options['title'];

	echo $before_widget;
	echo $before_title . $title . $after_title;

	if($d) {
?>
		<select name="archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'> <option value=""><?php echo attribute_escape(__('Select Month')); ?></option> <?php wp_get_archives("type=monthly&format=option&show_post_count=$c"); ?> </select>
<?php
	} else {
?>
		<ul>
		<?php wp_get_archives("type=monthly&show_post_count=$c"); ?>
		</ul>
<?php
	}

	echo $after_widget;
}

function wp_widget_archives_control() {
	$options = $newoptions = get_option('widget_archives');
	if ( $_POST["archives-submit"] ) {
		$newoptions['count'] = isset($_POST['archives-count']);
		$newoptions['dropdown'] = isset($_POST['archives-dropdown']);
		$newoptions['title'] = strip_tags(stripslashes($_POST["archives-title"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_archives', $options);
	}
	$count = $options['count'] ? 'checked="checked"' : '';
	$dropdown = $options['dropdown'] ? 'checked="checked"' : '';
	$title = attribute_escape($options['title']);
?>
			<p><label for="archives-title"><?php _e('Title:'); ?> <input class="widefat" id="archives-title" name="archives-title" type="text" value="<?php echo $title; ?>" /></label></p>
			<p>
				<label for="archives-count"><input class="checkbox" type="checkbox" <?php echo $count; ?> id="archives-count" name="archives-count" /> <?php _e('Show post counts'); ?></label>
				<br />
				<label for="archives-dropdown"><input class="checkbox" type="checkbox" <?php echo $dropdown; ?> id="archives-dropdown" name="archives-dropdown" /> <?php _e('Display as a drop down'); ?></label>
			</p>
			<input type="hidden" id="archives-submit" name="archives-submit" value="1" />
<?php
}

function wp_widget_meta($args) {
	extract($args);
	$options = get_option('widget_meta');
	$title = empty($options['title']) ? __('Meta') : $options['title'];
?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<ul>
			<?php wp_register(); ?>
			<li><?php wp_loginout(); ?></li>
			<li><a href="<?php bloginfo('rss2_url'); ?>" title="<?php echo attribute_escape(__('Syndicate this site using RSS 2.0')); ?>"><?php _e('Entries <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
			<li><a href="<?php bloginfo('comments_rss2_url'); ?>" title="<?php echo attribute_escape(__('The latest comments to all posts in RSS')); ?>"><?php _e('Comments <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
			<li><a href="http://wordpress.org/" title="<?php echo attribute_escape(__('Powered by WordPress, state-of-the-art semantic personal publishing platform.')); ?>">WordPress.org</a></li>
			<?php wp_meta(); ?>
			</ul>
		<?php echo $after_widget; ?>
<?php
}
function wp_widget_meta_control() {
	$options = $newoptions = get_option('widget_meta');
	if ( $_POST["meta-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["meta-title"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_meta', $options);
	}
	$title = attribute_escape($options['title']);
?>
			<p><label for="meta-title"><?php _e('Title:'); ?> <input class="widefat" id="meta-title" name="meta-title" type="text" value="<?php echo $title; ?>" /></label></p>
			<input type="hidden" id="meta-submit" name="meta-submit" value="1" />
<?php
}

function wp_widget_calendar($args) {
	extract($args);
	$options = get_option('widget_calendar');
	$title = $options['title'];
	if ( empty($title) )
		$title = '&nbsp;';
	echo $before_widget . $before_title . $title . $after_title;
	echo '<div id="calendar_wrap">';
	get_calendar();
	echo '</div>';
	echo $after_widget;
}
function wp_widget_calendar_control() {
	$options = $newoptions = get_option('widget_calendar');
	if ( $_POST["calendar-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["calendar-title"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_calendar', $options);
	}
	$title = attribute_escape($options['title']);
?>
			<p><label for="calendar-title"><?php _e('Title:'); ?> <input class="widefat" id="calendar-title" name="calendar-title" type="text" value="<?php echo $title; ?>" /></label></p>
			<input type="hidden" id="calendar-submit" name="calendar-submit" value="1" />
<?php
}

// See large comment section at end of this file
function wp_widget_text($args, $widget_args = 1) {
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('widget_text');
	if ( !isset($options[$number]) )
		return;

	$title = $options[$number]['title'];
	$text = apply_filters( 'widget_text', $options[$number]['text'] );
?>
		<?php echo $before_widget; ?>
			<?php if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
			<div class="textwidget"><?php echo $text; ?></div>
		<?php echo $after_widget; ?>
<?php
}

function wp_widget_text_control($widget_args) {
	global $wp_registered_widgets;
	static $updated = false;

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('widget_text');
	if ( !is_array($options) )
		$options = array();

	if ( !$updated && !empty($_POST['sidebar']) ) {
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			if ( 'wp_widget_text' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['widget-text'] as $widget_number => $widget_text ) {
			$title = strip_tags(stripslashes($widget_text['title']));
			if ( current_user_can('unfiltered_html') )
				$text = stripslashes( $widget_text['text'] );
			else
				$text = stripslashes(wp_filter_post_kses( $widget_text['text'] ));
			$options[$widget_number] = compact( 'title', 'text' );
		}

		update_option('widget_text', $options);
		$updated = true;
	}

	if ( -1 == $number ) {
		$title = '';
		$text = '';
		$number = '%i%';
	} else {
		$title = attribute_escape($options[$number]['title']);
		$text = format_to_edit($options[$number]['text']);
	}
?>
		<p>
			<input class="widefat" id="text-title-<?php echo $number; ?>" name="widget-text[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
			<textarea class="widefat" rows="16" cols="20" id="text-text-<?php echo $number; ?>" name="widget-text[<?php echo $number; ?>][text]"><?php echo $text; ?></textarea>
			<input type="hidden" id="text-submit-<?php echo $number; ?>" name="text-submit-<?php echo $number; ?>" value="1" />
		</p>
<?php
}

function wp_widget_text_register() {
	$options = get_option('widget_text');
	$widget_ops = array('classname' => 'widget_text', 'description' => __('Arbitrary text or HTML'));
	$control_ops = array('width' => 460, 'height' => 350, 'id_base' => 'text');
	$name = __('Text');

	// If there are none, we register the widget's existance with a generic template
	if ( !$options ) {
		wp_register_sidebar_widget( 'text-1', $name, 'wp_widget_text', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'text-1', $name, 'wp_widget_text_control', $control_ops, array( 'number' => -1 ) );
	}

	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) || !isset($options[$o]['text']) )
			continue;
		$id = "text-$o"; // Never never never translate an id
		wp_register_sidebar_widget($id, $name, 'wp_widget_text', $widget_ops, array( 'number' => $o ));
		wp_register_widget_control($id, $name, 'wp_widget_text_control', $control_ops, array( 'number' => $o ));
	}
}

// See large comment section at end of this file
function wp_widget_categories($args, $widget_args = 1) {
	extract($args, EXTR_SKIP);
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract($widget_args, EXTR_SKIP);

	$options = get_option('widget_categories');
	if ( !isset($options[$number]) )
		return;

	$c = $options[$number]['count'] ? '1' : '0';
	$h = $options[$number]['hierarchical'] ? '1' : '0';
	$d = $options[$number]['dropdown'] ? '1' : '0';

	$title = empty($options[$number]['title']) ? __('Categories') : $options[$number]['title'];

	echo $before_widget;
	echo $before_title . $title . $after_title;

	$cat_args = "orderby=name&show_count={$c}&hierarchical={$h}";

	if ( $d ) {
		wp_dropdown_categories($cat_args . '&show_option_none= ' . __('Select Category'));
?>

<script type='text/javascript'>
/* <![CDATA[ */
    var dropdown = document.getElementById("cat");
    function onCatChange() {
		if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo get_option('home'); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
		}
    }
    dropdown.onchange = onCatChange;
/* ]]> */
</script>

<?php
	} else {
?>
		<ul>
		<?php wp_list_categories($cat_args . '&title_li='); ?>
		</ul>
<?php
	}

	echo $after_widget;
}

function wp_widget_categories_control( $widget_args ) {
	global $wp_registered_widgets;
	static $updated = false;

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract($widget_args, EXTR_SKIP);

	$options = get_option('widget_categories');

	if ( !is_array( $options ) )
		$options = array();

	if ( !$updated && !empty($_POST['sidebar']) ) {
		$sidebar = (string) $_POST['sidebar'];
		
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();
		
		foreach ( $this_sidebar as $_widget_id ) {
			if ( 'wp_widget_categories' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				unset($options[$widget_number]);
			}   
		}

		foreach ( (array) $_POST['widget-categories'] as $widget_number => $widget_cat ) {
			$title = trim(strip_tags(stripslashes($widget_cat['title'])));
			$count = isset($widget_cat['count']);
			$hierarchical = isset($widget_cat['hierarchical']);
			$dropdown = isset($widget_cat['dropdown']);
			$options[$widget_number] = compact( 'title', 'count', 'hierarchical', 'dropdown' );
		}

		update_option('widget_categories', $options);
		$updated = true;
	}

	if ( -1 == $number ) {
		$title = '';
		$count = false;
		$hierarchical = false;
		$dropdown = false;
		$number = '%i%';
	} else {
		$title = attribute_escape( $options[$number]['title'] );
		$count = (bool) $options[$number]['count'];
		$hierarchical = (bool) $options[$number]['hierarchical'];
		$dropdown = (bool) $options[$number]['dropdown'];
	}
?>
			<p>
				<label for="categories-title-<?php echo $number; ?>">
					<?php _e( 'Title:' ); ?> 
					<input class="widefat" id="categories-title-<?php echo $number; ?>" name="widget-categories[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>

			<p>
				<label for="categories-dropdown-<?php echo $number; ?>">
					<input type="checkbox" class="checkbox" id="categories-dropdown-<?php echo $number; ?>" name="widget-categories[<?php echo $number; ?>][dropdown]"<?php checked( $dropdown, true ); ?> />
					<?php _e( 'Show as dropdown' ); ?>
				</label>
				<br />
				<label for="categories-count-<?php echo $number; ?>">
					<input type="checkbox" class="checkbox" id="categories-count-<?php echo $number; ?>" name="widget-categories[<?php echo $number; ?>][count]"<?php checked( $count, true ); ?> />
					<?php _e( 'Show post counts' ); ?>
				</label>
				<br />
				<label for="categories-hierarchical-<?php echo $number; ?>">
					<input type="checkbox" class="checkbox" id="categories-hierarchical-<?php echo $number; ?>" name="widget-categories[<?php echo $number; ?>][hierarchical]"<?php checked( $hierarchical, true ); ?> />
					<?php _e( 'Show hierarchy' ); ?>
				</label>
			</p>

			<input type="hidden" id="categories-submit-<?php echo $number; ?>" name="categories-submit-<?php echo $number; ?>" value="1" />
<?php
}

function wp_widget_categories_register() {
	$options = get_option( 'widget_categories' );
	if ( isset($options['title']) )
		$options = wp_widget_categories_upgrade();

	$widget_ops = array( 'classname' => 'widget_categories', 'description' => __( "A list or dropdown of categories" ) );

	$name = __( 'Categories' );

	// If there are none, we register the widget's existance with a generic template
	if ( !$options ) {
		wp_register_sidebar_widget( 'categories-1', $name, 'wp_widget_categories', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'categories-1', $name, 'wp_widget_categories_control', array( 'id_base' => 'categories' ), array( 'number' => -1 ) );
	}

	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) )
			continue;
		$id = "categories-$o";
		wp_register_sidebar_widget( $id, $name, 'wp_widget_categories', $widget_ops, array( 'number' => $o ) );
		wp_register_widget_control( $id, $name, 'wp_widget_categories_control', array( 'id_base' => 'categories' ), array( 'number' => $o ) );
	}

}

function wp_widget_categories_upgrade() {
	$options = get_option( 'widget_categories' );

	if ( !isset( $options['title'] ) )
		return $options;

	$newoptions = array( 1 => $options );

	update_option( 'widget_categories', $newoptions );

	$sidebars_widgets = get_option( 'sidebars_widgets' );
	if ( is_array( $sidebars_widgets ) ) {
		foreach ( $sidebars_widgets as $sidebar => $widgets ) {
			if ( is_array( $widgets ) ) {
				foreach ( $widgets as $widget )
					$new_widgets[$sidebar][] = ( $widget == 'categories' ) ? 'categories-1' : $widget;
			} else {
				$new_widgets[$sidebar] = $widgets;
			}
		}
		if ( $new_widgets != $sidebars_widgets )
			update_option( 'sidebars_widgets', $new_widgets );
	}

	return $newoptions;
}

function wp_widget_recent_entries($args) {
	if ( $output = wp_cache_get('widget_recent_entries') )
		return print($output);

	ob_start();
	extract($args);
	$options = get_option('widget_recent_entries');
	$title = empty($options['title']) ? __('Recent Posts') : $options['title'];
	if ( !$number = (int) $options['number'] )
		$number = 10;
	else if ( $number < 1 )
		$number = 1;
	else if ( $number > 15 )
		$number = 15;

	$r = new WP_Query("showposts=$number&what_to_show=posts&nopaging=0&post_status=publish");
	if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<ul>
			<?php  while ($r->have_posts()) : $r->the_post(); ?>
			<li><a href="<?php the_permalink() ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?> </a></li>
			<?php endwhile; ?>
			</ul>
		<?php echo $after_widget; ?>
<?php
	endif;
	wp_cache_add('widget_recent_entries', ob_get_flush());
}

function wp_flush_widget_recent_entries() {
	wp_cache_delete('widget_recent_entries');
}

add_action('save_post', 'wp_flush_widget_recent_entries');
add_action('deleted_post', 'wp_flush_widget_recent_entries');

function wp_widget_recent_entries_control() {
	$options = $newoptions = get_option('widget_recent_entries');
	if ( $_POST["recent-entries-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["recent-entries-title"]));
		$newoptions['number'] = (int) $_POST["recent-entries-number"];
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_recent_entries', $options);
		wp_flush_widget_recent_entries();
	}
	$title = attribute_escape($options['title']);
	if ( !$number = (int) $options['number'] )
		$number = 5;
?>

			<p><label for="recent-entries-title"><?php _e('Title:'); ?> <input class="widefat" id="recent-entries-title" name="recent-entries-title" type="text" value="<?php echo $title; ?>" /></label></p>
			<p>
				<label for="recent-entries-number"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="recent-entries-number" name="recent-entries-number" type="text" value="<?php echo $number; ?>" /></label>
				<br />
				<small><?php _e('(at most 15)'); ?></small>
			</p>
			<input type="hidden" id="recent-entries-submit" name="recent-entries-submit" value="1" />
<?php
}

function wp_widget_recent_comments($args) {
	global $wpdb, $comments, $comment;
	extract($args, EXTR_SKIP);
	$options = get_option('widget_recent_comments');
	$title = empty($options['title']) ? __('Recent Comments') : $options['title'];
	if ( !$number = (int) $options['number'] )
		$number = 5;
	else if ( $number < 1 )
		$number = 1;
	else if ( $number > 15 )
		$number = 15;

	if ( !$comments = wp_cache_get( 'recent_comments', 'widget' ) ) {
		$comments = $wpdb->get_results("SELECT comment_author, comment_author_url, comment_ID, comment_post_ID FROM $wpdb->comments WHERE comment_approved = '1' ORDER BY comment_date_gmt DESC LIMIT $number");
		wp_cache_add( 'recent_comments', $comments, 'widget' );
	}
?>

		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<ul id="recentcomments"><?php
			if ( $comments ) : foreach ($comments as $comment) :
			echo  '<li class="recentcomments">' . sprintf(__('%1$s on %2$s'), get_comment_author_link(), '<a href="'. get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>';
			endforeach; endif;?></ul>
		<?php echo $after_widget; ?>
<?php
}

function wp_delete_recent_comments_cache() {
	wp_cache_delete( 'recent_comments', 'widget' );
}
add_action( 'comment_post', 'wp_delete_recent_comments_cache' );
add_action( 'wp_set_comment_status', 'wp_delete_recent_comments_cache' );

function wp_widget_recent_comments_control() {
	$options = $newoptions = get_option('widget_recent_comments');
	if ( $_POST["recent-comments-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["recent-comments-title"]));
		$newoptions['number'] = (int) $_POST["recent-comments-number"];
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_recent_comments', $options);
		wp_delete_recent_comments_cache();
	}
	$title = attribute_escape($options['title']);
	if ( !$number = (int) $options['number'] )
		$number = 5;
?>
			<p><label for="recent-comments-title"><?php _e('Title:'); ?> <input class="widefat" id="recent-comments-title" name="recent-comments-title" type="text" value="<?php echo $title; ?>" /></label></p>
			<p>
				<label for="recent-comments-number"><?php _e('Number of comments to show:'); ?> <input style="width: 25px; text-align: center;" id="recent-comments-number" name="recent-comments-number" type="text" value="<?php echo $number; ?>" /></label>
				<br />
				<small><?php _e('(at most 15)'); ?></small>
			</p>
			<input type="hidden" id="recent-comments-submit" name="recent-comments-submit" value="1" />
<?php
}

function wp_widget_recent_comments_style() {
?>
<style type="text/css">.recentcomments a{display:inline !important;padding: 0 !important;margin: 0 !important;}</style>
<?php
}

function wp_widget_recent_comments_register() {
	$widget_ops = array('classname' => 'widget_recent_comments', 'description' => __( 'The most recent comments' ) );
	wp_register_sidebar_widget('recent-comments', __('Recent Comments'), 'wp_widget_recent_comments', $widget_ops);
	wp_register_widget_control('recent-comments', __('Recent Comments'), 'wp_widget_recent_comments_control');

	if ( is_active_widget('wp_widget_recent_comments') )
		add_action('wp_head', 'wp_widget_recent_comments_style');
}

// See large comment section at end of this file
function wp_widget_rss($args, $widget_args = 1) {
	extract($args, EXTR_SKIP);
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widegt_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract($widget_args, EXTR_SKIP);

	$options = get_option('widget_rss');

	if ( !isset($options[$number]) )
		return;

	if ( isset($options[$number]['error']) && $options[$number]['error'] )
		return;

	$num_items = (int) $options[$number]['items'];
	$show_summary = $options[$number]['show_summary'];
	if ( empty($num_items) || $num_items < 1 || $num_items > 10 ) $num_items = 10;
	$url = $options[$number]['url'];
	while ( strstr($url, 'http') != $url )
		$url = substr($url, 1);
	if ( empty($url) )
		return;

	require_once(ABSPATH . WPINC . '/rss.php');

	$rss = fetch_rss($url);
	$link = clean_url(strip_tags($rss->channel['link']));
	while ( strstr($link, 'http') != $link )
		$link = substr($link, 1);
	$desc = attribute_escape(strip_tags(html_entity_decode($rss->channel['description'], ENT_QUOTES)));
	$title = $options[$number]['title'];
	if ( empty($title) )
		$title = htmlentities(strip_tags($rss->channel['title']));
	if ( empty($title) )
		$title = $desc;
	if ( empty($title) )
		$title = __('Unknown Feed');
	$url = clean_url(strip_tags($url));
	if ( file_exists(dirname(__FILE__) . '/rss.png') )
		$icon = str_replace(ABSPATH, get_option('siteurl').'/', dirname(__FILE__)) . '/rss.png';
	else
		$icon = get_option('siteurl').'/wp-includes/images/rss.png';
	$title = "<a class='rsswidget' href='$url' title='" . attribute_escape(__('Syndicate this content')) ."'><img style='background:orange;color:white;border:none;' width='14' height='14' src='$icon' alt='RSS' /></a> <a class='rsswidget' href='$link' title='$desc'>$title</a>";
?>
		<?php echo $before_widget; ?>
			<?php $title ? print($before_title . $title . $after_title) : null; ?>
<?php
	if ( is_array( $rss->items ) && !empty( $rss->items ) ) {
		$rss->items = array_slice($rss->items, 0, $num_items);
		echo '<ul>';
		foreach ($rss->items as $item ) {
			while ( strstr($item['link'], 'http') != $item['link'] )
				$item['link'] = substr($item['link'], 1);
			$link = clean_url(strip_tags($item['link']));
			$title = attribute_escape(strip_tags($item['title']));
			if ( empty($title) )
				$title = __('Untitled');
			$desc = '';
			if ( $show_summary ) {
				$summary = '<div class="rssSummary">' . $item['description'] . '</div>';
			} else {
				if ( isset( $item['description'] ) && is_string( $item['description'] ) )
					$desc = str_replace(array("\n", "\r"), ' ', attribute_escape(strip_tags(html_entity_decode($item['description'], ENT_QUOTES))));
				$summary = '';
			}
			echo "<li><a class='rsswidget' href='$link' title='$desc'>$title</a>$summary</li>";
		}
		echo '</ul>';
	} else {
		echo '<ul><li>' . __( 'An error has occurred; the feed is probably down. Try again later.' ) . '</li></ul>';
	}

	echo $after_widget;
}

function wp_widget_rss_control($widget_args) {
	global $wp_registered_widgets;
	static $updated = false;

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract($widget_args, EXTR_SKIP);

	$options = get_option('widget_rss');
	if ( !is_array($options) )
		$options = array();

	$urls = array();
	foreach ( $options as $option )
		if ( isset($option['url']) )
			$urls[$option['url']] = true;

	if ( !$updated && !empty($_POST['sidebar']) ) {
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();
	
		foreach ( $this_sidebar as $_widget_id ) {
			if ( 'wp_widget_rss' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				unset($options[$widget_number]);
			}
		}

		foreach( (array) $_POST['widget-rss'] as $widget_number => $widget_rss ) {
			$items = (int) $widget_rss['items'];
			if ( $items < 1 )
				$items = 10;
			$url = sanitize_url(strip_tags(stripslashes($widget_rss['url'])));
			$title = trim(strip_tags(stripslashes($widget_rss['title'])));

			if ( !isset($urls[$url]) ) {
				require_once(ABSPATH . WPINC . '/rss.php');
				$rss = fetch_rss($url);
				$error = false;
				if ( !is_object($rss) ) {
					$url = wp_specialchars(__('Error: could not find an RSS or ATOM feed at that URL.'), 1);
					$error = sprintf(__('Error in RSS %1$d'), $widget_number );
				}
			}
			$options[$widget_number] = compact( 'title', 'url', 'items', 'error' );
		}

		update_option('widget_rss', $options);
		$updated = true;
	}

	if ( -1 == $number ) {
		$title = '';
		$url = '';
		$items = 10;
		$error = false;
		$number = '%i%';
	} else {
		$title = attribute_escape($options[$number]['title']);
		$url = attribute_escape($options[$number]['url']);
		$items = (int) $options[$number]['items'];
		if ( $items < 1 )
			$items = 10;
		$error = $options[$number]['error'];
	}

?>
			<p>
				<label for="rss-url-<?php echo $number; ?>"><?php _e('Enter the RSS feed URL here:'); ?>
					<input class="widefat" id="rss-url-<?php echo $number; ?>" name="widget-rss[<?php echo $number; ?>][url]" type="text" value="<?php echo $url; ?>" />
				</label>
			</p>
			<p>
				<label for="rss-title-<?php echo $number; ?>"><?php _e('Give the feed a title (optional):'); ?>
					<input class="widefat" id="rss-title-<?php echo $number; ?>" name="widget-rss[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>
			<p>
				<label for="rss-items-<?php echo $number; ?>"><?php _e('How many items would you like to display?'); ?>
					<select id="rss-items-<?php echo $number; ?>" name="widget-rss[<?php echo $number; ?>][items]">
						<?php
							for ( $i = 1; $i <= 10; ++$i )
								echo "<option value='$i' " . ( $items == $i ? "selected='selected'" : '' ) . ">$i</option>";
						?>
					</select>
				</label>
			</p>
			<input type="hidden" id="rss-submit-<?php echo $number; ?>" name="rss-submit-<?php echo $number; ?>" value="1" />
<?php
}

function wp_widget_rss_register() {
	$options = get_option('widget_rss');
	$widget_ops = array('classname' => 'widget_rss', 'description' => __( 'Entries from any RSS or Atom feed' ));
	$control_ops = array('width' => 410, 'height' => 200, 'id_base' => 'rss');
	$name = __('RSS');

	// If there are none, we register the widget's existance with a generic template
	if ( !$options ) {
		wp_register_sidebar_widget( 'rss-1', $name, 'wp_widget_rss', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'rss-1', $name, 'wp_widget_rss_control', $control_ops, array( 'number' => -1 ) );
	}

	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['url']) || !isset($options[$o]['title']) || !isset($options[$o]['items']) )
			contine;
		$id = "rss-$o"; // Never never never translate an id
		wp_register_sidebar_widget($id, $name, 'wp_widget_rss', $widget_ops, array( 'number' => $o ));
		wp_register_widget_control($id, $name, 'wp_widget_rss_control', $control_ops, array( 'number' => $o ));
	}
}

function wp_widget_tag_cloud($args) {
	extract($args);
	$options = get_option('widget_tag_cloud');
	$title = empty($options['title']) ? __('Tags') : $options['title'];

	echo $before_widget;
	echo $before_title . $title . $after_title;
	wp_tag_cloud();
	echo $after_widget;
}

function wp_widget_tag_cloud_control() {
	$options = $newoptions = get_option('widget_tag_cloud');

	if ( $_POST['tag-cloud-submit'] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST['tag-cloud-title']));
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_tag_cloud', $options);
	}

	$title = attribute_escape( $options['title'] );
?>
	<p><label for="tag-cloud-title">
	<?php _e('Title:') ?> <input type="text" class="widefat" id="tag-cloud-title" name="tag-cloud-title" value="<?php echo $title ?>" /></label>
	</p>
	<input type="hidden" name="tag-cloud-submit" id="tag-cloud-submit" value="1" />
<?php
}

function wp_widgets_init() {
	if ( !is_blog_installed() )
		return;

	$widget_ops = array('classname' => 'widget_pages', 'description' => __( "Your blog's WordPress Pages") );
	wp_register_sidebar_widget('pages', __('Pages'), 'wp_widget_pages', $widget_ops);
	wp_register_widget_control('pages', __('Pages'), 'wp_widget_pages_control' );

	$widget_ops = array('classname' => 'widget_calendar', 'description' => __( "A calendar of your blog's posts") );
	wp_register_sidebar_widget('calendar', __('Calendar'), 'wp_widget_calendar', $widget_ops);
	wp_register_widget_control('calendar', __('Calendar'), 'wp_widget_calendar_control' );

	$widget_ops = array('classname' => 'widget_archive', 'description' => __( "A monthly archive of your blog's posts") );
	wp_register_sidebar_widget('archives', __('Archives'), 'wp_widget_archives', $widget_ops);
	wp_register_widget_control('archives', __('Archives'), 'wp_widget_archives_control' );

	$widget_ops = array('classname' => 'widget_links', 'description' => __( "Your blogroll") );
	wp_register_sidebar_widget('links', __('Links'), 'wp_widget_links', $widget_ops);

	$widget_ops = array('classname' => 'widget_meta', 'description' => __( "Log in/out, admin, feed and WordPress links") );
	wp_register_sidebar_widget('meta', __('Meta'), 'wp_widget_meta', $widget_ops);
	wp_register_widget_control('meta', __('Meta'), 'wp_widget_meta_control' );

	$widget_ops = array('classname' => 'widget_search', 'description' => __( "A search form for your blog") );
	wp_register_sidebar_widget('search', __('Search'), 'wp_widget_search', $widget_ops);

	$widget_ops = array('classname' => 'widget_recent_entries', 'description' => __( "The most recent posts on your blog") );
	wp_register_sidebar_widget('recent-posts', __('Recent Posts'), 'wp_widget_recent_entries', $widget_ops);
	wp_register_widget_control('recent-posts', __('Recent Posts'), 'wp_widget_recent_entries_control' );

	$widget_ops = array('classname' => 'widget_tag_cloud', 'description' => __( "Your most used tags in cloud format") );
	wp_register_sidebar_widget('tag_cloud', __('Tag Cloud'), 'wp_widget_tag_cloud', $widget_ops);
	wp_register_widget_control('tag_cloud', __('Tag Cloud'), 'wp_widget_tag_cloud_control' );

	wp_widget_categories_register();
	wp_widget_text_register();
	wp_widget_rss_register();
	wp_widget_recent_comments_register();

	do_action('widgets_init');
}

add_action('init', 'wp_widgets_init', 1);

/* Pattern for widget which allows multiple instances (such as the text widget)

// Displays widget on blag
// $widget_args: number
//    number: which of the several widgets of this type do we mean
function widget_many( $args, $widget_args = 1 ) {
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );
	
	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_many');
	if ( !isset($options[$number]) )
		return;

	echo $before_widget;

	// Do stuff for this widget, drawing data from $options[$number]

	echo $after_widget;
}

// Displays form for a particular instance of the widget.  Also updates the data after a POST submit
// $widget_args: number
//    number: which of the several widgets of this type do we mean
function widget_many_control( $widget_args = 1 ) {
	global $wp_registered_widgets;
	static $updated = false; // Whether or not we have already updated the data after a POST submit

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_many');
	if ( !is_array($options) )
		$options = array();

	// We need to update the data
	if ( !$updated && !empty($_POST['sidebar']) ) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if ( 'widget_many' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['widget-many'] as $widget_number => $widget_many_instance ) {
			// compile data from $widget_many_instance
			$something = wp_specialchars( $widget_many_instance['something'] );
			$options[$widget_number] = array( 'something' => $something );  // Even simple widgets should store stuff in array, rather than in scalar
		}

		update_option('widget_text', $options);

		$updated = true; // So that we don't go through this more than once
	}


	// Here we echo out the form
	if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS
		$something = '';
		$number = '%i%';
	} else {
		$something = attribute_escape($options[$number]['something']);
	}

	// The form has inputs with names like widget-many[$number][something] so that all data for that instance of
	// the widget are stored in one $_POST variable: $_POST['widget-many'][$number]
?>
		<p>
			<input class="widefat" id="widget-many-something-<?php echo $number; ?>" name="widget-many[<?php echo $number; ?>][something]" type="text" value="<?php echo $data; ?>" />
			<input type="hidden" id="widget-many-submit-<?php echo $number; ?>" name="widget-many-<?php echo $number; ?>" value="1" />
		</p>
<?php
}

// Registers each instance of our widget on startup
function widget_many_register() {
	$options = get_option('widget_many');
	$widget_ops = array('classname' => 'widget_many', 'description' => __('Widget which allows multiple instances'));
	$control_ops = array('width' => 400, 'height' => 350, 'id_base' => 'many');
	$name = __('Many');

	// If there are none, we register the widget's existance with a generic template
	if ( !$options ) {
		wp_register_sidebar_widget( 'many-1', $name, 'widget_many', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'many-1', $name, 'widget_many_control', $control_ops, array( 'number' => -1 ) );
	}

	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['something']) ) // we used 'something' above in our exampple.  Replace with with whatever your real data are.
			continue;

		// $id should look like {$id_base}-{$o}
		$id = "many-$o"; // Never never never translate an id
		wp_register_sidebar_widget( $id, $name, 'wp_widget_text', $widget_ops, array( 'number' => $o ) );
		wp_register_widget_control( $id, $name, 'wp_widget_text_control', $control_ops, array( 'number' => $o ) );
	}
}

// This is important
add_action( 'widgets_init', 'widget_many_register' )

*/

?>
