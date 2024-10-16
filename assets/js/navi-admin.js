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
        // Vista previa del Marker
        $('#marker').on('change', mostrarVistaPrevia)
        // Vista previa del fondo
        $('#fondo').on('change', mostrarVistaPrevia);
        $('#fondo2').on('change', mostrarVistaPrevia);
        

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

        // Redirecciones
        $('#plantilla_id_redireccion').on('change', function() {
            var plantilla_id = $(this).val();
            console.log('Plantilla seleccionada:', plantilla_id);
            if (plantilla_id) {
                cargarPaisesSinSedes(plantilla_id);
            } else {
                $('#redirecciones-container').empty();
            }
        });

        $('#navi-redirecciones-form').on('submit', function(e) {
            e.preventDefault();
            guardarRedirecciones();
        });
    });

    function cargarPaisesSinSedes(plantilla_id) {
        console.log('Iniciando carga de países sin sedes para plantilla:', plantilla_id);
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_paises_sin_sedes',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id
            },
            success: function(response) {
                console.log('Respuesta recibida:', response);
                if (response.success) {
                    var paises = response.data.paises;
                    var redirecciones = response.data.redirecciones;
                    console.log('Países sin sedes:', paises);
                    console.log('Redirecciones:', redirecciones);
                    var container = $('#redirecciones-container');
                    container.empty();
    
                    if (paises.length === 0) {
                        container.append('<p>No hay países sin sedes para esta plantilla.</p>');
                    } else {
                        paises.forEach(function(pais) {
                            var redireccion = redirecciones[pais] || '';
                            container.append(
                                '<div class="navi-form-group">' +
                                '<label for="redireccion-' + pais + '">' + pais + '</label>' +
                                '<input type="text" id="redireccion-' + pais + '" name="redireccion[' + pais + ']" ' +
                                'value="' + redireccion + '" placeholder="URL de redirección">' +
                                '</div>'
                            );
                        });
                    }
                } else {
                    console.error('Error al cargar países sin sedes:', response.data);
                    $('#redirecciones-container').html('<p>Error al cargar países sin sedes: ' + response.data + '</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar países sin sedes:', status, error);
                console.log('Respuesta completa:', xhr.responseText);
                $('#redirecciones-container').html('<p>Error al cargar países sin sedes. Por favor, intenta de nuevo.</p>');
            }
        });
    }

    function guardarRedirecciones() {
        var plantilla_id = $('#plantilla_id_redireccion').val();
        var redirecciones = {};

        $('#redirecciones-container input').each(function() {
            var pais = $(this).attr('name').replace('redireccion[', '').replace(']', '');
            var url = $(this).val();
            if (url) {
                redirecciones[pais] = url;
            }
        });

        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_guardar_redirecciones',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id,
                redirecciones: JSON.stringify(redirecciones)
            },
            success: function(response) {
                if (response.success) {
                    $('#navi-redirecciones-mensaje').html('<p class="success">' + response.data + '</p>');
                } else {
                    $('#navi-redirecciones-mensaje').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al guardar redirecciones:', status, error);
                $('#navi-redirecciones-mensaje').html('<p class="error">Error al guardar las redirecciones. Por favor, intenta de nuevo.</p>');
            }
        });
    }

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
            ['País', 'Nivel 1', 'Nivel 1 Dato', 'Nivel 2', 'Nivel 2 Dato', 'Nivel 3', 'Nivel 3 Dato', 'Nombre', 'Dirección', 'Coordenada', 'Horario', 'Página web', 'Correo', 'Teléfono', 'Logo', 'Marker', 'Fondo', 'Fondo 2'],
            ['Perú', 'Departamento', 'Lima', 'Provincia', 'Lima', 'Distrito', 'Miraflores', 'Clínica de Fertilidad Lima', 'Av. Arequipa 1250', '-12.119014, -77.029642', 'Lunes a Viernes 8-20h, Sábados 8-14h', 'https://www.fertilidadlima.pe', 'info@fertilidadlima.pe', '+51 1 4445555', 'https://ejemplo.com/logo_lima.png', '', '', ''],
            ['Perú', 'Departamento', 'Arequipa', 'Provincia', 'Arequipa', 'Distrito', 'Yanahuara', 'Centro de Fertilidad Arequipa', 'Av. Ejército 101', '-16.391813, -71.535858', 'Lunes a Viernes 9-19h', 'https://www.fertilidadarequipa.pe', 'contacto@fertilidadarequipa.pe', '+51 54 222333', 'https://ejemplo.com/logo_arequipa.png', '', '', ''],
            ['Colombia', 'Departamento', 'Cundinamarca', 'Ciudad', 'Bogotá', '', '', 'Fertilidad Bogotá', 'Calle 127 # 20-78', '4.669802, -74.058431', 'Lunes a Sábado 7-18h', 'https://www.fertilidadbogota.co', 'citas@fertilidadbogota.co', '+57 1 6298000', 'https://ejemplo.com/logo_bogota.png', '', '', ''],
            ['México', 'Estado', 'Ciudad de México', 'Alcaldía', 'Miguel Hidalgo', '', '', 'Clínica Fertilidad CDMX', 'Av. Paseo de la Reforma 445', '19.424687, -99.173569', 'Lunes a Viernes 8-20h, Sábados 9-14h', 'https://www.fertilidadcdmx.mx', 'contacto@fertilidadcdmx.mx', '+52 55 5555 5555', 'https://ejemplo.com/logo_cdmx.png', '', '', ''],
            ['Chile', 'Región', 'Metropolitana de Santiago', 'Comuna', 'Las Condes', '', '', 'Instituto de Fertilidad Santiago', 'Av. Apoquindo 3990', '-33.417738, -70.594291', 'Lunes a Viernes 9-18h', 'https://www.fertilidadsantiago.cl', 'info@fertilidadsantiago.cl', '+56 2 2345 6789', 'https://ejemplo.com/logo_santiago.png', '', '', ''],
            ['Ecuador', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']
        ];
        var ws = XLSX.utils.aoa_to_sheet(ws_data);
        XLSX.utils.book_append_sheet(wb, ws, "Plantilla");
        XLSX.writeFile(wb, "plantilla_ejemplo_clinicas_fertilidad.xlsx");
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
        var inputId = this.id;
        var previewId = inputId + '-preview';

        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#' + previewId).html('<img src="' + e.target.result + '" alt="' + inputId + ' preview" style="max-width: 100px; max-height: 100px;">');
            }
            reader.readAsDataURL(file);
        }
    }

    function limpiarVistaPrevia() {
        var inputId = this.id;
        var previewId = inputId + '-preview';
        $('#' + previewId).empty();
    }

    $('#logo, #fondo, #fondo2').on('change', function () {
        if (!this.files || !this.files[0]) {
            limpiarVistaPrevia.call(this);
        }
    });


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
    
                // Asegurarse de que al menos el país esté presente
                if (header === 'País' && !value) {
                    isValid = false;
                    console.error('Fila ' + (i + 1) + ': El país no puede estar vacío');
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
        var horario = $('#horario').val().trim();
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

        if (horario === '') {
            alert('Por favor, ingrese el horario de atención de la sede.');
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
                    var listaPlantillas = $('#navi-plantilla-lista');
                    listaPlantillas.empty();

                    plantillas.forEach(function (plantilla) {
                        var item = $('<div class="navi-list-item"></div>');
                        item.append('<span class="navi-plantilla-nombre">' + plantilla.nombre + '</span>');
                        item.append('<button class="navi-button navi-button-danger eliminar-plantilla" data-id="' + plantilla.id + '">Eliminar</button>');
                        listaPlantillas.append(item);
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

    function cargarSedes(plantilla_id, pagina = 1) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_sedes',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id,
                pagina: pagina
            },
            success: function (response) {
                if (response.success) {
                    var sedes = response.data.sedes;
                    var totalPaginas = response.data.total_paginas;
                    var paginaActual = response.data.pagina_actual;
                    var listaSedes = $('#navi-sedes-lista');
                    listaSedes.empty();

                    sedes.forEach(function (sede) {
                        var item = $('<div class="navi-list-item"></div>');
                        item.append('<span class="navi-sede-nombre">' + sede.nombre + '</span>');

                        var buttonContainer = $('<div class="container-buttons"></div>');
                        buttonContainer.append('<button class="navi-button navi-button-edit editar-sede" data-id="' + sede.id + '">Editar</button>');
                        buttonContainer.append('<button class="navi-button navi-button-danger eliminar-sede" data-id="' + sede.id + '">Eliminar</button>');

                        item.append(buttonContainer);

                        listaSedes.append(item);
                    });

                    // Eliminar paginación existente antes de crear una nueva
                    $('.navi-paginacion').remove();

                    // Crear paginación solo si hay más de una página
                    if (totalPaginas > 1) {
                        crearPaginacion(totalPaginas, paginaActual, plantilla_id);
                    }

                    // Manejar edición y eliminación de sedes
                    manejarAccionesSedes();
                } else {
                    console.error('Error al cargar sedes:', response.data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error AJAX al cargar sedes:', textStatus, errorThrown);
            }
        });
    }

    function manejarAccionesSedes() {
        $('.editar-sede').off('click').on('click', function () {
            var sede_id = $(this).data('id');
            editarSede(sede_id);
        });

        $('.eliminar-sede').off('click').on('click', function () {
            var sede_id = $(this).data('id');
            if (confirm('¿Estás seguro de que deseas eliminar esta sede?')) {
                eliminarSede(sede_id);
            }
        });
    }

    function crearPaginacion(totalPaginas, paginaActual, plantilla_id) {
        var paginacion = $('<div class="navi-paginacion"></div>');

        // Botón anterior
        if (paginaActual > 1) {
            paginacion.append('<button class="navi-button navi-pagina" data-pagina="' + (paginaActual - 1) + '">Anterior</button>');
        }

        // Páginas
        var inicio = Math.max(1, paginaActual - 2);
        var fin = Math.min(totalPaginas, paginaActual + 2);

        if (inicio > 1) {
            paginacion.append('<button class="navi-button navi-pagina" data-pagina="1">1</button>');
            if (inicio > 2) {
                paginacion.append('<span>...</span>');
            }
        }

        for (var i = inicio; i <= fin; i++) {
            var clase = i === paginaActual ? 'navi-pagina-actual' : '';
            paginacion.append('<button class="navi-button navi-pagina ' + clase + '" data-pagina="' + i + '">' + i + '</button>');
        }

        if (fin < totalPaginas) {
            if (fin < totalPaginas - 1) {
                paginacion.append('<span>...</span>');
            }
            paginacion.append('<button class="navi-button navi-pagina" data-pagina="' + totalPaginas + '">' + totalPaginas + '</button>');
        }

        // Botón siguiente
        if (paginaActual < totalPaginas) {
            paginacion.append('<button class="navi-button navi-pagina" data-pagina="' + (paginaActual + 1) + '">Siguiente</button>');
        }

        $('#navi-sedes-lista').after(paginacion);

        // Manejar clicks en paginación
        $('.navi-pagina').off('click').on('click', function () {
            var nuevaPagina = $(this).data('pagina');
            cargarSedes(plantilla_id, nuevaPagina);
        });
    }

    function editarSede(sede_id) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_sede',
                nonce: navi_ajax.nonce,
                sede_id: sede_id
            },
            success: function (response) {
                if (response.success) {
                    var sede = response.data;
                    var formEdicion = crearFormularioEdicion(sede);
                    $('#navi-sedes-lista').html(formEdicion);
                } else {
                    console.error('Error al obtener datos de la sede:', response.data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error AJAX al obtener datos de la sede:', textStatus, errorThrown);
            }
        });
    }

    function crearFormularioEdicion(sede) {
        var form = $('<form id="form-edicion-sede" enctype="multipart/form-data"></form>');
        form.append('<input type="hidden" name="action" value="navi_guardar_cambios_sede">');
        form.append('<input type="hidden" name="nonce" value="' + navi_ajax.nonce + '">');
        form.append('<input type="hidden" name="sede_id" value="' + sede.id + '">');

        form.append('<div class="navi-form-group"><label>Nombre:</label><input type="text" name="nombre" value="' + sede.nombre + '" required></div>');
        form.append('<div class="navi-form-group"><label>Coordenada:</label><input type="text" name="coordenada" value="' + sede.coordenada + '" required></div>');

        // Logo
        form.append('<div class="navi-form-group"><label>Logo:</label><img src="' + (sede.logo || '#') + '" alt="Logo actual" class="navi-preview-img"><input type="file" name="logo" accept="image/*"></div>');

        // Marker
        form.append('<div class="navi-form-group"><label>Marker:</label><img src="' + (sede.marker || '#') + '" alt="Marker actual" class="navi-preview-img"><input type="file" name="marker" accept="image/*"></div>');

        // Fondo
        form.append('<div class="navi-form-group"><label>Fondo:</label><img src="' + (sede.fondo || '#') + '" alt="Fondo actual" class="navi-preview-img"><input type="file" name="fondo" accept="image/*"></div>');
        form.append('<div class="navi-form-group"><label>Fondo 2:</label><img src="' + (sede.fondo2 || '#') + '" alt="Fondo actual" class="navi-preview-img"><input type="file" name="fondo2" accept="image/*"></div>');

        form.append('<div class="navi-form-group"><label>País:</label><input type="text" name="pais" value="' + sede.pais + '" required></div>');
        form.append('<div class="navi-form-group"><label>' + sede.nivel1 + ':</label><input type="text" name="nivel1_dato" value="' + sede.nivel1_dato + '" required></div>');

        if (sede.nivel2) {
            form.append('<div class="navi-form-group"><label>' + sede.nivel2 + ':</label><input type="text" name="nivel2_dato" value="' + sede.nivel2_dato + '"></div>');
        }
        if (sede.nivel3) {
            form.append('<div class="navi-form-group"><label>' + sede.nivel3 + ':</label><input type="text" name="nivel3_dato" value="' + sede.nivel3_dato + '"></div>');
        }

        form.append('<div class="navi-form-group"><label>Correo:</label><input type="email" name="correo" value="' + sede.correo + '" required></div>');
        form.append('<div class="navi-form-group"><label>Teléfono:</label><input type="tel" name="telefono" value="' + sede.telefono + '" required></div>');
        form.append('<div class="navi-form-group"><label>Dirección:</label><textarea name="direccion" required>' + sede.direccion + '</textarea></div>');
        form.append('<div class="navi-form-group"><label>Horario:</label><textarea name="horario" required>' + sede.horario + '</textarea></div>');
        form.append('<div class="navi-form-group"><label>Página web:</label><input type="url" name="pagina_web" value="' + (sede.pagina_web || '') + '"></div>');

        form.append('<button type="submit" class="navi-button navi-button-primary">Guardar Cambios</button>');
        form.append('<button type="button" class="navi-button navi-button-secondary cancelar-edicion">Cancelar</button>');

        form.on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            guardarCambiosSede(formData);
        });

        form.find('.cancelar-edicion').on('click', function () {
            cargarSedes($('#filtro-plantilla').val());
        });

        return form;
    }

    function guardarCambiosSede(formData) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    alert('Cambios guardados con éxito');
                    cargarSedes($('#filtro-plantilla').val());
                } else {
                    console.error('Error al guardar cambios:', response.data);
                    alert('Error al guardar los cambios: ' + response.data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error AJAX al guardar cambios:', textStatus, errorThrown);
                alert('Error al guardar los cambios. Por favor, inténtalo de nuevo.');
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
             success: function (response) {
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
             error: function () {
                 alert('Error al subir el logo. Por favor, inténtalo de nuevo.');
             }
         });
     }

     function subirMarker(sedeId, file) {
        var formData = new FormData();
        formData.append('action', 'navi_actualizar_marker');
        formData.append('nonce', navi_ajax.nonce);
        formData.append('sede_id', sedeId);
        formData.append('marker', file);

        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    alert(response.data.message);
                    // Actualizar la imagen del marker en la tabla
                    $('input.marker-upload[data-sede-id="' + sedeId + '"]')
                        .siblings('img')
                        .attr('src', response.data.marker_url);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function () {
                alert('Error al subir el marker. Por favor, inténtalo de nuevo.');
            }
        });
    }

     function subirFondo(sedeId, file) {
         var formData = new FormData();
         formData.append('action', 'navi_actualizar_fondo');
         formData.append('nonce', navi_ajax.nonce);
         formData.append('sede_id', sedeId);
         formData.append('fondo', file);

         $.ajax({
             url: navi_ajax.ajax_url,
             type: 'POST',
             data: formData,
             processData: false,
             contentType: false,
             success: function (response) {
                 if (response.success) {
                     alert(response.data.message);
                     // Actualizar la imagen del fondo en la tabla
                     $('input.fondo-upload[data-sede-id="' + sedeId + '"]')
                         .siblings('img')
                         .attr('src', response.data.fondo_url);
                 } else {
                     alert('Error: ' + response.data);
                 }
             },
             error: function () {
                 alert('Error al subir el fondo. Por favor, inténtalo de nuevo.');
             }
         });
     }

     function subirFondo2(sedeId, file) {
        var formData = new FormData();
        formData.append('action', 'navi_actualizar_fondo2');
        formData.append('nonce', navi_ajax.nonce);
        formData.append('sede_id', sedeId);
        formData.append('fondo2', file);

        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    alert(response.data.message);
                    // Actualizar la imagen del fondo en la tabla
                    $('input.fondo2-upload[data-sede-id="' + sedeId + '"]')
                        .siblings('img')
                        .attr('src', response.data.fondo2_url);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function () {
                alert('Error al subir el fondo. Por favor, inténtalo de nuevo.');
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
                        var nivelGroup = $('<div class="navi-form-group"></div>');
                        nivelGroup.append('<label for="nivel' + (index + 1) + '_dato">' + nivel.nombre + '</label>');
                        nivelGroup.append('<input type="hidden" name="nivel' + (index + 1) + '" value="' + nivel.nombre + '">');
                        var select = $('<select id="nivel' + (index + 1) + '_dato" name="nivel' + (index + 1) + '_dato" ' + (index === 0 ? 'required' : '') + '></select>');
                        select.append('<option value="">Seleccione una opción</option>');
                        nivelGroup.append(select);
                        nivelesContainer.append(nivelGroup);

                        cargarOpcionesNivel(plantilla_id, index + 1, pais);
                    });

                    // Añadir eventos de cambio para cada nivel
                    niveles.forEach(function (nivel, index) {
                        $('#nivel' + (index + 1) + '_dato').on('change', function () {
                            var nivelActual = index + 1;
                            var valorSeleccionado = $(this).val();

                            // Limpiar y deshabilitar niveles inferiores
                            for (var i = nivelActual + 1; i <= niveles.length; i++) {
                                $('#nivel' + i + '_dato')
                                    .empty()
                                    .append('<option value="">Seleccione una opción</option>')
                                    .prop('disabled', true);
                            }

                            // Cargar opciones para el siguiente nivel si existe y se ha seleccionado un valor
                            if (nivelActual < niveles.length && valorSeleccionado) {
                                cargarOpcionesNivel(plantilla_id, nivelActual + 1, pais);
                            }
                        });
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar niveles por país:', error);
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
                    $('#marker-preview').empty();
                    $('#fondo-preview').empty();
                    $('#fondo2-preview').empty();
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
                            '<div class="navi-config-item">' +
                            '<label class="navi-switch">' +
                            '<input type="checkbox" name="campos_mostrar[]" value="' + campo + '" ' + checked + '>' +
                            '<span class="navi-slider"></span>' +
                            '</label>' +
                            '<label>' + camposDisponibles[campo] + '</label>' +
                            '</div>'
                        );
                    });
    
                    // Actualizar el switch de mostrar mapa
                    $('#mostrar-mapa-container').html(
                        '<div class="navi-config-item">' +
                        '<label class="navi-switch">' +
                        '<input type="checkbox" id="mostrar_mapa" name="mostrar_mapa" value="1" ' + (config.mostrar_mapa == 1 ? 'checked' : '') + '>' +
                        '<span class="navi-slider"></span>' +
                        '</label>' +
                        '<label for="mostrar_mapa">Mostrar mapa</label>' +
                        '</div>'
                    );
    
                    // Actualizar el switch de mostrar formulario
                    $('#mostrar-formulario-container').html(
                        '<div class="navi-config-item">' +
                        '<label class="navi-switch">' +
                        '<input type="checkbox" id="mostrar_formulario" name="mostrar_formulario" value="1" ' + (config.mostrar_formulario == 1 ? 'checked' : '') + '>' +
                        '<span class="navi-slider"></span>' +
                        '</label>' +
                        '<label for="mostrar_formulario">Mostrar formulario de contacto</label>' +
                        '</div>'
                    );
    
                    $('#campos-mostrar-container, #mostrar-mapa-container, #mostrar-formulario-container').show();
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
    var mostrar_formulario = $('#mostrar_formulario').is(':checked') ? 1 : 0;

    $.ajax({
        url: navi_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'navi_guardar_config',
            nonce: navi_ajax.nonce,
            plantilla_id: plantilla_id,
            campos_mostrar: JSON.stringify(campos_mostrar),
            mostrar_mapa: mostrar_mapa,
            mostrar_formulario: mostrar_formulario
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