<?php
// Funciones para el envío de correos

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enviar un correo electrónico.
 *
 * Esta función servirá como punto de entrada para la lógica de envío de emails
 * en futuras versiones del plugin.
 */
function cdb_mails_send_email( $to, $subject, $message, $headers = array(), $attachments = array() ) {
    if ( empty( $to ) || ! is_email( $to ) ) {
        return false;
    }

    // Registrar intento de envío para depuración.
    if ( function_exists( 'cdb_mails_log' ) ) {
        cdb_mails_log( 'Llamada a wp_mail() para ' . $to );
    }

    $content_type = 'Content-Type: text/html; charset=UTF-8';

    if ( empty( $headers ) ) {
        $headers = array( $content_type );
    } else {
        if ( ! is_array( $headers ) ) {
            $headers = array( $headers );
        }

        $has_content_type = false;
        foreach ( $headers as $header ) {
            if ( stripos( $header, 'content-type' ) !== false ) {
                $has_content_type = true;
                break;
            }
        }

        if ( ! $has_content_type ) {
            $headers[] = $content_type;
        }
    }

    return wp_mail( $to, $subject, $message, $headers, $attachments );
}

// Lanzar la notificación cuando se publica una nueva valoración. El CPT puede
// llamarse "cdb_valoracion" u "valoracion". Registramos el hook para ambos por
// compatibilidad con diferentes implementaciones del sitio.
add_action( 'save_post_cdb_valoracion', 'cdb_mails_new_valoracion_notification', 10, 3 );
add_action( 'save_post_valoracion', 'cdb_mails_new_valoracion_notification', 10, 3 );

// Integración con cdb-grafica: recibir notificación cuando se inserta una
// valoración directamente en las tablas personalizadas.
add_action( 'cdb_grafica_insert_bar_result', 'cdb_mails_handle_bar_result', 10, 1 );
add_action( 'cdb_grafica_insert_empleado_result', 'cdb_mails_handle_empleado_result', 10, 1 );

/**
 * Enviar notificación cuando se publique una nueva valoración.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated.
 */
function cdb_mails_new_valoracion_notification( $post_id, $post, $update ) {
    // Evitar envíos durante autosave o al actualizar una valoración existente.
    if ( function_exists( 'cdb_mails_log' ) ) {
        cdb_mails_log( 'Hook save_post_* disparado para post ' . $post_id );
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( $update ) { // Solo en creaciones nuevas, no en ediciones.
        return;
    }

    if ( 'publish' !== $post->post_status ) {
        return;
    }

    $employee_id = (int) get_post_meta( $post_id, 'empleado_id', true );
    if ( ! $employee_id ) {
        $employee_id = (int) $post->post_parent;
    }

    if ( ! $employee_id ) {
        return;
    }

    $employee = get_post( $employee_id );
    if ( ! $employee ) {
        return;
    }

    $user = get_user_by( 'id', $employee->post_author );
    if ( ! $user || ! is_email( $user->user_email ) ) {
        return;
    }

    cdb_mails_ensure_default_template();
    $template = cdb_mails_get_template_by_name( 'Nueva valoración recibida' );
    if ( ! $template ) {
        return;
    }

    $vars = array(
        '{send_date}'          => date_i18n( get_option( 'date_format' ) ),
        '{user_name}'          => $user->display_name,
        '{bar_name}'           => get_post_meta( $post_id, 'bar_name', true ),
        // Resumen de la valoración. Si no existe el meta, se recorta el contenid
        // o de la valoración como fallback.
        '{valoracion_resumen}' => get_post_meta( $post_id, 'valoracion_resumen', true ) ? get_post_meta( $post_id, 'valoracion_resumen', true ) : wp_trim_words( $post->post_content, 55 ),
        '{profile_url}'        => get_permalink( $employee_id ),
        '{review_date}'        => get_the_date( get_option( 'date_format' ), $post_id ),
    );

    $subject = str_replace( array_keys( $vars ), array_values( $vars ), $template['subject'] );
    $body    = str_replace( array_keys( $vars ), array_values( $vars ), $template['body'] );

    cdb_mails_send_email( $user->user_email, $subject, $body );
}

/**
 * Procesar inserciones directas en las tablas personalizadas de cdb-grafica.
 * Estas funciones actúan como puente con el plugin de gráficas y llaman a la
 * función global encargada de enviar la notificación.
 */
function cdb_mails_handle_bar_result( $review_id ) {
    if ( function_exists( 'cdb_mails_log' ) ) {
        cdb_mails_log( 'Hook cdb_grafica_insert_bar_result recibido para ID ' . $review_id );
    }
    cdb_mails_send_new_review_notification( $review_id, 'bar' );
}

function cdb_mails_handle_empleado_result( $review_id ) {
    if ( function_exists( 'cdb_mails_log' ) ) {
        cdb_mails_log( 'Hook cdb_grafica_insert_empleado_result recibido para ID ' . $review_id );
    }
    cdb_mails_send_new_review_notification( $review_id, 'empleado' );
}
