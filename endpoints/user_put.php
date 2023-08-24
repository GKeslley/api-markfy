<?php

function api_user_put($request)
{

    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id > 0) {
        $name = sanitize_text_field($request['name']);
        $email = sanitize_email($request['email']);
        $password = sanitize_text_field($request['senha']) ?: $user->user_pass;
        $phone_number = sanitize_text_field($request['phone']) ?: '';

        $email_exists = username_exists($email);

        if (!$email_exists || $email_exists === $user_id) {
            if ($user->display_name !== $name) {
                $unique_name = '@' . $name . substr(sha1('unique_key_prefix' . $user_id), 0, 8);
                update_user_meta($user_id, 'unique_name', $unique_name);
            }
            update_user_meta($user_id, 'phone_number', $phone_number);

            $stored_unique_name = get_user_meta($user_id, 'unique_name', true);

            $response = array(
              'ID' => $user_id,
              'user_email' => $email,
              'user_pass' => $password,
              'display_name' => $name,
              'user_nicename' => $name,
              'nickname' => $name,
              'first_name' => $name,
              'unique_name' => $stored_unique_name
            );

            global $wpdb;
            $wpdb->update(
                $wpdb->users,
                array('user_login' => $email),
                array('ID' => $user_id)
            );

            wp_update_user($response);
        } else {
            $response = new WP_Error('email', 'Email já cadastrado.', array('status' => 403));
        }
    } else {
        $response = new WP_Error('permissao', 'Usuário não possui permissão', array('status' => 401));
    }

    return rest_ensure_response($response);
}

function registrar_api_user_put()
{
    register_rest_route('api', 'usuario', array(
      array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'api_user_put',
      ),
    ));
}

add_action('rest_api_init', 'registrar_api_user_put');