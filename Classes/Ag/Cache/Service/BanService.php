<?php

namespace Ag\Cache\Service;


use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class BanService {

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $systemLogger;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param string $url
	 * @param bool $recursive
	 */
	public function ban($url, $recursive = FALSE) {
		if (array_key_exists('allowBan', $this->settings) && $this->settings['allowBan'] === TRUE) {
			$this->systemLogger->log('Ban ' . $url);
			$curlHandle = curl_init();

			curl_setopt($curlHandle, CURLOPT_URL, $url);
			curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PURGE');
			curl_setopt($curlHandle, CURLOPT_HEADER, 0);
			curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
			curl_exec($curlHandle);

			curl_close($curlHandle);
		}
	}
}
