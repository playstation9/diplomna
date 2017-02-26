<?php namespace App\Http\Controllers;

use DB;
use Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Interfaces\ActivationInterface;

class ActivationController extends Controller 
{

    protected $activation;
    
    public function __construct(ActivationInterface $activation)
    {
        $this->activation = $activation;
    }

    private function generateActivationCode()
    {
        return rand(1000000,9999999);
    }
    
    public function sendActivationCode($to)
    {
        
        $user = $this->activation->check($to);
        
        //client not found
        if($user->count() == 0) { 
            return Response::json(['status' => false, 'msg' => 'Client does not exists']);
        }
        //same phone on many clients
        if($user->count() > 1) { 
            return Response::json(['status' => false, 'msg' => 'More than one user exists with given credentials, please check with operator at the counter']);
        }
        //already been activated 
        if($user[0]->confirmed == 1) { 
            return Response::json(['status' => false, 'msg' => 'You have already been activated. If you don\'t remember your password, please use the reset password button']);
        }
        
        //generate activation code
        $code = $this->generateActivationCode();
        
        //save code in DB and send it via send method(sms,email,etc.). If any fails return error
        try {
            DB::beginTransaction();
            $user[0]->confirmation_code = $code;
            $user[0]->save();
            
            
            $send = $this->activation->sendActivationCode($user[0], $to, $code);
            
            if( ! $send['status']) { 
                return Response::json(['status' => false, 'msg' => $send['msg']]);
            }
            
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            return Response::json(['status' => false, 'msg' => 'Send failed, please try again later']);
        }
        
        return Response::json(['status' => true, 'msg' => 'Activation code has been send. Close this window and proceed with registration. If after two minutes you still haven\'t received anything, please try again.']);
    }
}
