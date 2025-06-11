<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="Estilos/Techmania.css">
    <link rel="stylesheet" href="Estilos/general.css">
    <script>
    function capitalizarPrimeraLetra(id) {
        const input = document.getElementById(id);
        let valor = input.value.toLowerCase().trim();
        valor = valor.replace(/\b\w/g, c => c.toUpperCase());
        input.value = valor;
    }

    function validarFormulario(e) {
        e.preventDefault(); // Prevenir envío por defecto
        const errores = [];
        const telefono = document.getElementById('telefono').value.trim();
        const captcha = document.getElementById('captcha').value.trim();
        const regexTelefono = /^\d{3}-\d{3}-\d{4}$/;

        if (!regexTelefono.test(telefono)) {
            errores.push("❌ El número de teléfono debe tener el formato 123-456-7890.");
        }

        if (captcha !== "8") {
            errores.push("❌ CAPTCHA incorrecto. ¿5 + 3?");
        }

        const erroresDiv = document.getElementById('errors');
        erroresDiv.innerHTML = '';

        if (errores.length > 0) {
            errores.forEach(err => {
                const li = document.createElement('li');
                li.textContent = err;
                li.style.color = 'red';
                erroresDiv.appendChild(li);
            });
        } else {
            // Enviar con fetch si no hay errores
            const form = document.getElementById('formRegistro');
            const formData = new FormData(form);
            fetch('procesar_registro.php', {
                method: 'POST',
                body: formData
            })
            .then(resp => resp.redirected ? window.location.href = resp.url : resp.text())
            .then(data => {
                if (typeof data === "string" && data.includes("registro_usuario.php")) {
                    window.location.href = "registro_usuario.php";
                }
            })
            .catch(error => {
                console.error("Error al enviar formulario:", error);
            });
        }
    }
    </script>
</head>
<body>
<div id="wrap">
    <div id="headerlogin"></div>
    <div align="center">
        <h2>Formulario de Registro</h2>

        <?php if (isset($_SESSION['registro_errores']) && count($_SESSION['registro_errores']) > 0): ?>
            <div>
                <ul style="list-style-type: none; padding: 0;">
                <?php foreach ($_SESSION['registro_errores'] as $error): ?>
                    <li style="color: red;"><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="procesar_registro.php" id="formRegistro" onsubmit="validarFormulario(event)">
            <div id="errors"></div>
            <table>
                <tr>
                    <td>Nombre:</td>
                    <td>
                        <input type="text" name="nombre" id="nombre" class="capitalize"
                            value="<?= htmlspecialchars($_SESSION['registro_valores']['nombre'] ?? '') ?>"
                            required onblur="capitalizarPrimeraLetra('nombre')" />
                    </td>
                </tr>
                <tr>
                    <td>Apellido:</td>
                    <td>
                        <input type="text" name="apellido" id="apellido" class="capitalize"
                            value="<?= htmlspecialchars($_SESSION['registro_valores']['apellido'] ?? '') ?>"
                            required onblur="capitalizarPrimeraLetra('apellido')" />
                    </td>
                </tr>
                <tr>
                    <td>Nombre de usuario:</td>
                    <td>
                        <input type="text" name="usuario"
                            value="<?= htmlspecialchars($_SESSION['registro_valores']['usuario'] ?? '') ?>"
                            required />
                    </td>
                </tr>
                <tr>
                    <td>Correo electrónico:</td>
                    <td>
                        <input type="email" name="correo"
                            value="<?= htmlspecialchars($_SESSION['registro_valores']['correo'] ?? '') ?>"
                            required />
                    </td>
                </tr>
                <tr>
                    <td>Teléfono:</td>
                    <td>
                        <input type="text" name="telefono" id="telefono" 
                            value="<?= htmlspecialchars($_SESSION['registro_valores']['telefono'] ?? '') ?>" 
                            placeholder="123-456-7890" required />
                    </td>
                </tr>
                <tr>
                    <td>Contraseña:</td>
                    <td><input type="password" name="password" required /></td>
                </tr>
                <tr>
                    <td>Sexo:</td>
                    <td>
                        <select name="sexo" required>
                            <option value="M" <?= (($_SESSION['registro_valores']['sexo'] ?? '') == 'M') ? 'selected' : '' ?>>Masculino</option>
                            <option value="F" <?= (($_SESSION['registro_valores']['sexo'] ?? '') == 'F') ? 'selected' : '' ?>>Femenino</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>CAPTCHA: ¿5 + 3?</td>
                    <td><input type="text" name="captcha" id="captcha" required /></td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <button type="submit">Registrarse</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php include("comunes/footer.php"); ?>
</div>

<?php
unset($_SESSION['registro_errores'], $_SESSION['registro_valores']);
?>

</body>
</html>
