<?php get_header();
pageBanner(array(
    'title' => 'Search Results',
    // We're using esc_html for secutiry purposes:
    'subtitle' => 'You searched for &ldquo;' . esc_html(get_search_query(false)) . '&rdquo;'
)) ?>

<div class="container container--narrow page-section">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            get_template_part('template-parts/content', get_post_type());
        }
        echo paginate_links();
    } else {
        echo '<h2 class="headling headline--small-plus">No results match the search</h2>';
    } 

    get_search_form();
    ?>

</div>

<?php get_footer(); ?>