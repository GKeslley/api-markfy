<?php

function api_transaction_get()
{
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if (!$user_id) {
        $response = new WP_error('permissao', 'Usuário não possui permissão', array('status' => 401));
        return rest_ensure_response($response);
    }

    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_total']) ?: 9;
    $user_unique_key = get_user_meta($user_id, 'unique_key', true);

    $meta_query = array(
      'key' => 'comprador_id',
      'value' => $user_unique_key,
      'compare' => '='
    );

    $query = array(
      'post_type' => 'transacao',
      'posts_per_page' => $_limit,
      'paged' => $_page,
      'orderby' => 'date',
      'meta_query' => array(
        $meta_query
      )
    );

    $loop = new WP_Query($query);

    if (!$loop->have_posts()) {
        $response = new WP_Error('naoexiste', 'Produtos não encontrados', array('status' => 404));
        return rest_ensure_response($response);
    }

    $posts = $loop->posts;
    $total = $loop->found_posts;

    $products = array();

    foreach($posts as $key => $value) {
        $post_id = $value->ID;
        $post_meta = get_post_meta($post_id);

        $products[] = array(
         'vendedor_id' => $post_meta['vendedor_id'][0],
         'endereco' => json_decode($post_meta['endereco'][0]),
         'produto' => json_decode($post_meta['produto'][0]),
         'data' => $value->post_date
        );
    }

    $response = rest_ensure_response($products);
    $response->header('X-Total-Count', $total);

    return $response;
}

function register_api_transaction_get()
{
    register_rest_route('api', 'transacao', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_transaction_get',
      ),
    ));
}

add_action('rest_api_init', 'register_api_transaction_get');
