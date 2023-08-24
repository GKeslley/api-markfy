<?php

function api_user_post_comment($request)
{
    $comment_author = sanitize_text_field($request['author']);
    $comment_content = sanitize_text_field($request['content']);
    $comment_post_ID = sanitize_text_field($request['idPost']);
    $comment_author_ID = sanitize_text_field($request['idUser']);
    $comment_parent_ID = $request['commentParentID'] ?: 0;

    $time = current_time('mysql');

    if ($comment_author and $comment_content and $comment_post_ID and $comment_author_ID) {
        $commentData = array(
           'comment_author' => $comment_author, // nome do autor do comentário
           'comment_content' => $comment_content, // conteúdo do comentário
           'comment_date' => $time,
           'comment_parent' => $comment_parent_ID,
           'comment_type' => '', // tipo de comentário (vazio para comentários normais)
           'comment_post_ID' => $comment_post_ID
        );

        if ($comment_parent_ID) {
            $parent_comment = get_comment($comment_parent_ID);
            update_comment_meta($parent_comment->comment_ID, 'comment_reply', $comment_content);
            return rest_ensure_response(
                array('comment_reply' => $comment_content,
                                         'parent_id' => $comment_parent_ID)
            );
        }

        $comment_id = wp_insert_comment($commentData);

        add_comment_meta($comment_id, 'comment_ID', $comment_id);
        add_comment_meta($comment_id, 'post_id', $comment_post_ID);
        add_comment_meta($comment_id, 'comment_author_ID', $comment_author_ID);

        $response = array(
            'comment_author' => $comment_author,
            'comment_content' => $comment_content,
            'comment_date' => $time,
            'comment_parent' => $comment_parent_ID,
            'comment_post_ID' => $comment_post_ID,
            'meta' => array(
                'comment_ID' => $comment_id,
                'post_id' => $comment_post_ID,
                'comment_author_ID' => $comment_author_ID,
            ),
        );

    } else {
        $response = new WP_Error('comentario', 'Não foi possivel realizar este comentário.', array('status' => 401));
    }

    return rest_ensure_response($response);
}

function register_api_user_post_comment()
{
    register_rest_route('api', 'comentario', array(
      array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_user_post_comment',
      ),
    ));
}

add_action('rest_api_init', 'register_api_user_post_comment');


//// GET


function api_product_get_comments($request, $args = false)
{
    $slug = $request['slug'];
    $total = $request['_total'] ?: 9;
    $page = $request['_page'] ?: 1;

    $post_id = getproduct_id_by_slug($slug);

    if ($args == false) {
        $args = array(
           'meta_key'   => 'post_id',
           'meta_value' => $slug,
           'number' => $total,
           'paged' => $page,
           'meta_compare' => '='
        );
    }

    $comments = get_comments($args);
    $comments_array = array();

    if ($post_id) {
        if ($comments) {
            foreach ($comments as $comment) {
                $comment_id = $comment->comment_ID;
                $comment_author = $comment->comment_author;
                $comment_content = $comment->comment_content;
                $comment_date = $comment->comment_date;
                $comment_parent = $comment->comment_parent;

                $post_id = get_comment_meta($comment_id, 'post_id', true);
                $comment_author_ID = get_comment_meta($comment_id, 'comment_author_ID', true);
                $comment_reply = get_comment_meta($comment_id, 'comment_reply', true);

                $comments_data = array(
                    "comment_id" => $comment_id,
                    "comment_author" => $comment_author,
                    "comment_content" => $comment_content,
                    "comment_parent" => $comment_parent,
                    "comment_date" => $comment_date,
                    "post_id" => $post_id,
                    "comment_author_ID" => $comment_author_ID,
                    "comment_reply" => $comment_reply
                );
                $comments_array[] = $comments_data;
            }
            $response = $comments_array;
        } else {
            $response = [];
        }
    } else {
        $response = new WP_Error('comentario', 'Produto não encontrado.', array('status' => 404));
    }

    return $response;
}

function register_api_user_get_comments()
{
    register_rest_route('api', '/comentarios/(?P<slug>[-\w]+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_product_get_comments',
      ),
    ));
}

add_action('rest_api_init', 'register_api_user_get_comments');