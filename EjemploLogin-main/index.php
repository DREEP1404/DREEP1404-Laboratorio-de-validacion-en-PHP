<?php
session_start();  
include("clases/mysql.inc.php");    
include("clases/SanitizarEntrada.php");
include("comunes/loginfunciones.php");
include("clases/objLoginAdmin.php");

$db = new mod_db();

if (isset($_POST["tolog"]) && $_POST["tolog"] === "true") {
    $Usuario = SanitizarEntrada::limpiarCadena($_POST['usuario'] ?? '');
    $ClaveKey = SanitizarEntrada::limpiarCadena($_POST['contrasena'] ?? '');
    $ipRemoto = $_SERVER['REMOTE_ADDR'] ?? 'IP_NO_DETECTADA';

    // Validaciones mínimas
    if (empty($Usuario) || empty($ClaveKey)) {
        $_SESSION["emsg"] = "Usuario o contraseña vacíos.";
        redireccionar("login.php");
        exit;
    }

    $Logearme = new ValidacionLogin($Usuario, $ClaveKey, $ipRemoto, $db);
    $loginExitoso = $Logearme->logger(); // Ejecutar solo UNA VEZ

    // Registrar intento de login
    $db->insertSeguro('intentos_login', [
        'Usuario' => $Usuario,
        'ipRemoto' => $ipRemoto,
        'login_exitoso' => $loginExitoso ? 1 : 0
    ]);

    if ($loginExitoso) {
        // Obtener datos del usuario
        $usuario_data = $db->log($Usuario); // Devuelve un objeto con la info del usuario

        if (!$usuario_data) {
            $_SESSION["emsg"] = "Usuario no encontrado.";
            redireccionar("login.php");
            exit;
        }

        $_SESSION['NombreReal'] = $usuario_data->nombre ?? 'Usuario';
        $_SESSION['Usuario'] = $Usuario;

        // Verificación 2FA
        if (!empty($usuario_data->{'secret_2fa'})) {
            $_SESSION['usuario_temp'] = $usuario_data->id;
            redireccionar("Autenticar.php");
        } else {
            $_SESSION['autenticado'] = "SI";
            redireccionar("formularios/PanelControl.php");
        }

    } else {
        $_SESSION["emsg"] = "Usuario o contraseña incorrectos.";
        redireccionar("login.php");
    }
}
