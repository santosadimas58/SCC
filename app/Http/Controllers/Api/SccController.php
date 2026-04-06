<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SccData;
use Illuminate\Http\Request;

class SccController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vpv'        => 'required|numeric',
            'ipv'        => 'required|numeric',
            'vbat'       => 'required|numeric',
            'ibat'       => 'required|numeric',
            'soc'        => 'required|numeric',
            'duty_cycle' => 'required|numeric',
            'fase'       => 'required|string',
            'label_e'    => 'required|string',
            'label_de'   => 'required|string',
        ]);

        $data = SccData::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 201);
    }

    public function latest()
    {
        $data = SccData::latest()->first();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function history()
    {
        $data = SccData::latest()->take(50)->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}

