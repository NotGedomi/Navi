<?php
class Navi_Config {
    private $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function render_pagina() {
        ?>
        <div class="wrap navi-admin">
            <h1>Configuración de Navi</h1>
            <div class="navi-card">
                <form id="navi-config-form">
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
                    <div id="campos-mostrar-container" class="navi-form-group" style="display:none;">
                        <h3>Campos a mostrar</h3>
                        <div id="campos-mostrar" class="navi-checkbox-group">
                            <!-- Los campos se cargarán dinámicamente aquí -->
                        </div>
                    </div>
                    <div id="mostrar-mapa-container" class="navi-form-group" style="display:none;">
                        <div class="navi-config-item">
                            <label class="navi-switch">
                                <input type="checkbox" id="mostrar_mapa" name="mostrar_mapa" value="1">
                                <span class="navi-slider"></span>
                            </label>
                            <label for="mostrar_mapa">Mostrar mapa</label>
                        </div>
                    </div>
                    <div id="mostrar-formulario-container" class="navi-form-group" style="display:none;">
                        <div class="navi-config-item">
                            <label class="navi-switch">
                                <input type="checkbox" id="mostrar_formulario" name="mostrar_formulario" value="1">
                                <span class="navi-slider"></span>
                            </label>
                            <label for="mostrar_formulario">Mostrar formulario de contacto</label>
                        </div>
                    </div>
                    <button type="submit" class="navi-button navi-button-primary">Guardar Configuración</button>
                </form>
                <div id="navi-config-mensaje"></div>
                <div id="navi-shortcode"></div>
            </div>
            <div class="navi-card">
                <h2>Configuración de Redirecciones</h2>
                <form id="navi-redirecciones-form">
                    <div class="navi-form-group">
                        <label for="plantilla_id_redireccion">Plantilla</label>
                        <select id="plantilla_id_redireccion" name="plantilla_id_redireccion" required>
                            <option value="">Seleccione una plantilla</option>
                            <?php
                            $plantillas = $this->obtener_plantillas();
                            foreach ($plantillas as $plantilla) {
                                echo "<option value='{$plantilla['id']}'>{$plantilla['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div id="redirecciones-container"></div>
                    <button type="submit" class="navi-button navi-button-primary">Guardar Redirecciones</button>
                </form>
                <div id="navi-redirecciones-mensaje"></div>
            </div>
        </div>
        <?php
    }

    public function ajax_obtener_paises_sin_sedes() {
        error_log('Iniciando ajax_obtener_paises_sin_sedes');
        
        if (!check_ajax_referer('navi_ajax_nonce', 'nonce', false)) {
            error_log('Nonce inválido');
            wp_send_json_error('Nonce inválido');
            return;
        }
    
        if (!current_user_can('manage_options')) {
            error_log('Usuario sin permisos');
            wp_send_json_error('No tienes permisos para realizar esta acción.');
            return;
        }
    
        if (!isset($_POST['plantilla_id'])) {
            error_log('Plantilla ID no proporcionado');
            wp_send_json_error('Plantilla ID no proporcionado');
            return;
        }
    
        $plantilla_id = intval($_POST['plantilla_id']);
        error_log('Plantilla ID: ' . $plantilla_id);
    
        $tabla_plantillas = $this->db->prefix . 'navi_plantillas';
        $tabla_sedes = $this->db->prefix . 'navi_sedes';
    
        $plantilla = $this->db->get_row($this->db->prepare(
            "SELECT datos FROM $tabla_plantillas WHERE id = %d",
            $plantilla_id
        ), ARRAY_A);
    
        if (!$plantilla) {
            error_log('Plantilla no encontrada');
            wp_send_json_error('Plantilla no encontrada.');
            return;
        }
    
        $datos_plantilla = json_decode($plantilla['datos'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Error al decodificar datos de la plantilla: ' . json_last_error_msg());
            wp_send_json_error('Error al procesar datos de la plantilla');
            return;
        }
    
        $paises_en_plantilla = array();
        foreach ($datos_plantilla as $fila) {
            if (!empty($fila['País'])) {
                $paises_en_plantilla[] = $fila['País'];
            }
        }
        $paises_en_plantilla = array_unique($paises_en_plantilla);
        error_log('Países en plantilla: ' . print_r($paises_en_plantilla, true));
    
        $paises_con_sedes = $this->db->get_col($this->db->prepare(
            "SELECT DISTINCT pais FROM $tabla_sedes WHERE plantilla_id = %d AND (nombre != '' OR coordenada != '' OR nivel1_dato != '')",
            $plantilla_id
        ));
        error_log('Países con sedes: ' . print_r($paises_con_sedes, true));
    
        $paises_sin_sedes = array_diff($paises_en_plantilla, $paises_con_sedes);
        error_log('Países sin sedes: ' . print_r($paises_sin_sedes, true));
    
        $tabla_redirecciones = $this->db->prefix . 'navi_redirecciones';
        $redirecciones = $this->db->get_results($this->db->prepare(
            "SELECT pais, url_redireccion FROM $tabla_redirecciones WHERE plantilla_id = %d",
            $plantilla_id
        ), ARRAY_A);
    
        $redirecciones_por_pais = array();
        foreach ($redirecciones as $redireccion) {
            $redirecciones_por_pais[$redireccion['pais']] = $redireccion['url_redireccion'];
        }
        error_log('Redirecciones: ' . print_r($redirecciones_por_pais, true));
    
        wp_send_json_success(array(
            'paises' => array_values($paises_sin_sedes),
            'redirecciones' => $redirecciones_por_pais
        ));
    }

    public function ajax_guardar_redirecciones() {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        $plantilla_id = intval($_POST['plantilla_id']);
        $redirecciones = json_decode(stripslashes($_POST['redirecciones']), true);

        $tabla_redirecciones = $this->db->prefix . 'navi_redirecciones';

        // Eliminar redirecciones existentes para esta plantilla
        $this->db->delete($tabla_redirecciones, array('plantilla_id' => $plantilla_id), array('%d'));

        // Insertar nuevas redirecciones
        foreach ($redirecciones as $pais => $url) {
            $this->db->insert(
                $tabla_redirecciones,
                array(
                    'plantilla_id' => $plantilla_id,
                    'pais' => $pais,
                    'url_redireccion' => $url
                ),
                array('%d', '%s', '%s')
            );
        }

        wp_send_json_success('Redirecciones guardadas con éxito.');
    }
    
    private function obtener_plantillas() {
        $tabla = $this->db->prefix . 'navi_plantillas';
        return $this->db->get_results("SELECT id, nombre FROM $tabla", ARRAY_A);
    }

    public function ajax_guardar_config() {
        check_ajax_referer('navi_ajax_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }
    
        $plantilla_id = intval($_POST['plantilla_id']);
        $campos_mostrar = json_decode(stripslashes($_POST['campos_mostrar']), true);
        $mostrar_mapa = intval($_POST['mostrar_mapa']);
        $mostrar_formulario = intval($_POST['mostrar_formulario']);
    
        if (!is_array($campos_mostrar)) {
            wp_send_json_error('Los campos a mostrar no son válidos.');
        }
    
        $campos_mostrar_procesados = array();
        foreach ($campos_mostrar as $campo) {
            $campos_mostrar_procesados[] = sanitize_text_field($campo);
            if (strpos($campo, 'nivel') === 0) {
                $campos_mostrar_procesados[] = $campo . '_dato';
            }
        }
    
        $tabla = $this->db->prefix . 'navi_config';
        $datos = array(
            'plantilla_id' => $plantilla_id,
            'campos_mostrar' => json_encode($campos_mostrar_procesados),
            'mostrar_mapa' => $mostrar_mapa,
            'mostrar_formulario' => $mostrar_formulario
        );
    
        $formato = array('%d', '%s', '%d', '%d');
    
        $config_existente = $this->db->get_row($this->db->prepare(
            "SELECT id FROM $tabla WHERE plantilla_id = %d",
            $plantilla_id
        ));
    
        if ($config_existente) {
            $resultado = $this->db->update($tabla, $datos, array('plantilla_id' => $plantilla_id), $formato);
        } else {
            $resultado = $this->db->insert($tabla, $datos, $formato);
        }
    
        if ($resultado !== false) {
            $shortcode = '[navi_filtro_sedes plantilla_id="' . $plantilla_id . '"]';
            wp_send_json_success(array(
                'mensaje' => 'Configuración guardada con éxito.',
                'shortcode' => $shortcode
            ));
        } else {
            wp_send_json_error('Error al guardar la configuración.');
        }
    }

    private function obtener_campos_disponibles($plantilla_datos) {
        $campos_disponibles = array(
            'nombre' => 'Nombre',
            'coordenada' => 'Coordenada',
            'logo' => 'Logo',
            'marker' => 'Marker',
            'fondo' => 'Fondo',
            'fondo2' => 'Fondo2',
            'pais' => 'País',
            'correo' => 'Correo',
            'telefono' => 'Teléfono',
            'direccion' => 'Dirección',
            'horario' => 'Horario',
            'pagina_web' => 'Página web'
        );

        // Añadir niveles genéricos
        for ($i = 1; $i <= 3; $i++) {
            $nivel_key = "Nivel {$i}";
            if (isset($plantilla_datos[0][$nivel_key]) && !empty($plantilla_datos[0][$nivel_key])) {
                $campos_disponibles["nivel{$i}"] = "Nivel {$i} y datos";
            }
        }

        return $campos_disponibles;
    }

    public function ajax_obtener_config() {
        check_ajax_referer('navi_ajax_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }
    
        $plantilla_id = intval($_POST['plantilla_id']);
    
        $tabla_config = $this->db->prefix . 'navi_config';
        $tabla_plantillas = $this->db->prefix . 'navi_plantillas';
    
        $config = $this->db->get_row($this->db->prepare(
            "SELECT c.*, p.datos
             FROM $tabla_config c
             JOIN $tabla_plantillas p ON c.plantilla_id = p.id
             WHERE c.plantilla_id = %d",
            $plantilla_id
        ), ARRAY_A);
    
        if ($config) {
            $config['campos_mostrar'] = json_decode($config['campos_mostrar'], true);
            $plantilla_datos = json_decode($config['datos'], true);
            $config['campos_disponibles'] = $this->obtener_campos_disponibles($plantilla_datos);
        } else {
            // Si no existe configuración, crear una por defecto
            $plantilla = $this->db->get_row($this->db->prepare(
                "SELECT datos FROM $tabla_plantillas WHERE id = %d",
                $plantilla_id
            ), ARRAY_A);
    
            $plantilla_datos = json_decode($plantilla['datos'], true);
            $campos_disponibles = $this->obtener_campos_disponibles($plantilla_datos);
    
            $config = array(
                'plantilla_id' => $plantilla_id,
                'campos_mostrar' => array_keys($campos_disponibles),
                'mostrar_mapa' => 1,
                'mostrar_formulario' => 0,
                'campos_disponibles' => $campos_disponibles
            );
        }
    
        wp_send_json_success($config);
    }
}