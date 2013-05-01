<?php
namespace Ag\Cache\Annotations;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class Cache {

	/**
	 * @var int
	 */
	public $seconds = 0;

	/**
	 * @param array $values
	 */
	public function __construct(array $values) {
		if (isset($values['seconds'])) {
			$seconds = intval($values['seconds']);
			if($seconds>0) {
				$this->seconds = $seconds;
			}
		}
	}
}
?>