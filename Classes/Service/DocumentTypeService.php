<?php
declare(strict_types=1);

namespace T3G\Elasticorn\Service;


use Elastica\Client;
use Elastica\Query;
use Psr\Log\LoggerInterface;
use T3G\Elasticorn\Configuration\ApplicationConfiguration;

class DocumentTypeService
{
    /**
     * @var \Elastica\Client
     */
    protected $client;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    private $typeName;
    /**
     * @var string
     */
    private $indexName;

    /**
     * DocumentTypeService constructor.
     *
     * @param \Elastica\Client $client
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $indexName
     * @param string $typeName
     */
    public function __construct(
        Client $client,
        LoggerInterface $logger,
        string $indexName,
        string $typeName
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->indexName = $indexName;
        $this->typeName = $typeName;
    }

    public function deleteDocumentsOfType()
    {
        $this->logger->info('Removing all documents of type ' . $this->typeName);
        $languages = ApplicationConfiguration::getLanguageConfiguration();
        if (count($languages) > 0) {
            foreach ($languages as $language) {
                $index = $this->client->getIndex($this->indexName . '_' . $language);
                $this->logger->info('Removing documents of type ' . $this->typeName . ' from index ' . $index);
                $index->getType($this->typeName)->deleteByQuery(
                   new Query\MatchAll()
               );
                $index->refresh();
            }

        } else {
            $this->client->getIndex($this->indexName)->getType($this->typeName)->deleteByQuery(new Query\MatchAll());
            $this->client->getIndex($this->indexName)->refresh();
        }
        $this->logger->info('Removed all documents of type ' . $this->typeName);
    }
}