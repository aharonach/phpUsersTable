<?php

namespace Aharon;

use Exception;

class Integration
{
    private const API_URL = 'https://randomuser.me/api/';

    private array $curl_options = [
        CURLOPT_HEADER         => 0,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ];

    /**
     * Map table columns to record object from API.
     *
     * @var array
     */
    private array $data_mapping = [
        'name'        => [
            'name.first',
            'name.last'
        ],
        'email'       => 'email',
        'age'         => 'dob.age',
        'country'     => 'location.country',
        'profile_pic' => 'picture.large',
    ];

    /**
     * Make the request.
     *
     * @param array $query_args
     *
     * @return array
     * @throws Exception
     */
    public function request(array $query_args = []): array
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->getApiUrl($query_args));
        curl_setopt_array($curl, $this->curl_options);

        $response   = curl_exec($curl);
        $curl_error = curl_error($curl);

        if ($curl_error) {
            throw new Exception($curl_error);
        }

        $output = json_decode($response, true);

        if (!empty($output['error'])) {
            throw new Exception($output['error']);
        }

        curl_close($curl);

        $output = $output['results'] ?? $output;
        return array_map([ $this, 'mapData'], $output);
    }

    private function getApiUrl(array $query_args): string
    {
        return self::API_URL . '?' . http_build_query($query_args);
    }

    /**
     * Map record fields from API to match the table structure.
     *
     * @param array $record
     * @return array
     */
    private function mapData(array $record): array
    {
        $mapped_record = [];

        foreach ($this->data_mapping as $column_id => $column) {
            if (is_array($column)) {
                $value = [];

                foreach($column as $column_to_join) {
                    $value[] = $this->getValue($column_to_join, $record);
                }

                $value = implode(' ', $value);
            } else {
                $value = $this->getValue($column, $record);
            }

            $mapped_record[$column_id] = $value;
        }

        return $mapped_record;
    }

    /**
     * Get a value from a multidimensional array of a fetched record.
     *
     * @param array|string $column
     * @param array $record
     * @return mixed
     */
    private function getValue(array|string $column, array $record): mixed
    {
        $keys  = explode('.', $column);
        $value = $record[current($keys)];

        for ($i = 1; $i < count($keys); $i++) {
            $value = $value[$keys[$i]];
        }

        return $value;
    }
}