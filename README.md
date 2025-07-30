# cdb-mails

Plugin WordPress para la gestión de notificaciones por correo electrónico. Este repositorio contiene el esqueleto inicial del plugin preparado para futuras ampliaciones.

## Estructura

```
cdb-mails.php           Archivo principal del plugin
inc/
  admin.php             Funciones administrativas y páginas del panel
  mailer.php            Punto de inicio para el envío de correos
  functions.php         Funciones globales y utilidades
  templates.php         Gestión básica de plantillas de correo
assets/
  js/                   Directorio para scripts (vacío por ahora)
  css/                  Directorio para estilos (vacío por ahora)
```

## Instalación

1. Copia la carpeta `cdb-mails` en el directorio `wp-content/plugins/`.
2. Activa el plugin desde el panel de administración de WordPress.
3. Accede al menú **Mails** para ver la página de bienvenida.

## Propósito

El objetivo es proporcionar una base limpia para desarrollar un sistema de notificaciones por correo. Actualmente solo se crea el menú en el administrador y se incluyen archivos preparados para añadir la lógica de envío y de plantillas en versiones posteriores.

## Integración con otros plugins

Se expone la función global `cdb_mails_send_new_review_notification( $review_id, $type )` para que otros plugins puedan disparar la notificación "Nueva valoración recibida".

Desde la versión 0.3.3 el plugin también escucha los hooks
`cdb_grafica_insert_bar_result` y `cdb_grafica_insert_empleado_result`,
pensados para que el plugin **cdb-grafica** los lance tras insertar una
valoración en sus tablas personalizadas. Al ejecutarse cualquiera de esos
hooks se enviará automáticamente el aviso por correo utilizando la plantilla
_Nueva valoración recibida_.

- **$review_id**: identificador de la valoración en la tabla personalizada.
- **$type**: puede ser `bar` o `empleado` según la tabla utilizada.

Un ejemplo de uso desde otro plugin sería:

```php
cdb_mails_send_new_review_notification( $review_id, 'empleado' );
```

## Licencia

GPL v2 o posterior.
