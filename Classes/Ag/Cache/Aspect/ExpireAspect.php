<?php
namespace Ag\Cache\Aspect;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class ExpireAspect {

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $systemLogger;

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

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
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 * @Flow\Inject
	 */
	protected $objectManager;


	/**
	 * Passes the signal over to the Dispatcher
	 *
	 * @Flow\AfterReturning("methodAnnotatedWith(Ag\Cache\Annotations\Expire)")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current join point
	 * @return void
	 */
	public function setExpire(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$expire = $this->reflectionService->getMethodAnnotation($joinPoint->getClassName(), $joinPoint->getMethodName(), 'Ag\Cache\Annotations\Expire');

		if($expire !== NULL && $expire instanceof \Ag\Cache\Annotations\Expire) {
			if(array_key_exists('enableExpire', $this->settings) && $this->settings['enableExpire'] === TRUE) {

				$controller = $this->objectManager->get($joinPoint->getClassName());

				if($controller instanceof \TYPO3\Flow\Mvc\Controller\ActionController) {
					$response = $controller->getControllerContext()->getResponse();

					if($response instanceof \TYPO3\Flow\Http\Response) {
						$response->setMaximumAge($expire->seconds);
						$response->setExpires(new \DateTime('+'.$expire->seconds .' seconds'));
					}
				}
			}
		}
	}
}
?>