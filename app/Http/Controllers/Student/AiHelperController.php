<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class AiHelperController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('student.dashboard')
            ->withErrors(['ai_helper' => __('AI Helper is currently unavailable for students.')]);
    }
}
