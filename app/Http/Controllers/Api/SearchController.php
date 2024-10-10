<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HashTag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $search['users'] = User::where('nick_name', 'like', '%' . $request->search_query . '%')->with('post')->get();
        $search['videos'] = Post::where('description', 'like', '%' . $request->search_query . '%')->get();
        $search['hashtags'] = HashTag::where('title', 'like', '%' . $request->search_query . '%')->with('posts')->get();
        return response()->json([
            'status' => '200',
            'data' => $search,
        ]);
    }
}
