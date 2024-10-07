<?php
class Navi_Plantillas {
    private $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function render_pagina() {
        ?>
        <div class="wrap">
            <h1>Gestionar Plantillas</h1>
            <button id="descargar-plantilla-ejemplo" class="button">Descargar Plantilla de Ejemplo</button>
            <form id="navi-plantilla-form" enctype="multipart/form-data">
                <input type="text" name="nombre_plantilla" placeholder="Nombre de la plantilla" required>
                <input type="file" name="plantilla_excel" accept=".xlsx,.xls" required>
                <button type="submit" class="button button-primary">Cargar Plantilla</button>
            </form>
            <div id="navi-plantilla-mensaje"></div>
            <h2>Plantillas Cargadas</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Nombre de la Plantilla</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="navi-plantilla-tabla">
                    <!-- Los datos se cargarán aquí dinámicamente -->
                </tbody>
            </table>
        </div>
        <?php
    }

    public function ajax_cargar_plantilla() {
        check_ajax_referer('navi_ajax_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }
    
        if (!isset($_POST['datos']) || !isset($_POST['nombre_plantilla'])) {
            wp_send_json_error('No se han recibido datos suficientes.');
        }
    
        $nombre_plantilla = sanitize_text_field($_POST['nombre_plantilla']);
        $datos = json_decode(stripslashes($_POST['datos']), true);
    
        if (empty($datos)) {
            wp_send_json_error('Los datos recibidos no son válidos.');
        }
    
        $this->db->query('START TRANSACTION');
    
        try {
            // Insertar la plantilla
            $resultado_plantilla = $this->db->insert(
                $this->db->prefix . 'navi_plantillas',
                array(
                    'nombre' => $nombre_plantilla,
                    'datos' => json_encode($datos)
                ),
                array('%s', '%s')
            );
    
            if (!$resultado_plantilla) {
                throw new Exception('No se pudo insertar la plantilla: ' . $this->db->last_error);
            }
    
            $plantilla_id = $this->db->insert_id;
            $tabla_sedes = $this->db->prefix . 'navi_sedes';
    
            foreach ($datos as $sede) {
                $resultado_sede = $this->db->insert(
                    $tabla_sedes,
                    array(
                        'plantilla_id' => $plantilla_id,
                        'nombre' => $sede['Nombre'],
                        'coordenada' => $sede['Coordenada'] ?? '',
                        'logo' => $sede['Logo'] ?? '',
                        'pais' => $sede['País'] ?? '',
                        'nivel1' => $sede['Nivel 1'] ?? '',
                        'nivel1_dato' => $sede['Nivel 1 Dato'] ?? '',
                        'nivel2' => $sede['Nivel 2'] ?? '',
                        'nivel2_dato' => $sede['Nivel 2 Dato'] ?? '',
                        'nivel3' => $sede['Nivel 3'] ?? '',
                        'nivel3_dato' => $sede['Nivel 3 Dato'] ?? '',
                        'correo' => $sede['Correo'] ?? '',
                        'telefono' => $sede['Teléfono'] ?? '',
                        'direccion' => $sede['Dirección'] ?? '',
                        'pagina_web' => $sede['Página web'] ?? ''
                    ),
                    array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                );
    
                if (!$resultado_sede) {
                    throw new Exception('Error al insertar una sede: ' . $this->db->last_error);
                }
            }
    
            $this->db->query('COMMIT');
            wp_send_json_success('Plantilla "' . $nombre_plantilla . '" y sus sedes cargadas con éxito.');
    
        } catch (Exception $e) {
            $this->db->query('ROLLBACK');
            wp_send_json_error($e->getMessage());
        }
    }
    
    public function ajax_obtener_plantillas() {
        check_ajax_referer('navi_ajax_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }
    
        $tabla = $this->db->prefix . 'navi_plantillas';
        $plantillas = $this->db->get_results("SELECT id, nombre FROM $tabla", ARRAY_A);
    
        wp_send_json_success($plantillas);
    }

    public function ajax_eliminar_plantilla() {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        if (!isset($_POST['id'])) {
            wp_send_json_error('No se ha recibido el ID de la plantilla.');
        }

        $id = intval($_POST['id']);
        $tabla = $this->db->prefix . 'navi_plantillas';

        $resultado = $this->db->delete($tabla, array('id' => $id), array('%d'));

        if ($resultado) {
            wp_send_json_success('Plantilla eliminada con éxito.');
        } else {
            wp_send_json_error('No se pudo eliminar la plantilla.');
        }
    }

    public function ajax_descargar_plantilla() {
        check_ajax_referer('navi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }

        $plantilla_id = intval($_POST['id']);
        $tabla_plantillas = $this->db->prefix . 'navi_plantillas';
        $tabla_sedes = $this->db->prefix . 'navi_sedes';

        $plantilla = $this->db->get_row($this->db->prepare(
            "SELECT * FROM $tabla_plantillas WHERE id = %d",
            $plantilla_id
        ), ARRAY_A);

        if (!$plantilla) {
            wp_send_json_error('Plantilla no encontrada.');
        }

        $sedes = $this->db->get_results($this->db->prepare(
            "SELECT * FROM $tabla_sedes WHERE plantilla_id = %d",
            $plantilla_id
        ), ARRAY_A);

        $datos_excel = array(
            array('Nombre', 'Coordenada', 'Logo', 'País', 'Nivel 1', 'Nivel 2', 'Nivel 3', 'Correo', 'Teléfono', 'Dirección', 'Página web')
        );

        foreach ($sedes as $sede) {
            $datos_excel[] = array(
                $sede['nombre'],
                $sede['coordenada'],
                $sede['logo'],
                $sede['prefijo_pais'],
                $sede['nivel1_dato'],
                $sede['nivel2_dato'],
                $sede['nivel3_dato'],
                $sede['correo'],
                $sede['telefono'],
                $sede['direccion'],
                $sede['pagina_web']
            );
        }

        wp_send_json_success(array(
            'nombre_plantilla' => $plantilla['nombre'],
            'datos' => $datos_excel
        ));
    }
}