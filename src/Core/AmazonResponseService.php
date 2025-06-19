<?php

namespace OxidSolutionCatalysts\AmazonPay\Core;

class AmazonResponseService
{
    public function isRequestError(array $result)
    {
        return ($result['status'] ?: 200) >= 400;
    }

    public function getRequestErrorMessage(array $result)
    {
        if ($this->isRequestError($result)) {
            $response = json_decode($result['response'], true);
            if (!is_array($response)) {
                return '';
            }
            return $response['message'] ?: '';
        }

        return '';
    }
}
