<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SccData;
use App\Services\Scc\FuzzyChargeController;
use App\Services\Scc\LoadManagementController;
use Illuminate\Http\Request;

class SccController extends Controller
{
    public function store(Request $request, FuzzyChargeController $controller, LoadManagementController $loadController)
    {
        if (! $this->hasValidToken($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid SCC API token.',
            ], 401);
        }

        $validated = $request->validate([
            'vpv'        => 'required|numeric',
            'ipv'        => 'required|numeric',
            'vbat'       => 'required|numeric',
            'ibat'       => 'required|numeric',
            'soc'        => 'required|numeric',
            'duty_cycle' => 'sometimes|numeric',
            'fase'       => 'sometimes|string',
            'label_e'    => 'sometimes|string',
            'label_de'   => 'sometimes|string',
        ]);

        $latest = SccData::latest()->first();
        $previousError = $latest
            ? $this->targetVoltage($latest->fase, $latest->vbat) - $latest->vbat
            : null;

        $evaluated = $controller->evaluate($validated, $previousError);
        $data = SccData::create([
            ...$evaluated,
            ...$loadController->evaluate($evaluated),
        ]);

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

    private function hasValidToken(Request $request): bool
    {
        $token = config('services.scc.api_token');

        if (blank($token)) {
            return app()->environment('local', 'testing');
        }

        $providedToken = $request->bearerToken() ?: $request->header('X-SCC-Token');

        return is_string($providedToken) && hash_equals($token, $providedToken);
    }

    private function targetVoltage(string $phase, float $vbat): float
    {
        return match ($phase) {
            'Bulk', 'Absorption' => FuzzyChargeController::BULK_TARGET,
            'Float' => FuzzyChargeController::FLOAT_TARGET,
            default => $vbat,
        };
    }
}
