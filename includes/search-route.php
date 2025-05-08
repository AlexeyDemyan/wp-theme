<?php

function universitySearchResults($data)
{
    $professors = new WP_Query(array(
        'post_type' => 'professor',
        // 's' is a reserved argument, stands for SEARCH
        // WP already does some security, but we use sanitizer here for good practice
        's' => sanitize_text_field($data['keyword'])
    ));

    $professorResults = array();

    while ($professors->have_posts()) {
        $professors->the_post();
        array_push($professorResults, array(
            'title' => get_the_title(),
            'permalink' => get_the_permalink()
        ));
    }

    return $professorResults;
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
        // WP passes our request to below callback, so we can access it in universitySearchResults
        'callback' => 'universitySearchResults'
    ));
};

add_action('rest_api_init', 'university_register_search');
