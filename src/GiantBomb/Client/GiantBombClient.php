<?php

namespace GiantBomb\Client;

use Guzzle\Common\Collection;
use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;

/**
 * Class GiantBombClient
 *
 * @package GiantBomb\Client
 */
class GiantBombClient extends Client
{
	/**
	 * Factory to create new GiantBombClient instance.
	 *
	 * @param array $config
	 *
	 * @returns \GiantBomb\Client\GiantBombClient
	 */
	public static function factory( $config = array() )
	{
		$default = array( 'baseUrl' => "https://www.giantbomb.com/api/", 'version' => '1.0', 'apiKey' => null, 'format' => 'json' );

		// Validate the configuration options
		self::validateConfig( $config );

		// Create client configuration
		$config = Collection::fromConfig( $config, $default );

		// Create the new GiantBomb Client with our Configuration
		$client = new self( $config->get( 'baseUrl' ), $config );

		// Set the Service Definition from the versioned file
		$file = 'giant-bomb-' . str_replace( '.', '_', $client->getConfig( 'version' ) ) . '.json';
		$client->setDescription( ServiceDescription::factory( __DIR__ . "/../Resources/config/{$file}" ) );

		// Set the content type header to use "application/json" for all requests
		$client->setDefaultOption( 'headers', array( 'Content-Type' => 'application/json' ) );

		return $client;
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

		return $this->getCommand( $method, isset( $args[ 0 ] ) ? $args[ 0 ] : array() )->getResult();
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
}
