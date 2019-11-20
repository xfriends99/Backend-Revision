<?php
namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

trait JsonApiResponse {

    static public function errorResponse($messages, $code)
    {
        return Response::json(array(
            'meta' => ['error' => true],
            'message' => $messages
        ), $code);
    }

    static public function success($data = []) {
        return Response::json(array(
            'meta' => ['error' => false],
            'data' => $data
        ), 200, [], JSON_PRETTY_PRINT);
    }

    static public function resourceCreated($data, $msg = '') {
        return Response::json(array(
            'meta' => ['error' => false],
            'data' => $data,
            'message' => $msg ? $msg : 'Resource created.'
        ), 201);
    }

    static public function resourceUpdated($data, $msg = '') {
        return Response::json(array(
            'meta' => ['error' => false],
            'data' => $data,
            'message' => $msg ? $msg : 'Resource updated.'
        ), 200);
    }

    static public function resourceNotFound($errors = '')
    {
        return self::errorResponse($errors ? $errors : 'Resource not found', 404);
    }

    static public function badRequest($errors = '')
    {
        return self::errorResponse($errors ? $errors : 'Bad Request', 400);
    }

    static public function internalServerError($errors = '')
    {
        Log::error($errors, ['API Internal server error']);
        return self::errorResponse($errors ? $errors : 'Internal server error', 500);
    }
}