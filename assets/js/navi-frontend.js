(function ($) {
    var sedesData = [];

    $(document).ready(function () {
        console.log('Documento listo');
        console.log('Leaflet disponible:', typeof L !== 'undefined');

        $('.navi-filtro-sedes').each(function () {
            var container = $(this);
            var plantilla_id = container.data('plantilla-id');
            cargarPaises(container, plantilla_id);

            container.on('change', '#navi-filtro-pais', function () {
                var pais = $(this).val();
                if (pais) {
                    cargarNivelesPorPais(container, plantilla_id, pais);
                } else {
                    container.find('#navi-filtro-niveles').empty();
                    container.find('#navi-resultados-sedes').empty();
                    ocultarMapa(container);
                }
            });

            container.on('change', '.navi-filtro-nivel', function () {
                var nivelActual = $(this).data('nivel');
                var valorSeleccionado = $(this).val();

                // Limpiar niveles inferiores
                container.find('.navi-filtro-nivel').each(function () {
                    if ($(this).data('nivel') > nivelActual) {
                        $(this).empty().append('<option value="">Seleccione una opción</option>');
                    }
                });

                // Cargar opciones para el siguiente nivel si existe y se ha seleccionado un valor
                if (valorSeleccionado) {
                    var siguienteNivel = nivelActual + 1;
                    var siguienteSelect = container.find('.navi-filtro-nivel[data-nivel="' + siguienteNivel + '"]');
                    if (siguienteSelect.length) {
                        cargarOpcionesNivel(container, plantilla_id, siguienteNivel, container.find('#navi-filtro-pais').val());
                    }
                }

                filtrarSedes(container, plantilla_id);
            });
        });
    });

    function cargarPaises(container, plantilla_id) {
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_paises',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id
            },
            success: function (response) {
                if (response.success) {
                    var paises = response.data;
                    
                    // Asegurarse de que paises sea un array
                    if (!Array.isArray(paises)) {
                        if (typeof paises === 'object') {
                            paises = Object.values(paises);
                        } else {
                            console.error('Formato de países inesperado:', paises);
                            return;
                        }
                    }
                    
                    var select = container.find('#navi-filtro-pais');
                    select.empty().append('<option value="">Seleccione un país</option>');
                    paises.forEach(function (pais) {
                        select.append('<option value="' + pais + '">' + pais + '</option>');
                    });
                } else {
                    console.error('Error al cargar países:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al cargar países:', status, error);
            }
        });
    }

    function cargarNivelesPorPais(container, plantilla_id, pais) {
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
                    var nivelesContainer = container.find('#navi-filtro-niveles');
                    nivelesContainer.empty();
    
                    niveles.forEach(function (nivel, index) {
                        nivelesContainer.append(
                            '<select class="navi-filtro-nivel" data-nivel="' + (index + 1) + '">' +
                            '<option value="">Seleccione ' + nivel.nombre + '</option>' +
                            '</select>'
                        );
                    });
    
                    cargarOpcionesNivel(container, plantilla_id, 1, pais);
                } else {
                    console.error('Error al cargar niveles:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al cargar niveles:', status, error);
            }
        });
    }

    function cargarOpcionesNivel(container, plantilla_id, nivel, pais) {
        var niveles = {};
        container.find('.navi-filtro-nivel').each(function(index) {
            if (index < nivel - 1) {
                niveles['nivel' + (index + 1)] = $(this).val();
            }
        });

        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_obtener_opciones_nivel',
                nonce: navi_ajax.nonce,
                plantilla_id: plantilla_id,
                nivel: nivel,
                pais: pais,
                niveles_anteriores: JSON.stringify(niveles)
            },
            success: function (response) {
                if (response.success) {
                    var opciones = response.data;
                    var select = container.find('.navi-filtro-nivel[data-nivel="' + nivel + '"]');
                    select.empty().append('<option value="">Seleccione una opción</option>');
                    opciones.forEach(function (opcion) {
                        select.append('<option value="' + opcion + '">' + opcion + '</option>');
                    });
                } else {
                    console.error('Error al cargar opciones de nivel:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al cargar opciones de nivel:', status, error);
            }
        });
    }

    function filtrarSedes(container, plantilla_id) {
        var filtros = {
            plantilla_id: plantilla_id,
            pais: container.find('#navi-filtro-pais').val()
        };
    
        container.find('.navi-filtro-nivel').each(function(index) {
            var nivel = index + 1;
            var valor = $(this).val();
            if (valor) {
                filtros['nivel' + nivel] = valor;
            }
        });
    
        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_filtrar_sedes',
                nonce: navi_ajax.nonce,
                filtros: filtros
            },
            success: function (response) {
                if (response.success) {
                    sedesData = response.data.sedes;
                    mostrarSedes(container, response.data.sedes, response.data.campos_mostrar);
                    if (response.data.mostrar_mapa) {
                        actualizarMapa(container, response.data.sedes);
                        container.find('#navi-mapa').show();
                    } else {
                        ocultarMapa(container);
                    }
                } else {
                    console.error('Error al filtrar sedes:', response.data);
                    container.find('#navi-resultados-sedes').html('<p>Error al cargar las sedes. Por favor, inténtelo de nuevo.</p>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al filtrar sedes:', status, error);
                container.find('#navi-resultados-sedes').html('<p>Error al cargar las sedes. Por favor, inténtelo de nuevo.</p>');
            }
        });
    }

    function mostrarSedes(container, sedes, campos_mostrar) {
        var resultadosContainer = container.find('#navi-resultados-sedes');
        resultadosContainer.empty();
    
        if (sedes.length === 0) {
            resultadosContainer.append('<p class="navi-no-results">No se encontraron sedes con los filtros seleccionados.</p>');
            return;
        }
    
        sedes.forEach(function (sede) {
            var sedeHtml = '<div class="navi-sede">';
            campos_mostrar.forEach(function (campo) {
                if (sede.hasOwnProperty(campo)) {
                    if (campo === 'logo' && sede[campo]) {
                        sedeHtml += '<img src="' + sede[campo] + '" alt="Logo" class="navi-sede-logo">';
                    } else if (campo === 'pagina_web') {
                        sedeHtml += '<p class="navi-sede-' + campo + '"><strong>' + campo + ':</strong> <a href="' + sede[campo] + '" target="_blank">' + sede[campo] + '</a></p>';
                    } else if (campo.includes('nivel') && !campo.includes('_dato')) {
                        var nivelDato = campo + '_dato';
                        sedeHtml += '<p class="navi-sede-' + campo + '"><strong>' + sede[campo] + ':</strong> ' + sede[nivelDato] + '</p>';
                    } else if (!campo.includes('_dato')) {
                        sedeHtml += '<p class="navi-sede-' + campo + '"><strong>' + campo + ':</strong> ' + sede[campo] + '</p>';
                    }
                }
            });
            sedeHtml += '</div>';
            resultadosContainer.append(sedeHtml);
        });
    }

    function actualizarMapa(container, sedes) {
        console.log('Iniciando actualización del mapa');
        if (typeof L === 'undefined') {
            console.error('Leaflet no está disponible. No se puede actualizar el mapa.');
            return;
        }

        var mapaContainer = container.find('#navi-mapa');
        if (mapaContainer.length === 0) {
            return;
        }

        if (container.data('mapa')) {
            container.data('mapa').remove();
        }

        mapaContainer.css({
            'height': '400px',
            'width': '100%'
        });

        var mapa = L.map(mapaContainer[0]).setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(mapa);

        var marcadores = L.featureGroup().addTo(mapa);

        sedes.forEach(function (sede, index) {
            if (sede.coordenada) {
                var coordenadas = sede.coordenada.split(',').map(parseFloat);

                var icono;
                if (sede.logo) {
                    icono = L.icon({
                        iconUrl: sede.logo,
                        iconSize: [32, 32],
                        iconAnchor: [16, 32],
                        popupAnchor: [0, -32]
                    });
                } else {
                    icono = L.ExtraMarkers.icon({
                        icon: 'fa-building',
                        markerColor: 'blue',
                        shape: 'square',
                        prefix: 'fa'
                    });
                }

                var marcador = L.marker(coordenadas, { icon: icono }).addTo(marcadores);
                marcador.sedeData = sede;
                marcador.sedeIndex = index;

                var popupContent = customPopupContent(sede);
                marcador.bindPopup(popupContent);

                marcador.on('click', function () {
                    if (typeof window.onSedeMarkerClick === 'function') {
                        window.onSedeMarkerClick(this.sedeData, this.sedeIndex);
                    }
                });
            }
        });

        if (marcadores.getBounds().isValid()) {
            mapa.fitBounds(marcadores.getBounds(), { padding: [50, 50] });
        }

        container.data('mapa', mapa);

        setTimeout(function () {
            mapa.invalidateSize();
        }, 100);
    }

    function customPopupContent(sede) {
        return `
            <div class="navi-sede-popup">
                <h3 class="sede-nombre">${sede.nombre}</h3>
                ${sede.direccion ? `<p class="sede-direccion">${sede.direccion}</p>` : ''}
                ${sede.telefono ? `<p class="sede-telefono">Tel: ${sede.telefono}</p>` : ''}
                ${sede.correo ? `<p class="sede-correo">Email: ${sede.correo}</p>` : ''}
                ${sede.pagina_web ? `<a href="${sede.pagina_web}" target="_blank" class="sede-web">Sitio web</a>` : ''}
            </div>
        `;
    }

    function ocultarMapa(container) {
        var mapaContainer = container.find('#navi-mapa');
        if (mapaContainer.length > 0) {
            mapaContainer.hide();
        }
    }

    window.navi = {
        obtenerDatosSedes: function () {
            return sedesData;
        },
        personalizarPopup: function (funcionPersonalizada) {
            if (typeof funcionPersonalizada === 'function') {
                customPopupContent = funcionPersonalizada;
            }
        }
    };

})(jQuery);