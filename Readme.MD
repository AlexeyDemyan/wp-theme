#There is also a file public/wp-content/mu-plugins/university-post-types.php with the following code:

<?php

// we're placing this function and registration in mu-plugins
// so that in case the user changes the theme - they will still have access to Events
// show_in_rest parameter allows to use the new cool Block Editor for editing Events

function university_post_types()
{
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
};

add_action('init', 'university_post_types');
