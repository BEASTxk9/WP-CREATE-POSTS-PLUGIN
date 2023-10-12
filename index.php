<?php
/**
 * Plugin Name: Subscriber Posts
 * Description: Allow subscribers to create posts.
 * Version: 0.1
 * Author: Shane Stevens
 */

 require_once plugin_dir_path(__FILE__) . './includes/1create/create_posts.php';
 require_once plugin_dir_path(__FILE__) . './includes/4delete/delete_posts.php';




 // _________________________________________
// (!IMPORTANT DO NOT TOUCH)  CREATE PAGE FUNCTION  (!IMPORTANT DO NOT TOUCH)
function create_page($title_of_the_page, $content, $parent_id = NULL)
{
	$objPage = get_page_by_title($title_of_the_page, 'OBJECT', 'page');
	if (!empty($objPage)) {
		echo "Page already exists:" . $title_of_the_page . "<br/>";
		return $objPage->ID;
	}
	$page_id = wp_insert_post(
		array(
			'comment_status' => 'close',
			'ping_status' => 'close',
			'post_author' => 1,
			'post_title' => ucwords($title_of_the_page),
			'post_name' => strtolower(str_replace(' ', '-', trim($title_of_the_page))),
			'post_status' => 'publish',
			'post_content' => $content,
			'post_type' => 'page',
			'post_parent' => $parent_id //'id_of_the_parent_page_if_it_available'
		)
	);
	echo "Created page_id=" . $page_id . " for page '" . $title_of_the_page . "'<br/>";
	return $page_id;
}




// _________________________________________
// ACTIVATE PLUGIN
function on_activating_your_plugin()
{
    // _________________________________________
	//  CREATE WP PAGES AUTOMATICALLY ANLONG WITH SHORT CODE TO DISPLAY THE CONTENT
	// eg.  create_page('page-name', '[short-code]');
    // _________________________________________
    
    // 1CREATE
    create_page('create_posts', '[create_posts]');

    // 4DELETE
    create_page('delete_posts', '[delete_posts]');
}
register_activation_hook(__FILE__, 'on_activating_your_plugin');




// _________________________________________
// DEACTIVATE PLUGIN
function on_deactivating_your_plugin()
{
    // _________________________________________
	//  DELETE WP PAGES AUTOMATICALLY ANLONG WITH SHORT CODE TO DISPLAY THE CONTENT
	// eg. 	
    // $page_name = get_page_by_path('page_name');
	// wp_delete_post($page_name->ID, true);
    // _________________________________________

    // 1CREATE
   $create_posts = get_page_by_path('create_posts');
	wp_delete_post($create_posts->ID, true); // create_posts

    // 4DELETE
    $delete_posts = get_page_by_path('delete_posts');
	wp_delete_post($delete_posts->ID, true); // delete_posts

}
register_deactivation_hook(__FILE__, 'on_deactivating_your_plugin');

?>