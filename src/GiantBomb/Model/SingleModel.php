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
		return new self( $command );
	}

	public function setResults( $results )
	{
		parent::setResults( new BaseEntity( $this->getClient(), $results ) );
	}
}
