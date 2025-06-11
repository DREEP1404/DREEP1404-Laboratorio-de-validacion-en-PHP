<?php
require_once 'logger.php'; // Asegúrate de que el archivo logger.php esté en la misma carpeta o ajusta la ruta según sea necesario
class mod_db
{
	private $conexion; // Conexión a la base de datos
	private $perpage = 5; // Cantidad de registros por página
	private $total;
	private $pagecut_query;
	private $debug = false; // Cambiado a false para mantener la configuración original

	public function __construct()
	{
		
		##### Setting SQL Vars #####
		$sql_host = "localhost";
		$sql_name = "company_info";
		$sql_user = "brochin";	
		$sql_pass = "misterizipunchline";

		$dsn = "mysql:host=$sql_host;dbname=$sql_name;charset=utf8";
		try {
			$this->conexion = new PDO($dsn, $sql_user, $sql_pass);
			$this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if ($this->debug) {
				echo "Conexión exitosa a la base de datos<br>";
			}
		} catch (PDOException $e) {
			echo "Error de conexión: " . $e->getMessage();
			exit;
		}
	}

	public function getConexion (){

		return $this->conexion;
	}

	public function disconnect()
	{
		$this->conexion = null; // Cierra la conexión a la base de datos
	}

	public function insert($tb_name, $cols, $val)
	{
		$cols = $cols ? "($cols)" : "";
		$sql = "INSERT INTO $tb_name $cols VALUES ($val)";
		
		try {
			$this->conexion->exec($sql);
		} catch (PDOException $e) {
			echo "Error al insertar: " . $e->getMessage();
		}
	}

	public function insertSeguro($tb_name, $data)
	{
		$columns = implode(", ", array_keys($data));
		$placeholders = ":" . implode(", :", array_keys($data));
		$sql = "INSERT INTO $tb_name ($columns) VALUES ($placeholders)";

		try {
			$stmt = $this->conexion->prepare($sql);
			foreach ($data as $key => $value) {
				$stmt->bindValue(":$key", $value);
			}
			$stmt->execute();

			logger::info("Se insertó en la tabla '$tb_name': " . json_encode($data)); // 👈 LOG
			return true;
		} catch (PDOException $e) {
			logger::error("Error en insertSeguro ($tb_name): " . $e->getMessage()); // 👈 LOG
			return false;
		}
	}


	public function update($tb_name, $string, $astriction)
	{
		$sql = "UPDATE $tb_name SET $string";
		$result = $this->executeQuery($sql, $astriction);
		if ($result) {
			logger::info("Actualización en '$tb_name' con: SET $string WHERE $astriction");
		}
	}


	public function del($tb_name, $astriction)
	{
		$sql = "DELETE FROM $tb_name";
		if ($astriction) {
			$sql .= " WHERE $astriction";
		}
		$result = $this->executeQuery($sql);
		if ($result) {
			logger::warning("Se eliminó registro de '$tb_name' con condición: $astriction");
		}
	}


	public function query($string)
	{
		return $this->executeQuery($string);
	}


	public function log($Usuario){

	 // Preparar la consulta

		 try {
		 $sql = "SELECT * FROM usuarios WHERE Usuario = :User";
		 $stmt = $this->conexion->prepare($sql);
		 $stmt->bindParam(':User', $Usuario, PDO::PARAM_STR);

		 // Ejecutar la consulta
		 $stmt->execute();

			// Retornar el objeto directamente
            return $stmt->fetchObject();
		
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
            return null;
		}

	} //log(usuario)


	public function nums($string = "", $stmt = null)
	{
		if ($string) {
			$stmt = $this->query($string);
		}
		$this->total = $stmt ? $stmt->rowCount() : 0; // Cuenta el número de filas
		return $this->total;
	}

	public function objects($string = "", $stmt = null)
	{
		if ($string) {
			$stmt = $this->query($string);
		}
		return $stmt ? $stmt->fetch(PDO::FETCH_OBJ) : null; // Retorna un objeto
	}

	public function insert_id()
	{
		return $this->conexion->lastInsertId(); // Retorna el último ID insertado
	}

	public function page_cut($string, $nowpage = 0)
	{
		$start = $nowpage ? ($nowpage - 1) * $this->perpage : 0; // Calcula el inicio de la página
		$this->pagecut_query = "$string LIMIT $start, $this->perpage";
		return $this->pagecut_query;
	}

	public function show_page_cut($string = "", $num = "", $url = "")
	{
		$nowpage = isset($_REQUEST['nowpage']) ? $_REQUEST['nowpage'] : 1; // Obtiene la página actual
		$this->total = $string ? $this->nums($string) : $num; // Total de registros
		$pages = ceil($this->total / $this->perpage); // Calcula el total de páginas
		$pagecut = "";

		for ($i = 1; $i <= $pages; $i++) {
			if ($nowpage == $i) {
				$pagecut .= $i; // Página actual
			} else {
				$pagecut .= "<a href='$url&nowpage=$i'><font color='336600' style='font-size:10pt'>$i</font></a>";
			}
		}

		return $pagecut; // Retorna el paginador
	}

	private function executeQuery($sql, $astriction = "")
	{
		try {
			if ($astriction) {
				$sql .= " WHERE $astriction";
			}
			$stmt = $this->conexion->prepare($sql); // ✅ CORREGIDO
			$stmt->execute();
			if ($this->debug) {
				echo "Query ejecutada: $sql<br>";
			}
			return $stmt;
		} catch (PDOException $e) {
			echo "Error en la consulta: " . $e->getMessage();
			return null;
		}
	}

	public function select($tb_name, $cols = "*", $condicion = []) {
    $query = "SELECT $cols FROM $tb_name";
    $params = [];
		// Si hay condiciones (array asociativo)
		if (!empty($condicion) && is_array($condicion)) {
			$where_clauses = [];
			foreach ($condicion as $col => $val) {
				$param_name = ":param_" . $col;
				$where_clauses[] = "$col = $param_name";
				$params[$param_name] = $val;
			}
			$query .= " WHERE " . implode(" AND ", $where_clauses);
		}

		try {
			$stmt = $this->conexion->prepare($query);
			foreach ($params as $param => $value) {
				$stmt->bindValue($param, $value);
			}
			$stmt->execute();
			logger::info("SELECT en '$tb_name' columnas: $cols con condiciones: " . json_encode($condicion));
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error al hacer SELECT: " . $e->getMessage();
			return false;
		}
	}


	
	
	public function registrarTrazabilidad($tabla, $accion, $codigoRegistro, $usuario) {
    $fechaSistema = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? gethostbyname(gethostname()) ?? 'IP_NO_DETECTADA';

    $traza = [
        'Tabla' => $tabla,
        'Accion' => $accion,
        'CodigoRegistro' => $codigoRegistro,
        'Usuario' => $usuario,
        'FechaSistema' => $fechaSistema,
        'IP' => $ip
    ];

    if (!$this->insertSeguro('trazabilidad_acciones', $traza)) {
        logger::error("❌ Error al registrar trazabilidad de $accion en '$tabla' para el usuario '$usuario'.");
    }
}

}
