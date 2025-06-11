<?php
require_once 'logger.php'; // Para registrar errores           
class SanitizarEntrada {

    public static function limpiarCadena($cadena) {
        return trim(strip_tags($cadena));
    }

    public static function limpiarEntero($entero) {
        return filter_var($entero, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function limpiarFloat($float) {
        return filter_var($float, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    // CORREGIDO: no sanitizar con FILTER_SANITIZE_EMAIL para no dañar el formato
    public static function limpiarEmail($email) {
        return trim($email);
    }

    public static function capitalizarNombre($cadena) {
        return ucwords(strtolower(trim($cadena)));
    }

    public static function limpiarArray($array) {
        $resultado = [];
        foreach ($array as $clave => $valor) {
            if (is_array($valor)) {
                $resultado[$clave] = self::limpiarArray($valor);
            } else {
                $resultado[$clave] = self::limpiarCadena($valor);
            }
        }
        return $resultado;
    }

    public static function sanitizarQR_URL($urlQR) {
        $urlLimpia = filter_var(trim($urlQR), FILTER_SANITIZE_URL);
        return filter_var($urlLimpia, FILTER_VALIDATE_URL) ? $urlLimpia : '';
    }

    public static function sanitizarQR_Texto($textoQR): string {

        return htmlspecialchars(trim(strip_tags($textoQR)), ENT_QUOTES, 'UTF-8');
    }


    // Verifica si el correo ya está registrado
    public static function correoDisponible($db, $email) {
        try {
            $result = $db->select('usuarios', 'email', ['email' => $email]);
            if ($result && count($result) > 0) {
                logger::info("El correo ya está registrado: $email");
                return ["❌ El correo ya está registrado."];
            }
        } catch (Exception $e) {
            logger::error("Error al verificar correo: " . $e->getMessage());
            return ["❌ Error interno al verificar correo."];
        }

        return []; // Sin errores
    }

    // Verifica si el nombre de usuario ya está en uso
    public static function usuarioDisponible($db, $usuario) {
        try {
            $result = $db->select("usuarios", "Usuario", ['Usuario' => $usuario]);
            if ($result && count($result) > 0) {
                logger::info("El nombre de usuario ya está en uso: $usuario");
                return ["❌ El nombre de usuario ya está en uso."];
            }
        } catch (Exception $e) {
            logger::error("Error al verificar usuario: " . $e->getMessage());
            return ["❌ Error interno al verificar usuario."];
        }

        return []; // Sin errores
    }


}
?>