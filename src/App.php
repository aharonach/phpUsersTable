<?php

namespace Aharon;

class App
{
    /**
     * Integrations instance.
     *
     * @var Integration
     */
    public Integration $integration;

    /**
     * Database instance.
     *
     * @var Database
     */
    public Database $database;

    /**
     * Api instance.
     *
     * @var Api
     */
    public Api $api;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->database    = new Database($config['host'], $config['db_name'], $config['username'], $config['password']);
        $this->integration = new Integration();
        $this->api         = new Api($this);
        $this->database->migrate();
    }
}