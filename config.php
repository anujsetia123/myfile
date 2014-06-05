<?php
// configuration data
// must use your own id and key with no extra whitespace
$api = "https://api.unleashedsoftware.com/";
/*
$apiId = "ff9c6c89-514b-48e8-8f28-3031aecb225a"; // your id here
$apiKey = "nnbafy7boCfKSeXX7S0/59vbsbl9LKwWO2gvptZu3hYR02oqPH9WRFGtzVzPmEAm8AFjr7TUMQ+YUQbFaBb58Q=="; //your key here */

$apiId = "5e6159cb-ed9d-4aff-9056-089002c6c816"; // your id here
$apiKey = "VMJlTV1jEzoPiKDSC7P6RET7TY4vYlgDDrkqMJgpozxRAJXOnOn9A8i9HRUT4HaBK9gQ0WJ6WH4qvpLeqw=="; //your key here

// Get the request signature:
// Based on your API id and the request portion of the url 
// - $request is only any part of the url after the "?"
// - use $request = "" if there is no request portion 
// - for GET $request will only be the filters eg ?customerName=Bob
// - for POST $request will usually be an empty string
// - $request never includes the "?"
// Using the wrong value for $request will result in an 403 forbidden response from the API
function getSignature($request, $key) {
	return base64_encode(hash_hmac('sha256', $request, $key, true)); 
}
	
// Create the curl object and set the required options
// - $api will always be https://api.unleashedsoftware.com/
// - $endpoint must be correctly specified
// - $requestUrl does include the "?" if any
// Using the wrong values for $endpoint or $requestUrl will result in a failed API call
function getCurl($id, $key, $signature, $endpoint, $requestUrl, $format) {
	global $api;
		
	$curl = curl_init($api . $endpoint . $requestUrl); 
    curl_setopt($curl, CURLOPT_FRESH_CONNECT, true); 
    curl_setopt($curl, CURLINFO_HEADER_OUT, true); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/$format", 
                "Accept: application/$format", "api-auth-id: $id", "api-auth-signature: $signature")); 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
	// these options allow us to read the error message sent by the API
	curl_setopt($curl, CURLOPT_FAILONERROR, false);
	curl_setopt($curl, CURLOPT_HTTP200ALIASES, range(400, 599));
		
	return $curl;
}
	
// GET something from the API
// - $request is only any part of the url after the "?"
// - use $request = "" if there is no request portion 
// - for GET $request will only be the filters eg ?customerName=Bob
// - $request never includes the "?"
// Format agnostic method.  Pass in the required $format of "json" or "xml"
function get($id, $key, $endpoint, $request, $format) {
	$requestUrl = ""; 
	if (!empty($request)) $requestUrl = "?$request"; 
					
	try {
		// calculate API signature
		$signature = getSignature($request, $key);		
		// create the curl object
		$curl = getCurl($id, $key, $signature, $endpoint, $requestUrl, $format);	
		// GET something
		$curl_result = curl_exec($curl); 
		error_log($curl_result); 
		curl_close($curl); 
		return $curl_result;	
	} 
	catch (Exception $e) { 
		error_log('Error: ' + $e); 			
	}
}
	
// POST something to the API
// - $request is only any part of the url after the "?"
// - use $request = "" if there is no request portion 
// - for POST $request will usually be an empty string
// - $request never includes the "?"
// Format agnostic method.  Pass in the required $format of "json" or "xml"
function post($id, $key, $endpoint, $format, $dataId, $data) {
	if (!isset($dataId, $data)) { return null; }
		
	try {
		// calculate API signature
		$signature = getSignature("", $key);
		// create the curl object. 
		// - POST always requires the object's id
		$curl = getCurl($id, $key, $signature, "$endpoint/$dataId", "", $format);
		// set extra curl options required by POST
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			
		// POST something			
		$curl_result = curl_exec($curl); 		
		error_log($curl_result); 
		curl_close($curl); 
		return $curl_result;	
	} 
	catch (Exception $e) { 
		error_log('Error: ' + $e); 			
	}
}
	
// GET in XML format
// - gets the data from the API and converts it to an XML object
function getXml($id, $key, $endpoint, $request) {
	// GET it
	$xml = get($id, $key, $endpoint, $request, "xml");
	// Convert to XML object and return
	return new SimpleXMLElement($xml);
}
	
// POST in XML format
// - the object to POST must be a valid XML object. Not stdClass, not array, not associative.
// - converts the object to string and POSTs it to the API
function postXml($id, $key, $endpoint, $dataId, $data) {
	
	$xml = $data->asXML();

	// must remove the <xml version="1.0"> node if present, the API does not want it
	$pos = strpos($xml, '<?xml version="1.0"?>');
	if ($pos !== false) {
		$xml = str_replace('<?xml version="1.0"?>', '', $xml);
	}
		
	// if the data does not have the correct xml namespace (xmlns) then add it
	$pos1 = strpos($xml, 'xmlns="http://api.unleashedsoftware.com/version/1"');
	if ($pos1 === false) {
		// there should be a better way than this
		// using preg_replace with count = 1 will only replace the first occurance
		$xml = preg_replace('/>/i',' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://api.unleashedsoftware.com/version/1">',$xml,1);
	}
				
	// POST it
	$posted = post($id, $key, $endpoint, "xml", $dataId, $xml );
	// Convert to XML object and return
	// - the API always returns the POSTed object back as confirmation
	return new SimpleXMLElement($posted);
}
	
// GET in JSON format
// - gets the data from the API and converts it to an stdClass object	
function getJson($id, $key, $endpoint, $request) {
	// GET it, decode it, return it
	return json_decode(get($id, $key, $endpoint, $request, "json"));
}
	
// POST in JSON format
// - the object to POST must be a valid stdClass object. Not array, not associative.
// - converts the object to string and POSTs it to the API
function postJson($id, $key, $endpoint, $dataId, $data) {
	// POST it, return the API's response		
	return post($id, $key, $endpoint, "json", $dataId, json_encode($data));
}
?>
