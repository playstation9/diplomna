<?php

namespace App\Http\Controllers;

use DB;
use View;
use \Customer;
use Lang;
use Carbon\Carbon;

class DashboardController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * opens the dashboard page
     *
     * @return mixed - view
     */
    public function dashboard()
    {
        
        return View::make('dashboard');

    }
    
    public function getSearchModal($ss) 
    {
        
        return view('dashboard.partials.search_results_modal', ['ss' => $ss]);
        
    }

    public function getCreateModal($barcode = null) 
    {
        
        $data['barcode'] = $barcode;
        $data['bridgeUrl'] = $this->siteSettings->getSettingByKeyBySite('bridge-service-url', $this->userData['site']);
        
        return view('dashboard.partials.create_customer_modal', ['data' => $data]);
        
    }

    public function dashboardVisitDT()
    {

        $users = Visit::leftJoin('users', 'visits.customer_id', '=', 'users.id')
            ->leftJoin('visit_exercise_rel','visits.id','=','visit_exercise_rel.visit_id')
            ->leftJoin('user_event_rel','visits.id','=','user_event_rel.visit_id')
            ->leftJoin('customers', function($q){
                $q->on('visits.customer_id', '=', 'customers.user_id');
                $q->where('customers.is_temp','=',0);
            })
            ->leftJoin('services', 'visits.service_id', '=', 'services.id')
            ->leftJoin('purchases', function($q) {
                $q->on('purchases.item_id', '=', 'visits.id');
                $q->where('visits.customer_subscription_id', '=',0);  
                $q->where('purchases.purchase_type', '=', 'visit');
                $q->where('purchases.is_refund', '=', 0);
            })
            ->where('visits.site_id','=', $this->userData['site'])
            ->where('visits.created_at', '>=', Carbon::now()->startOfday())
            ->where('visits.created_at', '<', Carbon::now()->endOfDay())       
            ->select('users.id',
                    'user_event_rel.id as user_event_rel_id',
                    'visit_exercise_rel.id as visit_exercise_id',
                    'visits.subscription_title as service_title',
                    'users.avatar',
                    DB::raw('IF(users.first_name is not null,CONCAT(users.first_name," ",users.last_name),"' . Lang::get("pages.dashboard.anon") . '") as full_name'),                     
                    'visits.created_at', 
                    'visits.id as vid', 
                    'visits.coach_id', 
                    'visits.customer_id',
                    'barcode', 
                    'customer_subscription_id',
                    'purchases.batch',
                    'visits.subscription_title as service_title_original')
            ->groupBy('visits.id')
            ->orderBy('visits.created_at', 'desc')
            ->get();   
       
        return Datatables::of($users)
            ->edit_column(
                'avatar',
                '@if($avatar)<img class="tooltipPic" title="<img src=\'/uploads/customer/avatar_big/{{$id}}/{{$avatar}}\'/>" src="/uploads/customer/avatar_small/{{$id}}/{{$avatar}}" alt=""/>@else<img src="{{url(Config::get(\'app.default_avatar_small\'))}}" alt=""/>@endif')
            ->edit_column(
                'created_at',
                '{{Carbon\Carbon::createFromFormat("Y-m-d H:i:s",$created_at)->format("H:i")}}')
            ->add_column(
                'actions',   
                '@if($customer_subscription_id == 0)
                <div class="btn-group">
                    <a class="btn btn-xs white" onclick="requestAccess(this)" data-type="refund" data-info="{{$batch}}"                                                                    
                       title="{{ Lang::get(\'common.buttons.refund\') }}" href="javascript:;">
                        <i class="fa fa-lg fa-times"></i>
                    </a>
                </div>
                @else
                <div class="btn-group">
                    <a class="btn btn-xs white" onclick="requestAccess(this)" data-type="unvisit" data-info="{{$vid}}" data-addinfo="{{$customer_id}}"                                                                  
                       title="{{ Lang::get(\'common.buttons.unvisit\') }}" href="javascript:;">
                        <i class="fa fa-lg fa-times"></i>
                    </a>
                </div>
                @endif')
            ->edit_column(
                'service_title',
                '<a href="/dashboard/getvisitdetail/{{$vid}}" data-dismiss="modal" data-target="#visitDetails" data-toggle="modal">{{$service_title}}</a>@if($coach_id > 0) <i class="icon-graduation"></i>@endif @if($user_event_rel_id > 0) <i class="icon-users"></i>@endif @if($visit_exercise_id > 0) <i class="icon-trophy"></i>@endif')
            ->make(true);
        
    }
      
    public function getPurchases() 
    {
        
        $results = Purchase::leftJoin('users', 'users.id', '=', 'purchases.user_id')
                    ->leftJoin('purchase_modifiers', 'purchase_modifiers.purchase_id', '=', 'purchases.id')
                    ->leftJoin('customers', function($q){
                        $q->on('users.id', '=', 'customers.user_id');
                        $q->where('customers.is_temp','=',0);
                    })
                    ->leftJoin('periodic_subscriptions', function ($q) {
                        $q->on('periodic_subscriptions.purchase_id','=','purchases.id');
                        $q->where('purchases.purchase_type', '=', 'subscription');
                    })
                    ->where('purchases.created_at', '>=', Carbon::now()->startOfday())
                    ->where('purchases.created_at', '<', Carbon::now()->endOfDay())
                    ->where('purchases.site_id', $this->userData['site'])                    
                    ->orderBy('purchases.created_at', 'desc')
                    ->select(
                        // the name with all the items in the purchase
                        DB::raw('CONCAT(GROUP_CONCAT("<span ", IF(is_refund=1, "class=\'purchaseRefundRow\'", ""), ">", IF(purchase_type != "subscription",CONCAT(purchases.quantity, " x ", purchases.item_title, " (", purchases.paid, " '.Lang::get('common.currency').')"),IF(periodic_subscriptions.customer_subscription_id is not null,CONCAT(purchases.item_title, " - период ",periodic_subscriptions.period_number, " (", purchases.price, " '.Lang::get('common.currency').')"),CONCAT(purchases.item_title, " (", purchases.price, " '.Lang::get('common.currency').')"))), "</span>"  SEPARATOR "<br />")) AS purchase'),
                        'purchases.purchase_type', 
                        'purchases.id as p_id', 
                        'purchases.item_id', 
                        'periodic_subscriptions.id as periodic_purchase_id', 
                        DB::raw('GROUP_CONCAT(purchases.payment_type SEPARATOR " ") as payment_type'),
                        'purchases.user_id',                         
                        'first_name', 
                        'last_name', 
                        'purchase_modifiers.purchase_id as modId', 
                        'barcode',
                        'batch',
                        // sum_price == total_price except when it was not paied but charged to clients wallet
                        DB::raw('SUM(paid) as sum_paid'), 
                        DB::raw('sum(purchases.price) as sum_price'), 
                        // last mtime of the purchase as purchase_time. If purchase has returns this will make it go to first place in table(it isordered by date)
                        DB::raw('max(purchases.created_at) as last_purchase_time'),
                        // clients name as href
                        DB::raw('IF(purchases.user_id is not null,CONCAT("<a href=\"/cart/", purchases.user_id, "\">",users.first_name," ",users.last_name,"</a>"),"' . Lang::get("pages.dashboard.anon") . '") as full_name'))
                    ->groupBy('batch')->get();

        foreach($results as $key => $purchase) { 
            if(!is_null($purchase->periodic_purchase_id)) { 
                $totalPeriods = 0;                      
                foreach(DB::table('periodic_subscriptions')->where('customer_subscription_id',$purchase->item_id)->get() as $key => $period) { 
                    if($period->purchase_id == $purchase->p_id) {                       
                        $purchase->payed_periods = $key + 1;
                        $purchase->period_start = $period->period_start;
                        $purchase->period_end = $period->period_end;
                    }
                    $totalPeriods++;
                }
                $purchase->total_periods = $totalPeriods;
            }         
        }
        
        return Datatables::of($results)
                ->remove_column('purchase_type')
                ->remove_column('batch')
                ->remove_column('user_id')
                ->remove_column('quantity')
                ->remove_column('item_title')
                ->remove_column('item_id')
                ->remove_column('paid')
                ->edit_column(
                    'last_purchase_time',
                    '{{Carbon\Carbon::createFromFormat("Y-m-d H:i:s",$last_purchase_time)->format("H:i")}}')
                ->edit_column(
                    'purchase',
                    '<div text-overflow: ellipsis; style="cursor:pointer" onclick="showPurchaseDetails(\'{{$batch}}\')"> 
                        {!! $purchase !!} @if($purchase_type == "subscription") <span class="font-red bold">(A)</span> @endif
                    </div>')
                ->edit_column(
                    'full_name',
                    '@if($purchase_type == "system"){!! str_replace("/cart/", "/admins/", $full_name) !!} @else {!!$full_name!!}@endif')
                ->make(true); 
    }
    
    public function getPurchaseSummary()
    {       
        $data = [
            'cash' => 0,
            'card' => 0,
            'none' => 0
        ];
        
        $data['wallet_enable'] = $this->siteSettings->getSettingByKeyBySite('wallet_enable', $this->userData['site']) == 'on' ? true : false;
        
        $result = Purchase::leftJoin('purchase_modifiers', 'purchases.id', '=', 'purchase_modifiers.purchase_id')
//                    ->where('site_id',$this->userData['site']) // view bills on any site
                    ->where('purchases.created_at', '>=', Carbon::now()->startOfday())
                    ->where('purchases.created_at', '<', Carbon::now()->endOfDay())
                    ->select('purchases.payment_type',
                            'purchases.quantity',
                            'purchases.single_price',
                            'purchases.paid',
                            'purchases.price',
                            'purchases.site_id',
                            'purchase_modifiers.amount',
                            'purchase_modifiers.units')
                    ->get();
        
        foreach($result as $key => $purchase) { 
            if($purchase->site_id == $this->userData['site']) {
                if($purchase->payment_type == 'cash') { 
                    $data['cash'] += $purchase->paid;
                } elseif($purchase->payment_type == 'card') { 
                    $data['card'] += $purchase->paid;
                }
            } 
            if($purchase->payment_type == 'none') { 
                $data['none'] += $purchase->price;
            }
            
        }
        
        return view('dashboard.partials.cash_today_summary', ['data' => $data])->render();
        
    }
    
    public function searchtype($type, $searchstring) 
    {
        
        $raw = explode(" ", preg_replace('/\s+/', ' ', $searchstring));
        
        $searchString = $raw[0];
        
        if($type == 'products') {
            $resultdb = \Models\Product::leftJoin('site_product_rels', function ($q) { 
                $q->on('products.id', '=', 'site_product_rels.product_id');
                $q->where('site_product_rels.site_id', '=', $this->userData['site']);
                $q->where('site_product_rels.for_sale', '=', 1);
            })   
            ->where('title', 'LIKE', '%' . $searchString . '%')
            ->select('id','title', 'price', 'for_sale', 'quantity');
            
            
            
        } elseif($type == 'services') { 
            $resultdb = \Models\Service::leftJoin('services_sites_rel', function ($q) { 
                    $q->on('services.id', '=', 'services_sites_rel.service_id');
                    $q->where('services_sites_rel.site_id', '=', $this->userData['site']);
                }) 
                ->where('title', 'LIKE', '%' . $searchString . '%')
                ->select('id','title', 'price', 'for_sale');
                
                
        } elseif($type == 'promotions') { 
             $resultdb = \Models\Promotion::leftJoin('promotion_site_rels', function ($q) { 
                    $q->on('promotions.id', '=', 'promotion_site_rels.promotion_id');
                    $q->where('promotion_site_rels.site_id', '=', $this->userData['site']);
                })
                ->where('title', 'LIKE', '%' . $searchString . '%')
                ->select('id','title', 'price', 'start_time', 'end_time', 'status');
        }
        
        return Datatables::of($resultdb)->make(true);
    }
    
    public function search($searchStringRaw, Customer $customer) 
    {        
        $raw = explode(" ", preg_replace('/\s+/', ' ', $searchStringRaw));
        
        if(count($raw) == 1) {
            $searchString = $raw[0];
            
            $resultdb = $customer::leftJoin('customers', 'users.id', '=', 'customers.user_id')
                ->where(function ($q) use ($searchString) {
                    if(ctype_digit($searchString)) {
                        $q->orWhere('phone_1', 'LIKE', '%' . $searchString . '%');
                        $q->orWhere('customers.barcode', '=', $searchString);
                    } else {                
                        $q->orWhere('first_name', 'LIKE', '%' . $searchString . '%'); 
                        $q->orWhere('last_name', 'LIKE', '%' . $searchString . '%');
                        $q->orWhere('middle_name', 'LIKE', '%' . $searchString . '%');
                    }
                 })
                ->whereNull('users.deleted_at')
                ->where('user_type', '=', $customer::TYPE_CUSTOMER)
                ->select('users.id', DB::raw('CONCAT(first_name, " ", middle_name, " ", last_name) as full_name'),'customers.barcode', 'phone_1' )
                ->groupBy('users.id')
                ->get();
                 
        } elseif (count($raw) > 1) { 
            $searchStringF = $raw[0];
            $searchStringS = $raw[1];
            
            if(ctype_digit($searchStringF) && ctype_digit($searchStringS)) {
                $combined = $searchStringF . $searchStringS;
                
                $resultdb = $customer::leftJoin('customers', 'users.id', '=', 'customers.user_id')                        
                    ->whereNull('users.deleted_at')
                    ->where('user_type', '=', $customer::TYPE_CUSTOMER)
                    ->where(function ($q) use ($combined) {
                        $q->where('phone_1', 'LIKE', '%', $combined . '%');
                    })
                    ->select('users.id', DB::raw('CONCAT(first_name, " ", last_name) as full_name'),'customers.barcode', 'phone_1' )
                    ->groupBy('users.id')
                    ->get();
                
            } else {                 
                $resultdb = $customer::leftJoin('customers', 'users.id', '=', 'customers.user_id')
                    ->whereNull('users.deleted_at')
                    ->where('users.user_type', '=', $customer::TYPE_CUSTOMER)
                    ->where(function($q) use($searchStringF,$searchStringS) {

                        $q->orWhere(function($y) use($searchStringF,$searchStringS){
                            $y->where('first_name', 'LIKE', '%' . $searchStringF . '%'); 
                            $y->where('last_name', 'LIKE' , '%' . $searchStringS . '%');
                        });

                        $q->orWhere(function($s) use($searchStringF, $searchStringS) {
                            $s->where('first_name', 'LIKE', '%' . $searchStringF . '%'); 
                            $s->where('phone_1', 'LIKE' , '%' . $searchStringS . '%');
                        });
                    })
                    ->select('users.id', DB::raw('CONCAT(first_name, " ", last_name) as full_name'),'customers.barcode', 'phone_1' )
                    ->groupBy('users.id')
                    ->get();                                
            }
        } 

        return Datatables::of($resultdb)->make(true);
        
    }
   
    public function getVisitDetail($visitId)
    {
        $visit = Visit::where('visits.id', $visitId)
                ->leftJoin('blacklist',function($q){
                    $q->on('visits.customer_id','=','blacklist.user_id');
                    $q->on('visits.id','=','blacklist.type_id');
                    $q->where('blacklist.type','=','visit');
                })
                ->leftJoin('users as blackop','blacklist.created_by','=','blackop.id')
                ->leftJoin('users as operator', 'visits.created_by', '=', 'operator.id') 
                ->leftJoin('users as customer', 'visits.customer_id', '=', 'customer.id') 
                ->leftJoin('customers', function($q){
                    $q->on('customer.id', '=', 'customers.user_id');
                    $q->where('customers.is_temp','=',0);
                })
                ->leftJoin('users as coach', 'visits.coach_id', '=', 'coach.id') 
                ->leftJoin('user_event_rel', 'visits.id', '=', 'user_event_rel.visit_id')
                ->leftJoin('events', 'user_event_rel.event_id', '=', 'events.id')
                ->leftJoin('instructor_event_rel', 'user_event_rel.event_id', '=', 'instructor_event_rel.event_id')
                ->leftJoin('users as instructor', 'instructor_event_rel.user_id', '=', 'instructor.id')
                ->leftJoin('services', 'visits.service_id', '=', 'services.id')
                ->leftJoin('services_sites_rel', function($q){
                    $q->on('services.id', '=', 'services_sites_rel.service_id');
                    $q->on('services_sites_rel.site_id', '=', 'visits.site_id');
                })
                ->leftJoin('customer_subscription_rel', 'visits.customer_subscription_id', '=', 'customer_subscription_rel.id')
                ->leftJoin('sites','visits.site_id', '=', 'sites.id')
                ->leftJoin('purchases', function ($q){
                    $q->on('visits.id','=','purchases.item_id');
                    $q->where('purchases.purchase_type','=','visit');
                })
                ->select(
                        'blacklist.reason',
                        'blacklist.updated_at as black_updated',
                        'blackop.username as blackop_username',
                        'blackop.id as blackop_id',
                        'visits.subscription_title', 
                        'visits.id as visit_id',
                        'visits.customer_subscription_id',
                        DB::raw('CONCAT(customer.first_name, " ", customer.last_name) as customer_name'),
                        DB::raw('GROUP_CONCAT(instructor.first_name, " ", instructor.last_name) as instructor_name'),
                        DB::raw('CONCAT("<a href=\"/admins/",operator.id,"\">",operator.username,"</a>") as username'),
                        DB::raw('CONCAT(coach.first_name, " ", coach.last_name) as coach_name'),
                        DB::raw('IF(services_sites_rel.happy_hour_start < DATE_FORMAT(visits.created_at, "%H:%i") and happy_hour_end > DATE_FORMAT(visits.created_at, "%H:%i") and INSTR(happy_hour_days, (weekday(visits.created_at) + 1)),services_sites_rel.happy_hour_price,"") as happy_price'),                        
                        'user_event_rel.id as user_event_rel_id',
                        'services.title as service_title',
                        'customer_subscription_rel.title as customer_subscription_title',
                        'visits.created_at as visit_time',
                        'events.start as event_start',
                        'events.end as event_end',
                        'sites.title as site_title',
                        'purchases.paid',
                        'purchases.payment_type',
                        'purchases.created_at as purchase_time',
                        'purchases.batch',
                        'customer.avatar',
                        'customer.phone_1',
                        'customer.phone_2',
                        'customer.email',
                        'customer.created_at as customer_create_time',
                        'customers.barcode',
                        'customer.id as customer_id',
                        'services_sites_rel.price as real_price')
                ->groupBy('visits.id')
                ->first();
        
        $data['visit'] = $visit;
        $data['exercises'] = (new \Models\Exercise())->get();
        $data['performed_exercises'] = DB::table('visit_exercise_rel')
                ->join('exercises','visit_exercise_rel.exercise_id','=','exercises.id')
                ->leftJoin('visit_exercise_confirmed_rel','visit_exercise_rel.id','=','visit_exercise_confirmed_rel.visit_exercise_id')                
                ->where('visit_id','=',$visitId)
                ->select(
                    'exercises.title',
                    'visit_exercise_rel.id as visit_exercise_id',
                    'visit_exercise_rel.exercise_id',
                    'visit_exercise_rel.sets',
                    'visit_exercise_rel.reps',
                    'visit_exercise_rel.weight',
                    'visit_exercise_rel.time',
                    DB::raw('COUNT(visit_exercise_confirmed_rel.confirmed_by) as confirmed_count'))
                ->groupBy('visit_exercise_rel.id')
                ->get();                

        //fix note js new line
        if(!is_null($data['visit']->reason)) $data['visit']->reasonjs = preg_replace('/\n/', '\n\\ ', $data['visit']->reason);
        
        return view('dashboard.partials.visit_detail_modal', ['data' => $data]);
        
    }
   
    /*
     * Modal for showing reserved,but not yet performed visits
     */
    public function getBookedEventDetail($userEventRelId)
    {

        $reservation = DB::table('user_event_rel')
                ->leftJoin('blacklist',function($q){
                    $q->on('user_event_rel.user_id','=','blacklist.user_id');
                    $q->on('user_event_rel.id','=','blacklist.type_id');
                    $q->where('blacklist.type','=','reservation');
                })
                ->leftJoin('users as blackop','blacklist.created_by','=','blackop.id')
                ->join('users','user_event_rel.user_id','=','users.id')
                ->join('events','user_event_rel.event_id','=','events.id')
                ->leftJoin('sites','events.site_id','=','sites.id')
                ->leftJoin('instructor_event_rel', 'user_event_rel.event_id', '=', 'instructor_event_rel.event_id')
                ->leftJoin('users as instructor', 'instructor_event_rel.user_id', '=', 'instructor.id')
                ->where('user_event_rel.id','=',$userEventRelId)
                ->select(
                        'blacklist.reason',
                        'blacklist.updated_at as black_updated',
                        'blackop.username as blackop_username',
                        'blackop.id as blackop_id',
                        DB::raw('CONCAT(users.first_name," ",users.last_name) as customer_name'),
                        'users.id as customer_id',
                        'users.avatar',
                        'users.phone_1',
                        'users.phone_2',
                        'users.email',
                        'sites.title as site_title',
                        'events.title as event_title',
                        'events.current_people',
                        'events.people',
                        'events.start as event_start',
                        'events.end as event_end',
                        'user_event_rel.id as user_event_rel_id',
                        'user_event_rel.booked_at',
                        DB::raw('GROUP_CONCAT(instructor.first_name, " ", instructor.last_name SEPARATOR ", ") as instructor_names')
                        )
                ->groupBy('user_event_rel.id')
                ->first();
                
        $data['reservation'] = $reservation;
        $data['exercises'] = (new \Models\Exercise())->get();
        $data['performed_exercises'] = DB::table('visit_exercise_rel')
                ->join('exercises','visit_exercise_rel.exercise_id','=','exercises.id')
                ->leftJoin('visit_exercise_confirmed_rel','visit_exercise_rel.id','=','visit_exercise_confirmed_rel.visit_exercise_id')
                ->where('user_event_id','=', $userEventRelId)
                ->select(
                    'exercises.title',
                    'visit_exercise_rel.id as visit_exercise_id',
                    'visit_exercise_rel.exercise_id',
                    'visit_exercise_rel.sets',
                    'visit_exercise_rel.reps',
                    'visit_exercise_rel.weight',
                    'visit_exercise_rel.time',
                    DB::raw('COUNT(visit_exercise_confirmed_rel.confirmed_by) as confirmed_count'))
                ->groupBy('visit_exercise_rel.id')
                ->get();    
        
        //fix note js new line
        if(!is_null($data['reservation']->reason)) $data['reservation']->reasonjs = preg_replace('/\n/', '\n\\ ', $data['reservation']->reason);
        
        return view('dashboard.partials.reservation_detail_modal', ['data' => $data]);
    }
         
    public function getLastClients() { 
        
        $data['clients'] = Visit::leftJoin('users', 'visits.customer_id', '=', 'users.id')
            ->leftJoin('customers', function($q){
                $q->on('visits.customer_id', '=', 'customers.user_id');
                $q->where('customers.is_temp','=',0);
            })
            ->where('visits.customer_id', '>', 0)
            ->where('visits.site_id','=', $this->userData['site'])
            ->where('visits.created_at', '>', DB::raw('DATE_SUB(NOW(), INTERVAL 3 HOUR)'))
            ->select('visits.created_at', 'users.id','customers.barcode','users.first_name','users.last_name','users.avatar', 'visits.subscription_title')
            ->groupBy('visits.customer_id')
            ->orderBy('visits.created_at', 'desc')
            ->get();   
       
        
        return \View::make('cart.partials.last_clients_modal', ['data' => $data]);
        
    }

}
