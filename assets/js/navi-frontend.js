(function ($) {
    var sedesData = [];
    var nivelesCargados = {};

    function initCustomSelects() {
        $('.navi-select-wrapper').each(function () {
            if ($(this).find('.select-selected').length) return;

            var $select = $(this).find('select');
            var $wrapper = $(this);

            // Crear el div que mostrará la opción seleccionada
            var $selectedDiv = $('<div class="select-selected"></div>').text($select.find('option:selected').text());
            var $optionList = $('<div class="select-items select-hide"></div>');

            // Manejar la deshabilitación visual
            if ($select.prop('disabled')) {
                $selectedDiv.addClass('disabled');
            }

            $select.find('option').each(function (index) {
                if (index !== 0) {
                    $('<div></div>').text($(this).text()).appendTo($optionList);
                }
            });

            $wrapper.append($selectedDiv, $optionList);

            $selectedDiv.on('click', function (e) {
                if ($select.prop('disabled')) return;
                e.stopPropagation();
                closeAllSelect(this);
                $optionList.toggleClass('select-hide');
                $(this).toggleClass('select-arrow-active');
            });

            $optionList.on('click', 'div', function () {
                var selectedIndex = $(this).index() + 1;
                $select.prop('selectedIndex', selectedIndex).trigger('change');
                $selectedDiv.text($(this).text());
                $(this).addClass('same-as-selected').siblings().removeClass('same-as-selected');
                $optionList.addClass('select-hide');
                $selectedDiv.removeClass('select-arrow-active');
            });
        });

        $(document).on('click', closeAllSelect);
    }

    function closeAllSelect(elmnt) {
        $('.select-items').not($(elmnt).next('.select-items')).addClass('select-hide');
        $('.select-selected').not(elmnt).removeClass('select-arrow-active');
    }

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
                    var paises = Array.isArray(response.data) ? response.data : Object.values(response.data);
                    var $select = container.find('#navi-filtro-pais');
                    $select.empty().append('<option value="">Seleccione un país</option>');
                    paises.forEach(function (pais) {
                        $select.append($('<option>', { value: pais, text: pais }));
                    });
                    actualizarSelectPersonalizado($select);
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
                    var nivelesContainer = container.find('#navi-filtro-niveles').empty();
                    nivelesCargados = {}; // Reiniciar los niveles cargados

                    niveles.forEach(function (nivel, index) {
                        var nivelNum = index + 1;
                        var selectWrapper = $('<div class="custom-select navi-select-wrapper"></div>');
                        var select = $('<select>', {
                            class: 'navi-filtro-nivel',
                            'data-nivel': nivelNum,
                            disabled: nivelNum !== 1 // Solo el primer nivel estará habilitado inicialmente
                        }).append($('<option>', {
                            value: '',
                            text: 'Seleccione ' + nivel.nombre
                        }));

                        nivelesCargados['nivel' + nivelNum] = nivel.nombre;

                        selectWrapper.append(select);
                        nivelesContainer.append(selectWrapper);
                    });

                    initCustomSelects();
                    if (niveles.length > 0) {
                        cargarOpcionesNivel(container, plantilla_id, 1, pais);
                    }
                } else {
                    console.error('Error al cargar niveles:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al cargar niveles:', status, error);
            }
        });
    }

    function actualizarSelectPersonalizado($select) {
        var $wrapper = $select.closest('.navi-select-wrapper');
        var $selectedDiv = $wrapper.find('.select-selected');
        var $optionList = $wrapper.find('.select-items');

        $selectedDiv.text($select.find('option:selected').text());

        // Actualizar el estado visual de habilitación/deshabilitación
        if ($select.prop('disabled')) {
            $selectedDiv.addClass('disabled');
        } else {
            $selectedDiv.removeClass('disabled');
        }

        $optionList.empty();

        $select.find('option').each(function (index) {
            if (index !== 0) {
                $('<div></div>').text($(this).text()).appendTo($optionList);
            }
        });
    }

    function cargarOpcionesNivel(container, plantilla_id, nivel, pais) {
        var niveles = {};
        container.find('.navi-filtro-nivel').each(function (index) {
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
                    var $select = container.find('.navi-filtro-nivel[data-nivel="' + nivel + '"]');

                    $select.empty().append($('<option>', {
                        value: '',
                        text: 'Seleccione ' + nivelesCargados['nivel' + nivel]
                    }));

                    opciones.forEach(function (opcion) {
                        $select.append($('<option>', { value: opcion, text: opcion }));
                    });

                    // Habilitar este nivel y actualizar su apariencia
                    $select.prop('disabled', false);
                    actualizarSelectPersonalizado($select);

                    // Deshabilitar y reiniciar todos los niveles siguientes
                    var siguienteNivel = nivel + 1;
                    container.find('.navi-filtro-nivel').each(function () {
                        var nivelActual = $(this).data('nivel');
                        if (nivelActual > nivel) {
                            $(this).prop('disabled', true)
                                .empty()
                                .append($('<option>', {
                                    value: '',
                                    text: 'Seleccione ' + nivelesCargados['nivel' + nivelActual]
                                }));
                            actualizarSelectPersonalizado($(this));
                        }
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

        container.find('.navi-filtro-nivel').each(function (index) {
            var valor = $(this).val();
            if (valor) {
                filtros['nivel' + (index + 1)] = valor;
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
        var resultadosContainer = container.find('#navi-resultados-sedes').empty();

        if (sedes.length === 0) {
            resultadosContainer.append('<p class="navi-no-results">No se encontraron sedes con los filtros seleccionados.</p>');
            return;
        }

        sedes.forEach(function (sede) {
            var sedeHtml = $('<div class="navi-sede"></div>');
            campos_mostrar.forEach(function (campo) {
                if (sede.hasOwnProperty(campo)) {
                    if (campo === 'logo' && sede[campo]) {
                        sedeHtml.append($('<img>', { src: sede[campo], alt: 'Logo', class: 'navi-sede-logo' }));
                    } else if (campo === 'pagina_web') {
                        sedeHtml.append($('<p class="navi-sede-' + campo + '"><strong>' + campo + ':</strong> <a href="' + sede[campo] + '" target="_blank">' + sede[campo] + '</a></p>'));
                    } else if (campo.includes('nivel') && !campo.includes('_dato')) {
                        var nivelDato = campo + '_dato';
                        sedeHtml.append($('<p class="navi-sede-' + campo + '"><strong>' + sede[campo] + ':</strong> ' + sede[nivelDato] + '</p>'));
                    } else if (!campo.includes('_dato')) {
                        sedeHtml.append($('<p class="navi-sede-' + campo + '"><strong>' + campo + ':</strong> ' + sede[campo] + '</p>'));
                    }
                }
            });
            resultadosContainer.append(sedeHtml);
        });
    }

    function actualizarMapa(container, sedes) {
        if (typeof L === 'undefined') {
            console.error('Leaflet no está disponible. No se puede actualizar el mapa.');
            return;
        }

        var mapaContainer = container.find('#navi-mapa');
        if (mapaContainer.length === 0) return;

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
                var icono = sede.logo ? L.icon({
                    iconUrl: sede.logo,
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                }) : L.ExtraMarkers.icon({
                    icon: 'fa-building',
                    markerColor: 'blue',
                    shape: 'square',
                    prefix: 'fa'
                });

                var marcador = L.marker(coordenadas, { icon: icono }).addTo(marcadores);
                marcador.sedeData = sede;
                marcador.sedeIndex = index;
                marcador.bindPopup(customPopupContent(sede));

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
        container.find('#navi-mapa').hide();
    }

    $(document).ready(function () {
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

        initCustomSelects();
    });

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