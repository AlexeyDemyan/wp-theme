<?php get_header();
pageBanner(array(
    'title' => 'Past Events',
    'subtitle' => 'Recap of our past events'
))  ?>

<div class="container container--narrow page-section">
    <?php

    $today = date('Ymd');
    $pastEventPosts = new WP_Query(array(
        'paged' => get_query_var('paged', 1),
        // we were placing 1 here just to test pagination, ghax otherwise we would need more than 10 events to test
        //'posts_per_page' => 1,
        'post_type' => 'event',
        'meta_key' => 'event_date',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => 'event_date',
                'compare' => '<',
                'value' => $today,
                'type' => 'numeric'
            )
        )
    ));
    while ($pastEventPosts->have_posts()) {
        $pastEventPosts->the_post();
        get_template_part('template-parts/content-event');
    }
    echo paginate_links(array(
        'total' => $pastEventPosts->max_num_pages
    ));
    ?>
</div>

<?php get_footer(); ?>