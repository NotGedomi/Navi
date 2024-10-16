(function ($) {
    var nivelesCargados = {};

    function initCustomSelects() {
        $('.navi-select-wrapper').each(function () {
            if ($(this).find('.select-selected').length) return;

            var $select = $(this).find('select');
            var $wrapper = $(this);

            var $selectedDiv = $('<div class="select-selected"></div>').text($select.find('option:selected').text());
            var $optionList = $('<div class="select-items select-hide"></div>');

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
        container.find('#navi-resultados-sedes, #navi-mapa').css('opacity', '0.5');

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
                    if (response.data.redireccion) {
                        container.find('.navi-data').hide();
                        mostrarPopupRedireccion(response.data.redireccion);
                    } else {
                        container.find('.navi-data').show();
                        actualizarNiveles(container, response.data, plantilla_id, pais);
                    }
                } else {
                    console.error('Error al cargar niveles:', response.data);
                    mostrarError(container, 'Error al cargar niveles. Por favor, inténtelo de nuevo.');
                }
                container.find('#navi-resultados-sedes, #navi-mapa').css('opacity', '1');
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al cargar niveles:', status, error);
                mostrarError(container, 'Error de conexión. Por favor, inténtelo de nuevo.');
                container.find('#navi-resultados-sedes, #navi-mapa').css('opacity', '1');
            }
        });
    }

    function actualizarNiveles(container, niveles, plantilla_id, pais) {
        var nivelesContainer = container.find('#navi-filtro-niveles');
        nivelesContainer.empty();
        nivelesCargados = {};

        niveles.forEach(function (nivel, index) {
            var nivelNum = index + 1;
            var selectWrapper = $('<div class="custom-select navi-select-wrapper"></div>');
            var label = $('<label>', {
                for: 'navi-filtro-nivel-' + nivelNum,
                text: 'Selecciona tu ' + nivel.nombre
            });
            var select = $('<select>', {
                id: 'navi-filtro-nivel-' + nivelNum,
                class: 'navi-filtro-nivel',
                'data-nivel': nivelNum,
                disabled: nivelNum !== 1
            }).append($('<option>', {
                value: '',
                text: 'Seleccione ' + nivel.nombre
            }));

            nivelesCargados['nivel' + nivelNum] = nivel.nombre;
            selectWrapper.append(label, select);
            nivelesContainer.append(selectWrapper);
        });

        initCustomSelects();
        if (niveles.length > 0) {
            cargarOpcionesNivel(container, plantilla_id, 1, pais);
        }
        filtrarSedes(container, plantilla_id, 0);
    }

    function actualizarSelectPersonalizado($select) {
        var $wrapper = $select.closest('.navi-select-wrapper');
        var $selectedDiv = $wrapper.find('.select-selected');
        var $optionList = $wrapper.find('.select-items');

        $selectedDiv.text($select.find('option:selected').text());

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

                    $select.prop('disabled', false);
                    actualizarSelectPersonalizado($select);
                } else {
                    console.error('Error al cargar opciones de nivel:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al cargar opciones de nivel:', status, error);
            }
        });
    }

    function filtrarSedes(container, plantilla_id, nivelCambiado) {
        var filtros = {
            plantilla_id: plantilla_id,
            pais: container.find('#navi-filtro-pais').val()
        };

        var nivelesSeleccionados = [];

        container.find('.navi-filtro-nivel').each(function (index) {
            var nivel = index + 1;
            var valor = $(this).val();

            if (valor) {
                nivelesSeleccionados.push({
                    nivel: nivel,
                    valor: valor
                });
                filtros['nivel' + nivel] = valor;
            }

            if (nivel > nivelCambiado) {
                $(this).prop('disabled', true).empty().append($('<option>', {
                    value: '',
                    text: 'Seleccione ' + nivelesCargados['nivel' + nivel]
                }));
                actualizarSelectPersonalizado($(this));
            } else if (nivel === nivelCambiado + 1) {
                $(this).prop('disabled', false);
            }
        });

        container.find('#navi-resultados-sedes, #navi-mapa').css('opacity', '0.5');

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
                    if (response.data.redireccion) {
                        container.find('.navi-data').hide();
                        mostrarPopupRedireccion(response.data.redireccion);
                    } else {
                        mostrarSedes(container, response.data.sedes, response.data.campos_mostrar, response.data.use_custom_render);
                        if (response.data.mostrar_mapa) {
                            actualizarMapa(container, response.data.sedes);
                            container.find('#navi-mapa').show();
                        } else {
                            container.find('#navi-mapa').hide();
                        }

                        if (response.data.sedes.length === 0 && nivelesSeleccionados.length > 1) {
                            filtrarSedesFlexible(container, plantilla_id, nivelesSeleccionados[0]);
                        }
                    }
                } else {
                    console.error('Error al filtrar sedes:', response.data);
                    mostrarError(container, 'Error al cargar las sedes. Por favor, inténtelo de nuevo.');
                }
                container.find('#navi-resultados-sedes, #navi-mapa').css('opacity', '1');
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al filtrar sedes:', status, error);
                mostrarError(container, 'Error de conexión. Por favor, inténtelo de nuevo.');
                container.find('#navi-resultados-sedes, #navi-mapa').css('opacity', '1');
            }
        });
    }

    function filtrarSedesFlexible(container, plantilla_id, nivelMasAlto) {
        var filtrosFlexibles = {
            plantilla_id: plantilla_id,
            pais: container.find('#navi-filtro-pais').val(),
            ['nivel' + nivelMasAlto.nivel]: nivelMasAlto.valor
        };

        $.ajax({
            url: navi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'navi_filtrar_sedes',
                nonce: navi_ajax.nonce,
                filtros: filtrosFlexibles
            },
            success: function (responseFlexible) {
                if (responseFlexible.success && !responseFlexible.data.redireccion) {
                    mostrarSedes(container, responseFlexible.data.sedes, responseFlexible.data.campos_mostrar, responseFlexible.data.use_custom_render);
                    if (responseFlexible.data.mostrar_mapa) {
                        actualizarMapa(container, responseFlexible.data.sedes);
                        container.find('#navi-mapa').show();
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al filtrar sedes de manera flexible:', status, error);
            }
        });
    }

    function mostrarPopupRedireccion(url) {
        $('#navi-popup-redireccion').remove();

        // Realiza una solicitud AJAX para cargar la plantilla
        $.ajax({
            url: navi_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'navi_cargar_plantilla_redireccion',
                nonce: navi_ajax.nonce
            },
            success: function (response) {
                $('body').append(response);

                $('#navi-confirm').on('click', function () {
                    window.open(url, '_blank');
                    $('#navi-popup-redireccion').remove();
                });

                $('#navi-reject').on('click', function () {
                    $('#navi-popup-redireccion').remove();
                });
            },
            error: function () {
                console.error("No se pudo cargar la plantilla");
            }
        });
    }

    function mostrarSedes(container, sedes, campos_mostrar) {
        var resultadosContainer = container.find('#navi-resultados-sedes');
        var customRender = container.data('custom-render') === true;
        var mostrarFormulario = container.data('mostrar-formulario') === true;

        if (sedes.length === 0) {
            resultadosContainer.html('<p class="navi-no-results">No se encontraron sedes con los filtros seleccionados.</p>');
            return;
        }

        if (customRender && typeof window.naviCustomRender === 'function') {
            var customContent = window.naviCustomRender(sedes, campos_mostrar, false, mostrarFormulario);
            resultadosContainer.html(customContent.sedesHtml || '');
        } else {
            resultadosContainer.empty();
            sedes.forEach(function (sede) {
                var sedeHtml = $('<div class="navi-sede"></div>');
                campos_mostrar.forEach(function (campo) {
                    if (sede.hasOwnProperty(campo)) {
                        if (['logo', 'marker', 'fondo', 'fondo2'].includes(campo) && sede[campo]) {
                            sedeHtml.append($('<img>', { src: sede[campo], alt: campo, class: 'navi-sede-' + campo }));
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
                if (mostrarFormulario) {
                    var contactButton = $('<button class="contactButton">Contacto</button>');
                    for (var key in sede) {
                        if (sede.hasOwnProperty(key)) {
                            contactButton.attr('data-' + key, sede[key]);
                        }
                    }
                    sedeHtml.append(contactButton);
                }
                resultadosContainer.append(sedeHtml);
            });
        }
    }

    function actualizarMapa(container, sedes) {
        if (typeof L === 'undefined') {
            console.error('Leaflet no está disponible. No se puede actualizar el mapa.');
            return;
        }
    
        var mapaContainer = container.find('#navi-mapa-container');
        if (mapaContainer.length === 0) return;
    
        if (container.data('mapa')) {
            container.data('mapa').remove();
        }
    
        var customRender = container.data('custom-render') === true;
    
        if (customRender && typeof window.naviCustomRender === 'function') {
            var customContent = window.naviCustomRender(sedes, null, true);
            mapaContainer.html(customContent.mapHtml || '<div id="navi-mapa"></div>');
        } else {
            mapaContainer.html('<div id="navi-mapa"></div>');
        }
    
        var mapa = L.map('navi-mapa').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(mapa);
    
        var marcadores = L.featureGroup().addTo(mapa);
    
        sedes.forEach(function (sede, index) {
            if (sede.coordenada) {
                var coordenadas = sede.coordenada.split(',').map(parseFloat);
                var svgContent = `
                    <svg width="24" height="34" viewBox="0 0 24 34" fill="none" xmlns="http://www.w3.org/2000/svg" class="custom-icon" data-sede-id="sede-${sede.id}">
                        <path d="M0.213883 12.1696C-0.0873462 6.16278 4.28934 1.09505 9.92409 0.138201C14.478 -0.623731 19.404 1.82154 21.7252 6.07419C23.568 9.4763 23.4971 13.0556 22.7352 16.6704C21.3176 23.4392 17.6852 28.8613 12.5643 33.3797C11.9795 33.9113 11.572 33.9822 10.9341 33.4152C5.22846 28.3651 1.40108 22.2874 0.391077 14.6326C0.28476 13.8175 0.267041 13.0025 0.213883 12.1696ZM11.7669 21.2065C17.1359 21.1888 21.424 16.8476 21.4062 11.4609C21.3885 6.14506 17.0473 1.83926 11.7315 1.83926C6.32706 1.83926 2.02126 6.21594 2.03898 11.6203C2.07442 16.9184 6.43338 21.2242 11.7669 21.2065Z" fill="#EB3C96"/>
                        ${sede.marker ? `
                            <clipPath id="circle-clip-${sede.id}">
                                <circle cx="11.7315" cy="11.5229" r="7.3"/>
                            </clipPath>
                            <image href="${sede.marker}" x="4.4315" y="4.2229" width="14.6" height="14.6" clip-path="url(#circle-clip-${sede.id})"/>
                        ` : ''}
                    </svg>
                `;
                
                var icono = L.divIcon({
                    html: `<div class="marker-wrapper">${svgContent}</div>`,
                    className: 'custom-marker-container',
                    iconSize: [24, 34],
                    iconAnchor: [12, 34]
                });
    
                var marcador = L.marker(coordenadas, { icon: icono }).addTo(marcadores);
                marcador.sedeData = sede;
                marcador.sedeIndex = index;
    
                var marcadorElement = marcador.getElement();
                if (marcadorElement) {
                    marcadorElement.setAttribute('data-sede-id', 'sede-' + (sede.id || index));
                    marcadorElement.addEventListener('mouseenter', function () {
                        this.classList.add('hover');
                    });
                    marcadorElement.addEventListener('mouseleave', function () {
                        this.classList.remove('hover');
                    });
                }
    
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

    /*
    -- Función para añadir un Popup encima del marker

    function customPopupContent(sede) {
        return `
            <div class="navi-sede-popup">
                <h3 class="sede-nombre">${sede.nombre}</h3>
                ${sede.direccion ? `<p class="sede-direccion">${sede.direccion}</p>` : ''}
                ${sede.horario ? `<p class="sede-horario">${sede.horario}</p>` : ''}
                ${sede.telefono ? `<p class="sede-telefono">Tel: ${sede.telefono}</p>` : ''}
                ${sede.correo ? `<p class="sede-correo">Email: ${sede.correo}</p>` : ''}
                ${sede.pagina_web ? `<a href="${sede.pagina_web}" target="_blank" class="sede-web">Sitio web</a>` : ''}
            </div>
        `;
    }
    */

    function mostrarError(container, mensaje) {
        container.find('#navi-resultados-sedes').html('<p class="navi-error">' + mensaje + '</p>');
    }

    jQuery(document).ready(function ($) {
        $('.navi-filtro-sedes').each(function () {
            var container = $(this);
            var plantilla_id = container.data('plantilla-id');
            cargarPaises(container, plantilla_id);

            container.on('change', '#navi-filtro-pais', function () {
                var pais = $(this).val();
                if (pais) {
                    cargarNivelesPorPais(container, plantilla_id, pais);
                } else {
                    container.find('.navi-data').hide();
                    container.find('#navi-filtro-niveles').empty();
                    container.find('#navi-resultados-sedes').empty();
                    container.find('#navi-mapa').hide();
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

                filtrarSedes(container, plantilla_id, nivelActual);
            });
        });

        initCustomSelects();
    });

    window.navi = {
        personalizarPopup: function (funcionPersonalizada) {
            if (typeof funcionPersonalizada === 'function') {
                customPopupContent = funcionPersonalizada;
            }
        }
    };
})(jQuery);