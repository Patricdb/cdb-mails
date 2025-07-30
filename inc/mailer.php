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

add_action( 'save_post_valoracion', 'cdb_mails_new_valoracion_notification', 10, 3 );

/**
 * Enviar notificación cuando se publique una nueva valoración.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated.
 */
function cdb_mails_new_valoracion_notification( $post_id, $post, $update ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
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
        '{valoracion_resumen}' => wp_trim_words( $post->post_content, 55 ),
        '{profile_url}'        => get_permalink( $employee_id ),
        '{review_date}'        => get_the_date( get_option( 'date_format' ), $post_id ),
    );

    $subject = str_replace( array_keys( $vars ), array_values( $vars ), $template['subject'] );
    $body    = str_replace( array_keys( $vars ), array_values( $vars ), $template['body'] );

    cdb_mails_send_email( $user->user_email, $subject, $body );
}
