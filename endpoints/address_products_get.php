<?php 

function api_products_endereco_get($request) {
   $response = array('message' => 'aaaaaaaaa');
   return rest_ensure_response($response);
}

function enderecos() {
    register_rest_route('api', 'produtos/endereco', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_products_endereco_get',
      ),
    ));
}

add_action('rest_api_init', 'enderecos');


?>

