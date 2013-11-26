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
	public static function fromCommand( OperationCommand $command )
	{
		$data = $command->getResponse()->getBody( );
		$data = json_decode( $data, true );

		return new self( $command->getClient(), $data );
	}

	public function setResults( array $results )
	{
		$coll = new Collection( );
		foreach( $results as $key => $result ) {
			$coll->set( $key, new BaseEntity( $this->getClient(), $result ) );
		}


		parent::setResults( $coll );
	}
}