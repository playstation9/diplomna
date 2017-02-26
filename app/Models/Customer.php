<?php 

namespace App\Models;

use DB;
use Auth;
use Lang;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\SoftDeletes;



class Customer extends Model
{
    use SoftDeletes;
    protected $table = 'users';
    protected $data; 
   
    public function __construct()
    {
        parent::__construct();

        $this->data = new CustomerData();
    }
    

    /**
     * customer unique data relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function data()
    {
        return $this->hasOne('App\Models\CustomerData', 'user_id');
    }

    public function foods()
    {
        return $this->hasMany('App\Models\Foods', 'user_id');
    }
    
    public function saveNewEntry($data)
    {

//        array:10 [▼
//        "_token" => "D0lzCrQI86VitXD7P5wqIQLnMXqCrX2OK7MxdW12"
//        "first_name" => "Гошо"
//        "middle_name" => "Иванов"
//        "last_name" => "Петров"
//        "gender" => "male"
//        "phone_1" => "0955123123"
//        "phone_2" => "123991231"
//        "email" => "test@examepl.com"
//        "password" => "haha1233"
//        "password_confirmed" => "haha1233"
//      ]
//      
         // validator 
        $validator = $this->registerValidator($data);
        if($validator->fails()) 
        {
            Session::flash('validation_errors',$validator->errors()->all());
                
            return Redirect::back()->withInput();
                          
        }
        
        dd($data);
    }
    
    public function buildAPIWebView($clientId)
    {
        
        //BUILD WEB VIEW FOR MOBILE APP
        $data['item'] = $this->with('data')->find($clientId);
       
        //get current subscriptions
        $data['active_subscriptions'] = $data['item']->getSubscriptions('viewonly');
        foreach($data['active_subscriptions'] as $key => $subscription) {
            $data['item']->getStatusHtml($subscription, 'viewonly');
        }
        
        //GET NUMBER OF PAST SUBSCRIPTIONS
        $data['totalsubscriptionscount'] = $this->getTotalSubscriptionsCount($clientId) - count($data['active_subscriptions']);

        return $data;
    }   
    
    private function registerValidator($data)
    {
        $rules = [
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'required|email|unique',
            'password'          => 'required|confirmed|min:6'     
        ];

        $messages = [
            'email.required'           => Lang::get('pages.users.email_required'),
            'email.email'              => Lang::get('pages.users.email_email'),
            'email.unique'             => Lang::get('pages.users.email_unique'),
            'password.required'        => Lang::get('pages.users.password_required'),
            'password.confirmed'       => Lang::get('pages.users.password_confirmed'),
            'first_name.required'      => Lang::get('pages.users.first_name_required'),
            'last_name.required'       => Lang::get('pages.users.last_name_required'),
        ];

        return Validator::make($data, $rules, $messages);        

    }
    
}
