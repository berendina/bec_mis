<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Auth;

class CourseSupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
    	$districts = DB::table('districts')->get();
    	$managers = DB::table('branches')->select('manager')->distinct()->get();
    	$activities = DB::table('activities')->get();
    	return view('Activities.skill-development.course-support')->with(['districts'=> $districts,'managers'=>$managers,'activities'=>$activities]);
    }

    public function instituesList(Request $request){
    	if($request->get('query')){
          $query = $request->get('query');
          $data = DB::table('institutes')
            ->where('name', 'LIKE', "%{$query}%")
            ->get();
          $output = '<ul class="dropdown-menu" id="institute" style="display:block; position:relative">';
          foreach($data as $row)
          {
           $output .= '
           <li class="nav-item" id="'.$row->id.'"><a href="#" >'.$row->name.' | '.$row->location.'</a></li>
           ';
          }
          $output .= '</ul>';
          echo $output;
         }
    }

    public function courseList(Request $request){
    	if($request->get('query')){
          $query = $request->get('query');
          $data = DB::table('courses')
            ->where('name', 'LIKE', "%{$query}%")
            ->get();
          $output = '<ul class="dropdown-menu" id="course" style="display:block; position:relative">';
          foreach($data as $row)
          {
           $output .= '
           <li class="nav-item" id="'.$row->id.'"><a href="#" >'.$row->name.'</a></li>
           ';
          }
          $output .= '</ul>';
          echo $output;
         }
    }

    public function youthList(Request $request){
      $branch_id = auth()->user()->branch;

    	if($request->get('query')){
          $query = $request->get('query');
          $data = DB::table('youths')
            ->where('branch_id', '=',$branch_id)
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('nic', 'LIKE', "%{$query}%")
            
            ->get();
          $output = '<ul class="dropdown-menu" id="youths" style="display:block; position:relative">';
          foreach($data as $row)
          {
           $output .= '
           <li class="nav-item" id="'.$row->id.'"><a href="#" >'.$row->name.' | '.$row->nic.'</a></li>
           ';
          }
          $output .= '</ul>';
          echo $output;
         }
    }

    public function insert(Request $request){
    	$validator = Validator::make($request->all(),[
    		    'program_date'  => 'required',
    		    'total_male'  => 'required',
    		    'total_female'  => 'required',
                'district' => 'required',
                'dm_name' =>'required',	
                'review_report' => 'required',
                'institute_id' => 'required',
                'course_id' => 'required',
            ]);

            if($validator->passes()){
                $branch_id = auth()->user()->branch;
                $input = $request->all();
                
                $data1 = array(
                	'district' => $request->district,
                	'dsd' => $request->dsd,
	                'dm_name' =>$request->dm_name,
	                'title_of_action' =>json_encode($request->title_of_action),  
                    'activity_code' =>json_encode($request->activity_code),	
	                'program_date'	=>$request->program_date,
	                'start_date'=>$request->start_date,
	                'end_date' =>$request->end_date,
	                'institute_id'	=>$request->institute_id,
	                'institutional_review' =>$request->institutional_review,
	                'total_male' => $request->total_male,
	                'total_female'=>$request->total_female,
	                'pwd_male'=>$request->pwd_male,
	                'pwd_female'=>$request->pwd_female,
	                'course_id'=>$request->course_id,
	                'review_report' => $request->review_report,
	                'branch_id'	=> $branch_id,
                  'created_at' => date('Y-m-d H:i:s')
                );

                $total_youth = ($request->total_male+$request->total_female);
                $number = count($request->youth_id);
                //echo "<script>console.log('Debug Objects: " . $number . "' );</script>";
                if($total_youth==$number){
                //insert general data 
                $course_support = DB::table('course_supports')->insert($data1);
                $course_support_id = DB::getPdo()->lastInsertId();

                //insert youths
                if($number>0){
                    for($i=0; $i<$number; $i++){
                        $participants = DB::table('course_supports_youth')->insert(['youth_id'=>$request->youth_id[$i],'nature_of_support'=>$request->nature_of_support[$i],'institute_type'=> $request->institute_type[$i],'course_support_id'=>$course_support_id]);
                    }

                }
                else{
                    return response()->json(['error' => 'Submit youth details.']);
                }
              }
              else{
                    return response()->json(['error' => 'Youth details are mismatched']);

              }
            }
            else{
                return response()->json(['error' => $validator->errors()->all()]);
            }
    
    }

    public function reviewList(Request $request){

      if($request->get('query')){
          $query = $request->get('query');
          $data = DB::table('institutes')
                  ->join('institute_reviews','institute_reviews.institute_id','=','institutes.id')
                  ->where('institutes.name', 'LIKE', "%{$query}%")
                  ->select('institutes.*','institute_reviews.id as ins_id','institute_reviews.*')
                  ->get();
          $output = '<ul class="dropdown-menu" id="review_reports" style="display:block; position:relative">';
          foreach($data as $row)
          {
           $output .= '
           <li class="nav-item" id="'.$row->ins_id.'"><a href="#" >'.$row->name.' | '.$row->district.'</a></li>
           ';
          }
          $output .= '</ul>';
          echo $output;
         }
    }

    public function view(){
        $branch_id = Auth::user()->branch;
        if(is_null($branch_id)){
        $meetings = DB::table('course_supports')
                      //->leftjoin('mentoring_gvt_officials','mentoring_gvt_officials.mentoring_id','=','mentoring.id')
                      ->join('branches','branches.id','=','course_supports.branch_id')
                      ->get();
        //dd($mentorings);

        $participants2018 = DB::table('course_supports')                        
                        ->select(DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female"),DB::raw("SUM(pwd_male) as pwd_male"),DB::raw("SUM(pwd_female) as pwd_female"))
                        ->where(DB::raw('YEAR(program_date)'), '=', '2018' )
                        ->first();
                        //->groupBy(function ($val) {
                                // Carbon::parse($val->meeting_date)->format('Y');
                        //});
                        //->groupBy(DB::raw("year(meeting_date)"))
                        
           $participants2019 = DB::table('course_supports')                        
                        ->select(DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female"),DB::raw("SUM(pwd_male) as pwd_male"),DB::raw("SUM(pwd_female) as pwd_female"))
                        ->where(DB::raw('YEAR(program_date)'), '=', '2019' )
                        ->first();            
            $participants2020 = DB::table('course_supports')                        
                        ->select(DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female"),DB::raw("SUM(pwd_male) as pwd_male"),DB::raw("SUM(pwd_female) as pwd_female"))
                        ->where(DB::raw('YEAR(program_date)'), '=', '2020' )
                        ->first();   
            $participants2021 = DB::table('course_supports')                        
                        ->select(DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female"),DB::raw("SUM(pwd_male) as pwd_male"),DB::raw("SUM(pwd_female) as pwd_female"))
                        ->where(DB::raw('YEAR(program_date)'), '=', '2021' )
                        ->first();   

            $courses = DB::table('course_supports')
                   ->join('courses','courses.id','=','course_supports.course_id')
                   ->select('courses.name as course_name','courses.*')
                   ->distinct()
                   ->get();
        $institutes = DB::table('course_supports')
                   ->join('institutes','institutes.id','=','course_supports.institute_id')
                   ->select('institutes.name as institute_name','institutes.*')
                   ->distinct()
                   ->get();        
        //dd($participants2018);
        }
        else{
          $meetings = DB::table('course_supports')
                      //->leftjoin('mentoring_gvt_officials','mentoring_gvt_officials.mentoring_id','=','mentoring.id')
                      ->join('branches','branches.id','=','course_supports.branch_id')
                      ->where('course_supports.branch_id','=',$branch_id)
                      ->get();
        //dd($mentorings);

        $participants2018 = DB::table('course_supports')                        
                        ->select(DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female"),DB::raw("SUM(pwd_male) as pwd_male"),DB::raw("SUM(pwd_female) as pwd_female"))
                        ->where(DB::raw('YEAR(program_date)'), '=', '2018' )
                      ->where('course_supports.branch_id','=',$branch_id)
                        ->first();
                        //->groupBy(function ($val) {
                                // Carbon::parse($val->meeting_date)->format('Y');
                        //});
                        //->groupBy(DB::raw("year(meeting_date)"))
                        
           $participants2019 = DB::table('course_supports')                        
                        ->select(DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female"),DB::raw("SUM(pwd_male) as pwd_male"),DB::raw("SUM(pwd_female) as pwd_female"))
                        ->where(DB::raw('YEAR(program_date)'), '=', '2019' )
                      ->where('course_supports.branch_id','=',$branch_id)
                        ->first();            
            $participants2020 = DB::table('course_supports')                        
                        ->select(DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female"),DB::raw("SUM(pwd_male) as pwd_male"),DB::raw("SUM(pwd_female) as pwd_female"))
                        ->where(DB::raw('YEAR(program_date)'), '=', '2020' )
                      ->where('course_supports.branch_id','=',$branch_id)
                        ->first();   
            $participants2021 = DB::table('course_supports')                        
                        ->select(DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female"),DB::raw("SUM(pwd_male) as pwd_male"),DB::raw("SUM(pwd_female) as pwd_female"))
                        ->where(DB::raw('YEAR(program_date)'), '=', '2021' )
                      ->where('course_supports.branch_id','=',$branch_id)
                        ->first();  
            $courses = DB::table('course_supports')
                   ->join('courses','courses.id','=','course_supports.course_id')
                   ->select('courses.name as course_name','courses.*')
                  ->where('course_supports.branch_id','=',$branch_id)
                   ->distinct()
                   ->get();
        $institutes = DB::table('course_supports')
                   ->join('institutes','institutes.id','=','course_supports.institute_id')
                   ->select('institutes.name as institute_name','institutes.*')
                   ->distinct()
                   ->where('course_supports.branch_id','=',$branch_id)
                   ->get();

        }
        $branches = DB::table('branches')->get();

        
        return view('Activities.Reports.Skill-Development.gvt-support')->with(['meetings'=>$meetings,'branches'=>$branches,'participants2018'=>$participants2018,'participants2019'=>$participants2019,'participants2020'=>$participants2020,'participants2021'=>$participants2021,'courses'=>$courses,'institutes'=>$institutes]);
    }

     public function fetch(Request $request){
        if($request->ajax())
        {
            if($request->dateStart != '' && $request->dateEnd != '')
            {
                $branch_id = Auth::user()->branch;
                
                if($request->branch!='' && $request->course='' && $request->institute =''){
                    $data = DB::table('course_supports') 
                      ->join('branches','branches.id','=','course_supports.branch_id')
                      ->join('institutes','institutes.id','=','course_supports.institute_id')
                      ->join('courses','courses.id', '=' ,'course_supports.course_id')
                      ->whereBetween('program_date', array($request->dateStart, $request->dateEnd))
                      ->where('branch_id',$request->branch)
                      ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                      ->orderBy('program_date', 'desc')
                      ->get();  

                  }

                  if($request->branch!='' && $request->course!='' && $request->institute =''){
                  $data = DB::table('course_supports') 
                    ->join('branches','branches.id','=','course_supports.branch_id')
                    ->join('institutes','institutes.id','=','course_supports.institute_id')
                    ->join('courses','courses.id', '=' ,'course_supports.course_id')
                    ->where('course_supports.branch_id',$request->branch)
                    ->where('course_supports.course_id',$request->course)
                    ->whereBetween('program_date', array($request->dateStart, $request->dateEnd))
                    
                    ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                    ->orderBy('program_date', 'desc')
                    ->get();    
                  }

                  if($request->branch!='' && $request->institute !='' && $request->course=''){
                  $data = DB::table('course_supports') 
                    ->join('branches','branches.id','=','course_supports.branch_id')
                    ->join('institutes','institutes.id','=','course_supports.institute_id')
                    ->join('courses','courses.id', '=' ,'course_supports.course_id')
                    ->whereBetween('program_date', array($request->dateStart, $request->dateEnd))
                    ->where('branch_id',$request->branch)
                    ->where('institute_id',$request->institute)
                    ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                    ->orderBy('program_date', 'desc')
                    ->get();    
                  }

                  if($request->course!='' && $request->institute !='' && $request->branch=''){
                  $data = DB::table('course_supports') 
                    ->join('branches','branches.id','=','course_supports.branch_id')
                    ->join('institutes','institutes.id','=','course_supports.institute_id')
                    ->join('courses','courses.id', '=' ,'course_supports.course_id')
                    ->whereBetween('program_date', array($request->dateStart, $request->dateEnd))
                    ->where('course_id',$request->course)
                    ->where('institute_id',$request->institute)                      
                    //->where('course_supports.branch_id','=',$branch_id)
                    ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                    ->orderBy('program_date', 'desc')
                    ->get();    
                  }

                  if($request->course!='' && $request->institute ='' && $request->branch=''){
                  $data = DB::table('course_supports') 
                    ->join('branches','branches.id','=','course_supports.branch_id')
                    ->join('institutes','institutes.id','=','course_supports.institute_id')
                    ->join('courses','courses.id', '=' ,'course_supports.course_id')
                    ->whereBetween('program_date', array($request->dateStart, $request->dateEnd))
                    ->where('course_id',$request->course)                      
                    //->where('course_supports.branch_id','=',$branch_id)
                    ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                    ->orderBy('program_date', 'desc')
                    ->get();    
                  }

                  if($request->institute!='' && $request->course=''  && $request->branch=''){
                  $data = DB::table('course_supports') 
                    ->join('branches','branches.id','=','course_supports.branch_id')
                    ->join('institutes','institutes.id','=','course_supports.institute_id')
                    ->join('courses','courses.id', '=' ,'course_supports.course_id')
                    ->whereBetween('program_date', array($request->dateStart, $request->dateEnd))
                    ->where('institute_id',$request->institute)                      
                    //->where('course_supports.branch_id','=',$branch_id)
                    ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                    ->orderBy('program_date', 'desc')
                    ->get();    
                  }

                  if($request->institute!='' && $request->course!='' && $request->branch!=''){
                  $data = DB::table('course_supports') 
                        ->join('branches','branches.id','=','course_supports.branch_id')
                        ->join('institutes','institutes.id','=','course_supports.institute_id')
                        ->join('courses','courses.id', '=' ,'course_supports.course_id')
                        ->whereBetween('program_date', array($request->dateStart, $request->dateEnd))
                        ->where('branch_id',$request->branch)
                        ->where('institute_id',$request->institute)
                        ->where('course_id',$request->course)
                        ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                        ->orderBy('program_date', 'desc')
                        ->get();    
                  }
                  else{
                    $data = DB::table('course_supports') 
                        ->join('branches','branches.id','=','course_supports.branch_id')
                        ->join('institutes','institutes.id','=','course_supports.institute_id')
                        ->join('courses','courses.id', '=' ,'course_supports.course_id')
                        ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                        ->whereBetween('program_date', array($request->dateStart, $request->dateEnd))                      
                        //->where('course_supports.branch_id','=',$branch_id)
                        ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                        ->orderBy('program_date', 'desc')
                        ->get();
                    }
                
            }
        else
            {
                $branch_id = Auth::user()->branch;
                if(is_null($branch_id)){
                $data = DB::table('course_supports') 
                        ->join('branches','branches.id','=','course_supports.branch_id')
                        ->join('institutes','institutes.id','=','course_supports.institute_id')
                        ->join('courses','courses.id', '=' ,'course_supports.course_id')
                        ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                        ->orderBy('program_date', 'desc')
                        ->get();
                }
                else{

                $data = DB::table('course_supports') 
                        ->join('branches','branches.id','=','course_supports.branch_id')
                        ->join('institutes','institutes.id','=','course_supports.institute_id')
                        ->join('courses','courses.id', '=' ,'course_supports.course_id')
                        ->select('course_supports.*','branches.*','course_supports.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')                      
                        ->where('course_supports.branch_id','=',$branch_id)
                        ->orderBy('program_date', 'desc')
                        ->get();
                }
            }
                return response()->json($data);
        }
    
        

    }

     public function view_meeting($id){
        $meeting = DB::table('course_supports')
                   ->join('branches','branches.id','=','course_supports.branch_id')
                   ->join('institutes','institutes.id','=','course_supports.institute_id')
                   ->join('courses','courses.id', '=' ,'course_supports.course_id')
                   ->select('course_supports.*','branches.*','course_supports.id as m_id','course_supports.course_id as c_id','course_supports.institute_id as i_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','courses.*','courses.name as course_name','program_date as meeting_date')
                   ->where('course_supports.id',$id)
                   ->first();
        $youths = DB::table('course_supports_youth')
                  ->join('youths','youths.id','=','course_supports_youth.youth_id')
                  ->where('course_supports_youth.course_support_id',$id)
                  ->get();


       // dd($meeting);
        //dd($participants);

        return response()->json(array(
            'youths' => $youths,
            'meeting' => $meeting,

        ));
        

    }
} 


                  