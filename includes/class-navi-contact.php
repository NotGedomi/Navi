<?php
class Navi_Contact {
    private $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function init() {
        add_action('wp_ajax_navi_send_contact', array($this, 'process_contact_form'));
        add_action('wp_ajax_nopriv_navi_send_contact', array($this, 'process_contact_form'));
    }

    public function process_contact_form() {
        check_ajax_referer('navi-contact-form-nonce', 'nonce');

        $nombre = sanitize_text_field($_POST['nombre'] ?? '');
        $correo = sanitize_email($_POST['correo'] ?? '');
        $telefono = sanitize_text_field($_POST['telefono'] ?? '');
        $motivo = sanitize_text_field($_POST['motivo'] ?? '');
        $destinatario = sanitize_email($_POST['destinatario'] ?? '');

        if (empty($nombre) || empty($correo) || empty($telefono) || empty($motivo) || empty($destinatario)) {
            wp_send_json_error(['message' => 'Todos los campos son obligatorios']);
            return;
        }

        $admin_email = get_option('admin_email');
        $sitename = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $subject = "Formulario Ovocitos Merck | Queremos un bebé";

        $message = $this->get_email_html_template($nombre, $correo, $telefono, $motivo);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            "From: $sitename <$admin_email>",
            "Reply-To: $nombre <$correo>"
        );

        $sent = wp_mail($destinatario, $subject, $message, $headers);

        if ($sent) {
            wp_send_json_success(['message' => 'Mensaje enviado con éxito']);
        } else {
            wp_send_json_error(['message' => 'Hubo un error al enviar el mensaje']);
        }
    }

    private function get_email_html_template($nombre, $correo, $telefono, $motivo) {
        $sitename = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $html = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Nuevo mensaje de contacto</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h1 { color: #0073aa; }
                .info { background-color: #f4f4f4; padding: 15px; border-radius: 5px; }
                .info p { margin: 5px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Nuevo mensaje de contacto - ' . $sitename . '</h1>
                <div class="info">
                    <p><strong>Nombre:</strong> ' . esc_html($nombre) . '</p>
                    <p><strong>Correo:</strong> ' . esc_html($correo) . '</p>
                    <p><strong>Teléfono:</strong> ' . esc_html($telefono) . '</p>
                    <p><strong>Motivo de consulta:</strong> ' . esc_html($motivo) . '</p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }
}