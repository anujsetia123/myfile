<?php
header('Access-Control-Allow-Origin: *'); 
require_once('xml2array.php');
require_once('config.php');
	
// Example method: GET customer list in xml or json
function getCustomers($format) {
	global $apiId, $apiKey;
		
	if ($format == "xml") 
		return getXml($apiId, $apiKey, "Customers", "");
	else
		return getJson($apiId, $apiKey, "Customers", "");
}
function getOrders($format) {
	global $apiId, $apiKey;
		
	if ($format == "xml") 
		return getXml($apiId, $apiKey, "SalesOrders", "");
	else
		return getJson($apiId, $apiKey, "SalesOrders", "");
}	
function getproducts($format) {
	global $apiId, $apiKey;
		
	if ($format == "xml") 
		return getXml($apiId, $apiKey, "Products", "");
}	
// Example method: GET customer list, filtered by name, in xml or json	
function getCustomersByName($customerName,$format) {
	global $apiId, $apiKey;
	if ($format == "xml") 
		return getXml($apiId, $apiKey, "Customers", "customerName=$customerName");
	else
		return getJson($apiId, $apiKey, "Customers", "customerName=$customerName");		
}
	
	
// Example method: POST a purchase order in xml or json	
function postPurchaseOrder($purchase,$format) {
	global $apiId, $apiKey;
						
	if ($format == "xml") 
		return postXml($apiId, $apiKey, "PurchaseOrders", $purchase->Guid, $purchase);	
	else
		return postJson($apiId, $apiKey, "PurchaseOrders", $purchase->Guid, $purchase);	
}
        
        // Example method: POST a sales invoice in xml or json	
	    function postSalesInvoice($salesInvoice,$format) {
		    global $apiId, $apiKey;
						
		    if ($format == "xml") 
			    return postXml($apiId, $apiKey, "SalesInvoices", $salesInvoice->Guid, $salesInvoice);	
		    else
			    return postJson($apiId, $apiKey, "SalesInvoices", $salesInvoice->Guid, $salesInvoice);	
	    }
	
// Generate a new guid for use as the id when POSTing new items
// - there should be a better / official way to do this in PHP
// - do not use this method on a production system
function NewGuid()
{
	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

	
// Call the GET customers method and print the results
function testGetCustomers() {
	$xml = getCustomers("xml");
	$arraydata = $xml->asXML();
	
	//foreach ($xml->Customer as $customer) {
		//$code = $customer->CustomerCode;
		//$name = $customer->CustomerName;
		//echo "XML Customer: $code, $name<br />";
	//}
	return $arraydata;	
}
// Call the GET orders method and print the results
/*function testGetOrders() {
	$xml = getOrders("xml");
	$arraydata = xml2array($xml->asXML());
	return $arraydata;	
} */
function testGetProducts() {
	$xml = getproducts("xml");
	$arraydata = $xml->asXML();
	return $arraydata;	
}
        
       echo $customerinfo = testGetCustomers();
		//$orderinfo = testGetOrders();
		//echo $orderdata = testGetProducts();
		//pr($orderdata);
		?>
