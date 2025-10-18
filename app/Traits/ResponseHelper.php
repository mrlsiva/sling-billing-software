<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait ResponseHelper
{
    public function validationFailed($input_errors, $message = null, $status_code = 400)
    {
        $errors = [];
        foreach ($input_errors->all() as $msg) {
            array_push($errors, $msg);
        }
        return $this->errorResponse($errors, $status_code, $message);
    }

    public function successResponse($data, $status_code = 200, $message = null, $success = True)
    {
        $error = [
            "code"=> $status_code,
            "message"=> $message,
            "success"=> $success,
            "data"=> $data
        ];
        return response()->json($error, $status_code);
    }

    public function errorResponse($errors, $status_code = 400, $message = null, $success = False)
    {
        $error = [
            "code"=> $status_code,
            "message"=> $message,
            "success"=> $success,
            "data"=> null,
            "errors"=> $errors,
        ];
        return response()->json($error, $status_code);
    }
}
