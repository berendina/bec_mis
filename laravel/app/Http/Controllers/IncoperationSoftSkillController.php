<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Zipper;


class IncoperationSoftSkillController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
    	$districts = DB::table('districts')->get();
    	$managers = DB::table('branches')->select('manager')->distinct()->get();
    	$activities = DB::table('activities')->get();
    	return view('Activities.skill-development.incoperate')->with(['districts'=> $districts,'managers'=>$managers,'activities'=>$activities]);
    }

    public function insert(Request $request){
    	$validator = Validator::make($request->all(),[
    		    'review_date'  => 'required',
                'district' => 'required',
                'dm_name' =>'required',	
                'institute_id' => 'required',
            ]);

            if($validator->passes()){
                $branch_id = auth()->user()->branch;
                $input = $request->all();
            
            	if($request->hasFile('gsrn')){
	            	$input['gsrn'] = time().'.'.$request->file('gsrn')->getClientOriginalExtension();
	            	$request->gsrn->move(storage_path('activities/files/skill/incoperate/gsrn'), $input['gsrn']);
            	}
                $data1 = array(
                	'district' => $request->district,
                	'dsd' => $request->dsd,
	                'dm_name' =>$request->dm_name,
	                'title_of_action' =>$request->title_of_action,  
                    'activity_code' =>$request->activity_code,	
	                'review_date'	=>$request->review_date,	               
	                'institute_id'	=>$request->institute_id, 
	                'tvec_ex_date' => $request->tvec_ex_date,
	                'nature_of_assistance' => $request->nature_of_assistance,
	                'review_report' => $request->review_report,
	                'gsrn' => $input['gsrn'],
	                'branch_id'	=> $branch_id,
	                'created_at' => date('Y-m-d H:i:s')
                );

                //insert general data 
                $incoperation = DB::table('incoperation_soft_skills')->insert($data1);
                $iss_id = DB::getPdo()->lastInsertId();

                //insert photos
				if($request->hasFile('images')){
                    foreach ($request->file('images') as $key => $value) {
                	$imageName = time(). $key . '.' . $value->getClientOriginalExtension();
                	$value->move(storage_path('activities/files/skill/incoperate/images'), $imageName);
                	$images = DB::table('incoperation_soft_skills_photos')->insert(['images'=>$imageName,'iss_id'=>$iss_id]);
            		}
                }
            }
            else{
                return response()->json(['error' => $validator->errors()->all()]);
            }
    
    }

    public function view(){
        $meetings = DB::table('incoperation_soft_skills')
                      //->leftjoin('mentoring_gvt_officials','mentoring_gvt_officials.mentoring_id','=','mentoring.id')
                      ->join('branches','branches.id','=','incoperation_soft_skills.branch_id')
                      ->get();
        //dd($mentorings);       
        //dd($participants2018);
        $branches = DB::table('branches')->get();

        $institutes = DB::table('incoperation_soft_skills')
                   ->join('institutes','institutes.id','=','incoperation_soft_skills.institute_id')
                   ->select('institutes.name as institute_name','institutes.*')
                   ->distinct()
                   ->get();
        return view('Activities.Reports.Skill-Development.incoperation')->with(['meetings'=>$meetings,'branches'=>$branches,'institutes'=>$institutes]);
    }

    public function fetch(Request $request){
        if($request->ajax())
        {
            if($request->dateStart != '' && $request->dateEnd != '')
            {
                
                switch (true) {
                  case ($request->branch!=''):
                    $data = DB::table('incoperation_soft_skills') 
                      ->join('branches','branches.id','=','incoperation_soft_skills.branch_id')
                      ->join('institutes','institutes.id','=','incoperation_soft_skills.institute_id')
                      ->whereBetween('review_date', array($request->dateStart, $request->dateEnd))
                      ->where('branch_id',$request->branch)
                      ->select('incoperation_soft_skills.*','branches.*','incoperation_soft_skills.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','review_date as meeting_date')
                      ->orderBy('review_date', 'desc')
                      ->get();    
                    break;


                  case ($request->branch!='' and $request->institute !=''):
                  $data = DB::table('incoperation_soft_skills') 
                    ->join('branches','branches.id','=','incoperation_soft_skills.branch_id')
                    ->join('institutes','institutes.id','=','incoperation_soft_skills.institute_id')
                    ->whereBetween('review_date', array($request->dateStart, $request->dateEnd))
                    ->where('branch_id',$request->branch)
                    ->where('institute_id',$request->institute)
                    ->select('incoperation_soft_skills.*','branches.*','incoperation_soft_skills.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','review_date as meeting_date')
                    ->orderBy('review_date', 'desc')
                    ->get();    
                  break;

                

                  case ($request->institute!=''):
                  $data = DB::table('incoperation_soft_skills') 
                    ->join('branches','branches.id','=','incoperation_soft_skills.branch_id')
                    ->join('institutes','institutes.id','=','incoperation_soft_skills.institute_id')
                    ->whereBetween('review_date', array($request->dateStart, $request->dateEnd))
                    ->where('institute_id',$request->institute)
                    ->select('incoperation_soft_skills.*','branches.*','incoperation_soft_skills.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','review_date as meeting_date')
                    ->orderBy('review_date', 'desc')
                    ->get();    
                  break;

                  default:
                    $data = DB::table('incoperation_soft_skills') 
                        ->join('branches','branches.id','=','incoperation_soft_skills.branch_id')
                        ->join('institutes','institutes.id','=','incoperation_soft_skills.institute_id')
                        ->select('incoperation_soft_skills.*','branches.*','incoperation_soft_skills.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','review_date as meeting_date')
                        ->whereBetween('review_date', array($request->dateStart, $request->dateEnd))
                        ->select('incoperation_soft_skills.*','branches.*','incoperation_soft_skills.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','review_date as meeting_date')
                        ->orderBy('review_date', 'desc')
                        ->get();
                    break;
                }
                
            }
        else
            {
                $data = DB::table('incoperation_soft_skills') 
                        ->join('branches','branches.id','=','incoperation_soft_skills.branch_id')
                        ->join('institutes','institutes.id','=','incoperation_soft_skills.institute_id')
                        ->select('incoperation_soft_skills.*','branches.*','incoperation_soft_skills.id as m_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','review_date as meeting_date')
                        ->orderBy('review_date', 'desc')
                        ->get();
            }
                return response()->json($data);
        }
    
        

    }

    public function view_meeting($id){
        $meeting = DB::table('incoperation_soft_skills')
                   ->join('branches','branches.id','=','incoperation_soft_skills.branch_id')
                   ->join('institutes','institutes.id','=','incoperation_soft_skills.institute_id')
                   ->select('incoperation_soft_skills.*','branches.*','incoperation_soft_skills.id as m_id','incoperation_soft_skills.institute_id as i_id','institutes.*','institutes.name as institute_name','branches.name as branch_name','review_date as meeting_date')
                   ->where('incoperation_soft_skills.id',$id)
                   ->first();


       // dd($meeting);
        //dd($participants);

        return response()->json(array(
            'meeting' => $meeting,

        ));
        

    }

    public function download($file_name){
        //$file_name = $request->attendance;
        $file = storage_path('activities/files/skill/incoperate/gsrn/'.$file_name.'');
        //echo "<script>console.log( 'Debug Objects: " . $file_name . "' );</script>";

        $headers = [
                  'Content-Type' => 'application/pdf',
                  'Content-Type' => 'application/msword',
               ];
      // return Storage::download(filePath, Appended Text);
        return response()->file($file,$headers);
    }

    public function download_photos($id){
        $photos = DB::table('incoperation_soft_skills_photos')
            ->where('iss_id',$id)
            ->select('incoperation_soft_skills_photos.images')
            ->get();

        foreach($photos as $photo){
            //echo $photo->images;
            //$paths = storage_path('activities/files/mentoring/images/'.$photo->image.'');
            $headers = ["Content-Type"=>"application/zip"];
            
            $zipper = Zipper::make(storage_path('activities/files/skill/incoperate/images/'.$id.'.zip'))->add(storage_path('activities/files/skill/incoperate/images/'.$photo->images.''))->close();

        return response()->download(storage_path('activities/files/skill/incoperate/images/'.$id.'.zip','photos',$headers)); 

        }


        //$photos_array = $photos->toArray();
        //dd($photos);
       // Zipper::make('mydir/photos.zip')->add($paths);
       // return response()->download(('mydir/photos.zip')); 
    }

    
}
