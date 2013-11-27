<?php

namespace GiantBomb\Client;

use GiantBomb\Cache;
use Guzzle\Common\Collection;
use Guzzle\Common\Exception\RuntimeException;
use Guzzle\Http\Message\RequestFactory;
use Guzzle\Http\RedirectPlugin;
use Guzzle\Service\Client;
use Guzzle\Service\ClientInterface;
use Guzzle\Service\Description\ServiceDescription;

/**
 * Class GiantBombCacheClient
 *
 * @package GiantBomb\Client
 */
class GiantBombCacheClient extends GiantBombClient
{
	/**
	 * @var Cache\Cache
	 */
	protected $cache;

	public function __construct( $baseUrl = '', $config )
	{
		$this->createCache( $config->get( 'cache' ) );

		parent::__construct( $baseUrl, $config );
	}

	/**
	 * Magic method used to retrieve a command
	 * Overriden to allow the `event_collection` parameter to passed separately
	 * from the normal argument array.
	 *
	 * @param string $method Name of the command object to instantiate
	 * @param array  $args   Arguments to pass to the command
	 *
	 * @return mixed Returns the result of the command
	 * @throws \BadMethodCallException when a command is not found
	 */
	public function __call( $method, $args = array() )
	{
		if ( isset( $args[ 0 ] ) && is_string( $args[ 0 ] ) ) {
			$args[ 0 ] = array( 'event_collection' => $args[ 0 ] );

			if ( isset( $args[ 1 ] ) && is_array( $args[ 1 ] ) ) {
				$args[ 0 ] = array_merge( $args[ 1 ], $args[ 0 ] );
			}
		}

		$key = sprintf( "giant-bomb-api_%s-%s", $method, serialize( $args ) );

		if( $content = $this->cache->fetch( $key ) ) return $content;

		$content = $this->getCommand( $method, isset( $args[ 0 ] ) ? $args[ 0 ] : array() )->getResult();
		$this->cache->save( $key, $content, $this->cache->getConfig( 'timeout', 0 ) );

		return $content;
	}

	private function createCache( array $config )
	{
		switch( $config[ 'type' ] ) {
			case 'redis':
				if( !class_exists( '\Redis' ) ) {
					throw new \LogicException( "Redis is required." );
				}
				return $this->setCache( new Cache\RedisCache( $config ) );
			case 'memcached':
				if( !class_exists( '\Memcached' ) ){
					throw new \LogicException( "Memcached is required." );
				}
				return $this->setCache( new Cache\MemcachedCache( $config ) );
			default:
				throw new \InvalidArgumentException( sprintf( "%s is not a valid cache type. ", $config[ 'type' ] ) );
		}
	}

	/**
	 * @param \GiantBomb\Cache\Cache $cache
	 *
	 * @return bool
	 */
	public function setCache( Cache\Cache $cache )
	{
		$this->cache = $cache;

		return true;
	}

	/**
	 * @return \GiantBomb\Cache\Cache
	 */
	public function getCache()
	{
		return $this->cache;
	}
}
