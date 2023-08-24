<?php

function api_address_post($request)
{
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id > 0) {
        $cep = sanitize_text_field($request['cep']);
        $address = sanitize_text_field($request['endereco']);
        $number = sanitize_text_field($request['numero']);
        $complement = sanitize_text_field($request['complemento']);
        $neighborhood = sanitize_text_field($request['bairro']);
        $city = sanitize_text_field($request['cidade']);
        $reference = sanitize_text_field($request['referencia']);
        $uf = sanitize_text_field($request['uf']);

        $args = array(
            'post_type' => 'endereco',
            'post_status' => 'publish',
            'author' => $user_id,
        );

        $addresses = get_posts($args);

        if (empty($addresses)) {
            $response = array(
                'post_author' => $user_id,
                'post_type' => 'endereco',
                'post_title' => $address,
                'post_status' => 'publish',
                'meta_input' => array(
                    'cep' => $cep,
                    'endereco' => $address,
                    'numero' => $number,
                    'complemento' => $complement,
                    'cidade' => $city,
                    'uf' => $uf,
                    'bairro' => $neighborhood,
                    'referencia' => $reference
                )
            );

            $address_id = wp_insert_post($response);
            $response['id'] = get_post_field('post_name', $address_id);
        } else {
            $address_id = $addresses[0]->ID;

            $response = array(
                'ID' => $address_id,
                'post_title' => $address,
                'post_status' => 'publish',
                'meta_input' => array(
                    'cep' => $cep,
                    'endereco' => $address,
                    'numero' => $number,
                    'complemento' => $complement,
                    'cidade' => $city,
                    'uf' => $uf,
                    'bairro' => $neighborhood,
                    'referencia' => $reference
                )
            );

            wp_update_post($response);
            $response['id'] = get_post_field('post_name', $address_id);
        }
    } else {
        $response = new WP_Error('permissao', 'Usuário não possui permissão', array('status' => 401));
    }

    return rest_ensure_response($response);
}

function register_api_address_post()
{
    register_rest_route('api', 'endereco', array(
        array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'api_address_post',
        ),
    ));
}

add_action('rest_api_init', 'register_api_address_post');