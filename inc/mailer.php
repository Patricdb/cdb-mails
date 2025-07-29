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
    // Aquí se implementará la lógica de envío
}
