<?php
require_once 'mysql.inc.php';
require_once 'vendor/autoload.php';
require_once 'SanitizarEntrada.php';

class Registro {
    private $db;

    public function __construct() {
        $this->db = new mod_db();
    }

    public function registrarUsuario($datos) {
        try {
            $correo = SanitizarEntrada::limpiarEmail($datos['correo']);
            $usuario = SanitizarEntrada::limpiarCadena($datos['usuario']);
            $nombre = SanitizarEntrada::limpiarCadena($datos['nombre']);
            $apellido = SanitizarEntrada::limpiarCadena($datos['apellido']);
            $sexo = in_array($datos['sexo'], ['M', 'F']) ? $datos['sexo'] : 'N';

            $hashPassword = password_hash($datos['password'], PASSWORD_DEFAULT);
            $g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
            $secret2FA = SanitizarEntrada::limpiarCadena($g->generateSecret());

            $datosInsert = [
                'nombre'      => $nombre,
                'apellido'    => $apellido,
                'email'       => $correo,
                'sexo'        => $sexo,
                'Usuario'     => $usuario,
                'HashMagic'   => $hashPassword,
                'secret_2fa'  => $secret2FA
            ];

            $insertado = $this->db->insertSeguro('usuarios', $datosInsert);

            if ($insertado) {
                logger::info("Registro exitoso del usuario: $usuario");

                // Obtener el ID del usuario recién insertado
                $idUsuario = $this->db->insert_id();

                // Registrar trazabilidad con el ID
                $this->db->registrarTrazabilidad('usuarios', 'insert', $idUsuario, $usuario);
            } else {
                logger::warning("Fallo al registrar al usuario: $usuario");
            }

            return $insertado;
        } catch (Exception $e) {
            logger::error("Error durante el registro: " . $e->getMessage());
            return false;   
        }   
    }
}
?>
