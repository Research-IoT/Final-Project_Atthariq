<?php

namespace App\Helpers;

class Response
{
    protected static $response = [
        'code' => null,
    ];

    /** 
     * When data is successfully retrieved
     */
    public static function success($data = null, $code = 200)
    {
        self::$response['code'] = $code;
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['code']);
    }

    /** 
     * When data has bad request
     */
    public static function badRequest($error = null, $code = 400)
    {
        self::$response['code'] = $code;
        self::$response['error'] = $error;

        return response()->json(self::$response, self::$response['code']);
    }

    /** 
     * When detected server error
     */
    public static function internalServer($error = null, $code = 500)
    {
        self::$response['code'] = $code;
        self::$response['error'] = $error;

        return response()->json(self::$response, self::$response['code']);
    }
}
