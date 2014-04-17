<?php
/*
Plugin Name: Guerrilla's Work CPT
Plugin URI: http://madebyguerrilla.com
Description: This is a plugin that adds a work custom post type for freelancers to display their work.
Version: 1.0
Author: Mike Smith
Author URI: http://www.madebyguerrilla.com
*/

/*  Copyright 2014  Mike Smith (email : hi@madebyguerrilla.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Create Work Post Type
function guerrilla_work() {
	$labels = array(
		'name'                 => 'Work',
		'singular_name'        => 'Work',
		'menu_name'            => 'Work',
		'parent_item_colon'    => 'Parent Work:',
		'all_items'            => 'All Work',
		'view_item'            => 'View Work',
		'add_new_item'         => 'Add New Work',
		'add_new'              => 'New Work',
		'edit_item'            => 'Edit Work',
		'update_item'          => 'Update Work',
		'search_items'         => 'Search Work',
		'not_found'            => 'No work found',
		'not_found_in_trash'   => 'No work found in trash',
	);
	$args = array(
		'label'                => 'work',
		'description'          => 'Work',
		'labels'               => $labels,
		'supports'             => array( 'title', 'editor', 'thumbnail', ),
		'taxonomies'           => array(),
		'hierarchical'         => false,
		'public'               => true,
		'show_ui'              => true,
		'show_in_menu'         => true,
		'show_in_nav_menus'    => true,
		'show_in_admin_bar'    => true,
		'menu_position'        => 5,
		'can_export'           => true,
		'has_archive'          => true,
		'exclude_from_search'  => false,
		'publicly_queryable'   => true,
		'menu_icon' 		   => 'dashicons-portfolio',
		'capability_type'      => 'page',
		'register_meta_box_cb' => 'add_work_infoboxes'
	);
	register_post_type( 'work', $args );
	
	// Add the Work Info Meta Boxes
	function add_work_infoboxes() {
		add_meta_box('wpt_work_info', 'Work Info', 'wpt_work_info', 'work', 'side', 'default');
	}
	
	// The Work Info Data input boxes
	function wpt_work_info() {
		global $post;
		// Noncename needed to verify where the data originated
		echo '<input type="hidden" name="workinfo_noncename" id="workinfo_noncename" value="' .
		wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
		// Get the social data if its already been entered
		$industry = get_post_meta($post->ID, '_industry', true);
		$year = get_post_meta($post->ID, '_year', true);
		$website = get_post_meta($post->ID, '_website', true);
		// Echo out the field
		echo '<p>Industry:</p>';
		echo '<input type="text" name="_industry" value="' . $industry  . '" class="widefat" />';
		echo '<p>Year:</p>';
		echo '<input type="text" name="_year" value="' . $year  . '" class="widefat" />';
		echo '<p>Website:</p>';
		echo '<input type="text" name="_website" value="' . $website  . '" class="widefat" />';
	}
	
	// Save the Info Data
	function wpt_save_work_info($post_id, $post) {
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $_POST['workinfo_noncename'], plugin_basename(__FILE__) )) {
		return $post->ID;
		}
		// Is the user allowed to edit the post or page?
		if ( !current_user_can( 'edit_post', $post->ID ))
			return $post->ID;
		// OK, we're authenticated: we need to find and save the data
		// We'll put it into an array to make it easier to loop though.
		$work_info['_industry'] = $_POST['_industry'];
		$work_info['_year'] = $_POST['_year'];
		$work_info['_website'] = $_POST['_website'];
		// Add values of $events_meta as custom fields
		foreach ($work_info as $key => $value) { // Cycle through the $events_meta array!
			if( $post->post_type == 'revision' ) return; // Don't store custom data twice
			$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
			if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
				update_post_meta($post->ID, $key, $value);
			} else { // If the custom field doesn't have a value
				add_post_meta($post->ID, $key, $value);
			}
			if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
		}
	}
	add_action('save_post', 'wpt_save_work_info', 1, 2); // save the custom fields

		
	// Add the Type taxonomy for the Work
    function workinfo_taxonomy() {
       register_taxonomy(
        'work_type',
        'work',
        array(
            'hierarchical' => true,
            'label' => 'Work Type',
            'query_var' => true,
            'rewrite' => array('slug' => 'type')
        )
		);
    }
    add_action( 'init', 'workinfo_taxonomy' );

}

// Hook into the 'init' action
add_action( 'init', 'guerrilla_work', 0 );

// This code adds the default Work stylesheet to your website
function guerrilla_work_style() {
	// Register the style like this for a plugin:
	wp_register_style( 'guerrilla-work-custom-post-type', plugins_url( '/style.css', __FILE__ ), array(), '20140408', 'all' );
	// For either a plugin or a theme, you can then enqueue the style:
	wp_enqueue_style( 'guerrilla-work-custom-post-type' );
}

add_action( 'wp_enqueue_scripts', 'guerrilla_work_style' );

// The below code adds the Work page on activation and trashes it on deactivation

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'guerrilla_work_cpt_install'); 
/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'guerrilla_work_cpt_remove' );

function guerrilla_work_cpt_install() {

    global $wpdb;

    $the_page_title = 'Work';
    $the_page_name = 'work';

    // the menu entry...
    delete_option("guerrilla_work_cpt_page_title");
    add_option("guerrilla_work_cpt_page_title", $the_page_title, '', 'yes');
    // the slug...
    delete_option("guerrilla_work_cpt_page_name");
    add_option("guerrilla_work_cpt_page_name", $the_page_name, '', 'yes');
    // the id...
    delete_option("guerrilla_work_cpt_page_id");
    add_option("guerrilla_work_cpt_page_id", '0', '', 'yes');

    $the_page = get_page_by_title( $the_page_title );

    if ( ! $the_page ) {

        // Create post object
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "This page is only acting as a placeholder for the Guerrilla's Work CPT plugin. Nothing to see here, but this page has been added so you can now add the 'Work' page to your navigation";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );

    }
    else {
        // the plugin may have been previously active and the page may just be trashed...
        $the_page_id = $the_page->ID;

        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post( $the_page );

    }

    delete_option( 'guerrilla_work_cpt_page_id' );
    add_option( 'guerrilla_work_cpt_page_id', $the_page_id );

}

// This is the function to remove the WORK page when the plugin is deactivated
function guerrilla_work_cpt_remove() {

    global $wpdb;

    $the_page_title = get_option( "guerrilla_work_cpt_page_title" );
    $the_page_name = get_option( "guerrilla_work_cpt_page_name" );

    //  the id of our page...
    $the_page_id = get_option( 'guerrilla_work_cpt_page_id' );
    if( $the_page_id ) {

        wp_delete_post( $the_page_id ); // this will trash, not delete

    }

    delete_option("guerrilla_work_cpt_page_title");
    delete_option("guerrilla_work_cpt_page_name");
    delete_option("guerrilla_work_cpt_page_id");

}

?>