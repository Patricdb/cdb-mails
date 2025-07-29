# cdb-mails

Plugin WordPress para la gestión de notificaciones por correo electrónico. Este repositorio contiene el esqueleto inicial del plugin preparado para futuras ampliaciones.

## Estructura

```
cdb-mails.php           Archivo principal del plugin
inc/
  admin.php             Funciones administrativas y páginas del panel
  mailer.php            Punto de inicio para el envío de correos
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

## Licencia

GPL v2 o posterior.
