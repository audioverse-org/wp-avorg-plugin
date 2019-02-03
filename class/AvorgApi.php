<?php

namespace Avorg;

if (!\defined('ABSPATH')) exit;

class AvorgApi
{
	private $apiBaseUrl = "https://api2.audioverse.org";
	private $apiUser;
	private $apiPass;
	private $context;
	
	public function __construct()
	{
		$this->apiUser = \get_option("avorgApiUser");
		$this->apiPass = \get_option("avorgApiPass");
	}

	public function getPlaylist($id)
	{
		if (!is_numeric($id)) return false;
		$url = "$this->apiBaseUrl/playlist/$id";

		try {
			$response = $this->getResponse($url);
			$responseObject = json_decode($response);

			return $responseObject->result;
		} catch (\Exception $e) {
			return false;
		}
	}
	
	/**
	 * @param $id
	 * @return bool
	 * @throws \Exception
	 */
	public function getPresentation($id)
	{
		if (!is_numeric($id)) return false;
		
		$url = "$this->apiBaseUrl/recordings/{$id}";
		
		try {
			$response = $this->getResponse($url);
			
			return json_decode($response)->result[0];
		} catch (\Exception $e) {
			throw new \Exception("Couldn't retrieve presentation with ID $id");
		}
	}
	
	/**
	 * @param string $list
	 * @return null
	 * @throws \Exception
	 */
	public function getPresentations($list = "")
	{
		$url = "$this->apiBaseUrl/recordings/$list";
		$trimmedUrl = trim($url, "/");

		return $this->getPresentationsResponse($trimmedUrl);
	}

	/**
	 * @param $topicId
	 * @return null
	 * @throws \Exception
	 */
	public function getTopicPresentations($topicId)
	{
		$url = "$this->apiBaseUrl/recordings/topic/$topicId";

		return $this->getPresentationsResponse($url);
	}

	/**
	 * @param $apiUrl
	 * @return null
	 * @throws \Exception
	 */
	private function getPresentationsResponse($apiUrl)
	{
		try {
			$response = $this->getResponse($apiUrl);
			$responseObject = json_decode($response);

			return (isset($responseObject->result)) ? $responseObject->result : null;
		} catch (\Exception $e) {
			throw new \Exception("Couldn't retrieve list of presentations");
		}
	}
	
	/**
	 * @param $url
	 * @return bool|string
	 * @throws \Exception
	 */
	private function getResponse($url)
	{
		if (!$this->context) $this->context = $this->createContext();
		
		if ($result = @file_get_contents($url, false, $this->context)) {
			return $result;
		} else {
			throw new \Exception("Failed to get response from network");
		}
	}
	
	private function createContext()
	{
		$opts = array('http' =>
			array(
				'header' => "Content-Type: text/xml\r\n" .
					"Authorization: Basic " . base64_encode("$this->apiUser:$this->apiPass") . "\r\n"
			)
		);
		
		return stream_context_create($opts);
	}
}