<?php 
  function api_transacao_post($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;
  
  $product = $request['produto'];
  $sell_product = $product['vendido'] === 'false';

  if ($user_id > 0) {
    $product_slug = sanitize_text_field($product['id']);
    $product_name = sanitize_text_field($product['nome']);
    $buyer_id = get_user_meta($user_id, 'unique_name', true);
    $seller_id = sanitize_text_field($request['vendedor_id']);
    $address = json_encode($request['endereco'], JSON_UNESCAPED_UNICODE);

    $data_product = array(
      "id" => $product_slug,
      "nome" => $product_name,
      "preco" => $product['preco'],
      "fotos" => $product['fotos'],
      "categoria" => $product['categoria'],
      "subcategoria" => $product['subcategoria'],
      "nome_usuario" => $product['nome_usuario']
    );

    $buyer_email = get_userdata($user_id)->user_email;
    $buyer_key = get_user_meta($user_id, 'unique_key', true);

    $product_id = getproduct_id_by_slug($product_slug);
    $sell_product = get_post_meta($product_id, 'vendido', true);
    
    if (!$sell_product) {
      $error = new WP_Error('não encontrado', 'Produto não encontrado', array('status' => 404));
      return rest_ensure_response($response);

    }
    update_post_meta($product_id, 'vendido', 'true');

    $response = array(
      'post_author' => $user_id,
      'post_type' => 'transacao',
      'post_title' => $buyer_id . ' - ' . $product_name,
      'post_status' => 'publish',
      'meta_input' => array(
        'comprador_id' => $buyer_key,
        'vendedor_id' => $seller_id,
        'endereco' => $address,
        'produto' => json_encode($data_product),
        'vendido' => 'false'
      )
    );

    $post_id = wp_insert_post($response);

  } else {
    $response = new WP_Error('permissao', 'Usuário não possui permissão', array('status' => 401));
  }

    return rest_ensure_response($response);
  }

  function registrar_api_transacao_post() {
    register_rest_route('api', 'transacao', array(
      array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_transacao_post',
      ),
    ));
  }

  add_action('rest_api_init', 'registrar_api_transacao_post');

?>