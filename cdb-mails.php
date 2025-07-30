<?php
/**
 * Plugin Name: cdb-mails
 * Description: Gestor básico de notificaciones por correo electrónico.
 * Version: 0.3.1
 * Author: Proyecto CdB
 * License: GPL v2 or later
 */

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Directorio del plugin
$plugin_path = plugin_dir_path( __FILE__ );

// Incluir archivos principales
require_once $plugin_path . 'inc/admin.php';
require_once $plugin_path . 'inc/mailer.php';
require_once $plugin_path . 'inc/templates.php';

// Hooks de activación y desactivación
register_activation_hook( __FILE__, 'cdb_mails_activate' );
register_deactivation_hook( __FILE__, 'cdb_mails_deactivate' );

function cdb_mails_activate() {
    // Crear tabla personalizada para plantillas de correo
    global $wpdb;

    $table_name      = $wpdb->prefix . 'cdb_mail_templates';
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        subject varchar(255) NOT NULL,
        body longtext NOT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta( $sql );

    // Crear plantilla por defecto si no existe.
    cdb_mails_ensure_default_template();
}

function cdb_mails_deactivate() {
    // Se ejecutará al desactivar el plugin
}
