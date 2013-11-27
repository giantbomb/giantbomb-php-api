<?php
/**
 * @author    Aaron Scherer
 * @date      11/26/13
 * @copyright Underground Elephant
 */

namespace GiantBomb\Cache;

use Redis;

/**
 * Redis cache provider.
 */
class RedisCache extends Cache
{
	/**
	 * @var Redis|null
	 */
	private $redis;

	/**
	 * @param array $config
	 */
	public function __construct( array $config )
	{
		// Default Configs
		$config = array_merge( array(
				'servers'    => array( 'host' => 'localhost', 'port' => 6397, 'timeout' => 0 ),
				'timeout'    => 3600,
				'persistent' => false,
				'password'   => null,
				'dbindex'    => null,
			), $config
		);

		$redis = new Redis();
		$connect = 'connect';
		if( $config[ 'persistent' ] ) {
			$config = 'pconnect';
		}
		foreach( $config[ 'servers' ] as $server ) {
			$redis->{$connect}( $server[ 'host' ], $server[ 'port' ], $server[ 'timeout' ] );
		}

		if( null !== $config[ 'password' ] ) {
			$redis->auth( $config[ 'password' ] );
		}

		if( null !== $config[ 'dbindex' ] ) {
			$redis->select( $config[ 'dbindex' ] );
		}

		$redis->setOption( Redis::OPT_SERIALIZER, $this->getSerializerValue() );

		$this->setRedis( $redis );

		parent::__construct( $config );
	}

	/**
	 * Sets the redis instance to use.
	 *
	 * @param Redis $redis
	 *
	 * @return void
	 */
	public function setRedis( Redis $redis )
	{
		$this->redis = $redis;
	}

	/**
	 * Gets the redis instance used by the cache.
	 *
	 * @return Redis|null
	 */
	public function getRedis()
	{
		return $this->redis;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFetch( $id )
	{
		return $this->redis->get( $id );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doContains( $id )
	{
		return $this->redis->exists( $id );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doSave( $id, $data, $lifeTime = 0 )
	{
		if ( $lifeTime > 0 ) {
			return $this->redis->setex( $id, $lifeTime, $data );
		}

		return $this->redis->set( $id, $data );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doDelete( $id )
	{
		return $this->redis->delete( $id ) > 0;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFlush()
	{
		return $this->redis->flushDB();
	}

	/**
	 * Returns the serializer constant to use. If Redis is compiled with
	 * igbinary support, that is used. Otherwise the default PHP serializer is
	 * used.
	 *
	 * @return integer One of the Redis::SERIALIZER_* constants
	 */
	protected function getSerializerValue()
	{
		return defined( 'Redis::SERIALIZER_IGBINARY' ) ? Redis::SERIALIZER_IGBINARY : Redis::SERIALIZER_PHP;
	}
}