<?php

/**
 * This file is part of OXID eSales AmazonPay module.
 *
 * OXID eSales AmazonPay module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales AmazonPay module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales AmazonPay module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2020
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

    /**
     * @param float $num
     * @return string
     */
    public static function getMoneyValue($num): string
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
