<?php
class Navi_Plantillas {
    private $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function render_pagina() {
        ?>
        <div class="wrap navi-admin">
            <h1>Gestionar Plantillas</h1>
            <div class="navi-card">
                <h2>Cargar Nueva Plantilla</h2>
                <button id="descargar-plantilla-ejemplo" class="navi-button">Descargar Plantilla de Ejemplo</button>
                <form id="navi-plantilla-form" enctype="multipart/form-data">
                    <div class="navi-form-group">
                        <label for="nombre_plantilla">Nombre de la plantilla</label>
                        <input type="text" id="nombre_plantilla" name="nombre_plantilla" required>
                    </div>
                    <div class="navi-form-group">
                        <label for="plantilla_excel">Archivo Excel</label>
                        <input type="file" id="plantilla_excel" name="plantilla_excel" accept=".xlsx,.xls" required>
                    </div>
                    <button type="submit" class="navi-button navi-button-primary">Cargar Plantilla</button>
                </form>
                <div id="navi-plantilla-mensaje"></div>
            </div>
            
            <div class="navi-card">
                <h2>Plantillas Cargadas</h2>
                <div id="navi-plantilla-lista" class="navi-list">
                    <!-- Los datos se cargarán aquí dinámicamente -->
                </div>
            </div>
        </div>
        <?php
    }

    public function ajax_reemplazar_plantilla() {
        check_ajax_referer('navi_ajax_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }
    
        if (!isset($_POST['plantilla_id']) || !isset($_POST['datos'])) {
            wp_send_json_error('No se han recibido datos suficientes.');
        }
    
        $plantilla_id = intval($_POST['plantilla_id']);
        $datos = json_decode(stripslashes($_POST['datos']), true);
    
        if (empty($datos)) {
            wp_send_json_error('Los datos recibidos no son válidos.');
        }
    
        $this->db->query('START TRANSACTION');
    
        try {
            // Actualizar los datos de la plantilla
            $resultado_plantilla = $this->db->update(
                $this->db->prefix . 'navi_plantillas',
                array('datos' => json_encode($datos)),
                array('id' => $plantilla_id),
                array('%s'),
                array('%d')
            );
    
            if ($resultado_plantilla === false) {
                throw new Exception('No se pudo actualizar la plantilla: ' . $this->db->last_error);
            }
    
            // Eliminar las sedes existentes para esta plantilla
            $this->db->delete($this->db->prefix . 'navi_sedes', array('plantilla_id' => $plantilla_id), array('%d'));
    
            // Insertar las nuevas sedes
            $tabla_sedes = $this->db->prefix . 'navi_sedes';
            foreach ($datos as $sede) {
                if (isset($sede['País']) && !empty($sede['País'])) {
                    $resultado_sede = $this->db->insert(
                        $tabla_sedes,
                        array(
                            'plantilla_id' => $plantilla_id,
                            'pais' => $sede['País'],
                            'nivel1' => $sede['Nivel 1'] ?? '',
                            'nivel1_dato' => $sede['Nivel 1 Dato'] ?? '',
                            'nivel2' => $sede['Nivel 2'] ?? '',
                            'nivel2_dato' => $sede['Nivel 2 Dato'] ?? '',
                            'nivel3' => $sede['Nivel 3'] ?? '',
                            'nivel3_dato' => $sede['Nivel 3 Dato'] ?? '',
                            'nombre' => $sede['Nombre'] ?? '',
                            'direccion' => $sede['Dirección'] ?? '',
                            'coordenada' => $sede['Coordenada'] ?? '',
                            'horario' => $sede['Horario'] ?? '',
                            'pagina_web' => $sede['Página web'] ?? '',
                            'correo' => $sede['Correo'] ?? '',
                            'telefono' => $sede['Teléfono'] ?? '',
                            'logo' => $sede['Logo'] ?? '',
                            'marker' => $sede['Marker'] ?? '',
                            'fondo' => $sede['Fondo'] ?? '',
                            'fondo2' => $sede['Fondo 2'] ?? ''
                        ),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                    );
    
                    if ($resultado_sede === false) {
                        throw new Exception('Error al insertar una sede: ' . $this->db->last_error);
                    }
                }
            }
    
            $this->db->query('COMMIT');
            wp_send_json_success('Plantilla reemplazada con éxito.');
    
        } catch (Exception $e) {
            $this->db->query('ROLLBACK');
            wp_send_json_error($e->getMessage());
        }
    }

    public function ajax_editar_nombre_plantilla() {
        check_ajax_referer('navi_ajax_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción.');
        }
    
        if (!isset($_POST['plantilla_id']) || !isset($_POST['nuevo_nombre'])) {
            wp_send_json_error('No se han recibido datos suficientes.');
        }
    
        $plantilla_id = intval($_POST['plantilla_id']);
        $nuevo_nombre = sanitize_text_field($_POST['nuevo_nombre']);
    
        if (empty($nuevo_nombre)) {
            wp_send_json_error('El nombre de la plantilla no puede estar vacío.');
        }
    
        $resultado = $this->db->update(
            $this->db->prefix . 'navi_plantillas',
            array('nombre' => $nuevo_nombre),
            array('id' => $plantilla_id),
            array('%s'),
            array('%d')
        );
    
        if ($resultado !== false) {
            wp_send_json_success('Nombre de la plantilla actualizado con éxito.');
        } else {
            wp_send_json_error('Error al actualizar el nombre de la plantilla.');
        }
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
                if (isset($sede['País']) && !empty($sede['País'])) {
                    $resultado_sede = $this->db->insert(
                        $tabla_sedes,
                        array(
                            'plantilla_id' => $plantilla_id,
                            'pais' => $sede['País'],
                            'nivel1' => $sede['Nivel 1'] ?? '',
                            'nivel1_dato' => $sede['Nivel 1 Dato'] ?? '',
                            'nivel2' => $sede['Nivel 2'] ?? '',
                            'nivel2_dato' => $sede['Nivel 2 Dato'] ?? '',
                            'nivel3' => $sede['Nivel 3'] ?? '',
                            'nivel3_dato' => $sede['Nivel 3 Dato'] ?? '',
                            'nombre' => $sede['Nombre'] ?? '',
                            'direccion' => $sede['Dirección'] ?? '',
                            'coordenada' => $sede['Coordenada'] ?? '',
                            'horario' => $sede['Horario'] ?? '',
                            'pagina_web' => $sede['Página web'] ?? '',
                            'correo' => $sede['Correo'] ?? '',
                            'telefono' => $sede['Teléfono'] ?? '',
                            'logo' => $sede['Logo'] ?? '',
                            'marker' => $sede['Marker'] ?? '',
                            'fondo' => $sede['Fondo'] ?? '',
                            'fondo2' => $sede['Fondo 2'] ?? ''
                        ),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                    );
    
                    if (!$resultado_sede) {
                        throw new Exception('Error al insertar una sede: ' . $this->db->last_error);
                    }
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
            array('Nombre', 'Coordenada', 'Logo', 'Marker', 'Fondo', 'Fondo2', 'País', 'Nivel 1', 'Nivel 2', 'Nivel 3', 'Correo', 'Teléfono', 'Dirección', 'Horario', 'Página web')
        );

        foreach ($sedes as $sede) {
            $datos_excel[] = array(
                $sede['nombre'],
                $sede['coordenada'],
                $sede['logo'],
                $sede['marker'],
                $sede['fondo'],
                $sede['fondo2'],
                $sede['prefijo_pais'],
                $sede['nivel1_dato'],
                $sede['nivel2_dato'],
                $sede['nivel3_dato'],
                $sede['correo'],
                $sede['telefono'],
                $sede['direccion'],
                $sede['horario'],
                $sede['pagina_web']
            );
        }

        wp_send_json_success(array(
            'nombre_plantilla' => $plantilla['nombre'],
            'datos' => $datos_excel
        ));
    }
}