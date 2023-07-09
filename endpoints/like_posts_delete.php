<?php 
  function api_like_delete($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    $slug = $request['slug'];
    $unique_like = $slug.'-'.$user_id;

    $unique_key = get_user_meta($user_id, 'unique_key', true);

    $query = new WP_Query(array(
    'name' => $unique_like,
    'post_type' => 'curtida',
    'post_status' => 'publish',
    'numberposts' => 1,
    'fields' => 'ids',
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => 'slug',
            'value' => $slug,
            'compare' => '='
        ),
        array(
            'key' => 'chave_unica',
            'value' => $unique_key,
            'compare' => '='
        )
    )
));

    $posts = $query->get_posts();

    $produto_id = array_shift($posts);
    $author_id = (int) get_post_field('post_author', $produto_id);

    if ($user_id === $author_id) {
      $images = get_attached_media('image', $produto_id);
      if ($images) {
        foreach($images as $key => $value) {
          wp_delete_attachment($value->ID, true);
        }
      }

      $response = wp_delete_post($produto_id, true);
    } else {
      $response = new WP_Error('permissao', 'Usuário não possui permissão', array('status' => 401));
    }


    return rest_ensure_response($response);
  }

  function registrar_api_like_delete() {
    register_rest_route('api', '/curtida/(?P<slug>[-\w]+)', array(
      array(
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => 'api_like_delete',
      ),
    ));
  }

  add_action('rest_api_init', 'registrar_api_like_delete');

?>
