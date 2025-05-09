#There is also a file public/wp-content/mu-plugins/university-post-types.php with the following code:

<?php

// we're placing this function and registration in mu-plugins
// so that in case the user changes the theme - they will still have access to Events
// show_in_rest parameter allows to use the new cool Block Editor for editing Events

function university_post_types()
{
    // Event Post Type
    register_post_type('event', array(
        // important to still explicitly list title and editor
        // in case I am choosing to add support to anything at all
        'supports' => array('title', 'editor', 'excerpt'),
        'rewrite' => array('slug' => 'events'),
        'has_archive' => true,
        'public' => true,
        'labels' => array(
            'name' => 'Events',
            'add_new_item' => 'Add New Event',
            'edit_item' => 'Edit Event',
            'all_items' => 'All Events',
            'singular_name' => 'Event'
        ),
        'menu_icon' => 'dashicons-buddicons-friends',
        'show_in_rest' => true
    ));

    // Program Post Type
    register_post_type('program', array(
        'show_in_rest' => true,
        // Here we're removing 'editor' because we created custom main_body_content field to work around the search
        'supports' => array('title'),
        'rewrite' => array('slug' => 'programs'),
        'has_archive' => true,
        'public' => true,
        'labels' => array(
            'name' => 'Programs',
            'add_new_item' => 'Add New Program',
            'edit_item' => 'Edit Program',
            'all_items' => 'All Programs',
            'singular_name' => 'Program'
        ),
        'menu_icon' => 'dashicons-feedback',
    ));

    // Professor Post Type
    register_post_type('professor', array(
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'public' => true,
        'labels' => array(
            'name' => 'Professors',
            'add_new_item' => 'Add New Professor',
            'edit_item' => 'Edit Professor',
            'all_items' => 'All Professors',
            'singular_name' => 'Professor'
        ),
        'menu_icon' => 'dashicons-businessperson',
    ));

    // Campus Post Type
    register_post_type('campus', array(
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'campuses'),
        'has_archive' => true,
        'public' => true,
        'labels' => array(
            'name' => 'Campuses',
            'add_new_item' => 'Add New Campus',
            'edit_item' => 'Edit Campus',
            'all_items' => 'All Campuses',
            'singular_name' => 'Campus'
        ),
        'menu_icon' => 'dashicons-location',
        'show_in_rest' => true
    ));
};

add_action('init', 'university_post_types');
