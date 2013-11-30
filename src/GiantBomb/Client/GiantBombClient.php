<?php

namespace GiantBomb\Client;

use Guzzle\Common\Collection;
use Guzzle\Service\Client;
use Guzzle\Service\ClientInterface;
use Guzzle\Service\Description\ServiceDescription;

use GiantBomb\Cache;

/**
 * Class GiantBombClient
 *
 * @package GiantBomb\Client
 */
class GiantBombClient extends Client implements ClientInterface
{
	/**
	 * @var Cache\Cache
	 */
	private $cache;
	
	/**
	 * Factory to create new GiantBombClient instance.
	 *
	 * @param array $config
	 *
	 * @returns \GiantBomb\Client\GiantBombClient
	 */
	public static function factory( $config = array() )
	{
		$default = array(
			'baseUrl' => "http://www.giantbomb.com/",
			'version' => '1.0',
			'apiKey'  => null,
			'format'  => 'json',
			'limit'   => 100,
			'offset'  => 0,
			'cache'   => null
		);

		// Validate the configuration options
		self::validateConfig( $config );

		// Create client configuration
		$config = Collection::fromConfig( $config, $default );

		// Create the new GiantBomb Client with our Configuration
		$client = new self( $config->get( 'baseUrl' ), $config );
		if( $config->get( 'cache' ) !== null ) {
			$client->createCache( $config->get( 'cache' ) );
		}

		// Set the Service Definition from the versioned file
		$file = 'giant-bomb-' . str_replace( '.', '_', $client->getConfig( 'version' ) ) . '.json';
		$client->setDescription( ServiceDescription::factory( __DIR__ . "/../Resources/config/{$file}" ) );


		$parameters = array();
		foreach( array( 'apiKey', 'format' ) as $key ) {
			$parameters[ $key ] = $config->get( $key );
		}
		$config->set( 'command.params', $parameters );

		$client->setDefaultOption( 'query', array(
				'api_key' => $config->get( 'apiKey' ),
				'format'  => $config->get( 'format' ),
				'limit'   => $config->get( 'limit' ),
				'offset'  => $config->get( 'offset' )
			)
		);

		return $client;
	}

	/**
	 * Magic method used to retrieve a command
	 *
	 * @param string $method Name of the command object to instantiate
	 * @param array  $args   Arguments to pass to the command
	 *
	 * @return mixed Returns the result of the command
	 * @throws \BadMethodCallException when a command is not found
	 */
	public function __call( $method, $args = array() )
	{
		$args = isset( $args[ 0 ] ) ? $args[ 0 ] : array();

		if( $this->cache instanceof \GiantBomb\Cache\CacheInterface ) {
			$key = sprintf( "giant-bomb-api_%s-%s", $method, md5( serialize( $args ) ) );
			printf( "Key! %s\r\n", $key );
			if( $response = $this->cache->fetch( $key ) ) return $response;
		}

		$command  = $this->getCommand( $method, $args );
		$response = $command->execute();
		$response->setArguments( $args );

		if( $this->cache instanceof \GiantBomb\Cache\CacheInterface ) {
			$this->cache->save( $key, $response, $this->cache->getConfig( 'timeout', 0 ) );
		}

		return $response;
	}


	/**
	 * Sets the API Key used by the GiantBomb Client
	 *
	 * @param string $apiKey
	 */
	public function setApiKey( $apiKey )
	{
		self::validateConfig( array( 'apiKey' => $apiKey ) );

		$this->getConfig()->set( 'apiKey', $apiKey );

		// Add API Read Key to `command.params`
		$params             = $this->getConfig( 'command.params' );
		$params[ 'apiKey' ] = $apiKey;
		$this->getConfig()->set( 'command.params', $params );

	}

	/**
	 * Gets the API Write Key being used by the GiantBomb Client
	 *
	 * returns string|null Value of the ApiKey or NULL
	 */
	public function getApiKey()
	{
		return $this->getConfig( 'apiKey' );
	}

	/**
	 * Sets the API Version used by the GiantBomb Client.
	 * Changing the API Version will attempt to load a new Service Definition for that Version.
	 *
	 * @param string $version
	 */
	public function setVersion( $version )
	{
		self::validateConfig( array( 'version' => $version ) );

		$this->getConfig()->set( 'version', $version );

		/* Set the Service Definition from the versioned file */
		$file = 'giant-bomb-' . str_replace( '.', '_', $this->getConfig( 'version' ) ) . '.json';
		$this->setDescription( ServiceDescription::factory( __DIR__ . "/../Resources/config/{$file}" ) );
	}

	/**
	 * Gets the Version being used by the GiantBomb Client
	 *
	 * returns string|null Value of the Version or NULL
	 */
	public function getVersion()
	{
		return $this->getConfig( 'version' );
	}

	/**
	 * Validates the GiantBomb Client configuration options
	 *
	 * @params  array       $config
	 *
	 * @throws  \InvalidArgumentException    When a config value does not meet its validation criteria
	 */
	static function validateConfig( $config = array() )
	{
		foreach ( $config as $option => $value ) {
			if ( $option == 'version' && empty( $config[ 'version' ] ) )
				throw new \InvalidArgumentException( "Version can not be empty" );

			if ( $option == "apiKey" && !ctype_alnum( $value ) )
				throw new \InvalidArgumentException( "Api Key '{$value}' contains invalid characters or spaces." );
		}
	}
	
	/**
	 * Creates the cache providers
	 *
	 * @param array $config Array of cache configs
	 */
	private function createCache( array $config )
	{
		switch( $config[ 'type' ] ) {
			case 'redis':
				if( !class_exists( '\Redis' ) ) {
					throw new \LogicException( "Redis is required." );
				}
				return $this->cache = new Cache\RedisCache( $config );
			case 'memcached':
				if( !class_exists( '\Memcached' ) ){
					throw new \LogicException( "Memcached is required." );
				}
				return $this->cache = new Cache\MemcachedCache( $config );
			default:
				throw new \InvalidArgumentException( sprintf( "%s is not a valid cache type. ", $config[ 'type' ] ) );
		}
	}

	/**
	 * @return Cache\Cache
	 */
	public function getCache()
	{
		return $this->cache;
	}
}
