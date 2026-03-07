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
        $decoded = json_decode($json, true, 64);
        return is_array($decoded) ? $decoded : [];
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
        $decoded = json_decode((string)file_get_contents('php://input'), true, 64);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [];
    }
}
