<?php
/**
 * Plugin Name: cdb-mails
 * Description: Gestor básico de notificaciones por correo electrónico.
 * Version: 0.1.0
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
    // Se ejecutará al activar el plugin
}

function cdb_mails_deactivate() {
    // Se ejecutará al desactivar el plugin
}
