<?php namespace App\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Interfaces\ActivationInterface;

class SMSController extends Controller implements ActivationInterface 
{
    const SMS_SEND_URL = 'https://bsms.voicecom.bg/smsapi/bsms/sendsms/?sid=1433&encoding=urf-8';
    const SMS_STATUS_URL = 'https://bsms.voicecom.bg/smsapi/bsms/dlr/?sid=1433';
    const CHECK_LIMIT_URL = 'https://bsms.voicecom.bg/smsapi/bsms/sendsms/check_limit.php?sid=1433';
    const BG_PREFIX = '359';
    
    protected $phone;
    protected $message;
    protected $sendId;
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new \GuzzleHttp\Client();
    }

    public function setSMSMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getSMSMessage()
    {
        return $this->message;
    }
    
    public function sendActivationCode(User $user, $to, $code)
    {
        
        $this->phone = self::BG_PREFIX . substr($to, 1);
        $this->sendId = $user->id . '_' . date('YmdHi');
        $this->message = 'Hello from Fitness 33. Your account activation code is ' . $code;
        
        if($this->checkAlreadySend()) {
            return ['status' => false, 'msg' => 'Too many attempts, try again later'];
        }
        
        return $this->sendSMS();
        
    }
    
    public function check($data) 
    {
       
        return User::where('user_type', User::TYPE_CUSTOMER)
                ->where(function($q) use($data) {
                    $q->orWhere('phone_1', $data);
                    $q->orWhere('phone_2', $data);
                })->get();
    }
    
    private function sendSMS()
    {
       
        $response = $this->httpClient->get(self::SMS_SEND_URL . '&id=' . $this->sendId . '&msisdn=' . $this->phone . '&text=' . urlencode($this->message));
        
        if(preg_match("/^ERROR:.*/", $response->getBody()->getContents(), $matched)) {
            return ['status' => false, 'msg' => $matched[0]];
        } 
        
        return ['status' => true, 'msg' => ''];
    }
    
    /*
     * returns 0 if it hasnt been send or 1 if it was send (in the last hour)
     */
    private function checkAlreadySend()
    { 
        return (preg_match("/<row>/", $this->httpClient->get(self::SMS_STATUS_URL.'&id=' . $this->sendId)->getBody()->getContents()));
        
    }
}
