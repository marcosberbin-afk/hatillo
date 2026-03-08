<?php
class Logs
{
	/*--- Tipos de Usuario ---*/
	function login($sql, $login, $valido, $accion)
	{
        $ip = $_SERVER['REMOTE_ADDR'];
        
		$result=array();
		$query="INSERT INTO usuarios_logs(usuario, ip, valido, accion) 
                VALUES ('$login', '$ip', '$valido', '$accion')";
        
        $sql->ExecQuery($query);
		
	}
    
	/*
  function ListadoLogin($sql)
	{
       
		$result=array();
        
        $query="SELECT L.fecha, L.login, L.accion, L.valido, L.ip, U.nombres, U.apellidos, U.correo, U.telefono 
                FROM usuarios_logs L
                LEFT JOIN suscriptores U ON U.correo = L.login
                WHERE L.accion='login'
                LIMIT 0 , 100";
    	
		$resul=$sql->ExecQuery($query);
		
        $i=0;
		while($row=$sql->FetchArray($resul))
		{
            $result[$i]['login']=$row['login'];
            $result[$i]['nombre']=$row['nombres'] ." ".$row['apellidos'];
            $result[$i]['correo']=$row['correo'];
            $result[$i]['telefono']=$row['telefono'];
            $result[$i]['fecha']=$row['fecha'];
            $result[$i]['ip']=$row['ip'];
            $result[$i]['valido']=$row['valido'];
            $i++;
		}

		return $result;
	}
	*/
}