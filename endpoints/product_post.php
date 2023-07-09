<?php 
  function api_product_post($request) {

  $user = wp_get_current_user();
  $user_id = $user->ID;

  if ($user_id > 0) {
    $nome = sanitize_text_field($request['nome']);
    $preco = sanitize_text_field($request['preco']);
    $descricao = sanitize_text_field($request['descricao']);
    $categoria = $request['categoria'];
    $subcategoria = $request['subcategoria'];
    $stored_unique_key = get_user_meta($user_id, 'unique_key', true);
    $stored_unique_name = get_user_meta($user_id, 'unique_name', true);

    $usuario_id = $user->user_login;

    if ($nome and $preco and $descricao and $categoria) {
       $response = array(
          'post_author' => $user_id,
          'post_type' => 'produto',
          'post_title' => $nome,
          'post_status' => 'publish',
          'meta_input' => array(
            'nome' => $nome,
            'preco' => $preco,
            'categoria' => $categoria,
            'subcategoria' => $subcategoria,
            'descricao' => $descricao,
            'usuario_id' => $stored_unique_name,
            'chave_unica' => $stored_unique_key,
            'vendido' => 'false'
          )
        );

        $produto_id = wp_insert_post($response);
        $response['id'] = get_post_field('post_name', $produto_id); 

        $files = $request->get_file_params();

        if ($files) {
          require_once(ABSPATH . 'wp-admin/includes/image.php');
          require_once(ABSPATH . 'wp-admin/includes/file.php');
          require_once(ABSPATH . 'wp-admin/includes/media.php');

          foreach ($files as $file => $array) {
            media_handle_upload($file, $produto_id);
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

  function registrar_api_product_post() {
    register_rest_route('api', 'produto', array(
      array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_product_post',
      ),
    ));
  }

  add_action('rest_api_init', 'registrar_api_product_post');

?>
