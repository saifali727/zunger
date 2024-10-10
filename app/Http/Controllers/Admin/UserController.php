<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User,ActivityLog};
use Illuminate\Http\Request;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    public function student_index()
    {
        return view('admin.users.tiktok_index');
    }

    public function student_datatable(Request $request)
    {
        $users = User::get();

        if (request()->ajax()) {
            return DataTables::of($users)
                ->addIndexColumn()
                ->editColumn('status', function ($user) {

                    return ($user->status == 1) ? '<a type="button"  <span onclick="change_status(' . $user->id . ')" class="badge badge-success">active</span></a>' : '<a type="button"  <span onclick="change_status(' . $user->id . ')" class="badge badge-danger">disabled</span></a>';
                })
                ->rawColumns(['action', 'status'])
                ->toJson();
        }
        return view('admin.users.index');
    }

    public function user_status(Request $request, $id){
        $user =  User::where('id', $id)->first();
        if ($user->status == 1) {
            $user->update(['status' => 0]);
            ActivityLog::create([
                'title'=>'User has been disabled',
                'user_id'=>auth()->user()->id,
                'action_id'=>$id,
            ]);
        } else {
            $user->update(['status' => 1]);
            ActivityLog::create([
                'title'=>'User has been activated',
                'user_id'=>auth()->user()->id,
                'action_id'=>$id,
            ]);
        }


        return "success";
    }

    public function user_delete(Request $request,$id){

        $user =  User::where('id', $id)->first();
        $user->delete();
        ActivityLog::create([
            'title'=>'User has been delted',
            'user_id'=>auth()->user()->id,
            'action_id'=>$id,
        ]);
        return "success";
    }
}
