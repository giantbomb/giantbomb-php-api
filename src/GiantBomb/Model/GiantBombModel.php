<?php

/**
 * @author    Aaron Scherer
 * @date      11/22/13
 */
namespace GiantBomb\Model;

use Guzzle\Service\Command\OperationCommand;
use Guzzle\Service\Command\ResponseClassInterface;
use GiantBomb\Component\Collection;
use GiantBomb\Entity\BaseEntity;
use GiantBomb\Client\GiantBombClient;

class GiantBombModel implements ResponseClassInterface
{
    /**
     * @var GiantBombClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $commandName;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var string A text string representing the status_code
     */
    protected $error;

    /**
     * @var int The value of the limit filter specified, or 100 if not specified
     */
    protected $limit;

    /**
     * @var int The value of the offset filter specified, or 0 if not specified
     */
    protected $offset;

    /**
     * @var int The number of results on this page
     */
    protected $numberOfPageResults;

    /**
     * @var int The number of total results matching the filter conditions specified
     */
    protected $numberOfTotalResults;

    /**
     * An integer indicating the result of the request. Acceptable values are:.
     *
     *      1:OK
     *      100:Invalid API Key
     *      101:Object Not Found
     *      102:Error in URL Format
     *      103:'jsonp' format requires a 'json_callback' argument
     *      104:Filter Error
     *      105:Subscriber only video is for subscribers only
     *
     * @var int
     */
    protected $statusCode;

    /**
     * @var array|Collection|BaseEntity Zero or more items that match the filters specified
     */
    protected $results;

    /**
     * Constructor.
     *
     * @param OperationCommand $command
     */
    public function __construct(OperationCommand $command)
    {
        $this->setClient($command->getClient());
        $this->setCommandName($command->getName());

        $data = $command->getResponse()->json();
        foreach ($data as $key => $value) {
            $setter = 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            }
        }
    }

    /**
     * Create a response model object from a completed command.
     *
     * @param OperationCommand $command That serialized the request
     *
     * @return self
     */
    public static function fromCommand(OperationCommand $command)
    {
        return new self($command);
    }

    /**
     * @param \GiantBomb\Client\GiantBombClient $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return \GiantBomb\Client\GiantBombClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string
     */
    public function setCommandName($commandName)
    {
        $this->commandName = $commandName;
    }

    /**
     * @return string
     */
    public function getCommandName()
    {
        return $this->commandName;
    }

    /**
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $numberOfPageResults
     */
    public function setNumberOfPageResults($numberOfPageResults)
    {
        $this->numberOfPageResults = $numberOfPageResults;
    }

    /**
     * @return int
     */
    public function getNumberOfPageResults()
    {
        return $this->numberOfPageResults;
    }

    /**
     * @param int $numberOfTotalResults
     */
    public function setNumberOfTotalResults($numberOfTotalResults)
    {
        $this->numberOfTotalResults = $numberOfTotalResults;
    }

    /**
     * @return int
     */
    public function getNumberOfTotalResults()
    {
        return $this->numberOfTotalResults;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param array|Collection|BaseEntity $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * @return array|Collection|BaseEntity
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
