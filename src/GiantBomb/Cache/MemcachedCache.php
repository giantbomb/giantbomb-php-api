<?php
/**
 * @author    Aaron Scherer
 * @date      11/26/13
 * @copyright Underground Elephant
 */

namespace GiantBomb\Cache;

use Memcached;

/**
 * Memcached cache provider.
 */
class MemcachedCache extends Cache
{
	/**
	 * @var Memcached|null
	 */
	private $memcached;


	/**
	 * @param array $config
	 */
	public function __construct( array $config )
	{
		// Default Configs
		$config = array_merge( array(
				'servers'    => array( 'host' => 'localhost', 'port' => 11211, 'weight' => 100 ),
				'timeout'    => 3600,
				'persistent' => false,
				'options'    => null
			), $config
		);
		
		parent::__construct( $config );
	}
	
	/**
	 * Connects to the cache (If neccessary);
	 */
	public function connect()
	{
		$memcached = new Memcached( $this->config[ 'persistent' ] ? serialize( $this->config[ 'servers' ] ) : null );

		foreach( $this->config[ 'servers' ] as $server ) {
			$memcached->addServer( $server[ 'host' ], $server[ 'port' ], $server[ 'weight' ] );
		}

		if( null !== $this->config[ 'options' ] ) {
			$memcached->setOptions( $this->config[ 'options' ] );
		}

		$this->setMemcached( $memcached );
	}

	/**
	 * Sets the memcache instance to use.
	 *
	 * @param Memcached $memcached
	 *
	 * @return void
	 */
	public function setMemcached( Memcached $memcached )
	{
		$this->memcached = $memcached;
	}

	/**
	 * Gets the memcached instance used by the cache.
	 *
	 * @return Memcached|null
	 */
	public function getMemcached()
	{
		return $this->memcached;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFetch( $id )
	{
		return $this->memcached->get( $id );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doContains( $id )
	{
		return ( false !== $this->memcached->get( $id ) );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doSave( $id, $data, $lifeTime = 0 )
	{
		if ( $lifeTime > 30 * 24 * 3600 ) {
			$lifeTime = time() + $lifeTime;
		}

		return $this->memcached->set( $id, $data, (int)$lifeTime );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doDelete( $id )
	{
		return $this->memcached->delete( $id );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFlush()
	{
		return $this->memcached->flush();
	}

} 
