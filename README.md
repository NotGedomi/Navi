# Navi

## Descripción

Navi es un plugin de WordPress diseñado para gestionar y visualizar información de sedes o ubicaciones. Ofrece una solución completa para importar datos, filtrarlos y mostrarlos en mapas interactivos y listados personalizables.

## Características Principales

- Importación de datos de sedes desde archivos Excel
- Mapas interactivos para visualizar ubicaciones
- Sistema de filtrado por país y niveles jerárquicos
- Personalización de la visualización mediante funciones de renderizado customizables
- Panel de administración intuitivo para gestionar sedes
- Formulario de contacto integrado para cada sede
- Redirecciones personalizables por país
- Popup de confirmación para redirecciones externas

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

## Personalización Avanzada con naviCustomRender

La función `naviCustomRender` permite personalizar completamente la apariencia de las sedes y el mapa. Aquí tienes un tutorial detallado sobre cómo usarla:

1. Activa el renderizado personalizado en el shortcode:
   ```
   [navi_filtro_sedes plantilla_id="ID_DE_SU_PLANTILLA" custom_render="true"]
   ```

2. Define la función `naviCustomRender` en tu JavaScript:

```javascript
window.naviCustomRender = function(sedes, campos_mostrar, isMap, mostrarFormulario) {
    if (isMap) {
        // Personalización del mapa
        return {
            mapHtml: `
                <div id="navi-mapa" style="height: 400px;"></div>
                <div class="mapa-info">Mapa personalizado de sedes</div>
            `
        };
    } else {
        // Personalización de la lista de sedes
        return {
            sedesHtml: `
                <div class="lista-sedes-personalizada">
                    ${sedes.map(sede => `
                        <div class="sede-custom" data-sede-id="${sede.id}">
                            <h3>${sede.nombre}</h3>
                            <p>${sede.direccion}</p>
                            ${sede.logo ? `<img src="${sede.logo}" alt="Logo de ${sede.nombre}" class="sede-logo">` : ''}
                            ${mostrarFormulario ? `
                                <button class="contactButton" 
                                    data-nombre="${sede.nombre}"
                                    data-correo="${sede.correo}"
                                    data-telefono="${sede.telefono}"
                                    data-direccion="${sede.direccion}"
                                    data-horario="${sede.horario}"
                                    data-pagina_web="${sede.pagina_web}"
                                    data-fondo="${sede.fondo}"
                                    data-fondo2="${sede.fondo2}"
                                    data-sede-id="${sede.id}">
                                    Contactar
                                </button>
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
            `
        };
    }
};
```

3. Personalización del mapa:
   - La función devuelve un objeto con la propiedad `mapHtml` cuando `isMap` es `true`.
   - Puedes personalizar el HTML del contenedor del mapa y agregar elementos adicionales.

4. Personalización de la lista de sedes:
   - Cuando `isMap` es `false`, la función devuelve un objeto con la propiedad `sedesHtml`.
   - Utiliza `sedes.map()` para iterar sobre todas las sedes y crear el HTML para cada una.

5. Uso de campos personalizados:
   - Accede a los campos de cada sede directamente: `sede.nombre`, `sede.direccion`, etc.
   - Utiliza operadores ternarios para manejar campos opcionales como el logo.

6. Integración del formulario de contacto:
   - Verifica `mostrarFormulario` para decidir si incluir el botón de contacto.
   - Añade todos los datos necesarios como atributos `data-*` en el botón de contacto.

7. Estilos y comportamiento:
   - Añade clases CSS personalizadas para estilizar tus elementos.
   - Puedes incluir eventos JavaScript adicionales para interactividad.

8. Consideraciones adicionales:
   - Asegúrate de escapar correctamente los datos para prevenir problemas de seguridad.
   - Prueba tu renderizado con diferentes configuraciones de sedes para garantizar la robustez.

## Configuración Adicional

### Redirecciones por País

1. Ve a Navi > Configuración en el panel de administración.
2. Selecciona la plantilla deseada.
3. Configura las URL de redirección para países específicos.
4. Cuando un usuario seleccione un país con redirección configurada, se mostrará un popup de confirmación antes de redirigir.

### Formulario de Contacto

1. En la configuración de Navi, activa la opción "Mostrar formulario de contacto".
2. Personaliza los campos y el comportamiento del formulario según sea necesario.

### Popup de Confirmación para Redirecciones

El plugin Navi incluye un popup de confirmación que se muestra cuando un usuario está a punto de ser redirigido a un sitio externo. Este popup proporciona una capa adicional de seguridad y transparencia para los usuarios.

Características del popup:

- Se muestra automáticamente cuando se selecciona un país con redirección configurada.
- Informa al usuario que está a punto de abandonar el sitio actual.
- Ofrece opciones para continuar a la redirección o permanecer en el sitio actual.
- Es totalmente personalizable mediante CSS y JavaScript.

#### Activación y Personalización del Popup con customRender

Para activar y personalizar el popup usando customRender, sigue estos pasos:

1. Asegúrate de que el renderizado personalizado esté activado en el shortcode:
   ```
   [navi_filtro_sedes plantilla_id="ID_DE_SU_PLANTILLA" custom_render="true"]
   ```

2. En tu función `naviCustomRender`, puedes personalizar el comportamiento del popup añadiendo un manejador de eventos para el clic en los marcadores del mapa:

```javascript
window.naviCustomRender = function(sedes, campos_mostrar, isMap, mostrarFormulario) {
    if (isMap) {
        // ... código anterior del mapa ...

        // Personalizar el comportamiento del popup
        window.onSedeMarkerClick = function(sedeData, sedeIndex) {
            if (sedeData.url_redireccion) {
                // Mostrar popup personalizado
                mostrarPopupPersonalizado(sedeData.url_redireccion);
            } else {
                // Comportamiento por defecto o personalizado para sedes sin redirección
            }
        };

        return { mapHtml: `...` };
    } else {
        // ... código anterior de la lista de sedes ...
    }
};

function mostrarPopupPersonalizado(url) {
    // Implementa tu lógica personalizada para mostrar el popup
    // Puedes usar el DOM para crear elementos o manipular un template existente
    var popup = document.createElement('div');
    popup.className = 'popup-personalizado';
    popup.innerHTML = `
        <h3>¿Desea salir del sitio?</h3>
        <p>Está a punto de ser redirigido a: ${url}</p>
        <button onclick="window.open('${url}', '_blank')">Continuar</button>
        <button onclick="this.parentElement.remove()">Cancelar</button>
    `;
    document.body.appendChild(popup);
}
```

3. Personaliza los estilos del popup añadiendo CSS a tu tema:

```css
.popup-personalizado {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    z-index: 1000;
}

.popup-personalizado button {
    margin: 10px;
    padding: 5px 10px;
}
```

Esta implementación te permite tener control total sobre la apariencia y el comportamiento del popup de redirección, integrándolo perfectamente con tu diseño personalizado.

Para personalizar el aspecto del popup predeterminado, puedes añadir estilos CSS adicionales en tu tema. Por ejemplo:

```css
#navi-popup-redireccion .popup {
    /* Estilos para el contenedor del popup */
}

#navi-popup-redireccion .redirection-disclaimer {
    /* Estilos para el texto del disclaimer */
}

#navi-popup-redireccion #navi-confirm,
#navi-popup-redireccion #navi-reject {
    /* Estilos para los botones del popup */
}
```

Si necesitas modificar el contenido HTML del popup predeterminado, puedes hacerlo editando el archivo `unlevel-redirection-template.php` en la carpeta del plugin.

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
- Correo electrónico: [xiadouofficial@gmail.com](mailto:xiadouofficial@gmail.com)

## Licencia

Navi está licenciado bajo la GPL v2 o posterior.

---

Desarrollado por [Gedomi](https://github.com/NotGedomi) para [Invitro Agencia](https://invitro.pe) | [Repositorio del Proyecto](https://github.com/NotGedomi/navi)
