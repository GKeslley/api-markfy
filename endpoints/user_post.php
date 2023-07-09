<?php 

  function api_user_post($request) {
    $nome = sanitize_text_field($request['nome']);
    $email = sanitize_email($request['email']);
    $senha = sanitize_text_field($request['senha']);

    $email_exists = email_exists($email);

    if ($nome && $email && $senha && !$email_exists) {
      $userID = wp_create_user($email, $senha, $email);

      $random_numbers = wp_generate_password(3, false);

       // Gere um identificador único estável usando a função md5() e o ID do usuário
       $unique_key = md5('unique_key_prefix' . $userID);
       $unique_name = '@' . $nome . substr(sha1('unique_key_prefix' . $userID), 0, 8);

      // Armazena o unique_key como um metadado do usuário
      update_user_meta($userID, 'unique_key', $unique_key);
      update_user_meta($userID, 'unique_name', $unique_name);

      // Recupera o unique_key do metadado do usuário
      $stored_unique_key = get_user_meta($userID, 'unique_key', true);
      $stored_unique_name = get_user_meta($userID, 'unique_name', true);

      $response = array(
        'ID' => $userID,
        'display_name' => $nome,
        'post-type' => 'usuario',
        'first_name' => $nome,
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

  function registrar_api_user_post() {
    register_rest_route('api', 'usuario', array(
      array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_user_post',
      ),
    ));
  }

  add_action('rest_api_init', 'registrar_api_user_post');

?>
