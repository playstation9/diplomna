@extends('index')
@section('content')

<div class="col-md-12" style="margin-top: 20px;">
    <form method="POST"
          enctype="multipart/form-data"
          action="{{ action('CustomerController@store') }}"
          class="form-horizontal form-bordered form-row-stripped">

        {{ csrf_field() }}
        
        <div class="form-body">
            @if(Session::has('validation_errors'))
            <div class="alert alert-danger">
                <ul>
                @foreach(Session::has('validation_errors') as $key => $error)
                <li>{{$error}}</li>    
                @endforeach
                </ul>
            </div>
            @endif
            
            {{-- NAMES --}}
            <div class="form-group">
                <label class="col-md-1 control-label" for="title">
                    Имена
                </label>

                <div class="col-md-2">
                    <input type="text"
                           name="first_name"
                           class="form-control"
                           placeholder="Име"
                           value=""/>
                </div>
                <div class="col-md-2">
                    <input type="text"
                           name="middle_name"
                           class="form-control"
                           placeholder="Презиме"
                           value=""/>
                </div>
             

                <div class="col-md-2">
                    <input type="text"
                           name="last_name"
                           class="form-control"
                           placeholder="Фамилия"
                           value=""/>
                </div>
            </div>                                            

            {{-- GENDER --}}
            <div class="form-group">
                <label class="col-md-1 control-label" for="gender">
                    Пол
                </label>
                <div class="col-md-2">
                    <select name="gender" class="form-control">
                        <option value="male">{{ Lang::get('common.male') }}</option>
                        <option value="female">{{ Lang::get('common.female') }}</option>
                    </select>
                </div>
            </div>
            
            {{-- PHONES --}}
            <div class="form-group">
                <label class="col-md-1 control-label" for="phone_1">
                    Телефон
                </label>

                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-phone"></i>
                        </span>
                        <input type="text" name="phone_1" class="form-control" placeholder="Телефон 1"/>
                    </div>
                </div>
                 <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-phone"></i>
                        </span>
                        <input type="text" name="phone_2" class="form-control" placeholder="Телефон 2"/>
                    </div>
                </div>
            </div>
            
            {{-- EMAIL --}}
            <div class="form-group">
                <label class="col-md-1 control-label" for="email">
                    Имейл
                </label>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-envelope"></i>
                        </span>
                        <input name="email" type="email" class="form-control" placeholder="Имейл">
                    </div>
                </div>
            </div>
            
            {{-- NEW PASSWORD --}}
            <div class="form-group">
                <label class="col-md-1 control-label" for="new_password">
                    Парола
                </label>

                <div class="col-md-2">
                    <input type="password"
                           name="password"
                           placeholder="Парола"
                           class="form-control"/>
                </div>
                <div class="col-md-2">
                    <input type="password"
                           name="password_confirmed"
                           placeholder="Повтори парола"
                           class="form-control"/>
                </div>
            </div>
            <span class="help-inline"></span>
        </div>
        <button class="btn btn-default btn-primary pull-right">Запази</button>
    </form>
</div>

@section('scripts')
@parent
<script>
    $(document).ready(function(){
        
    });
</script>
@stop

@stop
   