<?php
/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 7/1/2017
 * Time: 11:49 PM
 */

namespace Entity;


use Common\PLog;
use GuzzleHttp\Client;

class Currency
{
    private static $_rateApi = 'http://api.fixer.io/latest';
    private static $_currencies = [
        "USD" => 1,
        "AUD" => 1.3013,
        "BGN" => 1.7138,
        "BRL" => 3.2948,
        "CAD" => 1.2956,
        "CHF" => 0.95776,
        "CNY" => 6.781,
        "CZK" => 22.956,
        "DKK" => 6.5165,
        "GBP" => 0.77053,
        "HKD" => 7.8048,
        "HRK" => 6.4934,
        "HUF" => 270.74,
        "IDR" => 13327,
        "ILS" => 3.4953,
        "INR" => 64.62,
        "JPY" => 111.94,
        "KRW" => 1143.2,
        "MXN" => 18.037,
        "MYR" => 4.2925,
        "NOK" => 8.387,
        "NZD" => 1.363,
        "PHP" => 50.451,
        "PLN" => 3.703,
        "RON" => 3.989,
        "RUB" => 59.188,
        "SEK" => 8.4471,
        "SGD" => 1.3766,
        "THB" => 33.95,
        "TRY" => 3.5168,
        "ZAR" => 13.074,
        "EUR" => 0.87627,
        'credits' => 1
    ];

    public static function getCurrencies()
    {
        return self::$_currencies;
    }

    public static function isCurrency($currency)
    {
        return isset(self::$_currencies[$currency]);
    }

    public static function convert($amount, $fromCurrency, $toCurrency)
    {
        if (($fromCurrency === 'USD' && $toCurrency === 'credits')
            || ($fromCurrency === 'credits' && $toCurrency === 'USD')
            || ($fromCurrency === $toCurrency)
        ) {
            return $amount;
        }

        try {
            $client = new Client();
            $fromCurrency = $fromCurrency === 'credits' ? 'USD' : $fromCurrency;
            $toCurrency = $toCurrency === 'credits' ? 'USD' : $toCurrency;
            $queries = http_build_query([
                'base' => $fromCurrency,
                'symbols' => $toCurrency
            ]);
            $endPoint = self::$_rateApi . '?' . $queries;
            $res = $client->request('GET', $endPoint)->getBody();

            $res = json_decode($res);
            $rate = $res->rates->$toCurrency;

            $toAmount = $amount * $rate;
        } catch (\Exception $e) {
            PLog::error(__METHOD__, $e->getMessage());
            $currencies = self::$_currencies;

            $toAmount = $amount / $currencies[$fromCurrency] * $currencies[$toCurrency];
        }
        return $toAmount;
    }
}