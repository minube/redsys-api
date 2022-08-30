<html>
<body>
<?php
	error_reporting( E_ALL );
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	ini_set( 'html_errors', 1 );
	set_time_limit( 0 );

	include './src/ApiRedsys.php';

	// Se crea Objeto
	$miObj = new Redsys\ApiRedsys();

	parse_str( 'Ds_SignatureVersion=HMAC_SHA256_V1&Ds_MerchantParameters=eyJEc19EYXRlIjoiMzAlMkYwOCUyRjIwMjIiLCJEc19Ib3VyIjoiMTIlM0ExOCIsIkRzX1NlY3VyZVBheW1lbnQiOiIxIiwiRHNfQ2FyZF9Db3VudHJ5IjoiNzI0IiwiRHNfQW1vdW50IjoiMTU5MDAiLCJEc19DdXJyZW5jeSI6Ijk3OCIsIkRzX09yZGVyIjoiTU4wMDAwNDkiLCJEc19NZXJjaGFudENvZGUiOiIzNTU4NjgxMDAiLCJEc19UZXJtaW5hbCI6IjAwMSIsIkRzX1Jlc3BvbnNlIjoiMDAwMCIsIkRzX01lcmNoYW50RGF0YSI6IiIsIkRzX1RyYW5zYWN0aW9uVHlwZSI6IjAiLCJEc19Db25zdW1lckxhbmd1YWdlIjoiMSIsIkRzX0F1dGhvcmlzYXRpb25Db2RlIjoiMDAyNDEzIiwiRHNfQ2FyZF9CcmFuZCI6IjEiLCJEc19Qcm9jZXNzZWRQYXlNZXRob2QiOiI1IiwiRHNfQ29udHJvbF8xNjYxODU0NjgzOTg4IjoiMTY2MTg1NDY4Mzk4OCJ9&Ds_Signature=O9aFvML5qK8VBQhV4GK8kFHvmAQaSnX1hfckr7abzIs=', $_POST );
	//echo "<pre>";
	//print_r($_POST);
	//die();


	if ( !empty( $_POST ) ) {//URL DE RESP. ONLINE

		$version = $_POST[ "Ds_SignatureVersion" ];
		$datos = $_POST[ "Ds_MerchantParameters" ];
		$signatureRecibida = $_POST[ "Ds_Signature" ];


		$decodec = $miObj->decodeMerchantParameters( $datos );
		$kc = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7'; //Clave recuperada de CANALES
		$firma = $miObj->createMerchantSignatureNotif( $kc, $datos );

		if ( $firma === $signatureRecibida ) {
			echo "FIRMA OK";
		} else {
			echo "FIRMA KO";
		}
	} else {
		if ( !empty( $_GET ) ) {//URL DE RESP. ONLINE

			$version = $_GET[ "Ds_SignatureVersion" ];
			$datos = $_GET[ "Ds_MerchantParameters" ];
			$signatureRecibida = $_GET[ "Ds_Signature" ];


			$decodec = $miObj->decodeMerchantParameters( $datos );
			$kc = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7'; //Clave recuperada de CANALES
			$firma = $miObj->createMerchantSignatureNotif( $kc, $datos );

			if ( $firma === $signatureRecibida ) {
				echo "FIRMA OK";
			} else {
				echo "FIRMA KO";
			}
		} else {
			die( "No se recibió respuesta" );
		}
	}

?>
</body>
</html>
