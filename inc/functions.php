<?php
// Funciones globales utilizadas en cdb-mails

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Escribir un mensaje en el log del plugin y almacenarlo para mostrarlo en el
 * administrador.
 *
 * @param string $message Mensaje a registrar.
 */
function cdb_mails_log( $message ) {
    // Registrar en el log de PHP cuando WP_DEBUG_LOG está habilitado.
    if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        error_log( '[cdb-mails] ' . $message );
    }

    // Guardar un historial reducido para mostrarlo en el admin.
    $errors   = get_option( 'cdb_mails_errors', array() );
    $errors[] = date_i18n( 'Y-m-d H:i:s' ) . ' - ' . $message;
    if ( count( $errors ) > 20 ) { // Limitar a los últimos 20 mensajes.
        $errors = array_slice( $errors, -20 );
    }
    update_option( 'cdb_mails_errors', $errors );
}

/**
 * Envía la notificación por email de nueva valoración.
 *
 * @param int    $review_id ID de la nueva valoración (fila en la tabla personalizada)
 * @param string $type      'bar' o 'empleado'
 */
function cdb_mails_send_new_review_notification( $review_id, $type ) {
    global $wpdb;

    // Log de inicio de la función con los parámetros recibidos.
    cdb_mails_log( sprintf( 'Iniciando envio de notificación. review_id=%d, type=%s', $review_id, $type ) );

    // Determinar tabla y obtener datos básicos.
    if ( $type === 'empleado' ) {
        $table = $wpdb->prefix . 'grafica_empleado_results';
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $review_id ) );
        if ( ! $row ) {
            cdb_mails_log( 'No se encontró la valoración de empleado con ID ' . $review_id );
            return;
        }
        cdb_mails_log( 'Valoración de empleado encontrada. Post ID ' . $row->post_id . ' / User ID ' . $row->user_id );
        $post_id = $row->post_id;
        $user_id = $row->user_id;
    } elseif ( $type === 'bar' ) {
        $table = $wpdb->prefix . 'grafica_bar_results';
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $review_id ) );
        if ( ! $row ) {
            cdb_mails_log( 'No se encontró la valoración de bar con ID ' . $review_id );
            return;
        }
        cdb_mails_log( 'Valoración de bar encontrada. Post ID ' . $row->post_id . ' / User ID ' . $row->user_id );
        $post_id = $row->post_id;
        $user_id = $row->user_id;
    } else {
        return;
    }

    // Obtener datos del usuario valorado (destinatario)
    $user = get_userdata( $user_id );
    if ( ! $user || ! $user->user_email ) {
        cdb_mails_log( 'Usuario sin email para la valoración ' . $review_id );
        return;
    }

    cdb_mails_log( 'Se enviará notificación a ' . $user->user_email );

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

    // Asegurar que la plantilla por defecto existe.
    cdb_mails_ensure_default_template();
    $tpl_table = $wpdb->prefix . 'cdb_mail_templates';
    $tpl       = $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM $tpl_table WHERE name = %s LIMIT 1", 'Nueva valoración recibida' )
    );

    if ( ! $tpl ) {
        cdb_mails_log( 'Plantilla "Nueva valoración recibida" no encontrada.' );
        return;
    }

    cdb_mails_log( 'Plantilla cargada correctamente. Procediendo al envío.' );

    // Sustituir variables en el cuerpo del email
    $search  = array( '{send_date}', '{user_name}', '{bar_name}', '{valoracion_resumen}', '{profile_url}', '{review_date}' );
    $replace = array( $send_date, $user_name, $bar_name, $valoracion_resumen, $profile_url, $review_date );

    $subject = str_replace( $search, $replace, $tpl->subject );
    $body    = str_replace( $search, $replace, $tpl->body );

    // Enviar email utilizando el wrapper del plugin.
    $sent = cdb_mails_send_email( $user->user_email, $subject, $body );
    if ( $sent ) {
        cdb_mails_log( 'Notificación enviada correctamente a ' . $user->user_email );
    } else {
        cdb_mails_log( 'Fallo al enviar la notificación al usuario ID ' . $user_id );
    }
}

/**
 * Mostrar avisos en el administrador si existen errores registrados.
 */
function cdb_mails_admin_error_notice() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $errors = get_option( 'cdb_mails_errors', array() );
    if ( empty( $errors ) ) {
        return;
    }

    echo '<div class="notice notice-error"><p>';
    foreach ( $errors as $err ) {
        echo esc_html( $err ) . '<br />';
    }
    echo '</p></div>';

    delete_option( 'cdb_mails_errors' );
}
add_action( 'admin_notices', 'cdb_mails_admin_error_notice' );

