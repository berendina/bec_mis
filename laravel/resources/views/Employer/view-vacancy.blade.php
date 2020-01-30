@extends('layouts.main')
@section('content')
<div class="container" >
	<section class="content-header">
 
        <div class="row">
          <div class="col-sm-6">
            
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{Route('vacancies')}}">All Vacancies</a></li>
              <li class="breadcrumb-item active">{{$vacancy->title}}</li>
            </ol>
          </div>
        </div>
      
    </section>
    <div class="card" style="margin-top: 10px">
        <div class="card-header">
        	<div class="row">
        		
        		<div class="col-md-6">
        			<h2 class="card-title">{{$vacancy->title}} - {{$vacancy->location}}</h2> 
        			<p class="text-muted"> @cannot('youth') at <span class="text-success">{{$vacancy->employer->name}}</span> @endcannot<br><small><i class="fas fa-clock"></i> {{ $vacancy->created_at }}</small></p>

        		</div>
        		@can('apply-vacancy')
        		<div class="col-md-6 ">
        			<div class="col text-right">
        				<div class="form-group"	>
                  {{csrf_field()}}
        					<i style="display:none" id="loading" class="fa fa-spinner fa-lg faa-spin animated"></i> &nbsp;&nbsp;<button class="btn btn-primary btn-flat" data-id="{{$vacancy->id}}" id="apply-job"> <i class="fas fa-calendar-check nav-icon"> </i>&nbsp;&nbsp; Apply for this Job</button>
        				</div>
        			</div>
        		</div>
                @endcan
        	</div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">  
           <div class="row">
           		<div class="col-md-2 text-dark-grey">
           			<div class="nav-item">
           				Job Location
           			</div>
           			<div class="nav-item">
           				Employer / Company
           			</div>
           			<div class="nav-item">
           				Job Type
           			</div>
           			<div class="nav-item">
           				Business Function
           			</div>
           			<div class="nav-item">
           				Employer Industry
           			</div>
           			<div class="nav-item">
           				Salary
           			</div>	
           		</div>
           		<div class="col-md-4 text-muted">
           			<div class="nav-item">
           				{{ $vacancy->location }}
           			</div>
           			<div class="nav-item">
           				{{ $vacancy->employer->name }}
           			</div>
           			<div class="nav-item">
           				{{ $vacancy->job_type }}
           			</div>
           			<div class="nav-item">
           				{{ $vacancy->business_function }}
           			</div>
           			<div class="nav-item">
           				{{ $vacancy->employer->industry }}
           			</div>
           			<div class="nav-item">
           				@if($vacancy->salary){{ $vacancy->salary }}
           				@else
           				{{ "not mentioned" }}
           				@endif
           				
           			</div>	
           		</div>
           		<div class="col-md-3">
           			<div class="nav-item">
           				Total Vacancies
           			</div>
           			<div class="nav-item">
           				Min. Qualification
           			</div>
           			<div class="nav-item">
           				Educational Specialization
           			</div>
           			<div class="nav-item">
           				Required Skills
           			</div>
           			<div class="nav-item">
           				Gender Preferance
           			</div>
           			<div class="nav-item">
           				Closing Date
           			</div>	
           		</div>
           		<div class="col-md-3 text-muted">
           			<div class="nav-item">
           				@if($vacancy->total_vacancies){{ $vacancy->total_vacancies }}
           				@else
           				{{ "not mentioned" }}
           				@endif
           				
           			</div>
           			<div class="nav-item">
           				{{ $vacancy->min_qualification }}
           			</div>
           			<div class="nav-item">
           				{{ $vacancy->specializaion }}
           			</div>
           			<div class="nav-item">
           				@if($vacancy->skills){{ $vacancy->skills }}
           				@else
           				{{ "not mentioned" }}
           				@endif
           			</div>
           			<div class="nav-item">
           				{{ $vacancy->gender }}
           			</div>
           			<div class="nav-item">
           				{{ $vacancy->dedline }}
           			</div>	
           		</div>	
           </div>	      		
            
        </div>
        <div class="card" style="margin-top: 10px">
        
        <!-- /.card-header -->
        <div class="card-body">  
           <div class="row">
           		<div class="col-md-6 text-dark">
           			<h5>Job Description</h5>
           			<p class="text-muted">	{{ $vacancy->description }}</p>	
           		</div>
           		
           </div>	      		
            
        </div>
    </div> 
</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function(){
     $(document).on('click' , '#apply-job' ,function (){
        var id = $(this).data('id');
        $.ajax({
            type: 'POST',
            url: SITE_URL + '/vacancy/apply',
                      
            data: {
                '_token': $('input[name=_token]').val(),
                'id': id
            },
            beforeSend: function(){
              $('#loading').show();
            },
            complete: function(){
              $('#loading').hide();
            },          
            success: function(data) {
              if($.isEmptyObject(data.error)){              
              toastr.success('Succesfully apply for the vacancy ! ', 'Congratulations', {timeOut: 5000});
            }
            else{
            toastr.error('Error !', ""+data.error+"");
              
            }         
        },

            error: function (jqXHR, exception) {    
                console.log(jqXHR);
                toastr.error('Error !', 'Something Error')
            },
        });
    });
  });

  @if (session('error'))
  toastr.error('{{session('error')}}')
  @endif

</script>
@endsection