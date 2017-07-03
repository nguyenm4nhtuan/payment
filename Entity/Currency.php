<?php
/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 7/1/2017
 * Time: 11:49 PM
 */

namespace Entity;


use GuzzleHttp\Client;

class Currency
{
    private static $_rateApi = 'http://api.fixer.io/latest';
    private $_currencies = [
        "USD",
        "AUD",
        "BGN",
        "BRL",
        "CAD",
        "CHF",
        "CNY",
        "CZK",
        "DKK",
        "GBP",
        "HKD",
        "HRK",
        "HUF",
        "IDR",
        "ILS",
        "INR",
        "JPY",
        "KRW",
        "MXN",
        "MYR",
        "NOK",
        "NZD",
        "PHP",
        "PLN",
        "RON",
        "RUB",
        "SEK",
        "SGD",
        "THB",
        "TRY",
        "ZAR",
        "EUR",
        'credits'
    ];

    public function getCurrencies()
    {
        return $this->_currencies;
    }

    public static function isCurrency($currency)
    {
        $currencies = new static();
        return in_array($currency, $currencies->_currencies);
    }

    public static function convert($amount, $fromCurrency, $toCurrency)
    {
        if (($fromCurrency === 'USD' && $toCurrency === 'credits')
            || ($fromCurrency === 'credits' && $toCurrency === 'USD')
            || ($fromCurrency === $toCurrency)
        ) {
            return $amount;
        }
//        $client = new Client();
//        $queries = http_build_query([
//            'base' => $fromCurrency,
//            'symbols' => $toCurrency
//        ]);
//        $endPoint = self::$_rateApi . '?' . $queries;
//        try {
//
//        } catch (\Exception $e) {
//            $res = $client->request('GET', $endPoint);
//
//            $res->getBody();
//        }
        return $amount;
    }

    public static function format($format, $number)
    {
        try {
            money_format( $format , $number );
        } catch (\Exception $e) {

        }
    }
}