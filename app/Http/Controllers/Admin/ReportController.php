<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\{Report,User,Post,ActivityLog};

class ReportController extends Controller
{
    public function reports_index()
    {
        return view('admin.reports.index');
    }

    public function solved_reports()
    {
        return view('admin.reports.solved_index');
    }

    public function reports_datatable(Request $request)
    {
        $reports = Report::where('is_solved',0)->get();
        // $reports = Report::with('post')->get();
        // return $reports;
        // return $reports[0]->report_to->user_name;
        if (request()->ajax()) {
            return DataTables::of($reports)
                ->addIndexColumn()
                ->editColumn('action', function ($report) {

             return '<a type="button" onclick="delete_post(' . $report->post_id . ','.$report->id.')" class="btn btn-danger"><i class="fa-solid fa-trash-arrow-up"></i></a>
                    <a type="button" onclick="remove_report(' . $report->id . ')" class="btn btn-success"><i class="fa-solid fa-flag"></i></a>
                    <a type="button" onclick="banned_user(' . $report->reported_id .','.$report->id.')" class="btn btn-primary"><i class="fa-solid fa-user-large-slash"></i></a>';
                })
                ->editColumn('reportby', function ($report) {
                    $user_name = User::where('id', $report->report_by)->first();
                    return $user_name->user_name;
                })
                ->editColumn('reported', function ($report) {
                    $user_name = User::where('id', $report->reported_id)->first();
                    return $user_name->user_name;
                })
                ->editColumn('post', function ($report) {
                    $post = Post::where('id', $report->post_id)->first();
                    $videoUrl = env('APP_URL') . $post->url;
                    return '<video src="' . $videoUrl . '" controls style="max-width: 200px; max-height: 100px;"></video>';
                })
                ->rawColumns(['action', 'status','post'])
                ->toJson();
        }
        return view('admin.reports.index');
    }

    public function get_solved_reports(Request $request)
    {
        $reports = Report::where('is_solved',1)->get();
        // $reports = Report::with('post')->get();
        // return $reports;
        // return $reports[0]->report_to->user_name;
        if (request()->ajax()) {
            return DataTables::of($reports)
                ->addIndexColumn()
                ->editColumn('status', function ($report) {

             return '<span class="badge badge-warning">'.$report->action.'</span>';
                })
                ->editColumn('reportby', function ($report) {
                    $user_name = User::where('id', $report->report_by)->first();
                    return $user_name->user_name;
                })
                ->editColumn('reported', function ($report) {
                    $user_name = User::where('id', $report->reported_id)->first();
                    return $user_name->user_name;
                })
                ->editColumn('post', function ($report) {
                    $post = Post::where('id', $report->post_id)->first();
                    $videoUrl = env('APP_URL') . $post->url;
                    return '<video src="' . $videoUrl . '" controls style="max-width: 200px; max-height: 100px;"></video>';
                })
                ->rawColumns(['status','post'])
                ->toJson();
        }
        return view('admin.reports.solved_index');
    }


    public function post_delete(Request $request,$id,$report_id){
        // return $report_id;
        Post::find($id)->delete();
        ActivityLog::create([
            'title'=>'Post has been deleted',
            'user_id'=>auth()->user()->id,
            'action_id'=>$id,
        ]);
        $report = Report::find($report_id);
        $report->is_solved = 1;
        $report->action = "post delete";
        $report->save();
        return "success";

    }
    public function report_delete(Request $request,$id){
        // Report::find($id)->delete();

        ActivityLog::create([
            'title'=>'Report has been uplift',
            'user_id'=>auth()->user()->id,
            'action_id'=>$id,
        ]);

        $report = Report::find($id);
        $report->is_solved = 1;
        $report->action = "marked as false report";
        $report->save();
        return "success";

    }

    public function user_banned(Request $request,$id,$report_id){
        $user = User::find($id);
        $user->status = 0;
        $user->save();


        ActivityLog::create([
            'title'=>'User has been banned',
            'user_id'=>auth()->user()->id,
            'action_id'=>$id,
        ]);

        $report = Report::find($report_id);
        $report->is_solved = 1;
        $report->action = "user banned";
        $report->save();
        return "success";
    }

}
