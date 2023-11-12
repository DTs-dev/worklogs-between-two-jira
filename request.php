<?php
error_reporting(E_ALL);
global $jiraDomain, $user, $psw;

function jira_req( $reqUri, $reqType = 'GET', $data = NULL ) {
	global $jiraDomain, $user, $psw;

	$objCurl = curl_init();

	curl_setopt_array( $objCurl, [
		CURLOPT_URL => 'https://' . $jiraDomain . $reqUri,
		CURLOPT_USERPWD => $user . ':' . $psw,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_HTTPHEADER => [ 'Content-type: application/json' ],
		CURLOPT_CUSTOMREQUEST => $reqType,
		CURLOPT_SSL_VERIFYPEER => FALSE,
		CURLOPT_SSL_VERIFYHOST => 2
	]);

	if( $reqType == 'POST' ) {
		curl_setopt( $objCurl, CURLOPT_POSTFIELDS, json_encode( $data ) );
	}

	$respJson = curl_exec( $objCurl );

	$resp = json_decode( $respJson, true );

	$code = curl_getinfo( $objCurl, CURLINFO_HTTP_CODE );
	$code = (int)$code;

	if( !in_array( $code, [200, 201, 204] ) ) {
		$resp = curl_error( $objCurl ) ?: ( $resp ?: ( json_decode( json_encode( simplexml_load_string( $respJson, 'SimpleXMLElement', LIBXML_NOCDATA ) ), TRUE ) ?: $respJson ) );
	}

	curl_close( $objCurl );

	return $resp;
}
?>
