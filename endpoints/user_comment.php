<?php 

  function api_user_post_comment($request) {
    $comment_author = sanitize_text_field($request['author']);
    $comment_content = sanitize_text_field($request['content']);

    $comment_post_ID = sanitize_text_field($request['idPost']);
    $comment_author_ID = sanitize_text_field($request['idUser']);

    $comment_parent_ID = $request['commentParentID'] ?: 0;

    $time = current_time('mysql');

    if ($comment_author and $comment_content and $comment_post_ID and $comment_author_ID) {
         $commentData = array (
            'comment_author' => $comment_author, // nome do autor do comentário
            'comment_content' => $comment_content, // conteúdo do comentário
            'comment_date' => $time,
            'comment_parent' => $comment_parent_ID,
            'comment_type' => '', // tipo de comentário (vazio para comentários normais)
         );

         if ($comment_parent_ID) {
           $parent_comment = get_comment($comment_parent_ID);
           update_comment_meta($parent_comment->comment_ID, 'comment_reply', $comment_content);
           die();
        }

         $comment_id = wp_insert_comment($commentData);
         
         add_comment_meta($comment_id, 'comment_ID', $comment_id);
         add_comment_meta($comment_id, 'post_ID', $comment_post_ID);
         add_comment_meta($comment_id, 'comment_author_ID', $comment_author_ID);

        $response = array(
            'comment_author' => $comment_author,
            'comment_content' => $comment_content,
            'comment_date' => $time,
            'comment_parent' => $comment_parent_ID,
            'meta' => array(
                'comment_ID' => $comment_id,
                'post_ID' => $comment_post_ID,
                'comment_author_ID' => $comment_author_ID,
            ),
        );

    } else {
      $response = new WP_Error('comentario', 'Não foi possivel realizar este comentário.', array('status' => 401));
    }

    return rest_ensure_response($response);
  }

  function registrar_api_user_post_comment() {
    register_rest_route('api', 'comentario', array(
      array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_user_post_comment',
      ),
    ));
  }

  add_action('rest_api_init', 'registrar_api_user_post_comment');

?>
