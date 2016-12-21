@extends('layout')

@section('content')
<link href="{{ asset('/css/vl2.css') }}" rel="stylesheet">
<?php 
$facility_id = \Request::has('f')?'&f='.\Request::get('f'):"";
if($printed=='YES'){
    $printed_actv="class=active";
    $print_actv="";
}else{
    $print_actv="class=active";
    $printed_actv="";
}

$print_url="/results_list?printed=NO$facility_id";
$printed_url="/results_list?printed=YES$facility_id";
?>

<div style="text-align:center;text-decoration: underline;" class='print-ttl'>{{session('facility')}}</div>
<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li {{ $print_actv }} title='Print'><a href="{!! $print_url !!}" >Print</a></li>
    <li {{ $printed_actv }} title='Already printed'><a href="{!! $printed_url !!}" >Printed</a></li>
</ul>
{!! Form::open(array('url'=>'/result','id'=>'view_form', 'name'=>'view_form', 'target' => 'Map' )) !!}

{!! Form::hidden('printed', $printed) !!}

<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active" id="print">  
        {!! MyHTML::submit('Download selected','btn  btn-sm btn-danger','pdf') !!}
        <input type="button" class='btn btn-sm btn-danger' value="Print preview selected" onclick="viewSelected();"   /> 
        
        <table id="results-table" class="table table-condensed table-bordered" style="max-width:1100px">
            <thead>
            <tr>
                <th>Select</th>               
            	<th>Form Number</th>
                <th>Art Number</th>
                <th>Other ID</th>
                <th>Date of collection</th>
                <th>Date received</th>
                <th>Released on</th>
                @if($printed=='YES')
                 <th>Print date/time</th>
                 <th>Printed by</th>
                @endif

                <th>Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
  

{!! Form::close() !!}
<script type="text/javascript">

$('#results').addClass('active');

$(function() {
    $('#results-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! url("/results_list/data?printed=$printed$facility_id") !!}',
        columns: [
            {data: 'sample_checkbox', name: 'sample_checkbox', orderable: false, searchable: false},
            {data: 'formNumber', name: 'formNumber'},
            {data: 'artNumber', name: 'artNumber'},
            {data: 'otherID', name: 'otherID'},
            {data: 'collectionDate', name: 'collectionDate'},
            {data: 'receiptDate', name: 'receiptDate'},
            {data: 'qc_at', name: 'qc_at'},     
            @if($printed=='YES')
                {data: 'printed_at', name: 'printed_at'},
                {data: 'printed_by', name: 'printed_by'},                 
            @endif       
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });
});

function viewSelected() {     
   var mapForm = document.getElementById("view_form");
   map = window.open("","Map","width=1100,height=1000,menubar=no,resizable=yes,scrollbars=yes");
   //map=window.open("","Map","status=0,title=0,height=600,width=800,scrollbars=1");

   if (map) {
      mapForm.submit();
   } else {
      alert('You must allow popups for this map to work.');
   }
}
</script>
@endsection()