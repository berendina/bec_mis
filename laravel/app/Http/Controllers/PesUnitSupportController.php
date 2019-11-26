<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use DB;
use Illuminate\Support\Facades\Validator;
use Auth;
use Zipper;

class PesUnitSupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
    	$districts = DB::table('districts')->get();
    	$managers = DB::table('branches')->select('manager')->distinct()->get();
    	$activities = DB::table('activities')->get();
    	return view('Activities.career-guidance.pes-unit-support')->with(['districts'=> $districts,'managers'=>$managers,'activities'=>$activities]);
    }

    public function pes_List(Request $request){
    	if($request->get('query')){
          $query = $request->get('query');
          $data = DB::table('pes_units')
            ->where('dsd', 'LIKE', "%{$query}%")
            ->get();
          $output = '<ul class="dropdown-menu" id="dsds" style="display:block; position:relative">';
          foreach($data as $row)
          {
           $output .= '
           <li class="nav-item" id="'.$row->id.'"><a href="#" >'.$row->dsd.'('.$row->date.')'.'</a></li>
           ';
          }
          $output .= '</ul>';
          echo $output;
         }
    }

     public function insert(Request $request){
    	$validator = Validator::make($request->all(),[
                'district' => 'required',
                'dm_name' =>'required',
                'title_of_action' =>'required',	
                'activity_code' =>'required',	
                'dsd'	=>'required',
                'visit_date'	=>'required',
                'support_date'	=>'required',
                'gaps'	=>'required',
                'pes_identification_id'	=>'required',
                'photos.*' => 'image|mimes:jpeg,jpg,png,gif,svg',
	            
            ]);

            if($validator->passes()){
                $branch_id = auth()->user()->branch;
                $dsd_id =  $request->dsd;
                $dsd= DB::table('dsd_office')->where('ID',$dsd_id)->first();
                $dsd_name = $dsd->DSD_Name;
                $data1 = array(
                	'district' => $request->district,
                	'dsd' => $dsd_name,
	                'dm_name' =>$request->dm_name,
	                'title_of_action' =>$request->title_of_action,	
	                'activity_code' =>$request->activity_code,	
	                'visit_date' =>$request->visit_date,
	                'support_date'	=>$request->support_date,                
                    'gaps' => $request->gaps,
	                'pes_identification_id' => $request->pes_identification_id,
	                'created_at' => date('Y-m-d H:i:s'),
	                'branch_id' => $branch_id,
                );

                //insert general data 
                $pes = DB::table('pes_unit_supports')->insert($data1);
                $pes_units_support_id = DB::getPdo()->lastInsertId();
             
                 //insert images
                $input = $request->all();
                if($request->hasFile('photos')){
	                foreach ($request->file('photos') as $key => $value) {
	            	$imageName = time(). $key . '.' . $value->getClientOriginalExtension();
	            	$value->move(storage_path('activities/files/pes-supports/images'), $imageName);
	            	$images = DB::table('pes_units_support_images')->insert(['photos'=>$imageName,'pes_units_support_id'=>$pes_units_support_id]);
	        		}
        		}
        		//insert gsrns
        		if($request->hasFile('gsrns')){
	        		foreach ($request->file('gsrns') as $key => $value) {
	            	$gsrnsName = time(). $key . '.' . $value->getClientOriginalExtension();
	            	$value->move(storage_path('activities/files/pes-supports/gsrns'), $gsrnsName);
	            	$gsrns = DB::table('pes_units_support_gsrns')->insert(['gsrns'=>$gsrnsName,'pes_units_support_id'=>$pes_units_support_id]);
	        		}
        		}
                $number = count($request->gap_num);
                $sum_cost = 0;
                if($number>0){

                	for($i=0; $i<$number; $i++){
                		$services = DB::table('pes_units_gaps')->insert(['gap_num'=>$request->gap_num[$i],'meterials_provided'=>$request->meterials_provided[$i],'units'=> $request->units[$i],'date_provided'=> $request->date_provided[$i],'usage'=> $request->usage[$i],'cost'=> $request->cost[$i],'pes_units_support_id'=>$pes_units_support_id]);

                        $sum_cost += $request->cost[$i];
                	}

                    DB::table('pes_unit_supports')->where('id', $pes_units_support_id )->update(['total_cost' => $sum_cost]);
                }
                else{
                	return response()->json(['errors' => 'Submit materials provided for particular PES unit.']);
                } 

            }
            else{
                return response()->json(['error' => $validator->errors()->all()]);
            }
    
    }

    public function view(){
        $branch_id = Auth::user()->branch;
        if(is_null($branch_id)){
        $meetings = DB::table('pes_unit_supports')
                      //->leftjoin('mentoring_gvt_officials','mentoring_gvt_officials.mentoring_id','=','mentoring.id')
                      ->join('branches','branches.id','=','pes_unit_supports.branch_id')
                      ->get();
        //dd($mentorings);

        $participants2018 = DB::table('pes_unit_supports')                        
                        ->where(DB::raw('YEAR(support_date)'), '=', '2018' )
                        ->count();
                        //->groupBy(function ($val) {
                                // Carbon::parse($val->meeting_date)->format('Y');
                        //});
                        //->groupBy(DB::raw("year(meeting_date)"))
                        
           $participants2019 = DB::table('pes_unit_supports')                        
                        ->where(DB::raw('YEAR(support_date)'), '=', '2019' )
                        ->count();            
            $participants2020 = DB::table('pes_unit_supports')                        
                        ->where(DB::raw('YEAR(support_date)'), '=', '2020' )
                        ->count();  
            $participants2021 = DB::table('pes_unit_supports')                        
                        ->where(DB::raw('YEAR(support_date)'), '=', '2021' )
                        ->count();    
        }
        else{
            $meetings = DB::table('pes_unit_supports')
                      //->leftjoin('mentoring_gvt_officials','mentoring_gvt_officials.mentoring_id','=','mentoring.id')
                      ->join('branches','branches.id','=','pes_unit_supports.branch_id')
                      ->where('pes_unit_supports.branch_id','=',$branch_id)
                      ->get(); 
        //dd($mentorings);

        $participants2018 = DB::table('pes_unit_supports')                        
                        ->where(DB::raw('YEAR(support_date)'), '=', '2018' )
                        ->where('pes_unit_supports.branch_id','=',$branch_id)
                        ->count();
                        //->groupBy(function ($val) {
                                // Carbon::parse($val->meeting_date)->format('Y');
                        //});
                        //->groupBy(DB::raw("year(meeting_date)"))
                        
           $participants2019 = DB::table('pes_unit_supports')                        
                        ->where(DB::raw('YEAR(support_date)'), '=', '2019' )
                        ->where('pes_unit_supports.branch_id','=',$branch_id)
                        ->count();            
            $participants2020 = DB::table('pes_unit_supports')                        
                        ->where(DB::raw('YEAR(support_date)'), '=', '2020' )
                        ->where('pes_unit_supports.branch_id','=',$branch_id)
                        ->count();  
            $participants2021 = DB::table('pes_unit_supports')                        
                        ->where(DB::raw('YEAR(support_date)'), '=', '2021' )
                        ->where('pes_unit_supports.branch_id','=',$branch_id)
                        ->count();
        }      
        //dd($participants2018);
        $branches = DB::table('branches')->get();
        return view('Activities.Reports.career-guidance.sup_pes')->with(['meetings'=>$meetings,'branches'=>$branches,'participants2018'=>$participants2018,'participants2019'=>$participants2019,'participants2020'=>$participants2020,'participants2021'=>$participants2021]);
    }

    public function fetch(Request $request){
        if($request->ajax())
        {
            if($request->dateStart != '' && $request->dateEnd != '')
            {
                $branch_id = Auth::user()->branch;
        
                if($request->branch !=''){
                    $data = DB::table('pes_unit_supports') 
                        ->join('branches','branches.id','=','pes_unit_supports.branch_id')
                        ->whereBetween('pes_unit_supports.support_date', array($request->dateStart, $request->dateEnd))
                        ->where('pes_unit_supports.branch_id',$request->branch)
                        ->select('pes_unit_supports.*','branches.*','pes_unit_supports.id as m_id','branches.name as branch_name') 
                        ->get();


                }
                else{
                if(is_null($branch_id)){                    

                    $data = DB::table('pes_unit_supports') 
                        ->join('branches','branches.id','=','pes_unit_supports.branch_id')
                        ->whereBetween('pes_unit_supports.support_date', array($request->dateStart, $request->dateEnd))
                        ->select('pes_unit_supports.*','branches.*','pes_unit_supports.id as m_id','branches.name as branch_name')
                         //->where('pes_unit_supports.branch_id','=',$branch_id)
                        ->get();                     
                }
                else{
                    $data = DB::table('pes_unit_supports') 
                        ->join('branches','branches.id','=','pes_unit_supports.branch_id')
                        ->whereBetween('pes_unit_supports.support_date', array($request->dateStart, $request->dateEnd))
                        ->select('pes_unit_supports.*','branches.*','pes_unit_supports.id as m_id','branches.name as branch_name')
                         ->where('pes_unit_supports.branch_id','=',$branch_id)
                        ->get();
                }

                }
                
            }
        else
            {
                $branch_id = Auth::user()->branch;
                if(is_null($branch_id)){

                $data = DB::table('pes_unit_supports') 
                        ->join('branches','branches.id','=','pes_unit_supports.branch_id')
                        ->select('pes_unit_supports.*','branches.*','pes_unit_supports.id as m_id','branches.name as branch_name')
                        ->get();
                }
                else{
                    $data = DB::table('pes_unit_supports') 
                        ->join('branches','branches.id','=','pes_unit_supports.branch_id')
                        ->select('pes_unit_supports.*','branches.*','pes_unit_supports.id as m_id','branches.name as branch_name')
                        ->where('pes_unit_supports.branch_id','=',$branch_id)
                        ->get();                         

                }

            }
                return response()->json($data);

                //return dd($data);
        }

    }

    public function view_meeting($id){
        $meeting = DB::table('pes_unit_supports')
                   ->join('branches','branches.id','=','pes_unit_supports.branch_id')
                   ->select('pes_unit_supports.*','branches.*','pes_unit_supports.id as m_id','branches.name as branch_name')
                   ->where('pes_unit_supports.id',$id)
                   ->first();

        $gaps = DB::table('pes_units_gaps')
                     ->where('pes_units_gaps.pes_units_support_id',$id)
                     ->get();

       // dd($meeting);
        //dd($participants);

        return response()->json(array( 
            'meeting' => $meeting,
            'gaps' => $gaps,
        ));
        

    }

    public function download($id){
        $photos = DB::table('pes_units_support_gsrns')
                  ->where('pes_units_support_id',$id)
                  ->select('pes_units_support_gsrns.gsrns')
                  ->get();
        foreach($photos as $photo){
            //echo $photo->gsrns;
            $headers = ["Content-Type"=>"application/zip"];
            //$paths = storage_path('activities/files/mentoring/images/'.$photo->image.'');
            $zipper = Zipper::make(storage_path('activities/files/pes-supports/gsrns/'.$id.'.zip'))->add(storage_path('activities/files/pes-supports/gsrns/'.$photo->gsrns.''))->close();
        }
            return response()->download(storage_path('activities/files/pes-supports/gsrns/'.$id.'.zip','photos',$headers)); 
    }
    

    public function download_photos($id){
        $photos = DB::table('pes_units_support_images')
                  ->where('pes_units_support_id',$id)
                  ->select('pes_units_support_images.photos')
                  ->get();
        foreach($photos as $photo){
            //echo $photo->images;
            $headers = ["Content-Type"=>"application/zip"];
            //$paths = storage_path('activities/files/mentoring/images/'.$photo->image.'');
            $zipper = Zipper::make(storage_path('activities/files/pes-supports/images/'.$id.'.zip'))->add(storage_path('activities/files/pes-supports/images/'.$photo->photos.''))->close();
        }
            return response()->download(storage_path('activities/files/pes-supports/images/'.$id.'.zip','photos',$headers)); 

        //$photos_array = $photos->toArray();
        //dd($photos);
       // Zipper::make('mydir/photos.zip')->add($paths);
       // return response()->download(('mydir/photos.zip')); 
    }





}
