<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\SurveyRepository;

class SurveyController extends Controller
{
    public function __construct(
        Request $request, 
        SurveyRepository $surveyRepository,
     )
    {
        $this->request = $request;
        $this->surveyRepository = $surveyRepository;
    }

     public function detail() 
    {
        $detail = $this->surveyRepository->detail($this->request->id);
    
        return response()->json([
            'success' => true,
            'data' => $detail
        ]);
    }

    public function list(Request $request)
    {
        $posts = $this->surveyRepository->list(
            $request->input('limit', 10)
        );  

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }
}
