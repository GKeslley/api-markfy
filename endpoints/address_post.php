<?php

function api_address_post($request)
{

    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id > 0) {
        $cep = sanitize_text_field($request['cep']);
        $endereco = sanitize_text_field($request['endereco']);
        $numero = sanitize_text_field($request['numero']);
        $complemento = sanitize_text_field($request['complemento']);
        $cidade = sanitize_text_field($request['cidade']);
        $uf = sanitize_text_field($request['uf']);
        $telefone = sanitize_text_field($request['telefone']);

        $usuario_id = $user->user_login;

        $response = array(
        'post_author' => $user_id,
        'post_type' => 'endereco',
        'post_title' => $endereco,
        'post_status' => 'publish',
        'meta_input' => array(
        'cep' => $cep,
        'endereco' => $endereco,
        'numero' => $numero,
        'complemento' => $complemento,
        'cidade' => $cidade,
        'uf' => $uf,
        'telefone' => $telefone
        )
        );

        $address_id = wp_insert_post($response);
        $response['id'] = get_post_field('post_name', $address_id);

    } else {
        $response = new WP_Error('permissao', 'Usuário não possui permissão', array('status' => 401));
    }


    return rest_ensure_response($response);
}

function registrar_api_address_post()
{
    register_rest_route('api', 'endereco', array(
    array(
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'api_address_post',
    ),
    ));
}

add_action('rest_api_init', 'registrar_api_address_post');

?>