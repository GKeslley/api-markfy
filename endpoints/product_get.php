<?php


function products_scheme($slug)
{
    $post_id = getproduct_id_by_slug($slug);

    $stored_unique_key = get_post_meta($post_id, 'unique_key', true);

    $args = array(
      'meta_key'   => 'post_id',
      'meta_value' => $slug,
      'number' => 7,
      'meta_compare' => '='
    );

    $comments_array = api_product_get_comments(array('slug' => $slug), $args);

    if ($post_id) {
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

        $argsFindUser = array(
            'meta_key' => 'unique_key',
            'meta_value' => $post_meta['chave_unica'][0],
        );

        $user = get_users($argsFindUser);
        $user_unique_name = get_user_meta($user[0]->data->ID, 'unique_name', true);

        $response = array(
          'id' => $slug,
          'fotos' => $images_array,
          'nome' => $post_meta['nome'][0],
          'preco' => $post_meta['preco'][0],
          'descricao' => $post_meta['descricao'][0],
          'categoria' => $post_meta['categoria'][0],
          'subcategoria' => $post_meta['subcategoria'][0],
          'vendido' => $post_meta['vendido'][0],
          'usuario_id' => $user_unique_name,
          'nome_usuario' => $user[0]->data->display_name,
          'comentarios' => $comments_array,
          'slug' => $slug
        );

    } else {
        $response = new WP_Error('naoexiste', 'Produto não encontrado', array('status' => 404));
    }

    return $response;
}

function api_product_get($request)
{
    $response = products_scheme($request['slug']);

    return rest_ensure_response($response);
}

function registrar_api_product_get()
{
    register_rest_route('api', '/produto/(?P<slug>[-\w]+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_product_get',
      ),
    ));
}

add_action('rest_api_init', 'registrar_api_product_get');

// PRODUTOS

function api_products_get($request, $sold = false)
{

    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 1;
    $_limit = sanitize_text_field($request['_total']) ?: '9';
    $user = sanitize_text_field($request['_user']) ?: null;
    $order = $request['_order'] ?: null;
    $user_query = null;

    if ($user) {
        $args = array(
          'meta_key' => 'unique_name',
          'meta_value' => $user,
          'meta_compare' => '='
        );

        $find_user = get_users($args);

        foreach ($find_user as $userID) {
            $unique_key = get_user_meta($userID->ID, 'unique_key', true);
        }

        $user_query = array(
          'key' => 'chave_unica',
          'value' => $unique_key,
          'compare' => '='
        );
    }

    $sold = array(
      'key' => 'vendido',
      'value' => $sold ? 'true' : 'false',
      'compare' => '='
    );

    $query = array(
      'post_type' => 'produto',
      'posts_per_page' => $_limit,
      'paged' => $_page,
      's' => $q,
      'meta_query' => array(
        $sold,
        $user_query
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

    $products = array();

    foreach($posts as $key => $value) {
        $products[] = products_scheme($value->post_name, true);
    }

    $response = rest_ensure_response($products);
    $response->header('X-Total-Count', $total);

    return $response;
}

function register_api_products_get()
{
    register_rest_route('api', 'produtos', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_products_get',
      ),
    ));
}

add_action('rest_api_init', 'register_api_products_get');

// GET PRODUCTS SOLD

function get_products_sold($request)
{
    return api_products_get($request, true);
}

function endpoint_get_products_sold()
{
    register_rest_route('api', 'produtos/vendidos', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_products_sold',
      ),
    ));
}

add_action('rest_api_init', 'endpoint_get_products_sold');

// -------- GET PRODUCTS SOLD ---------

function get_produto_categoria_scheme($request)
{
    $category = $request['categoria'];
    $subcategory = $request['subcategoria'];
    $user = $request['user'];
    $order = $request['_order'] ?: null;


    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_limit']) ?: 9;

    $user_query = null;

    if ($user) {
        $args = array(
          'meta_key' => 'unique_name',
          'meta_value' => $user,
          'meta_compare' => '='
        );

        $findUser = get_users($args);

        foreach ($findUser as $userID) {
            $unique_key = get_user_meta($userID->ID, 'unique_key', true);
        }

        $user_query = array(
          'key' => 'chave_unica',
            'value' => $unique_key,
            'compare' => '='
        );
    }

    $category_query = null;

    if ($category) {
        $category_query = array(
          'key' => 'categoria',
          'value' => $category,
          'compare' => '='
        );
    }

    $subcategory_query = null;
    if ($subcategory) {
        $subcategory_query = array(
          'key' => 'subcategoria',
          'value' => $subcategory,
          'compare' => '='
        );
    }

    $sell = array(
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
        $sell,
        $user_query,
        $category_query,
        $subcategory_query
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

    if (!$loop->have_posts()) {
        $response = new WP_Error('naoexiste', 'Produto não encontrado', array('status' => 404));
        return $response;
    }

    $products = array();

    foreach($posts as $key => $value) {
        $products[] = products_scheme($value->post_name);
    }

    $response = rest_ensure_response($products);
    $response->header('X-Total-Count', $total);

    return rest_ensure_response($response);
}

function get_product_by_category($request)
{
    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_limit']) ?: 9;

    return get_produto_categoria_scheme($request);
}


function register_api_products_category()
{
    register_rest_route('api', 'produtos/(?P<categoria>[-\w]+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_product_by_category',
      ),
    ));
}

add_action('rest_api_init', 'register_api_products_category');

function get_product_by_subcategory($request)
{
    return get_produto_categoria_scheme($request);
}

function register_api_products_subcategory()
{
    register_rest_route('api', 'produtos/(?P<categoria>[-\w]+)/(?P<subcategoria>[-\w]+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_product_by_subcategory',
      ),
    ));
}

add_action('rest_api_init', 'register_api_products_subcategory');