<?php
namespace Ag\Cache\Annotations;

/**
 * Marks an action to send expires cache control headers
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Expire {

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