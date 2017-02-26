@extends('index')

@section('content')
<div class="page-container">
    <div class="page-content-wrapper">
        <div class="page-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light">
                        <div class="portlet-body">
                            <div class="table-container">
                                <div class="row">
                                    {{-- avatar area --}}
                                    <div class="col-md-2">
                                        <img src="{{ $data['item']->avatar }}" class="img-responsive" alt=""/>                                    
                                    </div>
                                    {{-- end avatar area --}}
                                    {{-- client info area --}}
                                    <div class="col-md-7">
                                        <div style="margin-left: -40px; margin-right: -40px; margin-top: -10px;" class="row">
                                            <div class="col-md-12 profile-info">
                                                <div class="inline-headers">
                                                    <h4 style="display: inline-block">
                                                        {{ $data['item']->first_name . ' ' .
                                                        $data['item']->middle_name . ' ' .
                                                        $data['item']->last_name}} 
                                                    </h4> 
                                                </div>

                                                <ul class="list-inline">                                                    
                                                    <li>
                                                        @if (!empty($data['item']->phone_1))
                                                        <i class="fa fa-phone"></i>
                                                        {{ $data['item']->phone_1 }}
                                                        @endif
                                                        @if (!empty($data['item']->phone_2))
                                                            | {{ $data['item']->phone_2 }}
                                                        @endif
                                                        @if (!empty($data['item']->email) && !preg_match('/@example.com$/',$data['item']->email))
                                                        <i class="fa fa-envelope"></i>
                                                        {{ $data['item']->email }}
                                                        @endif
                                                        <i class="fa fa-home"></i>
                                                        {{ App\TextFormat::fromDbToDisplayDate($data['item']->created_at) }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- end client info area --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light">
                        <div class="portlet-body">
                            <div class="tabbable-line">
                                <ul class="nav nav-tabs">
                                    <li class="active">
                                        <a href="#tab-foods" data-toggle="tab">Списък храни</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    {{-- TAB FOODS --}}
                                    <div class="tab-pane active" id="tab-foods">
                                        <div class="table-container">
                                            <table class="table table-bordered table-hover" id="foods-list-table">
                                                <thead>
                                                <tr role="row" class="heading">
                                                    <th width="35%">Име</th>
                                                    <th>Количество</th>
                                                    <th>Дата</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                   
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</div> 
@endsection

@section('scripts')
@parent
<script src="assets/js/datatables.min.js" type="text/javascript" ></script>

<script>
    
</script>
@stop
    