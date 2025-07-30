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
        '{user_name}' => 'Nombre de usuario',
        '{bar_name}'  => 'Nombre del bar',
        '{date}'      => 'Fecha',
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

            echo '<tr>';
            echo '<td>' . esc_html( $template['id'] ) . '</td>';
            echo '<td>' . esc_html( $template['name'] ) . '</td>';
            echo '<td>' . esc_html( $template['subject'] ) . '</td>';
            echo '<td>';
            echo '<a href="' . $edit_link . '">Editar</a> | ';
            echo '<a href="' . $delete_link . '" onclick="return confirm(\'¿Eliminar esta plantilla?\');">Eliminar</a>';
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
