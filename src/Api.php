<?php

namespace Aharon;

use Exception;

class Api
{
    const RESPONSE_SUCCESS = 'success';

    const RESPONSE_ERROR = 'error';

    /**
     * App instance.
     *
     * @var App
     */
    private App $app;

    /**
     * Constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Get all users from database and return their json data.
     *
     * @return string
     */
    public function getUsers(): string
    {
        try {
            $response_data = $this->app->database->getUsers();
            $response_type = self::RESPONSE_SUCCESS;
        } catch(Exception $exception) {
            $response_data = $exception->getMessage();
            $response_type = self::RESPONSE_ERROR;
        }

        return $this->prepareResponse($response_data, $response_type);
    }

    /**
     * Insert users to the database.
     *
     * @return string
     */
    public function insertUsers(): string
    {
        try {
            $results = filter_var( $_REQUEST['results'] ?? 10, FILTER_VALIDATE_INT );
            $users   = $this->app->integration->request(['results' => $results]);
            $this->app->database->insertUsers($users);
            $response_data = $this->app->database->getUsers();
            $response_type = self::RESPONSE_SUCCESS;
        } catch (Exception $exception) {
            $response_data = $exception->getMessage();
            $response_type = self::RESPONSE_ERROR;
        }

        return $this->prepareResponse($response_data, $response_type);
    }

    /**
     * Prepare response object.
     *
     * @param array $data
     * @param string $response_type
     * @return string
     */
    public function prepareResponse(array $data, string $response_type = self::RESPONSE_SUCCESS): string
    {
        return json_encode(
            match( $response_type ) {
                self::RESPONSE_ERROR => [
                    'error' => $data
                ],
                default => [
                    'success' => true,
                    'data'    => $data,
                ]
            }
        );
    }
}