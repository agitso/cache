<?php
namespace Ag\Cache\Aspect;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class CacheAspect {

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $systemLogger;

	/**
	 * @var \TYPO3\Flow\Cache\Frontend\StringFrontend
	 * @return void
	 */
	protected $cache;

	/**
	 * @param \TYPO3\Flow\Cache\CacheManager $cacheManager
	 */
	public function injectCache(\TYPO3\Flow\Cache\CacheManager $cacheManager) {
		$this->cache = $cacheManager->getCache('Ag_Cache_String');
	}

	/**
	 * Passes the signal over to the Dispatcher
	 *
	 * @Flow\Around("methodAnnotatedWith(Ag\Cache\Annotations\Cache)")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current join point
	 * @throws \Exception
	 * @return mixed
	 */
	public function cacheMethod(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$start = microtime(TRUE);

		$cache = $this->reflectionService->getMethodAnnotation($joinPoint->getClassName(), $joinPoint->getMethodName(), 'Ag\Cache\Annotations\Cache');

		if ($cache !== NULL && $cache instanceof \Ag\Cache\Annotations\Cache && $cache->seconds > 0) {

			$cacheKey = $joinPoint->getClassName() . $joinPoint->getMethodName();
			foreach ($joinPoint->getMethodArguments() as $arg) {
				$cacheKey .= serialize($arg);
			}

			$cacheKey = hash('sha256', $cacheKey);

			if($this->cache->has($cacheKey)) {
				$this->systemLogger->log('\\'.$joinPoint->getClassName().'->'.$joinPoint->getMethodName() . ' fetched from cache', LOG_DEBUG);
				$result = unserialize($this->cache->get($cacheKey));
			} else {
				$this->systemLogger->log('\\'.$joinPoint->getClassName().'->'.$joinPoint->getMethodName() . ' inserted in cache', LOG_DEBUG);

				$result = $joinPoint->getAdviceChain()->proceed($joinPoint);
				$this->cache->set($cacheKey, serialize($result), array(), $cache->seconds);
			}

			$time = (microtime(true) - $start) * 1000;
			$this->systemLogger->log('\\' . $joinPoint->getClassName().'->'.$joinPoint->getMethodName() . ' took ' . $time .' ms to complete.', LOG_DEBUG);

			return $result;
		}

		throw new \Exception('Cache aspect did not work as expected.');
	}
}

?>