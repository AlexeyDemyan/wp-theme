<?php

function universitySearchResults()
{
    return 'Custom Route Created';
};

function university_register_search()
{
    // Args: namespace, route (ending part of url), array of stuff
    // in http://fictional-university.local/wp-json/wp/v2/professor?search=brad, "wp" would be the namespace
    // v2 is the version part of namespace, which is convenient when you develop and change API, 
    // so people using the old API don't get rugpulled
    register_rest_route('university/v1', 'search', array(
        // a tehcnicallt safer way is this:
        // 'methods' => WP_REST_SERVER::READABLE
        'methods' => 'GET',
        'callback' => 'universitySearchResults'
    ));
};

add_action('rest_api_init', 'university_register_search');
