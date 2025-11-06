<?php

namespace App\Traits;
use App\Models\Notification;
use Request;

trait Notifications

{
    public static function notification($shop_id,$branch_id,$model,$table_id,$old_value,$new_value,$send_on,$send_by,$message,$url,$bulk_upload_file,$notification_type)

    {

    	$notification = [];
    	$notification['shop_id'] = $shop_id;
        $notification['branch_id'] = $branch_id;
        $notification['model'] = $model;
        $notification['table_id'] = $table_id;
        $notification['old_value'] = $old_value;
        $notification['new_value'] = $new_value;
        $notification['send_on'] = $send_on;
        $notification['send_by'] = $send_by;
    	$notification['message'] = $message;
    	$notification['url'] = $url;
    	$notification['bulk_upload_file'] = $bulk_upload_file;
        $notification['notification_type_id'] = $notification_type;

    	Notification::create($notification);

    }
}