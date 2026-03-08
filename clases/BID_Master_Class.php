<?php
class BID_Master_Class
{
	//------------------------------------------------------------Agregar
	function Agregar($sql, $arreglo)
	{
		$all_campos = '';
		$all_datos = '';
		$campos = array_keys($arreglo);

		$i = 0;
		foreach ($campos as $campo) {
			if ($i > 0) {
				$all_campos .= ',';
			}
			$arreglo_campo = str_replace("'", "\'", $campo);

			$all_campos .= '`' . $arreglo_campo . '`';
			$i++;
		}

		$i = 0;
		foreach ($arreglo as $arrg) {
			if ($i > 0) {
				$all_datos .= ',';
			}
			$all_datos .= "'" . $arrg . "'";
			$i++;
		}

		$query = "INSERT INTO $this->_tabla ($all_campos) 
              VALUES ($all_datos)";

		$sql->ExecQuery($query);

		$query = "SELECT LAST_INSERT_ID() as id;";
		$resul = $sql->ExecQuery($query);

		if ($row = $sql->FetchArray($resul)) {
			$res = $row['id'];
		}
		return $res;
	}

	//------------------------------------------------------------Modificar
	function Modificar($sql, $arreglo, $condicion, $valor)
	{
		$all_campos = '';
		$campos = array_keys($arreglo);

		$i = 0;
		foreach ($campos as $campo) {
			if ($i > 0) {
				$all_campos .= ',';
			}

			$arreglo_campo = str_replace("'", "\'", $arreglo[$campo]);

			$all_campos .= '`' . $campo . '`' . "= '" . $arreglo_campo . "' ";
			$i++;
		}

		$query = "UPDATE $this->_tabla 
              SET $all_campos
              WHERE $condicion='$valor'";

		$sql->ExecQuery($query);
	}

	//------------------------------------------------------------ModificarCondicional
	function ModificarCondicional($sql, $arreglo, $condicion)
	{
		$all_campos = '';
		$campos = array_keys($arreglo);

		$i = 0;
		foreach ($campos as $campo) {
			if ($i > 0) {
				$all_campos .= ',';
			}
			
			$arreglo_campo = str_replace("'", "\'", $arreglo[$campo]);

			$all_campos .= '`' . $campo . '`' . "= '" . $arreglo_campo . "' ";
			$i++;
		}

		$query = "UPDATE $this->_tabla 
								SET $all_campos
								WHERE $condicion";

		$sql->ExecQuery($query);
	}

	//------------------------------------------------------------Consultar
	function Consultar($sql, $campo, $valor)
	{
		$result = array();

		$query = "SELECT *
              FROM $this->_tabla
              WHERE $campo='$valor' AND  eliminado = 0";

		$resul = $sql->ExecQuery($query);

		$i = 0;
		while ($row = $sql->FetchArray($resul)) {
			$campos = array_keys($row);
			foreach ($campos as $campo) {
				$result[$i][$campo] = $row[$campo];
			}
			$i++;
		}

		return $result;
	}

	//------------------------------------------------------------ConsultarCondicional
	function ConsultarCondicional($sql, $condicion)
	{
		$result = array();

		$query = "SELECT *
              FROM $this->_tabla
              WHERE $condicion AND  eliminado = 0";

		$resul = $sql->ExecQuery($query);

		$i = 0;
		while ($row = $sql->FetchArray($resul)) {
			$campos = array_keys($row);
			foreach ($campos as $campo) {
				$result[$i][$campo] = $row[$campo];
			}
			$i++;
		}

		return $result;
	}


	//------------------------------------------------------------Listado
	function Listado($sql, $campo = '', $valor = '')
	{
		$result = array();

		$condicion = '';

		if ($campo and $valor) {
			$condicion = $campo . '= "' . $valor . '" AND ';
		}

		$query = "SELECT *
              FROM $this->_tabla
              WHERE $condicion eliminado = 0";

		$resul = $sql->ExecQuery($query);

		$i = 0;
		while ($row = $sql->FetchArray($resul)) {
			$campos = array_keys($row);
			foreach ($campos as $campo) {
				$result[$i][$campo] = $row[$campo];
			}
			$i++;
		}

		return $result;
	}


	//------------------------------------------------------------ListadoCondicional
	function ListadoCondicional($sql, $condicion)
	{
		$result = array();

		$query = "SELECT *
								FROM $this->_tabla
								WHERE $condicion AND eliminado = 0";

		$resul = $sql->ExecQuery($query);

		$i = 0;
		while ($row = $sql->FetchArray($resul)) {
			$campos = array_keys($row);
			foreach ($campos as $campo) {
				$result[$i][$campo] = $row[$campo];
			}
			$i++;
		}

		return $result;
	}


	//------------------------------------------------------------Listado
	function ListadoOrdenado($sql, $campo = '', $tipo_orden = 'ASC')
	{
		$result = array();

		$condicion = '';

		$query = "SELECT *
								FROM $this->_tabla
								WHERE $condicion eliminado = 0
								ORDER BY $campo $tipo_orden";

		$resul = $sql->ExecQuery($query);

		$i = 0;
		while ($row = $sql->FetchArray($resul)) {
			$campos = array_keys($row);
			foreach ($campos as $campo) {
				$result[$i][$campo] = $row[$campo];
			}
			$i++;
		}

		return $result;
	}



	//-----------------------
	function ConsultarCNE($documento_tipo, $documento_numero)
	{
		$url = 'http://www.cne.gob.ve/web/registro_electoral/consultamovil?tipo=RE';
		$url .= '&nacionalidad=' . $documento_tipo . '&cedula=' . $documento_numero;

		//$url = 'http://www.cne.gob.ve/web/registro_electoral/consultamovil?tipo=RE&nacionalidad=V&cedula=5522479;

		// create the context 2 segundos maximo para esta busqueda
		$arContext = array('http' => array());
		$arContext['http']['timeout'] = 2;
		$context = stream_context_create($arContext);
		$json = file_get_contents($url, FALSE, $context);

		//Busco en donde comienza el Json--
		$pos = strpos($json, '{"ci"');
		$json = substr($json, $pos);
		//---------------------------------

		$resultado = json_decode($json, true);

		$result =  NULL;
		$i = 0;
		if ($resultado['ci']) {
			$result[$i]['nacionalidad'] = $documento_tipo;
			$result[$i]['cedula'] = $documento_numero;
			/*
			$result[$i]['nombres'] = $resultado['nb1'] . ' ' .$resultado['nb2'];
			$result[$i]['apellidos'] = $resultado['ap1'] . ' ' . $resultado['ap2'];
			*/
			if ($resultado['nb1'] == '' and $resultado['ap1'] == '') {
				$result[$i]['nombres'] = $resultado['nb'];
			}

			$result[$i]['nombres'] = $resultado['nb'];


			$fn = strtoupper($resultado['fecha_nacimiento']);
			$fechanac = substr($fn, 6, 4) . '-' . substr($fn, 3, 2) . '-' . substr($fn, 0, 2);

			$result[$i]['fecha_nacimiento'] = $fechanac;

			$result[$i]['estado'] = $resultado['stdo'];
			$result[$i]['municipio'] = $resultado['mcp'];
			$result[$i]['parroquia'] = $resultado['par'];

			$result[$i]['direccion'] = strtok($resultado['dir'], '.');
		}

		if ($result) {
			return $result;
		} else return NULL;
	}
}
