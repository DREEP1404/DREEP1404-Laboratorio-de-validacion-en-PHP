<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta name="Description" content="Ejemplo de Login" />
<meta name="Keywords" content="your, keywords" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="Distribution" content="Global" />
<meta name="Author" content="Irina Fong - dreamsweb7@gmail.com" />
<meta name="Robots" content="index,follow" />

<script src="jquery/jquery-latest.js" type="text/javascript"></script> 
<script src="jquery/jquery.validate.js" type="text/javascript"></script>
<link rel="shortcut icon" href="patria/5564844.png">
<link rel="stylesheet" href="css/cmxform.css" type="text/css" />
<link rel="stylesheet" href="Estilos/Techmania.css" type="text/css" />
<link rel="stylesheet" href="Estilos/general.css" type="text/css">
<title>Ejemplo de Prueba del Login</title>

<script type="text/javascript">
$(document).ready(function(){
    $("#deteccionUser").validate({
        rules: {
            usuario: "required",
            contrasena: "required",
        }
    });
});
</script>

</head>
<body>
<!-- wrap starts here -->	
<div id="wrap">
    <div id="headerlogin"></div>

    <div align="center">
        <form class="cmxform" id="deteccionUser" name="deteccionUser" method="post" action="index.php">
            <br />
            <table width="89%" border="0" align="center">
                <tr>
                    <td height="19" colspan="2" align="center">Desarrollo de Software VII | UTP | INICIAR SESION</td>
                </tr>
                <tr>
                    <td width="25%">Usuario:</td>
                    <td width="42%"><input id="usuario" name="usuario" type="text" minlength="4" /></td>
                </tr>
                <tr>
                    <td>Contraseña:</td>
                    <td><input id="contrasena" name="contrasena" type="password" /></td>
                </tr>
                <input type="hidden" name="tolog" id="tolog" value="true"/>
                <tr>
                    <td colspan="2" align="center">
                        <input name="Submit" type="submit" class="clear" value="Buscar" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <a href="registro_usuario.php"><button type="button">Registro</button></a>
                    </td>
                </tr>
            </table>
            <br />
        </form>
    </div>
    
    <?PHP include("comunes/footer.php");?>
    <!-- wrap ends here -->		
</div>	
</body>
</html>
