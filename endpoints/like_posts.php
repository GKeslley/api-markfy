<?php

function like_posts($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    $usuario_id = get_user_meta($user_id, 'unique_name', true); 
    $unique_key = get_user_meta($user_id, 'unique_key', true); 

    $slug = $request['slug'];
    $unique_like = $slug.'-'.$user_id;

    if ($user_id) {

           // Verifica se já existe um post com o slug e o ID do usuário
        $existing_post = get_posts(array(
            'post_type' => 'curtida',
            'post_status' => 'publish',
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

        if (!empty($existing_post)) {
            // O post já existe, você pode lidar com isso da maneira apropriada
            // Aqui, estou apenas retornando uma mensagem de erro
            $error_response = array(
                'message' => 'O item já está salvo como favorito.'
            );
            return rest_ensure_response($error_response);
        }

        $response = array(
          'post_author' => $user_id,
          'post_type' => 'curtida',
          'post_title' => $unique_like,
          'post_name' => $unique_like,
          'post_status' => 'publish',
          'meta_input' => array(
            'slug' => $slug,
            'usuario_id' => $usuario_id,
            'chave_unica' => $unique_key
          )
        );

        $produto_id = wp_insert_post($response);
        $response['id'] = get_post_field('post_name', $produto_id); 


        return rest_ensure_response($response);
    }
}

function registrar_posts_curtidos() {
    register_rest_route('api', 'curtir', array(
      array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'like_posts',
      ),
    ));
}

add_action('rest_api_init', 'registrar_posts_curtidos');

?>
