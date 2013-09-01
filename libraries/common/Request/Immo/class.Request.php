<?php
/**
 * Класс Immo_MobileCommerce_Request_Immo_Request
 *
 */

class Immo_MobileCommerce_Request_Immo_Request
{
	public function getPartnerSslSert()
	{
		return isset($_SERVER['HTTP_SSL_CLIENT_CERT'])?$_SERVER['HTTP_SSL_CLIENT_CERT']:null;
	}
}
