<?php
/**
 * @author    Aaron Scherer
 * @date      11/22/13
 */

namespace GiantBomb\Entity;

use GiantBomb\Client\GiantBombClient;

class BaseEntity
{
	/**
	 * @var GiantBombClient
	 */
	protected $client;

	/**
	 * @var string
	 */
	protected $apiDetailUrl;

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * Constructor
	 *
	 * @param GiantBombClient $client Client from the request
	 * @param array $data Data from the request
	 */
	final public function __construct( $client, $data )
	{
		$this->client = $client;

		foreach( $data as $key => $value ) {
			$setter = 'set' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $key ) ) );
			if( method_exists( $this, $setter ) ) {
				$this->{$setter}( $value );
			} else {
				$this->data[ $key ] = $value;
			}
		}
	}

	final public function __call( $function, $arguments )
	{
		if( strpos( $function, 'get' ) === 0 ) {
			$name = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', str_replace( 'get', '', $function ) ) );

			if( array_key_exists( $name, $this->data ) ) {
				return $this->data[ $name ];
			}
		}

		if( strpos( $function, 'has' ) === 0 ) {
			$name = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', str_replace( 'has', '', $function ) ) );

			if( array_key_exists( $name, $this->data ) ) {
				return true;
			} else {
				return false;
			}
		}

		if( strpos( $function, 'set' ) === 0 ) {
			$name = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', str_replace( 'set', '', $function ) ) );

			return $this->{$name} = $arguments[ 0 ];
		}

		throw new \InvalidArgumentException( sprintf( "%s is an invalid function for this object.", $function ) );
	}

	public function getDetail( )
	{
		$urlInfo = parse_url( $this->getApiDetailUrl() );
		$matches = array();

		preg_match( '/^\/api\/(?P<function>\w+)\/?(?P<arg>[0-9-]+)?\/$/', $urlInfo[ 'path' ], $matches );
		$function = 'get' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $matches[ 'function' ] ) ) );
	
		return $this->client->{$function}( array( 'id' => $matches[ 'arg' ] ) );
	}

	/**
	 * @param string $apiDetailUrl
	 */
	public function setApiDetailUrl( $apiDetailUrl )
	{
		$this->apiDetailUrl = $apiDetailUrl;
	}

	/**
	 * @return string
	 */
	public function getApiDetailUrl()
	{
		return $this->apiDetailUrl;
	}

	/**
	 * @return bool
	 */
	public function hasApiDetailUrl()
	{
		return !empty( $this->apiDetailUrl );
	}
} 
