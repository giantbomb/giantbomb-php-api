<?php
/**
 * @author    Aaron Scherer
 * @date      11/22/13
 */

namespace GiantBomb\Model;

use Guzzle\Service\Command\OperationCommand;
use GiantBomb\Entity\BaseEntity;


class SingleModel extends GiantBombModel
{
	public static function fromCommand( OperationCommand $command )
	{
		$data = $command->getResponse()->getBody( );
		$data = json_decode( $data, true );

		return new self( $command->getClient(), $data );
	}

	public function setResults( array $results )
	{
		parent::setResults( new BaseEntity( $this->getClient(), $results ) );
	}
}