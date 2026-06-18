<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\SurveyRepository;
use App\Models\Survey;
use Illuminate\Support\Facades\Validator;

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

     public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ], [
            'title.required' => 'Title là bắt buộc',
            'description.required' => 'Đánh giá là bắt buộc',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $survey = Survey::create([
            'title' => $request->title,
            'description' => $request->description,
            'code' => $request->code,
            'point' => $request->point,
        ]);

        return response()->json([
            'success' => true,
            'data' => $survey,
        ]);
    }
}
