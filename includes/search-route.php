<?php

function universitySearchResults($data)
{
    $mainQuery = new WP_Query(array(
        'post_type' => array('post', 'page', 'professor', 'campus', 'program', 'event'),
        // 's' is a reserved argument, stands for SEARCH
        // WP already does some security, but we use sanitizer here for good practice
        's' => sanitize_text_field($data['keyword'])
    ));

    $results = array(
        'generalInfo' => array(),
        'professors' => array(),
        'campuses' => array(),
        'programs' => array(),
        'events' => array()
    );

    while ($mainQuery->have_posts()) {
        $mainQuery->the_post();

        if (get_post_type() == 'post' or get_post_type() == 'page') {

            array_push($results['generalInfo'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'postType' => get_post_type(),
                'authorName' => get_the_author()
            ));
        }

        if (get_post_type() == 'professor') {

            array_push($results['professors'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                // 0 here refers to current post
                'image' => get_the_post_thumbnail_url(0, 'professorLandscape')
            ));
        }

        if (get_post_type() == 'program') {
            $relatedCampuses = get_field('related_campus');

            if ($relatedCampuses) {
                foreach ($relatedCampuses as $item) {
                    array_push($results['campuses'], array(
                        'title' => get_the_title($item),
                        'permalink' => get_the_permalink($item)
                    ));
                }
            }

            array_push($results['programs'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'id' => get_the_ID()
            ));
        }

        if (get_post_type() == 'campus') {

            array_push($results['campuses'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink()
            ));
        }

        if (get_post_type() == 'event') {
            $eventDate = new DateTime(get_field('event_date'));
            $description = null;
            if (has_excerpt()) {
                $description = get_the_excerpt();
            } else {
                $description = wp_trim_words(get_the_content(), 18);
            }

            array_push($results['events'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'month' => $eventDate->format('M'),
                'day' => $eventDate->format('d'),
                'description' => $description
            ));
        }
    }

    // This IF check is so that if $programsMetaQuery returns empty, we return no professors
    if ($results['programs']) {
        $programsMetaQuery = array(
            'relation' => 'OR'
        );

        foreach ($results['programs'] as $item) {
            array_push($programsMetaQuery, array(
                'key' => 'related_program',
                'compare' => 'LIKE',
                // We're surrounding result with quotes here:
                'value' => '"' . $item['id'] . '"'
            ));
        };

        $programRelationshipQuery = new WP_Query(array(
            'post_type' => array('professor', 'event'),
            'meta_query' => $programsMetaQuery
            // This was the first draft, will leave it in the commit:

            // 'meta_query' => array(
            //     array(
            //         'key' => 'related_program',
            //         'compare' => 'LIKE',
            //         // We're surrounding result with quotes here:
            //         'value' => '"' . $results['programs'][0]['id'] . '"'
            //     )
            // )
        ));

        while ($programRelationshipQuery->have_posts()) {
            $programRelationshipQuery->the_post();

            if (get_post_type() == 'event') {
                $eventDate = new DateTime(get_field('event_date'));
                $description = null;
                if (has_excerpt()) {
                    $description = get_the_excerpt();
                } else {
                    $description = wp_trim_words(get_the_content(), 18);
                }

                array_push($results['events'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'month' => $eventDate->format('M'),
                    'day' => $eventDate->format('d'),
                    'description' => $description
                ));
            }

            if (get_post_type() == 'professor') {
                array_push($results['professors'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    // 0 here refers to current post
                    'image' => get_the_post_thumbnail_url(0, 'professorLandscape')
                ));
            }
        }

        // This is a PHP function that removes duplicates from the search
        $results['professors'] = array_unique($results['professors'], SORT_REGULAR);
        $results['events'] = array_unique($results['events'], SORT_REGULAR);
    }
    return $results;
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
