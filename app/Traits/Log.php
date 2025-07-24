<?php

namespace App\Traits;
use App\Models\AuditLog;
use Request;

trait Log

{
    public static function addToLog($unique_id,$user_id,$module,$model,$table,$table_id,$action,$old_value,$new_value,$status,$comment)

    {

    	$log = [];
    	$log['unique_id'] = $unique_id;
        $log['user_id'] = $user_id;
        $log['module'] = $module;
        $log['model'] = $model;
        $log['table'] = $table;
        $log['table_id'] = $table_id;
        $log['action'] = $action;
        $log['old_value'] = $old_value;
        $log['new_value'] = $new_value;
        $log['status'] = $status;
        $log['comment'] = $comment;
    	$log['url'] = Request::fullUrl();
    	$log['method'] = Request::method();
    	$log['ip'] = Request::ip();
    	$log['agent'] = Request::header('user-agent');

    	AuditLog::create($log);

    }

    public static function unique()
    {

        // Get the latest unique_id from the DB
        $last = AuditLog::where('unique_id', 'like', 'L-%')->orderByDesc('id')->first();

        if ($last && preg_match('/L-(\d+)/', $last->unique_id, $matches)) 
        {
            $number = (int)$matches[1] + 1;
        } 
        else 
        {
            $number = 1; // Start from 1 if none found
        }

        // Pad with leading zeros to match 5 digits
        $unique = 'L-' . str_pad($number, 5, '0', STR_PAD_LEFT);

        return $unique;
        
    }



}