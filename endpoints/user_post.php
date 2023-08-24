<?php

function api_user_post($request)
{
    $name = sanitize_text_field($request['nome']);
    $email = sanitize_email($request['email']);
    $password = sanitize_text_field($request['senha']);

    $email_exists = email_exists($email);

    if ($name && $email && $password && !$email_exists) {
        $user_id = wp_create_user($email, $password, $email);

        $random_numbers = wp_generate_password(3, false);
        $unique_key = md5('unique_key_prefix' . $user_id);
        $unique_name = '@' . $name . substr(sha1('unique_key_prefix' . $user_id), 0, 8);

        update_user_meta($user_id, 'unique_key', $unique_key);
        update_user_meta($user_id, 'unique_name', $unique_name);

        $stored_unique_key = get_user_meta($user_id, 'unique_key', true);
        $stored_unique_name = get_user_meta($user_id, 'unique_name', true);

        $response = array(
          'ID' => $user_id,
          'display_name' => $name,
          'post-type' => 'usuario',
          'first_name' => $name,
          'role' => 'subscriber',
          'unique_key' => $stored_unique_key,
          'unique_name' => $stored_unique_name
        );

        wp_update_user($response);
    } else {
        $response = new WP_Error('email', 'Email já cadastrado.', array('status' => 403));
    }

    return rest_ensure_response($response);
}

function register_api_user_post()
{
    register_rest_route('api', 'usuario', array(
      array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_user_post',
      ),
    ));
}

add_action('rest_api_init', 'register_api_user_post');