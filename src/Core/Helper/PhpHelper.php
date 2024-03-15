<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core\Helper;

class PhpHelper
{
    /**
     * @param string $json
     * @return array
     */
    public static function jsonToArray(string $json): array
    {
        /** @var array $decoded */
        $decoded = json_decode($json, true);
        return $decoded;
    }

    /**
     * @param string $needle
     * @param array $haystack
     * @return string|array|bool
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

    /**
     * @param float $num
     * @return string
     */
    public static function getMoneyValue(float $num): string
    {
        return number_format($num, 2, '.', '');
    }

    /**
     * Get POST from $_POST or php://input if set
     * @return array
     */
    public static function getPost(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        /** @var array $decoded */
        $decoded = json_decode((string)file_get_contents('php://input'), true);
        $post = $decoded;
        if (json_last_error() == JSON_ERROR_NONE) {
            return $post;
        }

        return [];
    }
}
