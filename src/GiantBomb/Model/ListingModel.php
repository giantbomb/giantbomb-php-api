<?php
/**
 * @author    Aaron Scherer
 * @date      11/22/13
 */

namespace GiantBomb\Model;

use GiantBomb\Component\Collection;
use Guzzle\Service\Command\OperationCommand;
use GiantBomb\Entity\BaseEntity;

class ListingModel extends GiantBombModel
{

	private static $position = null;

	public static function fromCommand( OperationCommand $command )
	{
		return new self( $command );
	}

	public function setResults(  $results )
	{
		$coll = new Collection( );
		foreach( $results as $key => $result ) {
			$coll->set( $key, new BaseEntity( $this->getClient(), $result ) );
		}

		parent::setResults( $coll );
	}

	public function hasMoreResults()
	{
		// If the status failed, return false.
		if( $this->getStatusCode() !== 1 ) return false;
		
		// If there are more total results than the current max, return true
		if( $this->getNumberOfTotalResults() > $this->getLimit() + $this->getOffset() ) return true;
		
		// Fallback false
		return false;	
	}
	
	public function getMoreResults()
	{
		if( !$this->hasMoreResults() ) return false;

		if( static::$position === null ) static::$position = $this->getOffset();

		$args = $this->getArguments();
		$args[ 'offset' ] = static::$position += $this->getLimit();
		$args[ 'limit' ]  = $this->getLimit();
	
		$response = $this->getClient()->{$this->getCommandName()}( $args );
		if( $response->getStatusCode() !== 1 ) return false;
		
		$results = $response->getResults();
		foreach( $results as $result ) {
			$this->getResults()->insert( $result );
		}

		return true;
	}
}
