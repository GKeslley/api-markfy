<?php

function like_posts_get($request)
{
    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_limit']) ?: 9;
    $user = $request['_user'];
    $order = $request['_order'] ?: null;

    $args = array(
    'meta_key' => 'unique_name',
    'meta_value' => $user,
    'meta_compare' => '='
    );

    $find_user = get_users($args);

    foreach ($find_user as $user_data) {
        $user_unique_key = get_user_meta($user_data->ID, 'unique_key', true);
    }

    $user_query = null;
    if ($user) {
        $user_query = array(
          'key' => 'chave_unica',
          'value' => $user_unique_key,
          'compare' => '='
        );
    }

    $query = array(
      'post_type' => 'curtida',
      'posts_per_page' => $_limit,
      'paged' => $_page,
      's' => $q,
      'meta_query' => array(
        $user_query
      )
    );

    $loop = new WP_Query($query);
    if (!$loop->have_posts()) {
        $response = new WP_Error('naoexiste', 'Produto não encontrado', array('status' => 404));
        return $response;
    }

    $posts = $loop->posts;
    $total = $loop->found_posts;

    $likes = null;
    foreach ($posts as $post) {
        $post_id = $post->ID;
        $slug = get_post_meta($post_id, 'slug', true);

        $likes[] = array(
            'produtos' => products_scheme($slug, 'likes')
        );
    }

    $response = rest_ensure_response($likes);
    $response->header('X-Total-Count', $total);

    return $response;
}

function register_get_posts_likes()
{
    register_rest_route('api', 'curtidas', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'like_posts_get',
      ),
    ));
}

add_action('rest_api_init', 'register_get_posts_likes');

//// CURTIDA

function like_post_get($request)
{
    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_limit']) ?: 9;

    $user_request = $request['_user'];
    $order = $request['_order'] ?: null;
    $slug = $request['slug'];

    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id > 0) {
        $unique_name = get_user_meta($user_id, 'unique_name', true);

        $args = array(
            'meta_key' => 'unique_name',
            'meta_value' => $unique_name,
            'meta_compare' => '='
        );

        $unique_key = get_user_meta($user_id, 'unique_key', true);

        $user_request_query = array(
           'key' => 'chave_unica',
           'value' => $unique_key,
           'compare' => '='
        );

        $query = array(
          'post_type' => 'curtida',
          'name' => $slug.'-'.$user_id,
          'title' => $slug.'-'.$user_id,
          'posts_per_page' => $_limit,
          'paged' => $_page,
          's' => $q,
          'meta_query' => array(
            $user_request_query
          )
        );

        $loop = new WP_Query($query);
        if (!$loop->have_posts()) {
            $response = new WP_Error('naoexiste', 'Produto não encontrado', array('status' => 404));
            return $response;
        }

        $posts = $loop->posts;
        $total = $loop->found_posts;

        if ($total > 0) {
            $curtida = true;
        }

        $response = rest_ensure_response($curtida);
        $response->header('X-Total-Count', $total);
    }

    return $response;
}

function register_get_post_like()
{
    register_rest_route('api', '/curtida/(?P<slug>[-\w]+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'like_post_get',
      ),
    ));
}

add_action('rest_api_init', 'register_get_post_like');