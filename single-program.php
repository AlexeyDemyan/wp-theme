<?php

get_header();
while (have_posts()) {
    the_post();
    pageBanner() ?>

    <div class="container container--narrow page-section">
        <div class="metabox metabox--position-up metabox--with-home-link">
            <p>
                <a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('program') ?>"><i class="fa fa-home" aria-hidden="true"></i> All Programs</a> <span class="metabox__main"><?php the_title() ?></span>
            </p>
        </div>
        <div class="generic-content">
            <?php the_field('main_body_content') ?></div>
        <?php

        $relatedProfessors = new WP_Query(array(
            // posts_pet_page set at -1 will give all posts that meet the conditions
            'posts_per_page' => -1,
            'post_type' => 'professor',
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => array(
                // here we're filtering for relevant professors
                array(
                    'key' => 'related_program',
                    'compare' => 'LIKE',
                    'value' => '"' . get_the_ID() . '"' // IMP! We're sanitizing the value with quotes to account for issue with serialization
                )
            )
        ));

        if ($relatedProfessors->have_posts()) {
            echo '<hr class="section-break">';
            echo '<h2 class="headline headline--medium">Related ' . get_the_title() . ' Professors</h2>';
            echo '<br>';
            echo '<ul class="professor-cards">';
            while ($relatedProfessors->have_posts()) {
                $relatedProfessors->the_post(); ?>
                <li class="professor-card__list-item">
                    <a class="professor-card" href="<?php the_permalink() ?>">
                        <img class="professor-card__image" src="<?php the_post_thumbnail_url('professorLandscape') ?>" alt="">
                        <span class="professor-card__name"><?php the_title() ?></span>
                    </a>
                </li>
            <?php }
            echo '</ul>';
        }

        // This is important to run the second query
        // Since we will need the ID of the page to pull the events 
        // It resets the data back to the DEFAULT URL-BASED QUERY
        wp_reset_postdata();

        $today = date('Ymd');
        $eventPosts = new WP_Query(array(
            // posts_pet_page set at -1 will give all posts that meet the conditions
            'posts_per_page' => 2,
            'post_type' => 'event',
            'meta_key' => 'event_date',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'event_date',
                    'compare' => '>=',
                    'value' => $today,
                    'type' => 'numeric'
                ),
                // here we're filtering for events that have the current program as their related program
                array(
                    'key' => 'related_program',
                    'compare' => 'LIKE',
                    'value' => '"' . get_the_ID() . '"' // IMP! We're sanitizing the value with quotes to account for issue with serialization
                )
            )
        ));

        if ($eventPosts->have_posts()) {
            echo '<hr class="section-break">';
            echo '<h2 class="headline headline--medium">Upcoming ' . get_the_title() . ' Events</h2>';
            echo '<br>';
            while ($eventPosts->have_posts()) {
                $eventPosts->the_post();
                get_template_part('template-parts/content-event');
            }
        }

        // again getting a clean slate here
        wp_reset_postdata();
        $relatedCampuses = get_field('related_campus');

        if ($relatedCampuses) {
            echo '<hr class="section-break">';
            echo '<h2 class="headline headline--medium">' . get_the_title() . ' is available at These Campuses: </h2>';
            echo '<ul class="min-list link-list">';
            foreach ($relatedCampuses as $campus) {
            ?>
                <li><a href="<?php echo get_the_permalink($campus) ?>"><?php echo get_the_title($campus) ?></a></li>
        <?php
            }
            echo '</ul>';
        }

        ?>
    </div>
<?php
};

get_footer();
