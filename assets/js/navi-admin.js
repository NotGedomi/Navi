(function ($) {
    $(document).ready(function () {
        // Cargar plantillas y sedes al iniciar la página
        cargarPlantillas();
        cargarSedes();

        // Manejar el formulario de plantillas
        $('#navi-plantilla-form').on('submit', manejarSubmitPlantilla);

        // Descargar plantilla de ejemplo
        $('#descargar-plantilla-ejemplo').on('click', descargarPlantillaEjemplo);

        // Manejar el formulario de sedes
        $('#navi-sede-form').on('submit', manejarSubmitSede);

        // Manejar los selects dependientes
        $('#plantilla_id').on('change', function () {
            var plantilla_id = $(this).val();
            if (plantilla_id) {
                cargarNiveles(plantilla_id);
                cargarConfig(plantilla_id);
                $('#campos-mostrar-container, #mostrar-mapa-container').show();
            } else {
                $('#pais').empty().append('<option value="">Seleccione un país</option>');
                $('#campos-mostrar-container, #mostrar-mapa-container').hide();
                $('#campos-mostrar').empty();
                $('#mostrar_mapa').prop('checked', false);
            }
        });

        // Ocultar las opciones de configuración al cargar la página
        $('#campos-mostrar-container, #mostrar-mapa-container').hide();

        // Vista previa del logo
        $('#logo').on('change', mostrarVistaPrevia);

        // Manejar el formulario de configuración
        $('#navi-config-form').on('submit', function (e) {
            e.preventDefault();
            guardarConfig();
        });

        // Manejar la eliminación de plantillas
        $(document).on('click', '.eliminar-plantilla', function () {
            var plantilla_id = $(this).data('id');
            if (confirm('¿Estás seguro de que deseas eliminar esta plantilla? Esta acción eliminará también todas las sedes asociadas.')) {
                eliminarPlantilla(plantilla_id);
            }
        });

        // Manejar la eliminación de sedes
        $(document).on('click', '.eliminar-sede', function () {
            var sede_id = $(this).data('id');
            if (confirm('¿Estás seguro de que deseas eliminar esta sede?')) {
                eliminarSede(sede_id);
            }
        });
    });

    function manejarSubmitPlantilla(e) {
        e.preventDefault();

        var nombre_plantilla = $('input[name="nombre_plantilla"]').val();
        if (!nombre_plantilla) {
            alert('Por favor, ingrese un nombre para la plantilla.');
            return;
        }

        var fileInput = $('input[name="plantilla_excel"]')[0];
        var file = fileInput.files[0];
        var reader = new FileReader();

        reader.onload = function (e) {
            var data = new Uint8Array(e.target.result);
            var workbook = XLSX.read(data, { type: 'array' });
            var firstSheet = workbook.Sheets[workbook.SheetNames[0]];
            var jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

            // Procesar y validar los datos
            var processedData = processExcelData(jsonData);

            // Enviar los datos al servidor
            guardarPlantilla(processedData, nombre_plantilla);
        };

        reader.readAsArrayBuffer(file);
    }

    function descargarPlantillaEjemplo() {
        var wb = XLSX.utils.book_new();
        var ws_data = [
            ['Nombre', 'Coordenada', 'Logo', 'País', 'Nivel 1', 'Nivel 1 Dato', 'Nivel 2', 'Nivel 2 Dato', 'Nivel 3', 'Nivel 3 Dato', 'Correo', 'Teléfono', 'Dirección', 'Página web'],
            ['Sede Central', '41.40338, 2.17403', 'https://ejemplo.com/logo.png', 'España', 'Comunidad Autónoma', 'Cataluña', 'Provincia', 'Barcelona', 'Municipio', 'Barcelona', 'info@sede.com', '+34123456789', 'Calle Ejemplo, 123', 'https://www.sede.com'],
            ['Sucursal Norte', '41.98722, 2.82499', 'https://ejemplo.com/logo2.png', 'España', 'Comunidad Autónoma', 'Cataluña', 'Provincia', 'Girona', 'Municipio', 'Figueres', 'norte@sede.com', '+34987654321', 'Avenida Norte, 456', 'https://www.sede-norte.com']
        ];
        var ws = XLSX.utils.aoa_to_sheet(ws_data);
        XLSX.utils.book_append_sheet(wb, ws, "Plantilla");
        XLSX.writeFile(wb, "plantilla_ejemplo_navi.xlsx");
    }

    function manejarSubmitSede(e) {
        e.preventDefault();

        if (!validarFormularioSede()) {
            return;
        }

        var formData = new FormData(this);
        formData.append('action', 'navi_guardar_sede');
        formData.append('nonce', navi_ajax.nonce);

        guardarSede(formData);
    }

    function mostrarVistaPrevia() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#logo-preview').html('<img src="' + e.target.result + '" alt="Logo preview" style="max-width: 100px; max-height: 100px;">');
            }
            reader.readAsDataURL(file);
        }
    }

    function processExcelData(data) {
        var headers = data[0];
        var processedData = [];

        for (var i = 1; i < data.length; i++) {
            var row = data[i];

            // Verificar si la fila está vacía
            if (row.every(cell => !cell)) continue;

            var item = {};
            var isValid = true;

            for (var j = 0; j < headers.length; j++) {
                var header = headers[j];
                var value = row[j] || '';

                // Verificar si el nombre está vacío
                if (header === 'Nombre' && !value) {
                    isValid = false;
                    console.error('Fila ' + (i + 1) + ': El nombre no puede estar vacío');
                    break;
                }

                item[header] = value;
            }

            if (isValid) {
                processedData.push(item);
            }
        }

        return processedData;
    }

    function validarFormularioSede() {
        var plantilla_id = $('#plantilla_id').val();
        var nombre = $('#nombre').val().trim();
        var coordenada = $('#coordenada').val().trim();
        var pais = $('#pais').val();
        var nivel1 = $('#nivel1_dato').val();
        var correo = $('#correo').val().trim();
        var telefono = $('#telefono').val().trim();
        var direccion = $('#direccion').val().trim();
        var pagina_web = $('#pagina_web').val().trim();

        if (!plantilla_id) {
            alert('Por favor, selecciona una plantilla.');
            return false;
        }

        if (nombre === '') {
            alert('Por favor, ingrese el nombre de la sede.');
            return false;
        }

        if (coordenada === '' || !/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/.test(coordenada)) {
            alert('Por favor, ingrese una coordenada válida (latitud,longitud).');
            return false;
        }

        if (pais === '') {
            alert('Por favor, selecciona un país.');
            return false;
        }

        if (nivel1 === '') {
            alert('Por favor, seleccione el nivel 1.');
            return false;
        }

        if (correo === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
            alert('Por favor, ingrese un correo electrónico válido.');
            return false;
        }

        if (telefono === '' || !/^\+?[0-9]{6,15}$/.test(telefono)) {
            alert('Por favor, ingrese un número de teléfono válido (6-15 dígitos, puede incluir + al inicio).');
            return false;
        }

        if (direccion === '') {
            alert('Por favor, ingrese la dirección de la sede.');
            return false;
        }

        if (pagina_web !== '' && !/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test(pagina_web)) {
            alert('Por favor, ingrese una URL válida para la página web.');
            return false;
        }

        return true;
    }

    function cargarPlantillas() {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_plantillas',
                nonce: navi_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    var plantillas = response.data;
                    var tabla = $('#navi-plantilla-tabla');
                    tabla.empty();

                    plantillas.forEach(function (plantilla) {
                        var fila = '<tr>' +
                            '<td>' + plantilla.nombre + '</td>' +
                            '<td><button class="button eliminar-plantilla" data-id="' + plantilla.id + '">Eliminar</button></td>' +
                            '</tr>';
                        tabla.append(fila);
                    });

                    actualizarSelectsPlantillas(plantillas);
                } else {
                    console.error('Error al cargar plantillas:', response.data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error AJAX al cargar plantillas:', textStatus, errorThrown);
            }
        });
    }

    function actualizarSelectsPlantillas(plantillas) {
        var selects = $('#plantilla_id, #config-plantilla_id');
        selects.empty().append('<option value="">Seleccione una plantilla</option>');
        plantillas.forEach(function (plantilla) {
            selects.append('<option value="' + plantilla.id + '">' + plantilla.nombre + '</option>');
        });
    }

    function guardarPlantilla(datos, nombre_plantilla) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_cargar_plantilla',
                nonce: navi_ajax.nonce,
                datos: JSON.stringify(datos),
                nombre_plantilla: nombre_plantilla
            },
            success: function (response) {
                if (response.success) {
                    $('#navi-plantilla-mensaje').html('<p class="success">' + response.data + '</p>');
                    cargarPlantillas();
                } else {
                    $('#navi-plantilla-mensaje').html('<p class="error">' + response.data + '</p>');
                }
            }
        });
    }

    function cargarSedes(plantilla_id) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_sedes',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id
            },
            success: function (response) {
                if (response.success) {
                    var sedes = response.data;
                    var tabla = $('#navi-sedes-tabla');
                    tabla.empty();
    
                    sedes.forEach(function (sede) {
                        var fila = '<tr>' +
                            '<td>' + sede.nombre + '</td>' +
                            '<td>' + sede.nombre_plantilla + '</td>' +
                            '<td>' + sede.coordenada + '</td>' +
                            '<td>' +
                            '<img src="' + (sede.logo || '#') + '" alt="Logo" style="max-width: 50px; max-height: 50px;"><br>' +
                            '<input type="file" class="logo-upload" data-sede-id="' + sede.id + '" accept="image/*">' +
                            '<button class="button upload-logo" data-sede-id="' + sede.id + '">Subir Logo</button>' +
                            '</td>' +
                            '<td>' + sede.pais + '</td>' +
                            '<td>' + sede.nivel1 + '</td>' +
                            '<td>' + sede.nivel1_dato + '</td>' +
                            '<td>' + (sede.nivel2 || '') + '</td>' +
                            '<td>' + (sede.nivel2_dato || '') + '</td>' +
                            '<td>' + (sede.nivel3 || '') + '</td>' +
                            '<td>' + (sede.nivel3_dato || '') + '</td>' +
                            '<td>' + sede.correo + '</td>' +
                            '<td>' + sede.telefono + '</td>' +
                            '<td>' + sede.direccion + '</td>' +
                            '<td>' + (sede.pagina_web || '') + '</td>' +
                            '<td>' +
                            '<button class="button eliminar-sede" data-id="' + sede.id + '">Eliminar</button>' +
                            '</td>' +
                            '</tr>';
                        tabla.append(fila);
                    });
    
                    // Manejar la subida de logos
                    $('.upload-logo').off('click').on('click', function(e) {
                        e.preventDefault();
                        var sedeId = $(this).data('sede-id');
                        var fileInput = $(this).siblings('.logo-upload')[0];
                        var file = fileInput.files[0];
                        if (file) {
                            subirLogo(sedeId, file);
                        } else {
                            alert('Por favor, selecciona un archivo primero.');
                        }
                    });
    
                    // Manejar la eliminación de sedes
                    $('.eliminar-sede').off('click').on('click', function() {
                        var sede_id = $(this).data('id');
                        if (confirm('¿Estás seguro de que deseas eliminar esta sede?')) {
                            eliminarSede(sede_id);
                        }
                    });
                } else {
                    console.error('Error al cargar sedes:', response.data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error AJAX al cargar sedes:', textStatus, errorThrown);
            }
        });
    }
    
    function subirLogo(sedeId, file) {
        var formData = new FormData();
        formData.append('action', 'navi_actualizar_logo');
        formData.append('nonce', navi_ajax.nonce);
        formData.append('sede_id', sedeId);
        formData.append('logo', file);
    
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    // Actualizar la imagen del logo en la tabla
                    $('input.logo-upload[data-sede-id="' + sedeId + '"]')
                        .siblings('img')
                        .attr('src', response.data.logo_url);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error al subir el logo. Por favor, inténtalo de nuevo.');
            }
        });
    }

    // Añade este código para manejar el filtrado por plantilla
    $(document).ready(function () {
        cargarSedes(); // Cargar todas las sedes al inicio

        $('#filtro-plantilla').on('change', function () {
            var plantilla_id = $(this).val();
            cargarSedes(plantilla_id);
        });
    });

    function cargarNiveles(plantilla_id) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_niveles',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id
            },
            success: function (response) {
                if (response.success) {
                    var data = response.data;
                    var paises = data.paises;
                    var niveles = data.niveles;
                    var nivelesContainer = $('#niveles-container');
                    nivelesContainer.empty();

                    // Actualizar el select de país
                    var paisSelect = $('#pais');
                    paisSelect.empty().append('<option value="">Seleccione un país</option>');
                    paises.forEach(function (pais) {
                        paisSelect.append('<option value="' + pais + '">' + pais + '</option>');
                    });

                    // Añadir evento de cambio para el país
                    paisSelect.off('change').on('change', function () {
                        var paisSeleccionado = $(this).val();
                        if (paisSeleccionado) {
                            cargarNivelesPorPais(plantilla_id, paisSeleccionado);
                        } else {
                            nivelesContainer.empty();
                        }
                    });
                }
            }
        });
    }

    function cargarNivelesPorPais(plantilla_id, pais) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_niveles_por_pais',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id,
                pais: pais
            },
            success: function (response) {
                if (response.success) {
                    var niveles = response.data;
                    var nivelesContainer = $('#niveles-container');
                    nivelesContainer.empty();
    
                    niveles.forEach(function (nivel, index) {
                        nivelesContainer.append(
                            '<tr>' +
                            '<th><label for="nivel' + (index + 1) + '_dato">' + nivel.nombre + '</label></th>' +
                            '<td>' +
                            '<input type="hidden" name="nivel' + (index + 1) + '" value="' + nivel.nombre + '">' +
                            '<select id="nivel' + (index + 1) + '_dato" name="nivel' + (index + 1) + '_dato" ' + (index === 0 ? 'required' : '') + '>' +
                            '<option value="">Seleccione una opción</option>' +
                            '</select>' +
                            '</td>' +
                            '</tr>'
                        );
                        cargarOpcionesNivel(plantilla_id, index + 1, pais);
                    });
    
                    // Añadir eventos de cambio para cada nivel
                    niveles.forEach(function (nivel, index) {
                        $('#nivel' + (index + 1) + '_dato').on('change', function () {
                            var nivelActual = index + 1;
                            var valorSeleccionado = $(this).val();
    
                            // Limpiar y deshabilitar niveles inferiores
                            for (var i = nivelActual + 1; i <= niveles.length; i++) {
                                $('#nivel' + i + '_dato').empty().append('<option value="">Seleccione una opción</option>').prop('disabled', true);
                            }
    
                            // Cargar opciones para el siguiente nivel si existe y se ha seleccionado un valor
                            if (nivelActual < niveles.length && valorSeleccionado) {
                                cargarOpcionesNivel(plantilla_id, nivelActual + 1, pais);
                            }
                        });
                    });
                }
            }
        });
    }
    
    function cargarOpcionesNivel(plantilla_id, nivel, pais) {
        var nivelesAnteriores = {};
        for (var i = 1; i < nivel; i++) {
            nivelesAnteriores['nivel' + i] = $('#nivel' + i + '_dato').val();
        }
    
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_opciones_nivel',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id,
                nivel: nivel,
                pais: pais,
                niveles_anteriores: JSON.stringify(nivelesAnteriores)
            },
            success: function (response) {
                if (response.success) {
                    var opciones = response.data;
                    var select = $('#nivel' + nivel + '_dato');
                    select.empty().append('<option value="">Seleccione una opción</option>');
                    opciones.forEach(function (opcion) {
                        select.append('<option value="' + opcion + '">' + opcion + '</option>');
                    });
                    select.prop('disabled', false);
                }
            }
        });
    }

    function guardarSede(formData) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $('#navi-sede-mensaje').html('<p class="success">' + response.data + '</p>');
                    $('#navi-sede-form')[0].reset();
                    $('#logo-preview').empty();
                    cargarSedes();
                } else {
                    $('#navi-sede-mensaje').html('<p class="error">' + response.data + '</p>');
                }
            }
        });
    }

    function cargarConfig(plantilla_id) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_config',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    var config = response.data;
                    var camposMostrar = $('#campos-mostrar');
                    camposMostrar.empty();

                    var camposDisponibles = config.campos_disponibles;

                    Object.keys(camposDisponibles).forEach(function (campo) {
                        var checked = config.campos_mostrar.includes(campo) ? 'checked' : '';
                        camposMostrar.append(
                            '<label>' +
                            '<input type="checkbox" name="campos_mostrar[]" value="' + campo + '" ' + checked + '> ' +
                            camposDisponibles[campo] +
                            '</label><br>'
                        );
                    });

                    $('#mostrar_mapa').prop('checked', config.mostrar_mapa == 1);
                } else {
                    console.error('Error al cargar la configuración:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al cargar la configuración:', status, error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        });
    }

    function guardarConfig() {
        var plantilla_id = $('#plantilla_id').val();
        var campos_mostrar = $('input[name="campos_mostrar[]"]:checked').map(function () {
            return this.value;
        }).get();
        var mostrar_mapa = $('#mostrar_mapa').is(':checked') ? 1 : 0;

        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_guardar_config',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id,
                campos_mostrar: JSON.stringify(campos_mostrar),
                mostrar_mapa: mostrar_mapa
            },
            success: function (response) {
                if (response.success) {
                    $('#navi-config-mensaje').html('<p class="success">' + response.data.mensaje + '</p>');
                    $('#navi-shortcode').html('<p>Shortcode: <code>' + response.data.shortcode + '</code></p>');
                } else {
                    $('#navi-config-mensaje').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al guardar la configuración:', error);
                $('#navi-config-mensaje').html('<p class="error">Error al guardar la configuración. Por favor, intenta de nuevo.</p>');
            }
        });
    }

    function eliminarPlantilla(plantilla_id) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_eliminar_plantilla',
                nonce: navi_ajax.nonce,
                id: plantilla_id
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data);
                    cargarPlantillas();
                    cargarSedes();
                } else {
                    alert('Error al eliminar la plantilla: ' + response.data);
                }
            }
        });
    }

    function eliminarSede(sede_id) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_eliminar_sede',
                nonce: navi_ajax.nonce,
                id: sede_id
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data);
                    cargarSedes();
                } else {
                    alert('Error al eliminar la sede: ' + response.data);
                }
            }
        });
    }
})(jQuery);