<?php 
  function api_transacao_post($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;
  
  $sell_product = $request['produto']['vendido'] === 'false';

  if ($user_id > 0) {
    $product = json_encode($request['produto'], JSON_UNESCAPED_UNICODE);
    $product_slug = sanitize_text_field($request['produto']['id']);
    $product_name = sanitize_text_field($request['produto']['nome']);
    $buyer_id = sanitize_text_field($request['comprador_id']);
    $seller_id = sanitize_text_field($request['vendedor_id']);
    $address = json_encode($request['endereco'], JSON_UNESCAPED_UNICODE);

    $buyer_email = get_userdata($user_id)->user_email;
    $buyer_key = get_user_meta($user_id, 'unique_key', true);

    $product_id = getproduct_id_by_slug($product_slug);
    $sell_product = get_post_meta($product_id, 'vendido', true);
    if ($sell_product === 'true') {
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
        'comprador_id' => $buyer_id,
        'vendedor_id' => $seller_id,
        'endereco' => $address,
        'produto' => $product,
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
