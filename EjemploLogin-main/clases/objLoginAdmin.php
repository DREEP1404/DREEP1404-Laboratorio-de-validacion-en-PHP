<?PHP
require_once 'logger.php'; // Para registrar errores
final class ValidacionLogin{ 
    
	Private $id;
	Private $usuario;
	Private $contrasena; 
	Private $hastGenerado;
	Private $loginExitoso;
	Private $ip;
	Private $pdo;
	

	Public function __construct($usuario,$contrasena, $ipRemoto, $pdo){ 
	
		//$this->usuario  = trim($usuario); 
		//$nombreLimpio = SanitizarEntrada::limpiarCadena($nombre); 

		$this->usuario  = SanitizarEntrada::limpiarCadena($usuario); 
		$this->contrasena  = SanitizarEntrada::limpiarCadena($contrasena); 
		$this->ip  = $ipRemoto;

		$this->pdo = $pdo;

		
	} //introduceDatos

 	// Simulación de autenticación (puedes reemplazar con base de datos)

	 Private function generarHash(){

			$options = [
				// Increase the bcrypt cost from 12 to 13.
				'cost' => 13,
			];
		
			
			//$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
			$this->hastGenerado =  password_hash($this->contrasena, PASSWORD_BCRYPT, $options);
			
	}// no quisiera que se generará el password en otra parte

	public function logger() {
		try {
			$usuariologueado = $this->pdo->log($this->usuario);

			if ($usuariologueado) {
				$this->id =  $usuariologueado->id;
				$this->hastGenerado =  $usuariologueado->HashMagic;
				logger::info("Usuario encontrado: $this->usuario");
				return true;
			} else {
				logger::warning("Intento de login con usuario inexistente: $this->usuario");
				return false;
			}
		} catch (Exception $e) {
			logger::error("Error al buscar usuario: " . $e->getMessage());
			return false;
		}
	}

	public function autenticar() {
		try {
			if (password_verify($this->contrasena, $this->hastGenerado)) {
				echo 'Password is valid!';
				$this->loginExitoso  = 1;
				logger::info("Login exitoso para usuario: $this->usuario desde IP: $this->ip");
			} else {
				echo 'Invalid password.';
				$this->loginExitoso  = 0;
				logger::warning("Contraseña incorrecta para usuario: $this->usuario desde IP: $this->ip");
			}
		} catch (Exception $e) {
			logger::error("Error en autenticación: " . $e->getMessage());
			$this->loginExitoso = 0;
		}
	}

	public function registrarIntentos() {
		try {
			$data = array(
				"Usuario" => "$this->usuario",
				"ipRemoto" => "$this->ip",
				"deteccion_anomalia" => $this->loginExitoso
			);
			$this->pdo->insertSeguro("intentos_login", $data);
			logger::info("Intento de login registrado para usuario: $this->usuario con resultado: $this->loginExitoso");
		} catch (Exception $e) {
			logger::error("Error al registrar intento de login: " . $e->getMessage());
		}
	}



	public function getIntentoLogin(){
		return $this->loginExitoso;
	}
	

	public function getUsuario(){
		return $this->usuario;

	}
	
	public function getContrasena(){
		return $this->contrasena;
		
	}

	public function getHashGenerado(){
		return $this->hastGenerado;
		
	}
	
	// // Cerrar la conexión
		// $stmt = null;
		// $pdo = null;

} //fin ValidacionLogin

/* foreach($result as $key => $value) {
	$resp[$key]=$value;
	}//fin del foreach
	*/
?>		