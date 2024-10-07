<?php
class Navi_Config {
    private $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function render_pagina() {
        ?>
        <div class="wrap">
            <h1>Configuración de Navi</h1>
            <form id="navi-config-form">
                <table class="form-table">
                    <tr>
                        <th><label for="plantilla_id">Plantilla</label></th>
                        <td>
                            <select id="plantilla_id" name="plantilla_id" required>
                                <option value="">Seleccione una plantilla</option>
                                <?php
                                $plantillas = $this->obtener_plantillas();
                                foreach ($plantillas as $plantilla) {
                                    echo "<option value='{$plantilla['id']}'>{$plantilla['nombre']}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr id="campos-mostrar-container" style="display:none;">
                        <th>Campos a mostrar</th>
                        <td id="campos-mostrar">
                            <!-- Los campos se cargarán dinámicamente aquí -->
                        </td>
                    </tr>
                    <tr id="mostrar-mapa-container" style="display:none;">
                        <th><label for="mostrar_mapa">Mostrar mapa</label></th>
                        <td>
                            <input type="checkbox" id="mostrar_mapa" name="mostrar_mapa" value="1">
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Configuración">
                </p>
            </form>
            <div id="navi-config-mensaje"></div>
            <div id="navi-shortcode"></div>
        </div>
        <?php
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
            'mostrar_mapa' => $mostrar_mapa
        );
    
        $formato = array('%d', '%s', '%d');
    
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
                'campos_disponibles' => $campos_disponibles
            );
        }
    
        wp_send_json_success($config);
    }

    private function obtener_campos_disponibles($plantilla_datos) {
        $campos_disponibles = array(
            'nombre' => 'Nombre',
            'coordenada' => 'Coordenada',
            'logo' => 'Logo',
            'pais' => 'País',
            'correo' => 'Correo',
            'telefono' => 'Teléfono',
            'direccion' => 'Dirección',
            'pagina_web' => 'Página web'
        );

        // Añadir niveles dinámicamente
        for ($i = 1; $i <= 3; $i++) {
            $nivel_key = "Nivel {$i}";
            if (isset($plantilla_datos[0][$nivel_key]) && !empty($plantilla_datos[0][$nivel_key])) {
                $campos_disponibles["nivel{$i}"] = $plantilla_datos[0][$nivel_key];
            }
        }

        return $campos_disponibles;
    }
}