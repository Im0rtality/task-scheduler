<?php

namespace Im0rtality\TaskSchedulerBundle\Backend\MongoDB;

class MongoCollectionFactory
{

    /** @var  mixed */
    protected $config;

    /** @var  \MongoClient */
    protected $client;

    public function create()
    {
        return $this->client
            ->{$this->config['database']}
            ->{$this->config['collection']};
    }

    /**
     * @param \MongoClient $client
     */
    public function setClient(\MongoClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
}
