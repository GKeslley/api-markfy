<?php 

  function products_scheme($slug) {
    $post_id = getproduct_id_by_slug($slug);

     // Recupera o unique_key do metadado do produto
    $stored_unique_key = get_post_meta($post_id, 'unique_key', true);

    $args = array(
        'meta_key'   => 'post_ID',
        'meta_value' => $slug,
        'meta_compare' => '=', // Pode ser usado para comparar o valor do meta_key
    );

    $comments = get_comments($args);

    $comments_array = array();

    if ($post_id) {
      foreach ($comments as $comment) {
        // Obtém o ID do comentário
        $comment_id = $comment->comment_ID;
        $comment_author = $comment->comment_author;
        $comment_content = $comment->comment_content;
        $comment_date = $comment->comment_date;
        $comment_parent = $comment->comment_parent;


        // Obtém os metadados do comentário
        $post_ID = get_comment_meta($comment_id, 'post_ID', true);
        $comment_author_ID = get_comment_meta($comment_id, 'comment_author_ID', true);
        $comment_reply = get_comment_meta($comment_id, 'comment_reply', true);

        $comments_data = array(
            "comment_id" => $comment_id,
            "comment_author" => $comment_author,
            "comment_content" => $comment_content,
            "comment_parent" => $comment_parent,
            "comment_date" => $comment_date,
            "post_ID" => $post_ID,
            "comment_author_ID" => $comment_author_ID,
            "comment_reply" => $comment_reply
        );
        $comments_array[] = $comments_data;
      }
    
      $post_meta = get_post_meta($post_id);

      $images = get_attached_media('image', $post_id);
      $images_array = null;

      if ($images) {
        $images_array = array();
        foreach($images as $key => $value) {
          $images_array[] = array(
            'titulo' => $value->post_name,
            'src' => $value->guid
          );
        }
      }

       $args = array(
           'meta_key' => 'unique_key',
           'meta_value' => $post_meta['chave_unica'][0],
      );

      $users = get_users($args);

      $usuario_id = get_user_meta($users[0]->data->ID, 'unique_name', true);  
    
      $response = array(
        'id' => $slug,
        'fotos' => $images_array,
        'nome' => $post_meta['nome'][0],
        'preco' => $post_meta['preco'][0],
        'descricao' => $post_meta['descricao'][0],
        'categoria' => $post_meta['categoria'][0],
        'subcategoria' => $post_meta['subcategoria'][0],
        'vendido' => $post_meta['vendido'][0],
        'usuario_id' => $usuario_id,
        'nome_usuario' => $users[0]->data->display_name,
        'comentarios' => $comments_array,
        'slug' => $slug
      );

    } else {
      $response = new WP_Error('naoexiste', 'Produto não encontrado', array('status' => 404));
    }

    return $response;
  }

  function api_product_get($request) {
    $response = products_scheme($request['slug']);

    return rest_ensure_response($response);
  }

  function registrar_api_product_get() {
    register_rest_route('api', '/produto/(?P<slug>[-\w]+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_product_get',
      ),
    ));
  }

  add_action('rest_api_init', 'registrar_api_product_get');

  // PRODUTOS

  function api_products_get($request) {

    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_total']) ?: 9;
    $usuario_id = sanitize_text_field($request['_user']) ?: null;
    $order = $request['_order'] ?: null;

    $args = array(
    'meta_key' => 'unique_name',
    'meta_value' => $usuario_id,
    'meta_compare' => '='
    );

    $usuarios = get_users($args);

    foreach ($usuarios as $usuario) {
        $chave_unica = get_user_meta($usuario->ID, 'unique_key', true);
    }

    $usuario_id_query = null;
    if ($usuario_id) {
      $usuario_id_query = array(
        'key' => 'chave_unica',
        'value' => $chave_unica,
        'compare' => '='
      );
    } 

    $vendido = array(
      'key' => 'vendido',
      'value' => 'false',
      'compare' => '='
    );

    $query = array(
      'post_type' => 'produto',
      'posts_per_page' => $_limit,
      'paged' => $_page,
      's' => $q,
      'meta_query' => array(
        $usuario_id_query,
        $vendido
      )
    );

    if ($order) {
      $query['meta_key'] = 'preco';
      $query['orderby'] = 'preco';
      $query['order'] = $order;
    }


    $loop = new WP_Query($query);

    if (!$loop->have_posts()) {
       $response = new WP_Error('naoexiste', 'Produto não encontrado', array('status' => 404));
       return $response;
    }

    $posts = $loop->posts;
    $total = $loop->found_posts;

    $produtos = array();

   foreach($posts as $key => $value) {
     $produtos[] = products_scheme($value->post_name);
   }

   $response = rest_ensure_response($produtos);
   $response->header('X-Total-Count', $total);

    return $response;
  }

  function registrar_api_products_get() {
    register_rest_route('api', 'produtos', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_products_get',
      ),
    ));
  }

  add_action('rest_api_init', 'registrar_api_products_get');

  function get_produto_categoria_scheme($request) {
    $categoria = $request['categoria'];
    $subcategoria = $request['subcategoria'];
    $usuario_id = $request['user'];
    $order = $request['_order'] ?: null;

    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_limit']) ?: 9;

    $args = array(
    'meta_key' => 'unique_name',
    'meta_value' => $usuario_id,
    'meta_compare' => '='
    );

    $usuarios = get_users($args);

    foreach ($usuarios as $usuario) {
        $chave_unica = get_user_meta($usuario->ID, 'unique_key', true);
    }

    $usuario_id_query = null;

    if ($usuario_id) {
      $usuario_id_query = array(
        'key' => 'chave_unica',
        'value' => $chave_unica,
        'compare' => '='
      );
    }

    $categoria_query = null;

    if ($categoria) {
      $categoria_query = array(
        'key' => 'categoria',
        'value' => $categoria,
        'compare' => '='
      );
    }

    $subcategoria_query = null;
    if ($subcategoria) {
      $subcategoria_query = array(
        'key' => 'subcategoria',
        'value' => $subcategoria,
        'compare' => '='
      );
    }

    $query = array(
      'post_type' => 'produto',
      'posts_per_page' => $_limit,
      'paged' => $_page,
      's' => $q,
      'meta_query' => array(
        $usuario_id_query,
        $categoria_query,
        $subcategoria_query
      )
    );

    if ($order) {
      $query['meta_key'] = 'preco';
      $query['orderby'] = 'preco';
      $query['order'] = $order;
    }

    $loop = new WP_Query($query);
    $posts = $loop->posts;
    $total = $loop->found_posts;

    $produtos = array();
    foreach($posts as $key => $value) {
      $produtos[] = products_scheme($value->post_name);
    }


    $response = rest_ensure_response($produtos);
    $response->header('X-Total-Count', $total);

    return rest_ensure_response($response);
  }

  function get_produto_by_categoria($request) {
    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_limit']) ?: 9;

    return get_produto_categoria_scheme($request);
  }


  function registrar_api_products_categoria() {
    register_rest_route('api', 'produtos/(?P<categoria>[-\w]+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_produto_by_categoria',
      ),
    ));
  }

  add_action('rest_api_init', 'registrar_api_products_categoria');

  function get_produto_by_subcategoria($request) {
    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_limit']) ?: 9;
    
    
    return get_produto_categoria_scheme($request);
  }

  function registrar_api_products_subcategoria() {
    register_rest_route('api', 'produtos/(?P<categoria>[-\w]+)/(?P<subcategoria>[-\w]+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_produto_by_subcategoria',
      ),
    ));
  }

  add_action('rest_api_init', 'registrar_api_products_subcategoria');


    

function api_products_endereco_get($request) {
    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_limit']) ?: 9;

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
