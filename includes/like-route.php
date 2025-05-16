<?php

add_action('rest_api_init', 'universityLikeRoutes');

function universityLikeRoutes()
{
    register_rest_route('university/v1', 'manageLike', array(
        'methods' => 'POST',
        'callback' => 'createLike'
    ));

    register_rest_route('university/v1', 'manageLike', array(
        'methods' => 'DELETE',
        'callback' => 'deleteLike'
    ));
}

function createLike($data)
{
    if (is_user_logged_in()) {
        $professor = sanitize_text_field($data['professorId']);

        // Checking if current user already liked the target professor
        $existLikeQuery = new WP_Query(array(
            'author' => get_current_user_id(),
            'post_type' => 'like',
            'meta_query' => array(
                array(
                    'key' => 'liked_professor_id',
                    'compare' => '=',
                    'value' => $professor
                )
            )
        ));

        // Here we will also check if the professor ID actuallt belongs to a professor, and not a Campus or Page or something
        if ($existLikeQuery->found_posts == 0 and get_post_type($professor) == 'professor') {
            return wp_insert_post(array(
                'post_type' => 'like',
                'post_status' => 'publish',
                'post_title' => 'PHP crete Post test',
                'meta_input' => array(
                    'liked_professor_id' => $professor
                )
            ));
        } else {
            die("Invalid Professor ID");
        };
    } else {
        die("Only logged-in users can create a Like");
    }
}

function deleteLike($data)
{
    $likeId = sanitize_text_field($data['like']);
    if (get_current_user_id() == get_post_field('post_author', $likeId) and get_post_type($likeId) == 'like') {
        // second argument set to True would skip the Move-to-Trash step
        wp_delete_post($likeId, true);
        return "Like successfully deleted";
    } else {
        die("You do not have permission to delete that");
    }
}
