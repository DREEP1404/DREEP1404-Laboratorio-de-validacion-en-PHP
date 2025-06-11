<?php
session_start();

require_once 'clases/registro_db.php';
require_once 'clases/SanitizarEntrada.php';
require_once 'clases/logger.php'; // Aregamos require_once logger 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logger::info("Solicitud POST recibida en registro_usuario.php");

    $datosUsuario = [
        'nombre'    => SanitizarEntrada::capitalizarNombre($_POST['nombre'] ?? ''),
        'apellido'  => SanitizarEntrada::capitalizarNombre($_POST['apellido'] ?? ''),
        'usuario'   => SanitizarEntrada::limpiarCadena($_POST['usuario'] ?? ''),
        'correo'    => SanitizarEntrada::limpiarEmail($_POST['correo'] ?? ''),
        'password'  => SanitizarEntrada::limpiarCadena($_POST['password'] ?? ''),
        'sexo'      => SanitizarEntrada::limpiarCadena($_POST['sexo'] ?? '')
    ];

    $errores = [];

    // Validar campos vacíos
    foreach ($datosUsuario as $campo => $valor) {
        if (empty($valor)) {
            $errores[] = "❌ El campo '$campo' no puede estar vacío.";
        }
    }

    // Validar nombre y apellido
    foreach (['nombre', 'apellido'] as $campo) {
        $valor = $datosUsuario[$campo];
        if (strlen($valor) < 3) {
            $errores[] = "❌ El campo '$campo' debe tener al menos 3 caracteres.";
        } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s'-]+$/", $valor)) {
            $errores[] = "❌ El campo '$campo' solo debe contener letras.";
        }
    }

    // Validar nombre de usuario
    $usuario = $datosUsuario['usuario'];
    if (empty($usuario)) {
        $errores[] = "❌ El campo 'usuario' no puede estar vacío.";
    } elseif (strlen($usuario) < 4 || strlen($usuario) > 20) {
        $errores[] = "❌ El nombre de usuario debe tener entre 4 y 20 caracteres.";
    } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $usuario)) {
        $errores[] = "❌ El nombre de usuario solo puede contener letras, números, guiones, puntos o guiones bajos.";
    } else {
        // Solo si pasa validación de formato
        require_once("clases/mysql.inc.php");
        $db = new mod_db();
        $erroresUsuario = SanitizarEntrada::usuarioDisponible($db, $usuario);
        $errores = array_merge($errores, $erroresUsuario);
    }

    // Validar correo
    $correo = $datosUsuario['correo'];
    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "❌ El formato del correo electrónico es inválido o está vacío.";
    } else {
        $dominiosPermitidos = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'protonmail.com'];
        $partesCorreo = explode('@', $correo);
        $dominioCorreo = strtolower(end($partesCorreo));

        if (!in_array($dominioCorreo, $dominiosPermitidos)) {
            $errores[] = "❌ Solo se permiten correos con dominios confiables como gmail.com, yahoo.com, etc.";
        } else {
            // Solo si pasa validación de formato y dominio
            if (!isset($db)) {
                require_once("clases/mysql.inc.php");
                $db = new mod_db();
            }
            $erroresCorreo = SanitizarEntrada::correoDisponible($db, $correo);
            $errores = array_merge($errores, $erroresCorreo);
        }
    }

    // Validar contraseña
    if (strlen($datosUsuario['password']) < 6) {
        $errores[] = "❌ La contraseña debe tener al menos 6 caracteres.";
    }

    // Validar sexo
    if (!in_array($datosUsuario['sexo'], ['M', 'F'])) {
        $errores[] = "❌ El campo 'sexo' debe ser 'M' o 'F'.";
    }

    // Resultado de validación
    if (!empty($errores)) {
        logger::warning("Errores encontrados durante validación de registro: " . implode(' | ', $errores));
        $_SESSION['registro_errores'] = $errores;
        $_SESSION['registro_valores'] = $datosUsuario;
        header("Location: registro_usuario.php");
        exit;
    } else {
        logger::info("Datos válidos. Procediendo al registro del usuario: " . $datosUsuario['usuario']);
        $registro = new Registro();
        $registroExitoso = $registro->registrarUsuario($datosUsuario);

        if ($registroExitoso) {
            logger::info("Usuario registrado exitosamente: " . $datosUsuario['usuario']);
            header("Location: registro_exitoso.php");
        } else {
            logger::error("Fallo al registrar el usuario: " . $datosUsuario['usuario']);
            $_SESSION['registro_errores'] = ["❌ Error al registrar el usuario. Intenta nuevamente."];
            $_SESSION['registro_valores'] = $datosUsuario;
            header("Location: registro_usuario.php");
        }
        exit;
    }
}
