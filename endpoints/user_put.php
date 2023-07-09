<?php 
  function api_user_put($request) {

    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id > 0) {
      $nome = sanitize_text_field($request['nome']);
      $email = sanitize_email($request['email']);
      $senha = sanitize_text_field($request['senha']) ?: $user->user_pass; 

      $email_exists = username_exists($email);  

      if (!$email_exists || $email_exists === $user_id) {
        if ($user->display_name !== $nome) {
           $unique_name = '@' . $nome . substr(sha1('unique_key_prefix' . $user_id), 0, 8);
           update_user_meta($user_id, 'unique_name', $unique_name);
        }
        update_user_meta($user_id, 'phone_number', $senha);
        $stored_unique_name = get_user_meta($user_id, 'unique_name', true);

        $response = array(
          'ID' => $user_id,
          'user_email' => $email,
          'user_pass' => $senha,
          'display_name' => $nome,
          'user_nicename' => $email,
          'nickname' => $email,
          'first_name' => $nome,
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
    }  else {
      $response = new WP_Error('permissao', 'Usuário não possui permissão', array('status' => 401));
    }

    return rest_ensure_response($response);
  }

  function registrar_api_user_put() {
    register_rest_route('api', 'usuario', array(
      array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'api_user_put',
      ),
    ));
  }

  add_action('rest_api_init', 'registrar_api_user_put');

?>
