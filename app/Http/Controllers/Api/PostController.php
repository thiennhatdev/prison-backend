<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\PostRepository;

class PostController extends Controller
{
    //
     public function __construct(
        Request $request, 
        PostRepository $postRepository,
     )
    {
        $this->request = $request;
        $this->postRepository = $postRepository;
    }

     public function detail() 
    {
        $detail = $this->postRepository->postDetail($this->request->id);
    
        return response()->json([
            'success' => true,
            'data' => $detail
        ]);
    }

    public function list(Request $request)
    {
        $posts = $this->postRepository->list(
            $request->input('limit', 10)
        );  

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }
}
