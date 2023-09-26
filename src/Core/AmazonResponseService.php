<?php

namespace OxidSolutionCatalysts\AmazonPay\Core;

class AmazonResponseService
{
    public function isRequestError(array $result): bool
    {
        return ($result['status'] ?: 200) >= 400;
    }

    public function getRequestErrorMessage(array $result): string
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
