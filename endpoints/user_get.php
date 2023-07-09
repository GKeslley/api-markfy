<?php

function api_user_endereco_scheme($user, $user_id) {

    $args = array(
      'post_type' => 'endereco',
      'post_status' => 'publish',
      'author' => $user_id,
    );

    $enderecos = get_posts($args);

    $endereco_array = null;
    if ($enderecos) {
        $endereco_array = array();
        foreach ($enderecos as $key => $value) {
          $endereco_array[] = array(
            'cep' => get_post_meta($value->ID, 'cep', true),
            'endereco' => get_post_meta($value->ID, 'endereco', true),
            'numero' => get_post_meta($value->ID, 'numero', true),
            'complemento' => get_post_meta($value->ID, 'complemento', true),
            'cidade' => get_post_meta($value->ID, 'cidade', true),
            'uf' => get_post_meta($value->ID, 'uf', true),
            'telefone' => get_post_meta($value->ID, 'telefone', true),
            );
        }
    }

    return $endereco_array;
}

function api_user_get($request)
{

    $user = wp_get_current_user();
    $user_id = $user->ID;

    $endereco = api_user_endereco_scheme($user, $user_id);
    
    $usuario_id = get_user_meta($user_id, 'unique_name', true);
    $phone_number = get_user_meta($user_id, 'phone_number', true) ?: '';

    if ($user_id > 0) {
        $response = array(
          "id" => $user->display_name,
          "nome" => $user->display_name,
          "email" => $user->user_email,
          'endereco' => $endereco,
          'usuario_id' => $usuario_id,
          'numero_celular' => $phone_number  
        );
    } else {
        $response = new WP_Error('permissao', 'Usuário não possui permissão', array('status' => 401));
    }

    return rest_ensure_response($response);
}

function registrar_api_user_get()
{
    register_rest_route('api', 'usuario', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_user_get',
      ),
    ));
}

add_action('rest_api_init', 'registrar_api_user_get');

function api_other_user_get($request) {
  $usuario_id = $request['usuario'];
  $unique_name = '@' . $usuario_id;

  $args = array(
   'meta_key' => 'unique_name',
   'meta_value' => $unique_name,
  );

  $users = get_users($args);

   $usuario_id_query = null;
    if ($usuario_id) {
      $usuario_id_query = array(
        'key' => 'usuario_id',
        'value' => $unique_name,
        'compare' => '='
      );
    }

    $query = array(
      'post_type' => 'produto',
      'meta_query' => array(
        $usuario_id_query,
      )
    );

    $loop = new WP_Query($query);
    $posts = $loop->posts;
    $total = $loop->found_posts;
  
  if (!empty($users)) {
    $user = $users[0];

    $endereco = api_user_endereco_scheme($user, $user->ID);

    $response = array(
      'nome' => $user->display_name,
      'data_registro' => $user->user_registered,
      'endereco' => $endereco,
      'total_postagens' => $total
    );
  } else {
    $response = new WP_Error('usuario', 'Usuário não encontrado', array('status' => 401));
  }

  return rest_ensure_response($response);
}

function registrar_api_other_user_get()
{
    register_rest_route('api', 'usuario/(?P<usuario>[-\w]+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_other_user_get',
      ),
    ));
}

add_action('rest_api_init', 'registrar_api_other_user_get');
