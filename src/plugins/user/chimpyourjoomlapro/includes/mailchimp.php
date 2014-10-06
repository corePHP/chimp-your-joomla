<?php
/**
 * Super-simple, minimum abstraction MailChimp API v2 wrapper
 *
 * Uses curl if available, falls back to file_get_contents and HTTP stream.
 * This probably has more comments than code.
 *
 * Contributors:
 * Michael Minor <me@pixelbacon.com>
 * Lorna Jane Mitchell, github.com/lornajane
 *
 * @author Drew McLellan <drew.mclellan@gmail.com>
 * @version 1.1
 */
class MailChimp
{
	private $api_key;
	private $api_endpoint = 'https://<dc>.api.mailchimp.com/2.0';
	private $verify_ssl   = false;

	/**
	 * Create a new instance
	 * @param string $api_key Your MailChimp API key
	 */
	function __construct($api_key)
	{
		$this->api_key = $api_key;
		list(, $datacentre) = explode('-', $this->api_key);
		$this->api_endpoint = str_replace('<dc>', $datacentre, $this->api_endpoint);
	}


	/**
	 * Call an API method. Every request needs the API key, so that is added automatically -- you don't need to pass it in.
	 * @param  string $method The API method to call, e.g. 'lists/list'
	 * @param  array  $args   An array of arguments to pass to the method. Will be json-encoded for you.
	 * @return array          Associative array of json decoded API response.
	 */
	public function call($method, $args=array())
	{
		return $this->_raw_request($method, $args);
	}


	/**
	 * Performs the underlying HTTP request. Not very exciting
	 * @param  string $method The API method to be called
	 * @param  array  $args   Assoc array of parameters to be passed
	 * @return array          Assoc array of decoded result
	 */
	private function _raw_request($method, $args=array())
	{
		$args['apikey'] = $this->api_key;

		$url = $this->api_endpoint.'/'.$method.'.json';

		if (function_exists('curl_init') && function_exists('curl_setopt')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
			$result = curl_exec($ch);
			curl_close($ch);
		}else{
			$json_data = json_encode($args);
			$result = file_get_contents($url, null, stream_context_create(array(
				'http' => array(
					'protocol_version' => 1.1,
					'user_agent'       => 'PHP-MCAPI/2.0',
					'method'           => 'POST',
					'header'           => "Content-type: application/json\r\n".
										  "Connection: close\r\n" .
										  "Content-length: " . strlen($json_data) . "\r\n",
					'content'          => $json_data,
				),
			)));
		}

		return $result ? json_decode($result, true) : false;
	}
}

/********************************/
/* Chimp Your Joomla! Class file */
/********************************/
class mc
{
	/********************************/
	/* Error Checking before script runs */
	/********************************/
	public function error_check( $api, $import = null, $size = null )
	{
		$ping = $api->call( 'helper/ping' );

		// Check to see if the file exist
		if( !file_exists( $import && $import != null ) ) {
			return "File not found. Make sure you specified the correct path.\n";
		}

		// Lets make sure everything is good with mailchimp
		if( empty( $ping ) ) {
			return "Mailchimp is having a problem?.\n";
		}

		// Lets check to make sure it is not empty
		if( !$size && $size != null ) {
			return "File is empty.\n";
		}

		if( !is_file( $import ) ) {
			return 'Not a file';
		}

		if( !is_readable( $import ) ) {
			return 'File Not readable';
		}

	}

	public function add( $api, $list_id, $email, $mergeVars, $chimp_auto )
	{
		return $api->call( 'lists/subscribe',
						array(
								'id' => $list_id,
								'email' => array( 'email' => $email ),
								'merge_vars' => $mergeVars,
								'double_optin' => $chimp_auto
						)
					);
	}

	public function update( $api, $list_id, $email, $mergeVars )
	{
		return $api->call( 'lists/update-member',
						array(
								'id' => $list_id,
								'email' => array( 'email' => $email ),
								'merge_vars' => $mergeVars
						)
					);
	}

	public function unsubscribe( $api, $list_id, $email, $delete_member, $send_goodbye )
	{
		return $api->call( 'lists/unsubscribe',
						array(
								'id' => $list_id,
								'email' => array( 'email' => $email ),
								'delete_member' => $delete_member,
								'send_goodbye' => $send_goodbye
						)
					);
	}

	public function batch_update( $api, $list_id, $batch, $chimp_auto )
	{
		return $api->call( 'lists/batch-subscribe',
						array(
								'id' => $list_id,
								'batch' => $batch,
								'double_optin' => $chimp_auto,
								'update_existing' => true
						)
					);
	}

	public function memberinfo( $api, $list_id, $email )
	{
		return $api->call( 'lists/member-info',
						array(
								'id' => $list_id,
								'emails' => array( array( 'email' => $email ) )
						)
					);
	}
}