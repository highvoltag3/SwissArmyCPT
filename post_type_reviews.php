<?php
//reviews post type


function stp_reviews() {

	/**
	 * Enable the Reviews custom post type
	 * http://codex.wordpress.org/Function_Reference/register_post_type
	 */

	$labels = array(
		'name' => __( 'Reviews', 'stp_reviews' ),
		'singular_name' => __( 'Reviews Item', 'stp_reviews' ),
		'add_new' => __( 'Add New Review', 'stp_reviews' ),
		'add_new_item' => __( 'Add New Reviews Item', 'stp_reviews' ),
		'edit_item' => __( 'Edit Reviews Item', 'stp_reviews' ),
		'new_item' => __( 'Add New Reviews Item', 'stp_reviews' ),
		'view_item' => __( 'View Item', 'stp_reviews' ),
		'search_items' => __( 'Search Reviews', 'stp_reviews' ),
		'not_found' => __( 'No review items found', 'stp_reviews' ),
		'not_found_in_trash' => __( 'No review items found in trash', 'stp_reviews' )
	);

	$args = array(
    	'labels' => $labels,
    	'public' => true,
		'supports' => array( 'title', 'editor', 'thumbnail', 'comments', 'revisions', 'author' ),
		'capability_type' => 'post',
		'rewrite' => array("slug" => "review"), // Permalinks format
		'menu_position' => 5,
		'has_archive' => 'reviews-archive',
		'taxonomies' => array('post_tag')
	);

	register_post_type( 'review', $args );

	/**
	 * Register a taxonomy for Reviews Categories
	 * http://codex.wordpress.org/Function_Reference/register_taxonomy
	 */

    $taxonomy_review_category_labels = array(
		'name' => _x( 'Reviews Categories', 'stp_reviews' ),
		'singular_name' => _x( 'Reviews Category', 'stp_reviews' ),
		'search_items' => _x( 'Search Reviews Categories', 'stp_reviews' ),
		'popular_items' => _x( 'Popular Reviews Categories', 'stp_reviews' ),
		'all_items' => _x( 'All Reviews Categories', 'stp_reviews' ),
		'parent_item' => _x( 'Parent Reviews Category', 'stp_reviews' ),
		'parent_item_colon' => _x( 'Parent Reviews Category:', 'stp_reviews' ),
		'edit_item' => _x( 'Edit Reviews Category', 'stp_reviews' ),
		'update_item' => _x( 'Update Reviews Category', 'stp_reviews' ),
		'add_new_item' => _x( 'Add New Reviews Category', 'stp_reviews' ),
		'new_item_name' => _x( 'New Reviews Category Name', 'stp_reviews' ),
		'separate_items_with_commas' => _x( 'Separate review categories with commas', 'stp_reviews' ),
		'add_or_remove_items' => _x( 'Add or remove review categories', 'stp_reviews' ),
		'choose_from_most_used' => _x( 'Choose from the most used review categories', 'stp_reviews' ),
		'menu_name' => _x( 'Reviews Categories', 'stp_reviews' ),
    );

    $taxonomy_review_category_args = array(
		'labels' => $taxonomy_review_category_labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_ui' => true,
		'show_tagcloud' => true,
		'hierarchical' => true,
		'rewrite' => array( 'slug' => 'review-category' ),
		'query_var' => true
    );

    register_taxonomy( 'review_category', array( 'review' ), $taxonomy_review_category_args );

}

add_action( 'init', 'stp_reviews' );

// Allow thumbnails to be used on review post type

add_theme_support( 'post-thumbnails', array( 'review' ) );

/**
 * Add Columns to Reviews Edit Screen
 * http://wptheming.com/2010/07/column-edit-pages/
 */

function stp_reviews_edit_columns($review_columns){
	$review_columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"title" => _x('Title', 'column name'),
		"review_thumbnail" => __('Thumbnail', 'stp_reviews'),
		"review_category" => __('Category', 'stp_reviews'),
		"review_tag" => __('Tags', 'stp_reviews'),
		"author" => __('Author', 'stp_reviews'),
		"comments" => __('Comments', 'stp_reviews'),
		"date" => __('Date', 'stp_reviews'),
	);
	$review_columns['comments'] = '<div class="vers"><img alt="Comments" src="' . esc_url( admin_url( 'images/comment-grey-bubble.png' ) ) . '" /></div>';
	return $review_columns;
}

add_filter( 'manage_edit-review_columns', 'stp_reviews_edit_columns' );

function stp_reviews_columns_display($review_columns, $post_id){

	switch ( $review_columns )

	{
		case "review_thumbnail":
			$width = (int) 35;
			$height = (int) 35;
			$thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );

			// Display the featured image in the column view if possible
			if ($thumbnail_id) {
				$thumb = wp_get_attachment_image( $thumbnail_id, array($width, $height), true );
			}
			if ( isset($thumb) ) {
				echo $thumb;
			} else {
				echo __('None', 'stp_reviews');
			}
		break;

		// Display the review tags in the column view
		case "review_category":

			if ( $category_list = get_the_term_list( $post_id, 'review_category', '', ', ', '' ) ) {
				echo $category_list;
			} else {
				echo __('None', 'stp_reviews');
			}
		break;

		// Display the review tags in the column view
		case "review_tag":

			if ( $tag_list = get_the_term_list( $post_id, 'post_tag', '', ', ', '' ) ) {
				echo $tag_list;
			} else {
				echo __('None', 'stp_reviews');
			}
		break;
	}
}

add_action( 'manage_posts_custom_column',  'stp_reviews_columns_display', 10, 2 );

/**
 * Add Review count to "Right Now" Dashboard Widget
 */

function add_review_counts() {
        if ( ! post_type_exists( 'review' ) ) {
             return;
        }

        $num_posts = wp_count_posts( 'review' );
        $num = number_format_i18n( $num_posts->publish );
        $text = _n( 'Reviews Item', 'Reviews Items', intval($num_posts->publish) );
        if ( current_user_can( 'edit_posts' ) ) {
            $num = "<a href='edit.php?post_type=review'>$num</a>";
            $text = "<a href='edit.php?post_type=review'>$text</a>";
        }
        echo '<td class="first b b-review">' . $num . '</td>';
        echo '<td class="t review">' . $text . '</td>';
        echo '</tr>';

        if ($num_posts->pending > 0) {
            $num = number_format_i18n( $num_posts->pending );
            $text = _n( 'Reviews Item Pending', 'Reviews Items Pending', intval($num_posts->pending) );
            if ( current_user_can( 'edit_posts' ) ) {
                $num = "<a href='edit.php?post_status=pending&post_type=review'>$num</a>";
                $text = "<a href='edit.php?post_status=pending&post_type=review'>$text</a>";
            }
            echo '<td class="first b b-review">' . $num . '</td>';
            echo '<td class="t review">' . $text . '</td>';

            echo '</tr>';
        }
}

add_action( 'right_now_content_table_end', 'add_review_counts' );

/**
 * Add contextual help menu
 */

function stp_reviews_add_help_text( $contextual_help, $screen_id, $screen ) {
	if ( 'review' == $screen->id ) {
		$contextual_help =
		'<p>' . __('The title field and the big Post Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop, and can minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to unhide more boxes (Excerpt, Send Trackbacks, Custom Fields, Discussion, Slug, Author) or to choose a 1- or 2-column layout for this screen.') . '</p>' .
		'<p>' . __('<strong>Title</strong> - Enter a title for your post. After you enter a title, you&#8217;ll see the permalink below, which you can edit.') . '</p>' .
		'<p>' . __('<strong>Post editor</strong> - Enter the text for your post. There are two modes of editing: Visual and HTML. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The HTML mode allows you to enter raw HTML along with your post text. You can insert media files by clicking the icons above the post editor and following the directions. You can go the distraction-free writing screen, new in 3.2, via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in HTML mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular post editor.') . '</p>' .
		'<p>' . __('<strong>Publish</strong> - You can set the terms of publishing your post in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting a post or making it stay at the top of your blog indefinitely (sticky). Publish (immediately) allows you to set a future or past date and time, so you can schedule a post to be published in the future or backdate a post.') . '</p>' .
		( ( current_theme_supports( 'post-formats' ) && post_type_supports( 'post', 'post-formats' ) ) ? '<p>' . __( '<strong>Post Format</strong> - This designates how your theme will display a specific post. For example, you could have a <em>standard</em> blog post with a title and paragraphs, or a short <em>aside</em> that omits the title and contains a short text blurb. Please refer to the Codex for <a href="http://codex.wordpress.org/Post_Formats#Supported_Formats">descriptions of each post format</a>. Your theme could enable all or some of 10 possible formats.' ) . '</p>' : '' ) .
		'<p>' . __('<strong>Featured Image</strong> - This allows you to associate an image with your post without inserting it. This is usually useful only if your theme makes use of the featured image as a post thumbnail on the home page, a custom header, etc.') . '</p>' .
		'<p>' . __('<strong>Send Trackbacks</strong> - Trackbacks are a way to notify legacy blog systems that you&#8217;ve linked to them. Enter the URL(s) you want to send trackbacks. If you link to other WordPress sites they&#8217;ll be notified automatically using pingbacks, and this field is unnecessary.') . '</p>' .
		'<p>' . __('<strong>Discussion</strong> - You can turn comments and pings on or off, and if there are comments on the post, you can see them here and moderate them.') . '</p>' .
		'<p><strong>' . __('For more information:') . '</strong></p>' .
		'<p>' . __('<a href="http://codex.wordpress.org/Posts_Add_New_Screen" target="_blank">Documentation on Writing and Editing Posts</a>') . '</p>' .
		'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>';
  } elseif ( 'edit-review' == $screen->id ) {
    $contextual_help =
	    '<p>' . __('You can customize the display of this screen in a number of ways:') . '</p>' .
		'<ul>' .
		'<li>' . __('You can hide/display columns based on your needs and decide how many posts to list per screen using the Screen Options tab.') . '</li>' .
		'<li>' . __('You can filter the list of posts by post status using the text links in the upper left to show All, Published, Draft, or Trashed posts. The default view is to show all posts.') . '</li>' .
		'<li>' . __('You can view posts in a simple title list or with an excerpt. Choose the view you prefer by clicking on the icons at the top of the list on the right.') . '</li>' .
		'<li>' . __('You can refine the list to show only posts in a specific category or from a specific month by using the dropdown menus above the posts list. Click the Filter button after making your selection. You also can refine the list by clicking on the post author, category or tag in the posts list.') . '</li>' .
		'</ul>' .
		'<p>' . __('Hovering over a row in the posts list will display action links that allow you to manage your post. You can perform the following actions:') . '</p>' .
		'<ul>' .
		'<li>' . __('Edit takes you to the editing screen for that post. You can also reach that screen by clicking on the post title.') . '</li>' .
		'<li>' . __('Quick Edit provides inline access to the metadata of your post, allowing you to update post details without leaving this screen.') . '</li>' .
		'<li>' . __('Trash removes your post from this list and places it in the trash, from which you can permanently delete it.') . '</li>' .
		'<li>' . __('Preview will show you what your draft post will look like if you publish it. View will take you to your live site to view the post. Which link is available depends on your post&#8217;s status.') . '</li>' .
		'</ul>' .
		'<p>' . __('You can also edit multiple posts at once. Select the posts you want to edit using the checkboxes, select Edit from the Bulk Actions menu and click Apply. You will be able to change the metadata (categories, author, etc.) for all selected posts at once. To remove a post from the grouping, just click the x next to its name in the Bulk Edit area that appears.') . '</p>' .
		'<p><strong>' . __('For more information:') . '</strong></p>' .
		'<p>' . __('<a href="http://codex.wordpress.org/Posts_Screen" target="_blank">Documentation on Managing Posts</a>') . '</p>' .
		'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>';

  }
  return $contextual_help;
}

add_action( 'contextual_help', 'stp_reviews_add_help_text', 10, 3 );

function stp_reviews_register_settings() {
	register_setting('stp_reviews_settings', 'stp_reviews_settings', 'stp_reviews_settings_validate');
}
add_action('admin_init', 'stp_reviews_register_settings');

function stp_reviews_update_settings() {
	global $stp_reviews_settings, $stp_reviews_defaults;
	if ( isset($stp_reviews_settings['update']) ) {
		if ( !is_numeric($stp_reviews_settings['per_page'] ) || $stp_reviews_settings['per_page'] < 1 ) {
			echo '<div class="error fade" id="message"><p>The Entries Per Page setting must be a positive integer, value reset to default.</p></div>';
			$stp_reviews_settings['per_page'] = $stp_reviews_defaults['per_page'];
		}
		$stp_reviews_settings['per_page'] = min( 80, $stp_reviews_settings['per_page'] );
		echo '<div class="updated fade" id="message"><p>Custom Post Order settings '.$stp_reviews_settings['update'].'.</p></div>';
		unset($stp_reviews_settings['update']);
		update_option('stp_reviews_settings', $stp_reviews_settings);
	}
}
function stp_reviews_settings_validate($input) {
	$input['post'] = ($input['post'] == 1 ? 1 : 0);
	$args = array( 'public' => true, '_builtin' => false );
	$output = 'objects';
	$post_types = get_post_types( $args, $output );
	foreach ( $post_types as $post_type ) {
		$input[$post_type->name] = ($input[$post_type->name] == 1 ? 1 : 0);
	}
	$input['per_page'] = wp_filter_nohtml_kses($input['per_page']);
	return $input;
}

//we need some custom scripts here.
add_action( 'admin_print_scripts-post-new.php', 'stp_reviews_admin_script', 11 );
add_action( 'admin_print_scripts-post.php', 'stp_reviews_admin_script', 11 );

function stp_reviews_admin_script() {
    global $post_type;
    if( 'review' == $post_type ) :
    	wp_enqueue_script( 'select2-plugin', get_stylesheet_directory_uri() . '/js/select2.min.js' );
    	wp_enqueue_style( 'select2-plugin-css', get_stylesheet_directory_uri() . '/css/select2.css', array(), '1.0' );
    	wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri() . '/js/custom-js.js' );
    endif;
}


//Now that we are done with the posttype itself let's move to the metabox

//! Add the Reviews Meta Box
function add_review_metabox() {
    add_meta_box(
		'custom_meta_box', // $id
		__( 'Review details', 'slidetoplay' ), // $title
		'show_custom_meta_box', // $callback
		'review', // only to our posttype
		'normal', // $context
		'high'); // $priority
}
add_action('add_meta_boxes', 'add_review_metabox');

// Field Array
$prefix = 'review_';
$custom_meta_fields = array(
	array(
		'label'	=> 'Featured',
		'desc'	=> 'Mark as featured',
		'id'	=> 'featured',
		'type'	=> 'checkbox'
	),
	array(
		'label'	=> 'Carousel Text',
		'desc'	=> 'Please specify the text to display on the carousel.',
		'id'	=> $prefix.'carousel_text',
		'type'	=> 'textarea'
	),
	array(
		'label'	=> 'Story Image',
		'desc'	=> 'Upload an image for this story.',
		'id'	=> $prefix.'storyimage',
		'type'	=> 'image'
	),
	array(
		'label'	=> 'Pros',
		'desc'	=> 'Pros of the game?',
		'id'	=> $prefix.'pros',
		'type'	=> 'textarea'
	),
	array(
		'label'	=> 'Cons',
		'desc'	=> 'What are the cons of this game?',
		'id'	=> $prefix.'cons',
		'type'	=> 'textarea'
	),
	array(
		'label'	=> 'Bottom Line',
		'desc'	=> 'The bottom line of the game.',
		'id'	=> $prefix.'bottomline',
		'type'	=> 'textarea'
	),
	array (
		'label'	=> 'STP Score',
		'desc'	=> 'What would the score be?',
		'id'	=> $prefix.'stpscore',
		'type'	=> 'radio',
		'options' => array (
			'one' => array (
				'label' => '1',
				'value'	=> '1'
			),
			'two' => array (
				'label' => '2',
				'value'	=> '2'
			),
			'three' => array (
				'label' => '3',
				'value'	=> '3'
			),
			'four' => array (
				'label' => '4',
				'value'	=> '4'
			)
		)
	),
	array(
		'label'	=> 'Exclude from Homepage',
		'desc'	=> 'Should this story be excluded from the homepage?',
		'id'	=> $prefix.'homeexclude',
		'type'	=> 'checkbox'
	),
	array(
		'label'	=> 'Updates',
		'desc'	=> 'You can add or create updates here.',
		'id'	=> $prefix.'update',
		'type'	=> 'repeatable_update'
	),
	array(
		'label'	=> 'Game for this review',
		'desc'	=> 'Type the name of the game the review belongs to',
		'id'	=> $prefix.'gameID',
		'type'	=> 'association'
	)
);

// add some custom js to the head of the page
function add_custom_scripts() {
	global $custom_meta_fields, $post;

	$output = '<script type="text/javascript">
				jQuery(function() {';

	foreach ($custom_meta_fields as $field) { // loop through the fields looking for certain types
		// date
		if($field['type'] == 'date')
			$output .= 'jQuery(".datepicker").datepicker();';
		// slider
		if ($field['type'] == 'slider') {
			$value = get_post_meta($post->ID, $field['id'], true);
			if ($value == '') $value = $field['min'];
			$output .= '
					jQuery( "#'.$field['id'].'-slider" ).slider({
						value: '.$value.',
						min: '.$field['min'].',
						max: '.$field['max'].',
						step: '.$field['step'].',
						slide: function( event, ui ) {
							jQuery( "#'.$field['id'].'" ).val( ui.value );
						}
					});';
		}
	}

	$output .= '});
		</script>';

	echo $output;
}
add_action('admin_head','add_custom_scripts');

// The Callback
function show_custom_meta_box() {
	global $custom_meta_fields, $post;
	// Use nonce for verification
	echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';

	// Begin the field table and loop
	echo '<table class="form-table">';
	foreach ($custom_meta_fields as $field) {
		// get value of this field if it exists for this post
		$meta = get_post_meta($post->ID, $field['id'], true);
		// begin a table row with
		echo '<tr>
				<th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
				<td>';
				switch($field['type']) {
					// text
					case 'text':
						echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
								<br /><span class="description">'.$field['desc'].'</span>';
					break;
					// textarea
					case 'textarea':
						echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea>
								<br /><span class="description">'.$field['desc'].'</span>';
					break;
					// checkbox
					case 'checkbox':
						echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
								<label for="'.$field['id'].'">'.$field['desc'].'</label>';
					break;
					// select
					case 'select':
						echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
						foreach ($field['options'] as $option) {
							echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
						}
						echo '</select><br /><span class="description">'.$field['desc'].'</span>';
					break;
					// association
					case 'association':
                        $items = array_map( function($obj){return $obj->post_title;}, get_associated_titles(get_the_ID()) );
                        $values = array_map( function($obj){return $obj->ID;}, get_associated_titles(get_the_ID()) );
                        $titles = implode('|', $items);
                        $values = implode(',', $values);
						echo '<input type="hidden" name="'.$field['id'].'" id="'.$titles.'" class="bigdrop_reviews" style="width: 60%; display: none;" value="'.$values.'" />';
						echo '<br /><span class="description">'.$field['desc'].'</span>';
					break;
					// radio
					case 'radio':
						foreach ( $field['options'] as $option ) {
							echo '<input type="radio" name="'.$field['id'].'" id="'.$option['value'].'" value="'.$option['value'].'" ',$meta == $option['value'] ? ' checked="checked"' : '',' />
									<label for="'.$option['value'].'">'.$option['label'].'</label><br />';
						}
						echo '<span class="description">'.$field['desc'].'</span>';
					break;
					// checkbox_group
					case 'checkbox_group':
						foreach ($field['options'] as $option) {
							echo '<input type="checkbox" value="'.$option['value'].'" name="'.$field['id'].'[]" id="'.$option['value'].'"',$meta && in_array($option['value'], $meta) ? ' checked="checked"' : '',' />
									<label for="'.$option['value'].'">'.$option['label'].'</label><br />';
						}
						echo '<span class="description">'.$field['desc'].'</span>';
					break;
					// tax_select
					case 'tax_select':
						echo '<select name="'.$field['id'].'" id="'.$field['id'].'">
								<option value="">Select One</option>'; // Select One
						$terms = get_terms($field['id'], 'get=all');
						$selected = wp_get_object_terms($post->ID, $field['id']);
						foreach ($terms as $term) {
							if (!empty($selected) && !strcmp($term->slug, $selected[0]->slug))
								echo '<option value="'.$term->slug.'" selected="selected">'.$term->name.'</option>';
							else
								echo '<option value="'.$term->slug.'">'.$term->name.'</option>';
						}
						$taxonomy = get_taxonomy($field['id']);
						echo '</select><br /><span class="description"><a href="'.get_bloginfo('home').'/wp-admin/edit-tags.php?taxonomy='.$field['id'].'">Manage '.$taxonomy->label.'</a></span>';
					break;
					// post_list
					case 'post_list':
					$items = get_posts( array (
						'post_type'	=> $field['post_type'],
						'posts_per_page' => -1
					));
						echo '<select name="'.$field['id'].'" id="'.$field['id'].'">
								<option value="">Select One</option>'; // Select One
							foreach($items as $item) {
								echo '<option value="'.$item->ID.'"',$meta == $item->ID ? ' selected="selected"' : '','>'.$item->post_type.': '.$item->post_title.'</option>';
							} // end foreach
						echo '</select><br /><span class="description">'.$field['desc'].'</span>';
					break;
					// date
					case 'date':
						echo '<input type="text" class="datepicker" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
								<br /><span class="description">'.$field['desc'].'</span>';
					break;
					// slider
					case 'slider':
					$value = $meta != '' ? $meta : '0';
						echo '<div id="'.$field['id'].'-slider"></div>
								<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$value.'" size="5" />
								<br /><span class="description">'.$field['desc'].'</span>';
					break;
					// image
					case 'image':
						$image = get_template_directory_uri() . '/images/story_image.png';
						echo '<span class="custom_default_image" style="display:none">'.$image.'</span>';
						if ($meta) { $image = wp_get_attachment_image_src($meta, 'medium');	$image = $image[0]; }
						echo	'<input name="'.$field['id'].'" type="hidden" class="custom_upload_image" value="'.$meta.'" />
									<img src="'.$image.'" class="custom_preview_image" alt="" /><br />
										<input class="custom_upload_image_button button" type="button" value="Choose Image" />
										<small>&nbsp;<a href="#" class="custom_clear_image_button">Remove Image</a></small>
										<br clear="all" /><span class="description">'.$field['desc'].'</span>';
					break;
					// repeatable
					case 'repeatable':
						echo '<a class="repeatable-add button" href="#">+</a>
								<ul id="'.$field['id'].'-repeatable" class="custom_repeatable">';
						$i = 0;
						if ($meta) {
							foreach($meta as $row) {
								echo '<li><span class="sort hndle">|||</span>
											<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="'.$row.'" size="30" />
											<a class="repeatable-remove button" href="#">-</a></li>';
								$i++;
							}
						} else {
							echo '<li><span class="sort hndle">|||</span>
										<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="" size="30" />
										<a class="repeatable-remove button" href="#">-</a></li>';
						}
						echo '</ul>
							<span class="description">'.$field['desc'].'</span>';
					break;
					// repeatable
					case 'repeatable_update':
						echo '<a class="repeatable-add button" href="#">+</a>
								<ul id="'.$field['id'].'-repeatable" class="custom_repeatable">';
						$i = 0;
						if ($meta) {
							foreach($meta as $row) {
								echo '<li><span class="sort hndle">|||</span>
											<textarea name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" cols="60" rows="4">'.$row.'</textarea>
											<a class="repeatable-remove button" href="#">-</a></li>';
								$i++;
							}
						} else {
							echo '<li><span class="sort hndle">|||</span>
										<textarea name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" cols="60" rows="4"></textarea>
										<a class="repeatable-remove button" href="#">-</a></li>';
						}
						echo '</ul>
							<span class="description">'.$field['desc'].'</span>';
					break;
				} //end switch
		echo '</td></tr>';
	} // end foreach
	echo '</table>'; // end table
}

function remove_featured_image_box() {
	remove_meta_box('postimagediv', 'review', 'side' );
}
add_action( 'admin_head' , 'remove_featured_image_box' );

// Save the Data
function save_custom_meta($post_id) {
    global $custom_meta_fields;

	// verify nonce
	if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__)))
		return $post_id;
	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return $post_id;
	// check permissions
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id))
			return $post_id;
		} elseif (!current_user_can('edit_post', $post_id)) {
			return $post_id;
	}

	// loop through fields and save the data
	foreach ($custom_meta_fields as $field) {
		if($field['type'] == 'tax_select') continue;
		$old = get_post_meta($post_id, $field['id'], true);
		$new = $_POST[$field['id']];
		if ($new && $new != $old) {
			update_post_meta($post_id, $field['id'], $new);
		} elseif ('' == $new && $old) {
			delete_post_meta($post_id, $field['id'], $old);
		}
	} // enf foreach

	// save taxonomies
	$post = get_post($post_id);
	$category = $_POST['category'];
	wp_set_object_terms( $post_id, $category, 'category' );
}
add_action('save_post', 'save_custom_meta');
?>
