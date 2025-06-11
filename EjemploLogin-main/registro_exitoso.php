<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso</title>
    <link rel="stylesheet" href="Estilos/Techmania.css">
    <link rel="stylesheet" href="Estilos/general.css">
</head>
<body>
<div id="wrap">
    <div id="headerlogin"></div>

    <div class="mensaje-exito" align="center">
        <h2>¡Registro Completado!</h2>
        <!-- Puedes agregar un ícono de éxito aquí -->
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#4CAF50" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </svg>
        <p>Tu cuenta ha sido creada exitosamente.</p>
        <a href="login.php" class="boton-inicio">Ir al Inicio de Sesión</a>
    </div>

    <?php include("comunes/footer.php"); ?>
</div>
</body>
</html>