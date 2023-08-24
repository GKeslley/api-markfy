<?php

function api_product_post($request)
{

    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id > 0) {
        $name = sanitize_text_field($request['nome']);
        $price = sanitize_text_field($request['preco']);
        $description = sanitize_text_field($request['descricao']);
        $category = $request['categoria'];
        $subcategory = $request['subcategoria'];

        $stored_unique_key = get_user_meta($user_id, 'unique_key', true);
        $stored_unique_name = get_user_meta($user_id, 'unique_name', true);

        if ($name and $price and $description and $category) {
            $response = array(
               'post_author' => $user_id,
               'post_type' => 'produto',
               'post_title' => $name,
               'post_status' => 'publish',
               'meta_input' => array(
                 'nome' => $name,
                 'preco' => $price,
                 'categoria' => $category,
                 'subcategoria' => $subcategory,
                 'descricao' => $description,
                 'usuario_id' => $stored_unique_name,
                 'chave_unica' => $stored_unique_key,
                 'vendido' => 'false'
               )
             );

            $files = $request->get_file_params();

            if (sizeof($files) > 8) {
                $response = new WP_Error('badrequest', 'Não é possível enviar mais que oito imagens, tente novamente', array('status' => 400));
                return rest_ensure_response($response);
            }

            $product_id = wp_insert_post($response);

            if ($files) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                foreach ($files as $file => $array) {
                    media_handle_upload($file, $product_id);
                }
            }

        } else {
            $response = new WP_Error('dados', 'Dados incompletos', array('status' => 401));
        }

    } else {
        $response = new WP_Error('permissao', 'Usuário não possui permissão', array('status' => 401));
    }


    return rest_ensure_response($response);
}

function register_api_product_post()
{
    register_rest_route('api', 'produto', array(
      array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_product_post',
      ),
    ));
}

add_action('rest_api_init', 'register_api_product_post');