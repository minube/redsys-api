# redsys-api
Redsys Api redir

```
/*Importar el fichero principal de la librería, tal y como se muestra
a continuación. El comercio debe decidir si la importación desea hacerla con la
función “include” o “required”, según los desarrollos realizados.*/
  include_once 'redsysHMAC256_API_PHP_4.0.2/apiRedsys.php'; 

/*Definir un objeto de la clase principal de la librería, tal y como se
muestra a continuación:*/
  $miObj = new RedsysAPI; 

/*Capturar los parámetros de la notificación on-line:*/
  $version = $_POST["Ds_SignatureVersion"]; 
  $params = $_POST["Ds_MerchantParameters"]; 
  $signatureRecibida = $_POST["Ds_Signature"]; 

/*Decodificar el parámetro Ds_MerchantParameters. Para llevar
a cabo la decodificación de este parámetro, se debe llamar a la
función de la librería “decodeMerchantParameters()”, tal y como
se muestra a continuación:*/
  $decodec = $miObj->decodeMerchantParameters($params); 

/*Una vez se ha realizado la llamada a la función
“decodeMerchantParameters()”, se puede obtener el valor de
cualquier parámetro que sea susceptible de incluirse en la
notificación on-line. Para llevar a cabo la obtención del valor de un
parámetro se debe llamar a la función “getParameter()” de la
librería con el nombre de parámetro, tal y como se muestra a
continuación para obtener el código de respuesta:*/
  $codigoRespuesta = $miObj->getParameter("Ds_Response"); 

/** NOTA IMPORTANTE: Para garantizar la seguridad y el
origen de las notificaciones el comercio debe llevar a cabo
la validación de la firma recibida y de todos los parámetros
que se envían en la notificación.*/

/*Validar el parámetro Ds_Signature. Para llevar a cabo la
validación de este parámetro se debe calcular la firma y
compararla con el parámetro Ds_Signature capturado. Para ello
se debe llamar a la función de la librería
“createMerchantSignatureNotif()” con la clave obtenida del
módulo de administración y el parámetro
Ds_MerchantParameters capturado, tal y como se muestra a
continuación:*/
  $claveModuloAdmin = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7'; 
  $signatureCalculada = $miObj->createMerchantSignatureNotif($claveModuloAdmin, $params); 

/*Una vez hecho esto, ya se puede validar si el valor de la firma
enviada coincide con el valor de la firma calculada, tal y como se
muestra a continuación:*/
  if ($signatureCalculada === $signatureRecibida) { 
  echo "FIRMA OK. Realizar tareas en el servidor";
  } else { 
  echo "FIRMA KO. Error, firma inválida"; 
  }
```
