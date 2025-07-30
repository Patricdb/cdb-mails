<?php
// Gestión de plantillas de correo

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Obtener el nombre de la tabla de plantillas.
 */
function cdb_mails_templates_table() {
    global $wpdb;
    return $wpdb->prefix . 'cdb_mail_templates';
}

/**
 * Devolver las variables disponibles para usar en las plantillas.
 *
 * Para añadir nuevas variables, simplemente amplía el array
 * o aplica filtros utilizando `cdb_mails_template_vars`.
 */
function cdb_mails_available_vars() {
    $vars = array(
        '{send_date}'         => 'Fecha de envío',
        '{user_name}'         => 'Nombre de usuario',
        '{bar_name}'          => 'Nombre del bar',
        '{valoracion_resumen}' => 'Resumen de la valoración',
        '{profile_url}'       => 'Enlace al perfil',
        '{review_date}'       => 'Fecha de la valoración',
    );

    return apply_filters( 'cdb_mails_template_vars', $vars );
}

/**
 * Obtener todas las plantillas almacenadas.
 */
function cdb_mails_get_all_templates() {
    global $wpdb;

    $table = cdb_mails_templates_table();

    return $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC", ARRAY_A );
}

/**
 * Obtener una plantilla a partir de su identificador.
 */
function cdb_mails_get_template_by_id( $id ) {
    global $wpdb;

    $table = cdb_mails_templates_table();

    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
}

/**
 * Obtener una plantilla por su nombre exacto.
 */
function cdb_mails_get_template_by_name( $name ) {
    global $wpdb;

    $table = cdb_mails_templates_table();

    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE name = %s", $name ), ARRAY_A );
}

/**
 * Guardar una plantilla nueva o existente.
 */
function cdb_mails_save_template( $data, $id = 0 ) {
    global $wpdb;

    $table = cdb_mails_templates_table();

    if ( $id ) {
        $wpdb->update(
            $table,
            array(
                'name'       => $data['name'],
                'subject'    => $data['subject'],
                'body'       => $data['body'],
                'updated_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $id ),
            array( '%s', '%s', '%s', '%s' ),
            array( '%d' )
        );
        return $id;
    }

    $wpdb->insert(
        $table,
        array(
            'name'       => $data['name'],
            'subject'    => $data['subject'],
            'body'       => $data['body'],
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' ),
        ),
        array( '%s', '%s', '%s', '%s', '%s' )
    );

    return $wpdb->insert_id;
}

/**
 * Eliminar una plantilla.
 */
function cdb_mails_delete_template( $id ) {
    global $wpdb;

    $table = cdb_mails_templates_table();

    $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
}

/**
 * Obtener una plantilla de correo.
 *
 * Aquí se añadirá la lógica para gestionar y procesar plantillas de correo
 * personalizadas.
 */
function cdb_mails_get_template( $template_name ) {
    // Lógica de plantillas pendiente de implementar
    return '';
}

/**
 * Crear la plantilla por defecto si no existe.
 */
function cdb_mails_ensure_default_template() {
    $name = 'Nueva valoración recibida';

    if ( cdb_mails_get_template_by_name( $name ) ) {
        return;
    }

    $body = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Notificación de Nueva Valoración</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="background: #faf8ee; color: #232323; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf8ee;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background: #faf8ee; margin: 32px auto;">
          <tr>
            <td style="padding: 32px 0 16px 0;">
              <!-- Logo y fecha de envío -->
              <table width="100%">
                <tr>
                  <td align="left" style="font-size: 2.5em; font-weight: bold; letter-spacing: -2px;">
                    CdB_
                  </td>
                  <td align="right" style="font-size: 1em; color: #232323;">
                    {send_date}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              <hr style="border: none; border-top: 2px solid #232323; margin: 0 0 32px 0;">
            </td>
          </tr>
          <tr>
            <td style="padding: 0 0 16px 0;">
              <h1 style="font-size: 2em; font-weight: bold; margin: 0 0 24px 0;">
                ¡Tienes una nueva valoración!
              </h1>
              <div style="font-size: 1.2em; margin-bottom: 24px;">
                Hola {user_name},<br><br>
                Has recibido una <b>nueva valoración</b> por tu trabajo en <b>{bar_name}</b>.<br><br>
                <b>Resumen:</b><br>
                {valoracion_resumen}
              </div>
              <div style="margin-bottom: 24px;">
                <a href="{profile_url}" style="display:inline-block;padding:10px 24px;border-radius:6px;background:#232323;color:#faf8ee;font-weight:bold;text-decoration:none;font-size:1.1em;">
                  Ver mi perfil
                </a>
              </div>
              <div style="font-size: 1em; color: #555;">
                Si tienes dudas sobre tu valoración, revisa tu perfil en la plataforma o contacta con el equipo.<br><br>
                ¡Gracias por tu profesionalidad!
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <hr style="border: none; border-top: 2px solid #232323; margin: 32px 0 0 0;">
            </td>
          </tr>
          <tr>
            <td align="right" style="font-size: 1em; color: #232323; padding-top: 16px;">
              Equipo Proyecto CdB
            </td>
          </tr>
          <tr>
            <td align="right" style="font-size: 0.9em; color: #232323; padding-top: 8px;">
              Valoración recibida el {review_date}
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

    $data = array(
        'name'    => $name,
        'subject' => '¡Has recibido una nueva valoración!',
        'body'    => $body,
    );

    cdb_mails_save_template( $data );
}

/**
 * Registrar el submenú de plantillas dentro de "Mails".
 */
function cdb_mails_templates_admin_menu() {
    add_submenu_page(
        'cdb-mails',
        'Plantillas',
        'Plantillas',
        'manage_options',
        'cdb-mail-templates',
        'cdb_mails_render_templates_page'
    );
}
add_action( 'admin_menu', 'cdb_mails_templates_admin_menu' );

/**
 * Renderizar la página de gestión de plantillas.
 */
function cdb_mails_render_templates_page() {
    $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
    $id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

    if ( 'delete' === $action && $id ) {
        check_admin_referer( 'cdb_mails_delete_template_' . $id );
        cdb_mails_delete_template( $id );
        echo '<div class="updated notice"><p>Plantilla eliminada.</p></div>';
        $action = '';
    }

    if ( 'edit' === $action || 'new' === $action ) {
        cdb_mails_render_template_form( $id );
        return;
    }

    if ( 'send_test' === $action && $id ) {
        cdb_mails_render_send_test_form( $id );
        return;
    }

    cdb_mails_ensure_default_template();
    cdb_mails_render_templates_list();
}

/**
 * Mostrar el listado de plantillas existentes.
 */
function cdb_mails_render_templates_list() {
    $templates = cdb_mails_get_all_templates();

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">Plantillas</h1> ';
    echo '<a href="?page=cdb-mail-templates&action=new" class="page-title-action">Añadir nueva</a>';
    echo '<hr class="wp-header-end">';

    if ( empty( $templates ) ) {
        echo '<p>No hay plantillas creadas.</p>';
    } else {
        echo '<table class="widefat">';
        echo '<thead><tr><th>ID</th><th>Nombre</th><th>Asunto</th><th>Acciones</th></tr></thead><tbody>';

        foreach ( $templates as $template ) {
            $edit_link   = esc_url( admin_url( 'admin.php?page=cdb-mail-templates&action=edit&id=' . $template['id'] ) );
            $delete_link = wp_nonce_url( admin_url( 'admin.php?page=cdb-mail-templates&action=delete&id=' . $template['id'] ), 'cdb_mails_delete_template_' . $template['id'] );
            $test_link   = esc_url( admin_url( 'admin.php?page=cdb-mail-templates&action=send_test&id=' . $template['id'] ) );

            echo '<tr>';
            echo '<td>' . esc_html( $template['id'] ) . '</td>';
            echo '<td>' . esc_html( $template['name'] ) . '</td>';
            echo '<td>' . esc_html( $template['subject'] ) . '</td>';
            echo '<td>';
            echo '<a href="' . $edit_link . '">Editar</a> | ';
            echo '<a href="' . $delete_link . '" onclick="return confirm(\'¿Eliminar esta plantilla?\');">Eliminar</a> | ';
            echo '<a href="' . $test_link . '">Enviar prueba</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    echo '</div>';
}

/**
 * Renderizar el formulario de creación/edición de plantillas.
 */
function cdb_mails_render_template_form( $id = 0 ) {
    $is_edit  = $id > 0;
    $template = array(
        'name'    => '',
        'subject' => '',
        'body'    => '',
    );

    if ( $is_edit ) {
        $data = cdb_mails_get_template_by_id( $id );
        if ( $data ) {
            $template = $data;
        }
    }

    if ( isset( $_POST['cdb_mails_template_nonce'] ) && wp_verify_nonce( $_POST['cdb_mails_template_nonce'], 'save_template' ) ) {
        $template['name']    = sanitize_text_field( $_POST['name'] );
        $template['subject'] = sanitize_text_field( $_POST['subject'] );
        $template['body']    = wp_kses_post( $_POST['body'] );

        $id = cdb_mails_save_template( $template, $id );

        echo '<div class="updated notice"><p>Plantilla guardada.</p></div>';
        $is_edit = true;
    }

    echo '<div class="wrap">';
    echo $is_edit ? '<h1>Editar plantilla</h1>' : '<h1>Nueva plantilla</h1>';

    echo '<form method="post">';
    wp_nonce_field( 'save_template', 'cdb_mails_template_nonce' );

    echo '<table class="form-table">';
    echo '<tr><th><label for="name">Nombre</label></th><td><input type="text" name="name" id="name" class="regular-text" value="' . esc_attr( $template['name'] ) . '"></td></tr>';
    echo '<tr><th><label for="subject">Asunto</label></th><td><input type="text" name="subject" id="subject" class="regular-text" value="' . esc_attr( $template['subject'] ) . '"></td></tr>';
    echo '<tr><th><label for="body">Cuerpo</label></th><td>';
    wp_editor( $template['body'], 'body', array( 'textarea_rows' => 10 ) );
    echo '</td></tr>';
    echo '</table>';

    // Información de variables disponibles.
    echo '<div class="notice notice-info"><p><strong>Variables disponibles:</strong> ';
    $vars = cdb_mails_available_vars();
    echo implode( ', ', array_keys( $vars ) );
    echo '</p></div>';

    submit_button( $is_edit ? 'Actualizar plantilla' : 'Crear plantilla' );

    echo '</form>';
    echo '</div>';
}

/**
 * Mostrar formulario para enviar un email de prueba con una plantilla.
 */
function cdb_mails_render_send_test_form( $id ) {
    // Obtener la plantilla seleccionada.
    $template = cdb_mails_get_template_by_id( $id );

    if ( ! $template ) {
        echo '<div class="wrap"><p>Plantilla no encontrada.</p></div>';
        return;
    }

    // Valores de ejemplo para las variables disponibles.
    $test_vars = array(
        '{user_name}' => 'Usuario Demo',
        '{bar_name}'  => 'Bar de Prueba',
        '{date}'      => date_i18n( get_option( 'date_format' ) ),
    );

    // Procesar el envío cuando el formulario se ha enviado.
    if ( isset( $_POST['cdb_mails_send_test_nonce'] ) && wp_verify_nonce( $_POST['cdb_mails_send_test_nonce'], 'cdb_mails_send_test' ) ) {
        $email = isset( $_POST['test_email'] ) ? sanitize_email( wp_unslash( $_POST['test_email'] ) ) : '';

        if ( empty( $email ) || ! is_email( $email ) ) {
            echo '<div class="error notice"><p>Dirección de correo no válida.</p></div>';
        } else {
            $subject = str_replace( array_keys( $test_vars ), array_values( $test_vars ), $template['subject'] );
            $body    = str_replace( array_keys( $test_vars ), array_values( $test_vars ), $template['body'] );

            $sent = wp_mail( $email, $subject, $body );

            if ( $sent ) {
                echo '<div class="updated notice"><p>Email de prueba enviado correctamente a ' . esc_html( $email ) . '.</p></div>';
            } else {
                echo '<div class="error notice"><p>No se pudo enviar el email de prueba.</p></div>';
            }
        }
    }

    // Vista previa del asunto y cuerpo procesados.
    $preview_subject = str_replace( array_keys( $test_vars ), array_values( $test_vars ), $template['subject'] );
    $preview_body    = str_replace( array_keys( $test_vars ), array_values( $test_vars ), $template['body'] );

    echo '<div class="wrap">';
    echo '<h1>Enviar prueba</h1>';

    echo '<p><strong>Asunto original:</strong> ' . esc_html( $template['subject'] ) . '</p>';
    echo '<p><strong>Cuerpo original:</strong></p>';
    echo '<div style="background:#fff;border:1px solid #ddd;padding:10px;">' . wp_kses_post( $template['body'] ) . '</div>';

    echo '<h2>Valores de prueba</h2>';
    echo '<ul>';
    foreach ( $test_vars as $var => $value ) {
        echo '<li>' . esc_html( $var ) . ' &rarr; ' . esc_html( $value ) . '</li>';
    }
    echo '</ul>';

    echo '<h2>Vista previa del email de prueba</h2>';
    echo '<p><strong>Asunto:</strong> ' . esc_html( $preview_subject ) . '</p>';
    echo '<div style="background:#fff;border:1px solid #ddd;padding:10px;">' . wpautop( wp_kses_post( $preview_body ) ) . '</div>';

    echo '<form method="post" style="margin-top:20px;">';
    wp_nonce_field( 'cdb_mails_send_test', 'cdb_mails_send_test_nonce' );
    echo '<table class="form-table">';
    echo '<tr><th><label for="test_email">Enviar a</label></th>';
    echo '<td><input type="email" name="test_email" id="test_email" class="regular-text" required></td></tr>';
    echo '</table>';
    submit_button( 'Enviar email de prueba' );
    echo '</form>';
    echo '</div>';
}
