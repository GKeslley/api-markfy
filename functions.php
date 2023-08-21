<?php 

function allow_cors_header() {
    header("Access-Control-Allow-Origin: http://localhost:3000");
}
add_action('init', 'allow_cors_header');

$template_diretorio = get_template_directory();

require_once($template_diretorio . '/custom-post-type/produto.php');
require_once($template_diretorio . '/custom-post-type/transacao.php');
require_once($template_diretorio . '/custom-post-type/curtida.php');
require_once($template_diretorio . '/endpoints/user_post.php');
require_once($template_diretorio . '/endpoints/user_get.php');
require_once($template_diretorio . '/endpoints/user_put.php');
require_once($template_diretorio . '/endpoints/user_comment.php');
require_once($template_diretorio . '/endpoints/product_post.php');
require_once($template_diretorio . '/endpoints/product_get.php');
require_once($template_diretorio . '/endpoints/product_delete.php');
require_once($template_diretorio . '/endpoints/address_post.php');
require_once($template_diretorio . '/endpoints/like_posts.php');
require_once($template_diretorio . '/endpoints/like_posts_get.php');
require_once($template_diretorio . '/endpoints/like_posts_delete.php');
require_once($template_diretorio . '/endpoints/profile_photo_post.php');
require_once($template_diretorio . '/endpoints/transacao_post.php');
require_once($template_diretorio . '/endpoints/transacao_get.php');


add_filter('jwt_auth_whitelist', function ($endpoints) {
  $endpoints[] = '/wp-json/api/usuario/(?P<usuario>[-\w]+)';
  return $endpoints;
});

function getproduct_id_by_slug($slug) {
  $query = new WP_Query(array(
    'name' => $slug,
    'post_type' => 'produto',
    'numberposts' => 1,
    'fields' => 'ids'
  ));

  $posts = $query->get_posts();

  return array_shift($posts);
}

add_action('rest_pre_serve_request', function () {
  header('Access-Control-Expose-Headers: X-Total-Count');
});

function expire_token() {
  return time() + (60*60*24);
}

add_action('jwt_auth_expire', 'expire_token');

function my_login_failed_message( $username ) {
  $error = new WP_Error();
  $error->add( 'invalid_login', __( 'A senha fornecida para o email está incorreta'));
  return $error;
}

add_filter( 'wp_login_failed', 'my_login_failed_message' );

?>
