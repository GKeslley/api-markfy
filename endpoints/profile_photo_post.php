<?php 

function api_user_profile_photo_post($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id > 0) {
        $files = $request->get_file_params();

        if ($files) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            
            $profile_photo_id = get_user_meta($user_id, 'profile_photo', true);

            
            if ($profile_photo_id) {
                wp_delete_attachment($profile_photo_id, true);
            }

            
            foreach ($files as $file => $array) {
                $attachment_id = media_handle_upload($file, 0);
            }

            if (is_wp_error($attachment_id)) {
                return rest_ensure_response($attachment_id);
            }

            update_user_meta($user_id, 'profile_photo', $attachment_id);

            $response = array(
                'success' => true,
                'message' => 'Foto de perfil atualizada com sucesso'
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Deu erro'
            );
        }
    } else {
        $response = new WP_Error('permissao', 'Usuário não possui permissão', array('status' => 401));
    }

    return rest_ensure_response($response);
}

function registrar_api_user_profile_photo_post() {
    register_rest_route('api', 'usuario/perfil', array(
        array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'api_user_profile_photo_post',
        ),
    ));
}

add_action('rest_api_init', 'registrar_api_user_profile_photo_post');
