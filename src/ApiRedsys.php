<?php
	namespace Redsys;


	class ApiRedsys {

		/******  Array de DatosEntrada ******/
		var $vars_pay = array();

		/**
		 * ApiRedsys constructor.
		 * @param array $vars_pay
		 */
		public function __construct() {
			if ( !defined( 'PHP_VERSION_ID' ) ) {
				$version = explode( '.', PHP_VERSION ); //5.2.7 ->  50207       5.5.28 -> 50528
				define( 'PHP_VERSION_ID', ( $version[ 0 ] * 10000 + $version[ 1 ] * 100 + $version[ 2 ] ) );
			}
		}


		/******  Set parameter ******/
		public function setParameter( $key, $value ) {
			$this->vars_pay[ $key ] = $value;
		}

		/******  Get parameter ******/
		public function getParameter( $key ) {
			return $this->vars_pay[ $key ];
		}


		//////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////
		////////////					FUNCIONES AUXILIARES:							  ////////////
		//////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////

		public function createMerchantSignature( $key ) {
			// Se decodifica la clave Base64
			$key = $this->decodeBase64( $key );
			// Se genera el parámetro Ds_MerchantParameters
			$ent = $this->createMerchantParameters();
			// Se diversifica la clave con el Número de Pedido
			$key = $this->encrypt_3DES( $this->getOrder(), $key );
			// MAC256 del parámetro Ds_MerchantParameters
			$res = $this->mac256( $ent, $key );

			// Se codifican los datos Base64
			return $this->encodeBase64( $res );
		}

		public function decodeBase64( $data ) {
			$data = base64_decode( $data );

			return $data;
		}

		public function createMerchantParameters() {
			// Se transforma el array de datos en un objeto Json
			$json = $this->arrayToJson();

			// Se codifican los datos Base64
			return $this->encodeBase64( $json );
		}

		/******  Convertir Array en Objeto JSON ******/
		public function arrayToJson() {
			if ( PHP_VERSION_ID < 50200 ) {
				$json = $this->json_encode4( $this->vars_pay );
			} else {
				$json = json_encode( $this->vars_pay ); //(PHP 5 >= 5.2.0)
			}

			return $json;
		}

		public function json_encode4( $data ) {
			$json = new Services_JSON();

			return $json->encode( $data );
		}

		public function encodeBase64( $data ) {
			$data = base64_encode( $data );

			return $data;
		}

		/******  3DES Function  ******/
		public function encrypt_3DES( $message, $key ) {
			// Se establece un IV por defecto
			$bytes = array(
				0,
				0,
				0,
				0,
				0,
				0,
				0,
				0
			); //byte [] IV = {0, 0, 0, 0, 0, 0, 0, 0}
			$iv = implode( array_map( "chr", $bytes ) ); //PHP 4 >= 4.0.2

			// Se cifra
			$ciphertext = mcrypt_encrypt( MCRYPT_3DES, $key, $message, MCRYPT_MODE_CBC, $iv ); //PHP 4 >= 4.0.2

			return $ciphertext;
		}

		/******  Obtener Número de pedido ******/
		public function getOrder() {
			$numPedido = "";
			if ( empty( $this->vars_pay[ 'DS_MERCHANT_ORDER' ] ) ) {
				$numPedido = $this->vars_pay[ 'Ds_Merchant_Order' ];
			} else {
				$numPedido = $this->vars_pay[ 'DS_MERCHANT_ORDER' ];
			}

			return $numPedido;
		}


		//////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////
		////////////	   FUNCIONES PARA LA GENERACIÓN DEL FORMULARIO DE PAGO:			  ////////////
		//////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////

		/******  MAC Function ******/
		public function mac256( $ent, $key ) {
			if ( PHP_VERSION_ID < 50102 ) {
				$res = hash_hmac4( 'sha256', $ent, $key, true );
			} else {
				$res = hash_hmac( 'sha256', $ent, $key, true );//(PHP 5 >= 5.1.2)
			}

			return $res;
		}

		public function decodeMerchantParameters( $datos ) {
			// Se decodifican los datos Base64
			$decodec = $this->base64_url_decode( $datos );
			// Los datos decodificados se pasan al array de datos
			$this->stringToArray( $decodec );

			return $decodec;
		}

		public function base64_url_decode( $input ) {
			return base64_decode( strtr( $input, '-_', '+/' ) );
		}

		/******  Convertir String en Array ******/
		public function stringToArray( $datosDecod ) {
			if ( PHP_VERSION_ID < 50200 ) {
				$this->vars_pay = $this->json_decode4( $datosDecod );
			} else {
				$this->vars_pay = json_decode( $datosDecod, true ); //(PHP 5 >= 5.2.0)
			}
		}



		//////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////
		//////////// FUNCIONES PARA LA RECEPCIÓN DE DATOS DE PAGO (Notif, URLOK y URLKO): ////////////
		//////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////

		/******  JSON Encode Functions (PHP4) ******/
		public function json_decode4( $data ) {
			$json = new Services_JSON();

			return $json->decode( $data );
		}

		public function createMerchantSignatureNotif( $key, $datos ) {
			// Se decodifica la clave Base64
			$key = $this->decodeBase64( $key );
			// Se decodifican los datos Base64
			$decodec = $this->base64_url_decode( $datos );
			// Los datos decodificados se pasan al array de datos
			$this->stringToArray( $decodec );
			// Se diversifica la clave con el Número de Pedido
			$key = $this->encrypt_3DES( $this->getOrderNotif(), $key );
			// MAC256 del parámetro Ds_Parameters que envía Redsys
			$res = $this->mac256( $datos, $key );

			// Se codifican los datos Base64
			return $this->base64_url_encode( $res );
		}

		/******  Obtener Número de pedido ******/
		public function getOrderNotif() {
			$numPedido = "";
			if ( empty( $this->vars_pay[ 'Ds_Order' ] ) ) {
				$numPedido = $this->vars_pay[ 'DS_ORDER' ];
			} else {
				$numPedido = $this->vars_pay[ 'Ds_Order' ];
			}

			return $numPedido;
		}

		/******  Base64 Functions  ******/
		public function base64_url_encode( $input ) {
			return strtr( base64_encode( $input ), '+/', '-_' );
		}

		/******  Notificaciones SOAP ENTRADA ******/
		public function createMerchantSignatureNotifSOAPRequest( $key, $datos ) {
			// Se decodifica la clave Base64
			$key = $this->decodeBase64( $key );
			// Se obtienen los datos del Request
			$datos = $this->getRequestNotifSOAP( $datos );
			// Se diversifica la clave con el Número de Pedido
			$key = $this->encrypt_3DES( $this->getOrderNotifSOAP( $datos ), $key );
			// MAC256 del parámetro Ds_Parameters que envía Redsys
			$res = $this->mac256( $datos, $key );

			// Se codifican los datos Base64
			return $this->encodeBase64( $res );
		}

		public function getRequestNotifSOAP( $datos ) {
			$posReqIni = strrpos( $datos, "<Request" );
			$posReqFin = strrpos( $datos, "</Request>" );
			$tamReqFin = strlen( "</Request>" );

			return substr( $datos, $posReqIni, ( $posReqFin + $tamReqFin ) - $posReqIni );
		}

		public function getOrderNotifSOAP( $datos ) {
			$posPedidoIni = strrpos( $datos, "<Ds_Order>" );
			$tamPedidoIni = strlen( "<Ds_Order>" );
			$posPedidoFin = strrpos( $datos, "</Ds_Order>" );

			return substr( $datos, $posPedidoIni + $tamPedidoIni, $posPedidoFin - ( $posPedidoIni + $tamPedidoIni ) );
		}

		/******  Notificaciones SOAP SALIDA ******/
		public function createMerchantSignatureNotifSOAPResponse( $key, $datos, $numPedido ) {
			// Se decodifica la clave Base64
			$key = $this->decodeBase64( $key );
			// Se obtienen los datos del Request
			$datos = $this->getResponseNotifSOAP( $datos );
			// Se diversifica la clave con el Número de Pedido
			$key = $this->encrypt_3DES( $numPedido, $key );
			// MAC256 del parámetro Ds_Parameters que envía Redsys
			$res = $this->mac256( $datos, $key );

			// Se codifican los datos Base64
			return $this->encodeBase64( $res );
		}

		public function getResponseNotifSOAP( $datos ) {
			$posReqIni = strrpos( $datos, "<Response" );
			$posReqFin = strrpos( $datos, "</Response>" );
			$tamReqFin = strlen( "</Response>" );

			return substr( $datos, $posReqIni, ( $posReqFin + $tamReqFin ) - $posReqIni );
		}
	}

?>
