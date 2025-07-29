<?php
// Funciones administrativas para cdb-mails

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registra el menú de administración del plugin.
 */
function cdb_mails_admin_menu() {
    add_menu_page(
        'cdb-mails',
        'Mails',
        'manage_options',
        'cdb-mails',
        'cdb_mails_admin_page',
        'dashicons-email'
    );
}
add_action( 'admin_menu', 'cdb_mails_admin_menu' );

/**
 * Muestra la página principal del plugin en el administrador.
 */
function cdb_mails_admin_page() {
    echo '<div class="wrap">';
    echo '<h1>Mails</h1>';
    echo '<p>Bienvenido a cdb-mails. Aquí podrás gestionar las notificaciones por correo electrónico.</p>';
    echo '</div>';
}
