# Navi

## Descripción

Navi es un plugin de WordPress diseñado para gestionar y visualizar información de sedes o ubicaciones. Ofrece una solución completa para importar datos, filtrarlos y mostrarlos en mapas interactivos y listados personalizables.

## Características Principales

- Importación de datos de sedes desde archivos Excel
- Mapas interactivos para visualizar ubicaciones
- Sistema de filtrado por país y niveles jerárquicos
- Personalización de la visualización mediante funciones de renderizado customizables
- Panel de administración intuitivo para gestionar sedes

## Instalación

1. Descargue el archivo ZIP del plugin Navi.
2. En su panel de WordPress, vaya a Plugins > Añadir Nuevo > Subir Plugin.
3. Seleccione el archivo ZIP de Navi y haga clic en "Instalar Ahora".
4. Una vez instalado, active el plugin.

## Uso Básico

1. Importar sedes:
   - Vaya a Navi > Gestionar Plantillas en el panel de administración.
   - Descargue la plantilla de Excel de ejemplo.
   - Complete la plantilla con sus datos y súbala.

2. Mostrar sedes en una página:
   Utilice el siguiente shortcode:
   ```
   [navi_filtro_sedes plantilla_id="ID_DE_SU_PLANTILLA"]
   ```

3. Personalizar la visualización:
   Active el renderizado personalizado en el shortcode y defina la función `naviCustomRender` en su JavaScript.
   ```
   [navi_filtro_sedes plantilla_id="ID_DE_SU_PLANTILLA" custom_render="true"]
   ```

## Personalización Avanzada

Para personalizar la apariencia de las sedes y el mapa, utilice la función `naviCustomRender`:

```javascript
window.naviCustomRender = function (sedes, campos_mostrar, isMap) {
    if (isMap) {
        return {
            mapHtml: `
                <div id="navi-mapa" style="height: 400px;"></div>
                <div class="mapa-info">Información adicional del mapa</div>
            `
        };
    } else {
        return {
            sedesHtml: `
                <div class="lista-sedes">
                    ${sedes.map(sede => `
                        <div class="sede-item">
                            <h3>${sede.nombre}</h3>
                            <p>${sede.direccion}</p>
                        </div>
                    `).join('')}
                </div>
            `
        };
    }
};
```

## Plantilla de Excel

Navi proporciona una plantilla de Excel prediseñada para facilitar la importación de datos. Puede descargarla desde el panel de administración de Navi.

## Requisitos

- WordPress 5.0 o superior
- PHP 7.2 o superior
- Navegadores web modernos

## Agradecimientos

Navi utiliza las siguientes bibliotecas de código abierto:

- [Leaflet](https://leafletjs.com/) para la generación de mapas interactivos.
- [SheetJS](https://sheetjs.com/) para el procesamiento de archivos Excel.

Agradecemos a los desarrolladores de estas herramientas por su valioso trabajo.

## Soporte y Contacto

Para soporte técnico, reportar problemas o sugerir mejoras:

- GitHub: [github.com/NotGedomi](https://github.com/NotGedomi)
- Correo electrónico: [xiadouofficial@gmail.com](xiadouofficial@gmail.com)

## Licencia

Navi está licenciado bajo la GPL v2 o posterior.

---

Desarrollado por [Gedomi](https://github.com/NotGedomi) para [Invitro Agencia](https://invitro.pe) | [Repositorio del Proyecto](https://github.com/NotGedomi/navi)
