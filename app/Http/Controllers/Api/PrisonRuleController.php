<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\PrisonRuleRepository;

class PrisonRuleController extends Controller
{
    public function __construct(
        Request $request, 
        PrisonRuleRepository $prisonRuleRepository,
     )
    {
        $this->request = $request;
        $this->prisonRuleRepository = $prisonRuleRepository;
    }

     public function detail() 
    {
        $detail = $this->prisonRuleRepository->detail($this->request->id);
    
        return response()->json([
            'success' => true,
            'data' => $detail
        ]);
    }

    public function list(Request $request)
    {
        $posts = $this->prisonRuleRepository->list(
            $request->input('limit', 10)
        );  

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }
}
