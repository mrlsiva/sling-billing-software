<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\EmailLog;
use App\Models\User;
use Carbon\Carbon;

class SMTPController extends Controller
{

	// public static function sendMail($emailId,$cc=null,$subject,$txt=null,$attachment=null) {

    //     $user = User::where('email',$emailId)->first();
    //     $now = Carbon::now();

    //     try
    //     {
    //         Mail::send([], [], function($message) use ($emailId,$cc,$subject,$txt,$attachment){

    //             $message->to($emailId);
    //             if($cc != null){
    //                 $message->cc($cc);
    //             }
    //             $message->subject($subject);
    //             $message->html($txt, 'text/html');
    //             if($attachment!=null)
    //                 $message->attach($attachment);
    //         });

    //         // check for failures
    //         if (Mail::flushMacros()) {

    //             $data['status'] = 0;

    //             EmailLog::create([
    //                 'user_id' => $user->id,
    //                 'email' => $emailId,
    //                 'subject' => $subject,
    //                 'body' => $txt,
    //                 'attachment' => $attachment ,
    //                 'msg' => 'Mail Not Sent',
    //                 'failed_on' => $now,
    //                 'send_on' => null,
    //                 'is_send' => 0,
    //             ]);

    //             $returndata = array('success' => false, 'err' => true, 'msg' => 'Mail Not Sent');
    //         }
    //         else
    //         {
    //             $data['status'] = 1;

    //             EmailLog::create([
    //                 'user_id' => $user->id,
    //                 'email' => $emailId,
    //                 'subject' => $subject,
    //                 'body' => $txt,
    //                 'attachment' => $attachment ,
    //                 'msg' => 'Mail Not Sent',
    //                 'failed_on' => null,
    //                 'send_on' => $now,
    //                 'is_send' => 1,
    //             ]);

    //             $returndata = array('success' => true, 'err' => false, 'msg' => 'Mail Sent Successfully');
    //         }
    //     }
    //     catch (\Exception $e)
    //     {

    //         EmailLog::create([
    //             'user_id' => $user->id,
    //             'email' => $emailId,
    //             'subject' => $subject,
    //             'body' => $txt,
    //             'attachment' => $attachment ,
    //             'msg' => $e->getMessage(),
    //             'failed_on' => $now,
    //             'send_on' => null,
    //             'is_send' => 0,
    //         ]);
    //     }
        
    // }

    public static function sendMail($emailId,$cc=null,$subject,$txt=null,$attachment=null) {

        $user = User::where('email',$emailId)->first();
        $now = Carbon::now();
        
        Mail::send([], [], function($message) use ($emailId,$cc,$subject,$txt,$attachment){

            $message->to($emailId);
            if($cc != null){
                $message->cc($cc);
            }
            $message->subject($subject);
            $message->html($txt, 'text/html');
            if($attachment!=null)
                $message->attach($attachment);
        });   
    }
}
