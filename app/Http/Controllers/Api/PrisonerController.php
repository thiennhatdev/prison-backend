<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prisoner;

class PrisonerController extends Controller
{
    public function index()
    {
        return response()->json(
            Prisoner::published()
                ->orderBy('id', 'desc')
                ->get()
        );
    }

    public function show($id)
    {
        $prisoner = Prisoner::findOrFail($id);

        return response()->json($prisoner);
    }
}
