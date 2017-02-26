@extends('index')
@section('content')
<div class="row">
    <div class="col-lg-12">
        <h2 class="page-header">{{Lang::get('pages.dashboard.title')}}</h2>
    </div>
</div>

<div class="col-md-12">
    <div class="btn-toolbar">        
        {{-- SEARCH --}}
        <input style="margin-bottom: 5px;" id="clientSearch" type="text" placeholder="{{ Lang::get('pages.dashboard.search_clients') }}..." class="form-control input-small input-inline" />
    </div>
    <div style="max-height: 400px; overflow-y: auto;">
        <table class="table table-condensed">
            <thead>
            <tr>
                <th>Име</th>
                <th>Имейл</th>
                <th>Дата</th>
            </tr>
            </thead>
            <tbody>
                @foreach($data['customers'] as $key => $value)
                <tr class="rowFilter">
                    <td><a href="/customers/{{$value->id}}">{{$value->first_name . ' ' . $value->last_name}}</a></td>
                    <td>{{$value->email}}</td>
                    <td>{{Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value->created_at)->format('d.m.Y H:i')}}</td>                    
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@section('scripts')
@parent
<script>
    $(document).ready(function(){
        $("#clientSearch").keyup(function () {
            var query = this.value;
            $('.rowFilter').hide().filter(function () {
                return $(this).has('td:contains("' + query + '")').length > 0;
            }).show();
        }); 
    });

    
</script>
@stop
    
    
    
@stop