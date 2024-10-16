<?php
class Navi_Sedes
{
    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function render_pagina()
    {
        ?>
        <div class="wrap navi-admin">
            <h1>Gestionar Sedes</h1>
            <div class="navi-card">
                <h2>Agregar Nueva Sede</h2>
                <form id="navi-sede-form" enctype="multipart/form-data">
                    <div class="navi-form-group">
                        <label for="plantilla_id">Plantilla</label>
                        <select id="plantilla_id" name="plantilla_id" required>
                            <option value="">Seleccione una plantilla</option>
                            <?php
                            $plantillas = $this->obtener_plantillas();
                            foreach ($plantillas as $plantilla) {
                                echo "<option value='{$plantilla['id']}'>{$plantilla['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="navi-form-group">
                        <label for="nombre">Nombre de la Sede</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="navi-form-group">
                        <label for="coordenada">Coordenada</label>
                        <input type="text" id="coordenada" name="coordenada" required
                            pattern="^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$"
                            title="Formato: latitud,longitud (ej: 41.40338, 2.17403)">
                    </div>
                    <div class="navi-form-group">
                        <label for="logo">Logo</label>
                        <input type="file" id="logo" name="logo" accept="image/*">
                        <div id="logo-preview" class="navi-preview"></div>
                    </div>
                    <div class="navi-form-group">
                        <label for="marker">Marker</label>
                        <input type="file" id="marker" name="marker" accept="image/*">
                        <div id="marker-preview" class="navi-preview"></div>
                    </div>
                    <div class="navi-form-group">
                        <label for="fondo">Fondo</label>
                        <input type="file" id="fondo" name="fondo" accept="image/*">
                        <div id="fondo-preview" class="navi-preview"></div>
                    </div>
                    <div class="navi-form-group">
                        <label for="fondo2">Fondo 2</label>
                        <input type="file" id="fondo2" name="fondo2" accept="image/*">
                        <div id="fondo2-preview" class="navi-preview"></div>
                    </div>
                    <div class="navi-form-group">
                        <label for="pais">País</label>
                        <select id="pais" name="pais" required>
                            <option value="">Seleccione un país</option>
                        </select>
                    </div>
                    <div id="niveles-container">
                        <!-- Los niveles se cargarán dinámicamente aquí -->
                    </div>
                    <div class="navi-form-group">
                        <label for="correo">Correo de contacto</label>
                        <input type="email" id="correo" name="correo" required>
                    </div>
                    <div class="navi-form-group">
                        <label for="telefono">Número de teléfono</label>
                        <input type="tel" id="telefono" name="telefono" required pattern="^\+?[0-9]{6,15}$"
                            title="Número de teléfono (6-15 dígitos, puede incluir + al inicio)">
                    </div>
                    <div class="navi-form-group">
                        <label for="direccion">Dirección</label>
                        <textarea id="direccion" name="direccion" required></textarea>
                    </div>
                    <div class="navi-form-group">
                        <label for="horario">Horario</label>
                        <textarea id="horario" name="horario" required></textarea>
                    </div>
                    <div class="navi-form-group">
                        <label for="pagina_web">Página web</label>
                        <input type="url" id="pagina_web" name="pagina_web">
                    </div>
                    <button type="submit" class="navi-button navi-button-primary">Guardar Sede</button>
                </form>
                <div id="navi-sede-mensaje"></div>
            </div>

            <div class="navi-card">
                <h2>Sedes Registradas</h2>
                <div class="navi-form-group">
                    <label for="filtro-plantilla">Filtrar por plantilla</label>
                    <select id="filtro-plantilla">
                        <option value="">Todas las plantillas</option>
                        <?php
                        $plantillas = $this->obtener_plantillas();
                        foreach ($plantillas as $plantilla) {
                            echo "<option value='{$plantilla['id']}'>{$plantilla['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div id="navi-sedes-lista" class="navi-list">
                    <!-- Los datos se cargarán aquí dinámicamente -->
                </div>
            </div>
        </div>
        <?php
    }

    private function obtener_plantillas()
    {
        $tabla = $this->db->prefix . 'navi_plantillas';
        return $this->db->get_results("SELECT DISTINCT id, nombre FROM $tabla", ARRAY_A);
    }

    public function ajax_editar_sede()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        $sede_id = intval($_POST['sede_id']);
        $plantilla_id = intval($_POST['plantilla_id']);
        $nombre = sanitize_text_field($_POST['nombre']);
        $coordenada = sanitize_text_field($_POST['coordenada']);
        $pais = sanitize_text_field($_POST['pais']);
        $nivel1 = sanitize_text_field($_POST['nivel1']);
        $nivel1_dato = sanitize_text_field($_POST['nivel1_dato']);
        $nivel2 = isset($_POST['nivel2']) ? sanitize_text_field($_POST['nivel2']) : '';
        $nivel2_dato = isset($_POST['nivel2_dato']) ? sanitize_text_field($_POST['nivel2_dato']) : '';
        $nivel3 = isset($_POST['nivel3']) ? sanitize_text_field($_POST['nivel3']) : '';
        $nivel3_dato = isset($_POST['nivel3_dato']) ? sanitize_text_field($_POST['nivel3_dato']) : '';
        $correo = sanitize_email($_POST['correo']);
        $telefono = sanitize_text_field($_POST['telefono']);
        $direccion = sanitize_textarea_field($_POST['direccion']);
        $horario = sanitize_textarea_field($_POST['horario']);
        $pagina_web = esc_url_raw($_POST['pagina_web']);

        // Validaciones
        if (!preg_match('/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/', $coordenada)) {
            wp_send_json_error('La coordenada no tiene un formato válido.');
        }

        if (!is_email($correo)) {
            wp_send_json_error('El correo electrónico no es válido.');
        }

        if (!preg_match('/^\+?[0-9]{6,15}$/', $telefono)) {
            wp_send_json_error('El número de teléfono no es válido.');
        }

        $datos_actualizar = array(
            'plantilla_id' => $plantilla_id,
            'nombre' => $nombre,
            'coordenada' => $coordenada,
            'pais' => $pais,
            'nivel1' => $nivel1,
            'nivel1_dato' => $nivel1_dato,
            'nivel2' => $nivel2,
            'nivel2_dato' => $nivel2_dato,
            'nivel3' => $nivel3,
            'nivel3_dato' => $nivel3_dato,
            'correo' => $correo,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'horario' => $horario,
            'pagina_web' => $pagina_web
        );

        // Manejar la actualización del logo si se proporciona uno nuevo
        if (isset($_FILES['logo']) && !empty($_FILES['logo']['name'])) {
            $upload = wp_handle_upload($_FILES['logo'], array('test_form' => false));
            if (isset($upload['url'])) {
                $datos_actualizar['logo'] = $upload['url'];
            }
        }

        // Manejar la actualización del marker si se proporciona uno nuevo
        if (isset($_FILES['marker']) && !empty($_FILES['marker']['name'])) {
            $upload = wp_handle_upload($_FILES['marker'], array('test_form' => false));
            if (isset($upload['url'])) {
                $datos_actualizar['marker'] = $upload['url'];
            }
        }

        // Manejar la actualización del fondo si se proporciona uno nuevo
        if (isset($_FILES['fondo']) && !empty($_FILES['fondo']['name'])) {
            $upload = wp_handle_upload($_FILES['fondo'], array('test_form' => false));
            if (isset($upload['url'])) {
                $datos_actualizar['fondo'] = $upload['url'];
            }
        }
        // Manejar la actualización del fondo2 si se proporciona uno nuevo
        if (isset($_FILES['fondo2']) && !empty($_FILES['fondo2']['name'])) {
            $upload = wp_handle_upload($_FILES['fondo2'], array('test_form' => false));
            if (isset($upload['url'])) {
                $datos_actualizar['fondo2'] = $upload['url'];
            }
        }

        $tabla = $this->db->prefix . 'navi_sedes';
        $resultado = $this->db->update(
            $tabla,
            $datos_actualizar,
            array('id' => $sede_id),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );

        if ($resultado !== false) {
            wp_send_json_success('Sede actualizada con éxito.');
        } else {
            wp_send_json_error('Error al actualizar la sede.');
        }
    }

    public function ajax_guardar_sede()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        $plantilla_id = intval($_POST['plantilla_id']);
        $nombre = sanitize_text_field($_POST['nombre']);
        $coordenada = sanitize_text_field($_POST['coordenada']);
        $pais = sanitize_text_field($_POST['pais']);
        $nivel1 = sanitize_text_field($_POST['nivel1']);
        $nivel1_dato = sanitize_text_field($_POST['nivel1_dato']);
        $nivel2 = isset($_POST['nivel2']) ? sanitize_text_field($_POST['nivel2']) : '';
        $nivel2_dato = isset($_POST['nivel2_dato']) ? sanitize_text_field($_POST['nivel2_dato']) : '';
        $nivel3 = isset($_POST['nivel3']) ? sanitize_text_field($_POST['nivel3']) : '';
        $nivel3_dato = isset($_POST['nivel3_dato']) ? sanitize_text_field($_POST['nivel3_dato']) : '';
        $correo = sanitize_email($_POST['correo']);
        $telefono = sanitize_text_field($_POST['telefono']);
        $direccion = sanitize_textarea_field($_POST['direccion']);
        $horario = sanitize_textarea_field($_POST['horario']);
        $pagina_web = esc_url_raw($_POST['pagina_web']);

        // Validaciones
        if (!preg_match('/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/', $coordenada)) {
            wp_send_json_error('La coordenada no tiene un formato válido.');
        }

        if (!is_email($correo)) {
            wp_send_json_error('El correo electrónico no es válido.');
        }

        if (!preg_match('/^\+?[0-9]{6,15}$/', $telefono)) {
            wp_send_json_error('El número de teléfono no es válido.');
        }

        // Manejar la subida del logo
        $logo_url = '';
        if (isset($_FILES['logo'])) {
            $upload = wp_handle_upload($_FILES['logo'], array('test_form' => false));
            if (isset($upload['url'])) {
                $logo_url = $upload['url'];
            }
        }

        // Manejar la subida del marker
        $marker_url = '';
        if (isset($_FILES['marker'])) {
            $upload = wp_handle_upload($_FILES['marker'], array('test_form' => false));
            if (isset($upload['url'])) {
                $marker_url = $upload['url'];
            }
        }

        // Manejar la subida del fondo
        $fondo_url = '';
        if (isset($_FILES['fondo'])) {
            $upload = wp_handle_upload($_FILES['fondo'], array('test_form' => false));
            if (isset($upload['url'])) {
                $fondo_url = $upload['url'];
            }
        }

        // Manejar la subida del fondo
        $fondo2_url = '';
        if (isset($_FILES['fondo2'])) {
            $upload = wp_handle_upload($_FILES['fondo2'], array('test_form' => false));
            if (isset($upload['url'])) {
                $fondo2_url = $upload['url'];
            }
        }

        $tabla = $this->db->prefix . 'navi_sedes';
        $resultado = $this->db->insert(
            $tabla,
            array(
                'plantilla_id' => $plantilla_id,
                'nombre' => $nombre,
                'coordenada' => $coordenada,
                'logo' => $logo_url,
                'marker' => $marker_url,
                'fondo' => $fondo_url,
                'fondo2' => $fondo2_url,
                'pais' => $pais,
                'nivel1' => $nivel1,
                'nivel1_dato' => $nivel1_dato,
                'nivel2' => $nivel2,
                'nivel2_dato' => $nivel2_dato,
                'nivel3' => $nivel3,
                'nivel3_dato' => $nivel3_dato,
                'correo' => $correo,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'horario' => $horario,
                'pagina_web' => $pagina_web
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($resultado) {
            wp_send_json_success('Sede guardada con éxito.');
        } else {
            wp_send_json_error('Error al guardar la sede.');
        }
    }

    public function ajax_obtener_sedes()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        $plantilla_id = isset($_POST['plantilla_id']) ? intval($_POST['plantilla_id']) : 0;
        $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
        $por_pagina = 10; // Número de sedes por página

        $tabla_sedes = $this->db->prefix . 'navi_sedes';
        $tabla_plantillas = $this->db->prefix . 'navi_plantillas';

        $query = "SELECT s.id, s.nombre, p.nombre as nombre_plantilla 
                  FROM $tabla_sedes s
                  JOIN $tabla_plantillas p ON s.plantilla_id = p.id";

        $args = array();
        if ($plantilla_id > 0) {
            $query .= " WHERE s.plantilla_id = %d";
            $args[] = $plantilla_id;
        }

        $total_query = "SELECT COUNT(1) FROM ($query) AS combined_table";
        $total = $this->db->get_var($this->db->prepare($total_query, $args));

        $total_paginas = max(1, ceil($total / $por_pagina));
        $pagina = min($pagina, $total_paginas);

        $offset = ($pagina - 1) * $por_pagina;
        $query .= " LIMIT %d OFFSET %d";
        $args[] = $por_pagina;
        $args[] = $offset;

        $sedes = $this->db->get_results($this->db->prepare($query, $args), ARRAY_A);

        wp_send_json_success(array(
            'sedes' => $sedes,
            'total_paginas' => $total_paginas,
            'pagina_actual' => $pagina
        ));
    }

    public function ajax_obtener_sede()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        $sede_id = isset($_POST['sede_id']) ? intval($_POST['sede_id']) : 0;

        $tabla_sedes = $this->db->prefix . 'navi_sedes';
        $sede = $this->db->get_row($this->db->prepare(
            "SELECT * FROM $tabla_sedes WHERE id = %d",
            $sede_id
        ), ARRAY_A);

        if ($sede) {
            wp_send_json_success($sede);
        } else {
            wp_send_json_error('Sede no encontrada');
        }
    }

    public function ajax_guardar_cambios_sede()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        $sede_id = isset($_POST['sede_id']) ? intval($_POST['sede_id']) : 0;

        $datos_actualizar = array(
            'nombre' => sanitize_text_field($_POST['nombre']),
            'coordenada' => sanitize_text_field($_POST['coordenada']),
            'pais' => sanitize_text_field($_POST['pais']),
            'nivel1_dato' => sanitize_text_field($_POST['nivel1_dato']),
            'nivel2_dato' => isset($_POST['nivel2_dato']) ? sanitize_text_field($_POST['nivel2_dato']) : '',
            'nivel3_dato' => isset($_POST['nivel3_dato']) ? sanitize_text_field($_POST['nivel3_dato']) : '',
            'correo' => sanitize_email($_POST['correo']),
            'telefono' => sanitize_text_field($_POST['telefono']),
            'direccion' => sanitize_textarea_field($_POST['direccion']),
            'horario' => sanitize_textarea_field($_POST['horario']),
            'pagina_web' => esc_url_raw($_POST['pagina_web'])
        );

        // Manejar la subida del logo
        if (!empty($_FILES['logo']['name'])) {
            $logo_url = $this->subir_imagen('logo', $sede_id);
            if ($logo_url) {
                $datos_actualizar['logo'] = $logo_url;
            }
        }

        // Manejar la subida del marker
        if (!empty($_FILES['marker']['name'])) {
            $marker_url = $this->subir_imagen('marker', $sede_id);
            if ($marker_url) {
                $datos_actualizar['marker'] = $marker_url;
            }
        }

        // Manejar la subida del fondo
        if (!empty($_FILES['fondo']['name'])) {
            $fondo_url = $this->subir_imagen('fondo', $sede_id);
            if ($fondo_url) {
                $datos_actualizar['fondo'] = $fondo_url;
            }
        }

        // Manejar la subida del fondo
        if (!empty($_FILES['fondo2']['name'])) {
            $fondo2_url = $this->subir_imagen('fondo2', $sede_id);
            if ($fondo2_url) {
                $datos_actualizar['fondo2'] = $fondo2_url;
            }
        }

        $tabla_sedes = $this->db->prefix . 'navi_sedes';
        $resultado = $this->db->update(
            $tabla_sedes,
            $datos_actualizar,
            array('id' => $sede_id),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );

        if ($resultado !== false) {
            wp_send_json_success('Cambios guardados con éxito');
        } else {
            wp_send_json_error('Error al guardar los cambios');
        }
    }

    private function subir_imagen($tipo, $sede_id)
    {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploaded_file = $_FILES[$tipo];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            return $movefile['url'];
        } else {
            error_log("Error al subir $tipo: " . $movefile['error']);
            return false;
        }
    }

    public function ajax_obtener_niveles()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        $plantilla_id = intval($_POST['plantilla_id']);

        $tabla = $this->db->prefix . 'navi_plantillas';
        $plantilla = $this->db->get_row($this->db->prepare(
            "SELECT datos FROM $tabla WHERE id = %d LIMIT 1",
            $plantilla_id
        ), ARRAY_A);

        if (!$plantilla) {
            wp_send_json_error('Plantilla no encontrada.');
        }

        $datos_plantilla = json_decode($plantilla['datos'], true);

        if (empty($datos_plantilla)) {
            wp_send_json_error('Los datos de la plantilla no son válidos.');
        }

        $paises = array();
        $niveles = array();

        foreach ($datos_plantilla as $fila) {
            if (!in_array($fila['País'], $paises)) {
                $paises[] = $fila['País'];
            }

            for ($i = 1; $i <= 3; $i++) {
                $nivel_key = "Nivel {$i}";
                if (!empty($fila[$nivel_key]) && !in_array($fila[$nivel_key], array_column($niveles, 'nombre'))) {
                    $niveles[] = array('nivel' => $i, 'nombre' => $fila[$nivel_key]);
                }
            }
        }

        $respuesta = array(
            'paises' => $paises,
            'niveles' => $niveles
        );

        wp_send_json_success($respuesta);
    }

    public function ajax_obtener_paises()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce', false);

        $plantilla_id = isset($_POST['plantilla_id']) ? intval($_POST['plantilla_id']) : 0;

        $tabla_plantillas = $this->db->prefix . 'navi_plantillas';
        $plantilla = $this->db->get_row($this->db->prepare(
            "SELECT datos FROM $tabla_plantillas WHERE id = %d",
            $plantilla_id
        ), ARRAY_A);

        if (!$plantilla) {
            wp_send_json_error('Plantilla no encontrada.');
            return;
        }

        $datos_plantilla = json_decode($plantilla['datos'], true);
        $paises = array_values(array_unique(array_column($datos_plantilla, 'País')));

        wp_send_json_success($paises);
    }

    public function ajax_obtener_niveles_por_pais()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce', false);

        $plantilla_id = intval($_POST['plantilla_id']);
        $pais = sanitize_text_field($_POST['pais']);

        $tabla = $this->db->prefix . 'navi_plantillas';
        $plantilla = $this->db->get_row($this->db->prepare(
            "SELECT datos FROM $tabla WHERE id = %d LIMIT 1",
            $plantilla_id
        ), ARRAY_A);

        if (!$plantilla) {
            wp_send_json_error('Plantilla no encontrada.');
        }

        $datos_plantilla = json_decode($plantilla['datos'], true);

        if (empty($datos_plantilla)) {
            wp_send_json_error('Los datos de la plantilla no son válidos.');
        }

        $niveles = array();

        foreach ($datos_plantilla as $fila) {
            if ($fila['País'] === $pais) {
                for ($i = 1; $i <= 3; $i++) {
                    $nivel_key = "Nivel {$i}";
                    if (!empty($fila[$nivel_key])) {
                        $niveles[] = array(
                            'nombre' => $fila[$nivel_key],
                            'dato' => $fila["Nivel {$i} Dato"]
                        );
                    } else {
                        break;
                    }
                }
                break;
            }
        }

        wp_send_json_success($niveles);
    }

    public function ajax_obtener_opciones_nivel()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        $plantilla_id = intval($_POST['plantilla_id']);
        $nivel = intval($_POST['nivel']);
        $pais = sanitize_text_field($_POST['pais']);
        $niveles_anteriores = json_decode(stripslashes($_POST['niveles_anteriores']), true);

        $tabla = $this->db->prefix . 'navi_plantillas';
        $plantilla = $this->db->get_row($this->db->prepare(
            "SELECT datos FROM $tabla WHERE id = %d LIMIT 1",
            $plantilla_id
        ), ARRAY_A);

        if (!$plantilla) {
            wp_send_json_error('Plantilla no encontrada.');
        }

        $datos_plantilla = json_decode($plantilla['datos'], true);

        if (empty($datos_plantilla)) {
            wp_send_json_error('Los datos de la plantilla no son válidos.');
        }

        $opciones = array();

        foreach ($datos_plantilla as $fila) {
            if ($fila['País'] === $pais) {
                $cumple_condiciones = true;
                for ($i = 1; $i < $nivel; $i++) {
                    if (!isset($niveles_anteriores['nivel' . $i]) || $fila["Nivel $i Dato"] !== $niveles_anteriores['nivel' . $i]) {
                        $cumple_condiciones = false;
                        break;
                    }
                }
                if ($cumple_condiciones && isset($fila["Nivel $nivel Dato"])) {
                    $opcion = $fila["Nivel $nivel Dato"];
                    if (!in_array($opcion, $opciones)) {
                        $opciones[] = $opcion;
                    }
                }
            }
        }

        wp_send_json_success($opciones);
    }

    public function shortcode_filtro_sedes($atts)
    {
        $atts = shortcode_atts(array(
            'plantilla_id' => '',
            'custom_render' => 'false',
        ), $atts, 'navi_filtro_sedes');

        if (empty($atts['plantilla_id'])) {
            return 'Error: Plantilla no especificada';
        }

        $plantilla_id = intval($atts['plantilla_id']);
        $config = $this->obtener_config_plantilla($plantilla_id);

        if (!$config) {
            return 'Error: Configuración no encontrada para la plantilla especificada';
        }

        $custom_render = filter_var($atts['custom_render'], FILTER_VALIDATE_BOOLEAN);

        // Crear una sede vacía para evitar errores en la plantilla
        $sede_vacia = array(
            'fondo' => '',
            'fondo2' => '',
            'nombre' => '',
            'direccion' => '',
            'telefono' => '',
            'correo' => '',
            'horario' => '',
            'pagina_web' => '',
        );

        ob_start();
        ?>
        <div class="navi-filtro-sedes" data-plantilla-id="<?php echo esc_attr($plantilla_id); ?>"
            data-custom-render="<?php echo $custom_render ? 'true' : 'false'; ?>"
            data-mostrar-formulario="<?php echo $config['mostrar_formulario'] ? 'true' : 'false'; ?>">
            <div class="navi-filtros">
                <div class="custom-select navi-select-wrapper">
                    <label for="navi-filtro-pais">Selecciona tu país</label>
                    <select id="navi-filtro-pais">
                        <option value="">Seleccione un país</option>
                    </select>
                </div>
                <div id="navi-filtro-niveles"></div>
            </div>
            <div class="navi-data">
                <div id="navi-resultados-sedes"></div>
                <?php if ($config['mostrar_mapa']): ?>
                    <div id="navi-mapa-container"></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($config['mostrar_formulario']): ?>
            <?php
            $sede = $sede_vacia; // Pasar la sede vacía a la plantilla
            include(NAVI_PLUGIN_DIR . 'templates/contact-form-template.php');
        ?>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }

    public function ajax_filtrar_sedes()
    {
        if (!check_ajax_referer('navi_ajax_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Permiso denegado. Nonce no válido.'));
            return;
        }

        $filtros = $_POST['filtros'];
        $plantilla_id = intval($filtros['plantilla_id']);
        $pais = sanitize_text_field($filtros['pais']);

        // Verificar si hay redirección para este país
        $redireccion = $this->obtener_redireccion($plantilla_id, $pais);
        if ($redireccion) {
            wp_send_json_success(array('redireccion' => $redireccion));
            return;
        }

        $tabla = $this->db->prefix . 'navi_sedes';

        $query = "SELECT * FROM $tabla WHERE plantilla_id = %d AND pais = %s";
        $params = array($plantilla_id, $pais);

        for ($i = 1; $i <= 3; $i++) {
            $nivel_key = "nivel{$i}_dato";
            if (!empty($filtros["nivel{$i}"])) {
                $query .= " AND $nivel_key = %s";
                $params[] = $filtros["nivel{$i}"];
            } else {
                break;
            }
        }

        $sedes = $this->db->get_results($this->db->prepare($query, $params), ARRAY_A);

        $config = $this->obtener_config_plantilla($plantilla_id);
        $campos_mostrar = json_decode($config['campos_mostrar'], true);

        wp_send_json_success(array(
            'sedes' => $sedes,
            'campos_mostrar' => $campos_mostrar,
            'mostrar_mapa' => $config['mostrar_mapa'],
        ));
    }

    private function obtener_redireccion($plantilla_id, $pais)
    {
        $tabla_redirecciones = $this->db->prefix . 'navi_redirecciones';
        return $this->db->get_var($this->db->prepare(
            "SELECT url_redireccion FROM $tabla_redirecciones WHERE plantilla_id = %d AND pais = %s",
            $plantilla_id,
            $pais
        ));
    }

    private function obtener_config_plantilla($plantilla_id)
    {
        $tabla = $this->db->prefix . 'navi_config';
        $config = $this->db->get_row($this->db->prepare(
            "SELECT * FROM $tabla WHERE plantilla_id = %d",
            $plantilla_id
        ), ARRAY_A);

        if (!$config) {
            $config = array(
                'campos_mostrar' => json_encode(array('nombre', 'direccion', 'horario', 'telefono', 'correo')),
                'mostrar_mapa' => 1
            );
        }

        return $config;
    }

    public function ajax_eliminar_sede()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        $sede_id = intval($_POST['id']);
        $tabla = $this->db->prefix . 'navi_sedes';

        $resultado = $this->db->delete($tabla, array('id' => $sede_id), array('%d'));

        if ($resultado) {
            wp_send_json_success('Sede eliminada con éxito.');
        } else {
            wp_send_json_error('No se pudo eliminar la sede.');
        }
    }


    public function ajax_actualizar_logo()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        $sede_id = intval($_POST['sede_id']);

        if (!isset($_FILES['logo'])) {
            wp_send_json_error('No se ha subido ningún archivo.');
        }

        $upload = wp_handle_upload($_FILES['logo'], array('test_form' => false));

        if (isset($upload['error'])) {
            wp_send_json_error('Error al subir el archivo: ' . $upload['error']);
        }

        $logo_url = $upload['url'];

        $tabla = $this->db->prefix . 'navi_sedes';
        $resultado = $this->db->update(
            $tabla,
            array('logo' => $logo_url),
            array('id' => $sede_id),
            array('%s'),
            array('%d')
        );

        if ($resultado) {
            wp_send_json_success(array('message' => 'Logo actualizado con éxito.', 'logo_url' => $logo_url));
        } else {
            wp_send_json_error('Error al actualizar el logo en la base de datos.');
        }
    }

    public function ajax_actualizar_marker()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        $sede_id = intval($_POST['sede_id']);

        if (!isset($_FILES['marker'])) {
            wp_send_json_error('No se ha subido ningún archivo.');
        }

        $upload = wp_handle_upload($_FILES['marker'], array('test_form' => false));

        if (isset($upload['error'])) {
            wp_send_json_error('Error al subir el archivo: ' . $upload['error']);
        }

        $marker_url = $upload['url'];

        $tabla = $this->db->prefix . 'navi_sedes';
        $resultado = $this->db->update(
            $tabla,
            array('marker' => $marker_url),
            array('id' => $sede_id),
            array('%s'),
            array('%d')
        );

        if ($resultado) {
            wp_send_json_success(array('message' => 'Marker actualizado con éxito.', 'marker_url' => $marker_url));
        } else {
            wp_send_json_error('Error al actualizar el marker en la base de datos.');
        }
    }

    public function ajax_actualizar_fondo()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        $sede_id = intval($_POST['sede_id']);

        if (!isset($_FILES['fondo'])) {
            wp_send_json_error('No se ha subido ningún archivo.');
        }

        $upload = wp_handle_upload($_FILES['fondo'], array('test_form' => false));

        if (isset($upload['error'])) {
            wp_send_json_error('Error al subir el archivo: ' . $upload['error']);
        }

        $fondo_url = $upload['url'];

        $tabla = $this->db->prefix . 'navi_sedes';
        $resultado = $this->db->update(
            $tabla,
            array('fondo' => $fondo_url),
            array('id' => $sede_id),
            array('%s'),
            array('%d')
        );

        if ($resultado) {
            wp_send_json_success(array('message' => 'Fondo actualizado con éxito.', 'fondo_url' => $fondo_url));
        } else {
            wp_send_json_error('Error al actualizar el Fondo en la base de datos.');
        }
    }

    public function ajax_actualizar_fondo2()
    {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        $sede_id = intval($_POST['sede_id']);

        if (!isset($_FILES['fondo2'])) {
            wp_send_json_error('No se ha subido ningún archivo.');
        }

        $upload = wp_handle_upload($_FILES['fondo2'], array('test_form' => false));

        if (isset($upload['error'])) {
            wp_send_json_error('Error al subir el archivo: ' . $upload['error']);
        }

        $fondo2_url = $upload['url'];

        $tabla = $this->db->prefix . 'navi_sedes';
        $resultado = $this->db->update(
            $tabla,
            array('fondo2' => $fondo2_url),
            array('id' => $sede_id),
            array('%s'),
            array('%d')
        );

        if ($resultado) {
            wp_send_json_success(array('message' => 'Fondo 2 actualizado con éxito.', 'fondo2_url' => $fondo2_url));
        } else {
            wp_send_json_error('Error al actualizar el Fondo 2 en la base de datos.');
        }
    }
}