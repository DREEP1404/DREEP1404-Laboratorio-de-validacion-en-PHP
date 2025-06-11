<?PHp
function verificarLogin($usuario, $contrasena, $conexion) {
    try {
        $sql = "SELECT * FROM usuarios WHERE Usuario = :user";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':user', $usuario);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si existe el usuario
        if (!$user) {
            return ['exito' => false, 'mensaje' => 'Usuario no encontrado.'];
        }

        // Verificar si está bloqueado
        if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
            return ['exito' => false, 'mensaje' => 'Cuenta bloqueada hasta: ' . $user['bloqueado_hasta']];
        }

        // Verificar contraseña
        if (password_verify($contrasena, $user['Contrasena'])) {
            // Login exitoso → Resetear intentos
            $stmt = $conexion->prepare("UPDATE usuarios SET intentos_fallidos = 0, ultimo_intento = NULL, bloqueado_hasta = NULL WHERE id = :id");
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();

            return ['exito' => true, 'mensaje' => 'Login exitoso'];
        } else {
            // Login fallido
            $intentos = $user['intentos_fallidos'];
            $ultimoIntento = $user['ultimo_intento'];

            $ahora = new DateTime();
            $ultimo = $ultimoIntento ? new DateTime($ultimoIntento) : null;

            if ($ultimo && $ahora->getTimestamp() - $ultimo->getTimestamp() > 900) {
                // Más de 15 minutos desde último intento → resetear contador
                $intentos = 1;
            } else {
                $intentos++;
            }

            // Bloquear si se pasa de 3 intentos
            if ($intentos >= 3) {
                $bloqueadoHasta = $ahora->add(new DateInterval('PT15M'))->format('Y-m-d H:i:s');
            } else {
                $bloqueadoHasta = null;
            }

            // Actualizar en la base de datos
            $stmt = $conexion->prepare("
                UPDATE usuarios 
                SET intentos_fallidos = :intentos, ultimo_intento = NOW(), bloqueado_hasta = :bloqueado
                WHERE id = :id
            ");
            $stmt->bindParam(':intentos', $intentos);
            $stmt->bindParam(':bloqueado', $bloqueadoHasta);
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();

            return ['exito' => false, 'mensaje' => 'Contraseña incorrecta. Intentos: ' . $intentos];
        }

    } catch (PDOException $e) {
        return ['exito' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}