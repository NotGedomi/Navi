<?php
class Navi_Database
{
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function crear_tablas()
    {
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql_plantillas = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}navi_plantillas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nombre varchar(100) NOT NULL,
        datos longtext NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

        $sql_sedes = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}navi_sedes (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        plantilla_id mediumint(9) NOT NULL,
        nombre varchar(100) NOT NULL,
        coordenada varchar(50) NOT NULL,
        logo varchar(255) DEFAULT '',
        marker varchar(255) DEFAULT '',
        fondo varchar(255) DEFAULT '',
        fondo2 varchar(255) DEFAULT '',
        pais varchar(100) NOT NULL,
        nivel1 varchar(100) NOT NULL,
        nivel1_dato varchar(100) NOT NULL,
        nivel2 varchar(100) DEFAULT '',
        nivel2_dato varchar(100) DEFAULT '',
        nivel3 varchar(100) DEFAULT '',
        nivel3_dato varchar(100) DEFAULT '',
        correo varchar(100) NOT NULL,
        telefono varchar(20) NOT NULL,
        direccion text NOT NULL,
        horario text NOT NULL,
        pagina_web varchar(255) DEFAULT '',
        PRIMARY KEY (id),
        FOREIGN KEY (plantilla_id) REFERENCES {$this->wpdb->prefix}navi_plantillas(id) ON DELETE CASCADE
    ) $charset_collate;";

        $sql_config = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}navi_config (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        plantilla_id mediumint(9) NOT NULL,
        campos_mostrar text NOT NULL,
        mostrar_mapa tinyint(1) NOT NULL DEFAULT 0,
        mostrar_formulario tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        FOREIGN KEY (plantilla_id) REFERENCES {$this->wpdb->prefix}navi_plantillas(id) ON DELETE CASCADE
    ) $charset_collate;";

        $sql_redirecciones = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}navi_redirecciones (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        plantilla_id mediumint(9) NOT NULL,
        pais varchar(100) NOT NULL,
        url_redireccion varchar(255) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (plantilla_id) REFERENCES {$this->wpdb->prefix}navi_plantillas(id) ON DELETE CASCADE
    ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_plantillas);
        dbDelta($sql_sedes);
        dbDelta($sql_config);
        dbDelta($sql_redirecciones);

        error_log('Tablas de Navi creadas o actualizadas');
    }

    public function actualizar_plantillas_existentes()
    {
        $tabla_plantillas = $this->wpdb->prefix . 'navi_plantillas';
        $tabla_sedes = $this->wpdb->prefix . 'navi_sedes';
        $plantillas = $this->wpdb->get_results("SELECT id, datos FROM $tabla_plantillas", ARRAY_A);

        foreach ($plantillas as $plantilla) {
            $datos = json_decode($plantilla['datos'], true);
            if (!is_array($datos))
                continue;

            // Eliminar sedes existentes para esta plantilla
            $this->wpdb->delete($tabla_sedes, array('plantilla_id' => $plantilla['id']));

            // Insertar nuevas sedes
            foreach ($datos as $sede) {
                $this->wpdb->insert(
                    $tabla_sedes,
                    array(
                        'plantilla_id' => $plantilla['id'],
                        'nombre' => $sede['Nombre'] ?? '',
                        'coordenada' => $sede['Coordenada'] ?? '',
                        'logo' => $sede['Logo'] ?? '',
                        'marker' => $sede['Marker'] ?? '',
                        'fondo' => $sede['Fondo'] ?? '',
                        'fondo2' => $sede['Fondo2'] ?? '',
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
                        'horario' => $sede['Horario'] ?? '',
                        'pagina_web' => $sede['Página web'] ?? ''
                    ),
                    array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                );
            }
        }
    }

    public function eliminar_tablas()
    {
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->wpdb->prefix}navi_sedes");
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->wpdb->prefix}navi_config");
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->wpdb->prefix}navi_plantillas");
        error_log('Tablas de Navi eliminadas');
    }

    public function limpiar_tablas()
    {
        $this->wpdb->query("TRUNCATE TABLE {$this->wpdb->prefix}navi_sedes");
        $this->wpdb->query("TRUNCATE TABLE {$this->wpdb->prefix}navi_config");
        $this->wpdb->query("TRUNCATE TABLE {$this->wpdb->prefix}navi_plantillas");
        error_log('Tablas de Navi limpiadas');
    }

    public function tabla_existe($nombre_tabla)
    {
        $tabla = $this->wpdb->prefix . $nombre_tabla;
        return $this->wpdb->get_var("SHOW TABLES LIKE '$tabla'") === $tabla;
    }

    public function actualizar_estructura_tablas()
    {
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql_plantillas = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}navi_plantillas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nombre varchar(100) NOT NULL,
        datos longtext NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

        $sql_sedes = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}navi_sedes (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        plantilla_id mediumint(9) NOT NULL,
        nombre varchar(100) NOT NULL,
        coordenada varchar(50) NOT NULL,
        logo varchar(255) DEFAULT '',
        marker varchar(255) DEFAULT '',
        fondo varchar(255) DEFAULT '',
        fondo2 varchar(255) DEFAULT '',
        pais varchar(100) NOT NULL,
        nivel1 varchar(100) NOT NULL,
        nivel1_dato varchar(100) NOT NULL,
        nivel2 varchar(100) DEFAULT '',
        nivel2_dato varchar(100) DEFAULT '',
        nivel3 varchar(100) DEFAULT '',
        nivel3_dato varchar(100) DEFAULT '',
        correo varchar(100) NOT NULL,
        telefono varchar(20) NOT NULL,
        direccion text NOT NULL,
        horario text NOT NULL,
        pagina_web varchar(255) DEFAULT '',
        PRIMARY KEY (id),
        FOREIGN KEY (plantilla_id) REFERENCES {$this->wpdb->prefix}navi_plantillas(id) ON DELETE CASCADE
    ) $charset_collate;";

        $sql_config = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}navi_config (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        plantilla_id mediumint(9) NOT NULL,
        campos_mostrar text NOT NULL,
        mostrar_mapa tinyint(1) NOT NULL DEFAULT 0,
        mostrar_formulario tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        FOREIGN KEY (plantilla_id) REFERENCES {$this->wpdb->prefix}navi_plantillas(id) ON DELETE CASCADE
    ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_plantillas);
        dbDelta($sql_sedes);
        dbDelta($sql_config);

        error_log('Tablas de Navi actualizadas');
    }

    public function migrar_datos_antiguos()
    {
        $tabla_plantillas = $this->wpdb->prefix . 'navi_plantillas';
        $tabla_sedes = $this->wpdb->prefix . 'navi_sedes';

        // Verificar si existen columnas antiguas en la tabla de plantillas
        $columnas_plantillas = $this->wpdb->get_col("DESC {$tabla_plantillas}", 0);
        if (in_array('nivel1', $columnas_plantillas)) {
            // Migrar datos de las columnas antiguas a la nueva estructura
            $plantillas = $this->wpdb->get_results("SELECT * FROM $tabla_plantillas", ARRAY_A);
            foreach ($plantillas as $plantilla) {
                $datos_antiguos = json_decode($plantilla['datos'], true);
                $datos_nuevos = array();
                foreach ($datos_antiguos as $dato) {
                    $datos_nuevos[] = array(
                        'Nombre' => $dato['nombre'] ?? '',
                        'Coordenada' => $dato['coordenada'] ?? '',
                        'Logo' => $dato['logo'] ?? '',
                        'Marker' => $dato['marker'] ?? '',
                        'Fondo' => $dato['fondo'] ?? '',
                        'Fondo2' => $dato['fondo2'] ?? '',
                        'País' => $dato['prefijo_pais'] ?? '',
                        'Nivel 1' => $plantilla['nivel1'],
                        'Nivel 2' => $plantilla['nivel2'],
                        'Nivel 3' => $plantilla['nivel3'],
                        'Correo' => $dato['correo'] ?? '',
                        'Teléfono' => $dato['telefono'] ?? '',
                        'Dirección' => $dato['direccion'] ?? '',
                        'Horario' => $dato['horario'] ?? '',
                        'Página web' => $dato['pagina_web'] ?? ''
                    );
                }
                $this->wpdb->update(
                    $tabla_plantillas,
                    array('datos' => json_encode($datos_nuevos)),
                    array('id' => $plantilla['id']),
                    array('%s'),
                    array('%d')
                );
            }

            // Eliminar columnas antiguas
            $this->wpdb->query("ALTER TABLE $tabla_plantillas DROP COLUMN nivel1, DROP COLUMN nivel2, DROP COLUMN nivel3");
        }

        // Actualizar la estructura de la tabla de sedes
        $this->crear_tablas();

        error_log('Migración de datos antiguos completada');
    }
}