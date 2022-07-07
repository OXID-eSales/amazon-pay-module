<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Core\Helper;

class PhpHelper
{
    /**
     * @param string $json
     * @return array
     */
    public static function jsonToArray(string $json): array
    {
        return json_decode($json, true);
    }

    /**
     * @param string $needle
     * @param array $haystack
     * @return bool|mixed
     */
    public static function getArrayValue(string $needle, array $haystack)
    {
        foreach ($haystack as $key => $value) {
            if ($key === $needle) {
                return $value;
            }

            if (is_array($value)) {
                $result = self::getArrayValue($needle, $value);
                if ($result !== false) {
                    return $result;
                }
            }
        }

        return false;
    }

    public static function getMoneyValue(float $num): string
    {
        return number_format($num, 2, '.', '');
    }

    /**
     * Get POST from $_POST or php://input if set
     * @return array|mixed
     */
    public static function getPost()
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $post = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $post;
        }

        return [];
    }
}
