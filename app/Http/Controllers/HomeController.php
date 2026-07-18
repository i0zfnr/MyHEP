<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $homeStats = $this->homeStats();

        return view('welcome', compact('homeStats'));
    }

    public function live(): JsonResponse
    {
        return response()->json([
            'data' => $this->homeStats(),
        ]);
    }

    private function homeStats(): array
    {
        return array_merge(\myhepHomeStatCounts(), [
            'server_time' => now()->format('Y-m-d H:i:s'),
            'system_online' => true,
        ]);
    }
}
