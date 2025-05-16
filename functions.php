<?php

require get_theme_file_path('/includes/search-route.php');
require get_theme_file_path('/includes/like-route.php');

// Adding a custom API field to post type, where we pull the name of the author of the post
function university_custom_rest()
{
    register_rest_field('post', 'authorName', array(
        'get_callback' => function () {
            return get_the_author();
        }
    ));

    // We're using these to count current user notes and adjust the error message accordingly
    register_rest_field('note', 'userNoteCount', array(
        'get_callback' => function () {
            return count_user_posts(get_current_user_id(), 'note');
        }
    ));
};

add_action('rest_api_init', 'university_custom_rest');

// We're passing NULL as default value to make the args optional
function pageBanner($args = NULL)
{
    if (!isset($args['title'])) {
        $args['title'] = get_the_title();
    }
    if (!isset($args['subtitle'])) {
        $args['subtitle'] = get_field('page_banner_subtitle');
    }

    if (!isset($args['photo'])) {
        if (get_field('page_banner_background_image') and !is_archive() and !is_home()) {
            $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
        } else {
            $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
        }
    }
?>
    <div class="page-banner">
        <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>)"></div>
        <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title"><?php echo $args['title'] ?></h1>
            <div class="page-banner__intro">
                <p><?php echo $args['subtitle'] ?></p>
            </div>
        </div>
    </div>
<?php
};

function university_files()
{
    $apiKey = GOOGLE_MAPS_API_KEY;
    wp_enqueue_script('googleMap', "//maps.googleapis.com/maps/api/js?key={$apiKey}", NULL, '1.0', true);
    wp_enqueue_script('main-university-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
    wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
    // First argument is referencing the js script that we loaded a couple lines above
    // Second argument is giving a whatever name
    // This is done to arrange the links in JS scripts
    // We are essentially creating a variable named universityData with an associative array assigned to it
    wp_localize_script('main-university-js', 'universityData', array(
        'root_url' => get_site_url(),
        'nonce' => wp_create_nonce('wp_rest')
    ));
};

add_action('wp_enqueue_scripts', 'university_files');

function university_features()
{
    register_nav_menu('headerMenuLocation', 'Header Menu Location');
    register_nav_menu('footerLocationOne', 'Footer Location One');
    register_nav_menu('footerLocationTwo', 'Footer Location Two');
    add_theme_support('title-tag');
    // This is to enable featured images, by default only on Blog Posts:
    add_theme_support('post-thumbnails');
    add_image_size('professorLandscape', 400, 260, true);
    add_image_size('professorPortrait', 480, 650, true);
    add_image_size('pageBanner', 1500, 350, true);
    // Here we have more control over cropping, but the issue is that this cropping will be applied to ALL:
    // add_image_size('professorPortrait', 480, 650, array('left', 'top'));
};

add_action('after_setup_theme', 'university_features');

function university_adjust_queries($query)
{
    if (!is_admin() and is_post_type_archive('program') and $query->is_main_query()) {
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', -1);
    }

    if (!is_admin() and is_post_type_archive('event') and $query->is_main_query()) {
        $today = date('Ymd');
        $query->set('posts_per_page', 3);
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
        $query->set('meta_query', array(
            array(
                'key' => 'event_date',
                'compare' => '>=',
                'value' => $today,
                'type' => 'numeric'
            )
        ));
    }

    if (!is_admin() and is_post_type_archive('campus') and $query->is_main_query()) {
        $query->set('posts_per_page', -1);
    }
};

add_action('pre_get_posts', 'university_adjust_queries');

function universityMapKey($api)
{
    $api['key'] = GOOGLE_MAPS_API_KEY;
    return $api;
};

add_filter('acf/fields/google_map/api', 'universityMapKey');


// Redirect subscriber accounts out of Admin and onto Homepage

function redirectSubsToFrontend()
{
    $currentUser = wp_get_current_user();

    // Checking if current user only has one role and that role is Subscriber
    if (count($currentUser->roles) === 1 and $currentUser->roles[0] === 'subscriber') {
        wp_redirect(site_url('/'));
        exit;
    }
};

// Since we're hooking this onto admin_init, event if we write "/wp-admin" in the url bar, it will still re-direct to Front
add_action('admin_init', 'redirectSubsToFrontend');

// Remove admin top bar from the Frontend of Subscriber

function noSubsAdminBar()
{
    $currentUser = wp_get_current_user();

    // Checking if current user only has one role and that role is Subscriber
    if (count($currentUser->roles) === 1 and $currentUser->roles[0] === 'subscriber') {
        show_admin_bar(false);
    }
};

// Since we're hooking this onto admin_init, event if we write "/wp-admin" in the url bar, it will still re-direct to Front
add_action('wp_loaded', 'noSubsAdminBar');

// Customizing Login Screen link to point to our website instead of Wordpress.org

function customHeaderUrl()
{
    return esc_url(site_url('/'));
};

add_filter('login_headerurl', 'customHeaderUrl');

// Customizing login screen CSS. In theory we could just create a separate CSS file to specifically override necessary styles

function customLoginCSS()
{
    wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
};

add_action('login_enqueue_scripts', 'customLoginCSS');

// Overriding login title:

function customLoginTitle()
{
    return get_bloginfo('name');
};

add_filter('login_headertitle', 'customLoginTitle');

// Force note posts to be private. This is a super powerful hook:
function makeNotePrivate($data, $postarr)
{
    // Sanitizing Note content to make sure that even basic HTML does not go through
    if($data['post_type'] == 'note') {
        // Implementing post amount limit per user: checking if current user ID already has more than 4 posts of type 'note'
        // Also checking if there is no post ID, which means this is a new post:
        if(count_user_posts(get_current_user_id(), 'note') > 4 AND !$postarr['ID']) {
            // Lol what :) So this stops further code execution and returns message in responseText
            die("You have reached your note limit!");
        }

        $data['post_title'] = sanitize_text_field($data['post_title']);
        $data['post_content'] = sanitize_textarea_field($data['post_content']);
    }

    // Making sure that when subscribers create Notes, they don't stay in Draft and are immediately visible in Private
    if ($data['post_type'] == 'note' AND $data['post_status'] != 'trash') {
        $data['post_status'] = 'private';
    }
    return $data;
};

// 1 here is the priority and 2 is the number of parameters of that function
// The lower the priority number, the earlier it will run
// The priority is an issue only if there are multiple functions running on the same hook
// But here we still need to specify because we need to access the 4th argument
add_filter('wp_insert_post_data', 'makeNotePrivate', 1, 2);
