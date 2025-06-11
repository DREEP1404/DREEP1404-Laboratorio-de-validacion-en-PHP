<?php
session_start();

require 'vendor/autoload.php';
require_once 'clases/SanitizarEntrada.php'; // Para sanear el Qr
require_once 'clases/logger.php'; // 👈 Integrar logger

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

include("clases/mysql.inc.php");
$db = new mod_db();
function registrarTrazabilidadSiEsGET($tabla, $tipo, $id_usuario, $nombre_usuario) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        global $db;
        $db->registrarTrazabilidad($tabla, $tipo, $id_usuario, $nombre_usuario);
    }
}

// Verificar acceso
if (!isset($_SESSION['usuario_temp'])) {
    logger::warning("Intento de acceso no autorizado a la página de autenticación 2FA.");
    header("Location: login.php");
    exit;
}
$usuario_id = $_SESSION['usuario_temp']; // ya definido

// Obtener email y secret del usuario
$usuario_array = $db->select("usuarios", "email, secret_2fa, Usuario", ["id" => $usuario_id]);

logger::info("Usuario temporal con ID $usuario_id accedió a la página de autenticación 2FA.");
if (!empty($usuario_array)) {
    $usuario = (object) $usuario_array[0];
    $correo = $usuario->email;
    $secret = $usuario->secret_2fa;
    $nombreUsuario = $usuario->Usuario; // ← ahora sí está definido
    registrarTrazabilidadSiEsGET('usuarios', 'select', $usuario_id, $usuario->Usuario);
    logger::info("Datos del usuario ID $usuario_id cargados exitosamente para 2FA.");
} else {
    logger::error("No se encontró el usuario con ID $usuario_id en la base de datos.");
    header("Location: login.php");
    exit;
}

// Generar nuevo secreto si no existe
if (empty($usuario->secret_2fa)) {
    $usuario->secret_2fa = $secret;
    $g = new GoogleAuthenticator();
    $secret = $g->generateSecret();
    $db->update("usuarios", "secret_2fa = '$secret'", "id = $usuario_id");
    logger::info("Se generó y guardó un nuevo secreto 2FA para el usuario ID $usuario_id.");
} else {
    $secret = $usuario->secret_2fa;
}

// Crear URL OTP y QR
$nombre_aplicacion = "MiSistema";
$email = $usuario->email;

$otpUrl = "otpauth://totp/"
    . rawurlencode($nombre_aplicacion . ':' . $email)
    . "?secret=$secret&issuer=" . rawurlencode($nombre_aplicacion);
$qr_url_data = SanitizarEntrada::sanitizarQR_URL($otpUrl);
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_url_data);

// Validación del código
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    logger::info("Usuario ID $usuario_id intentó validar código 2FA: $codigo");

    if (!ctype_digit($codigo) || strlen($codigo) !== 6) {
        $mensaje = "<p style='color: red;'>❌ Código inválido</p>";

        logger::warning("Código 2FA con formato inválido ingresado por el usuario ID $usuario_id.");
    } else {
        $g = new GoogleAuthenticator();
        if ($g->checkCode($secret, $codigo)) {
            $_SESSION['autenticado'] = "SI";
            $_SESSION['Usuario'] = $usuario_id;
            unset($_SESSION['usuario_temp']);
            logger::info("✅ Autenticación 2FA exitosa para usuario ID $usuario_id.");
            header("Location: formularios/PanelControl.php");
            exit;
        } else {
            $mensaje = "<p style='color: red;'>❌ Código incorrecto</p>";
            logger::warning("❌ Código 2FA incorrecto para el usuario ID $usuario_id.");
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Verificar 2FA</title>
    <style>
        body {
            background: #0f172a;
            color: #f1f5f9;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #1e293b;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0, 255, 150, 0.3);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h2 {
            color: #38bdf8;
            margin-bottom: 1rem;
        }

        img {
            margin: 1rem 0;
            border: 4px solid #0ea5e9;
            border-radius: 8px;
        }

        label, input {
            display: block;
            width: 100%;
            margin: 0.5rem 0;
        }

        input[type="text"] {
            padding: 0.6rem;
            border-radius: 8px;
            border: none;
            background-color: #334155;
            color: white;
            font-size: 1rem;
        }

        button {
            background-color: #10b981;
            color: #fff;
            border: none;
            padding: 0.7rem 1rem;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 1rem;
            transition: background-color 0.2s ease;
        }

        button:hover {
            background-color: #059669;
        }

        .error {
            color: #f87171;
            margin-top: 1rem;
        }

        .manual-code {
            margin-top: 0.5rem;
            background: #475569;
            padding: 0.4rem;
            border-radius: 6px;
            display: inline-block;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>Verificación de Dos Factores (2FA)</h2>
        <p>Escanea este código QR en Google Authenticator:</p>
        <img src="<?= $qr_url ?>" alt="QR de Autenticación">
        <p>O ingresa manualmente este código:</p>
        <p class="manual-code"><?= $secret ?></p>

        <form method="POST">
            <label for="codigo">Ingresa el código del Authenticator:</label>
            <input type="text" name="codigo" pattern="\d{6}" maxlength="6" required />
            <button type="submit">Verificar</button>
        </form>

        <?= $mensaje ?>
    </div>
</body>

</html>
