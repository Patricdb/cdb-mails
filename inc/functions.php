<?php
// Funciones globales utilizadas en cdb-mails

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Envía la notificación por email de nueva valoración.
 *
 * @param int    $review_id ID de la nueva valoración (fila en la tabla personalizada)
 * @param string $type      'bar' o 'empleado'
 */
function cdb_mails_send_new_review_notification( $review_id, $type ) {
    global $wpdb;

    // Determinar tabla y obtener datos básicos
    if ( $type === 'empleado' ) {
        $table = $wpdb->prefix . 'grafica_empleado_results';
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $review_id ) );
        if ( ! $row ) {
            return;
        }
        $post_id = $row->post_id;
        $user_id = $row->user_id;
    } elseif ( $type === 'bar' ) {
        $table = $wpdb->prefix . 'grafica_bar_results';
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $review_id ) );
        if ( ! $row ) {
            return;
        }
        $post_id = $row->post_id;
        $user_id = $row->user_id;
    } else {
        return;
    }

    // Obtener datos del usuario valorado (destinatario)
    $user = get_userdata( $user_id );
    if ( ! $user || ! $user->user_email ) {
        return;
    }

    // Obtener nombre del usuario valorado
    $user_name = $user->display_name;

    // Obtener nombre del bar (o del empleado si se prefiere personalizar)
    $bar_name = get_the_title( $post_id );

    // Preparar resumen de valoración
    $valoracion_resumen = 'Nueva valoración recibida.';

    // URL al perfil del usuario valorado
    $profile_url = get_author_posts_url( $user_id );

    // Fecha de envío y fecha de la valoración
    $send_date   = date_i18n( 'd \d\e F \d\e Y' );
    $review_date = date_i18n( 'd \d\e F \d\e Y', strtotime( $row->created_at ?? 'now' ) );

    // Buscar la plantilla “Nueva valoración recibida” en la tabla de plantillas
    $tpl_table = $wpdb->prefix . 'cdb_mail_templates';
    $tpl       = $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM $tpl_table WHERE name = %s LIMIT 1", 'Nueva valoración recibida' )
    );

    if ( ! $tpl ) {
        return;
    }

    // Sustituir variables en el cuerpo del email
    $search  = array( '{send_date}', '{user_name}', '{bar_name}', '{valoracion_resumen}', '{profile_url}', '{review_date}' );
    $replace = array( $send_date, $user_name, $bar_name, $valoracion_resumen, $profile_url, $review_date );

    $subject = str_replace( $search, $replace, $tpl->subject );
    $body    = str_replace( $search, $replace, $tpl->body );

    // Enviar email
    wp_mail( $user->user_email, $subject, $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
}

