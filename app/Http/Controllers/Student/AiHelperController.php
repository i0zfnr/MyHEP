<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AiProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AiHelperController extends Controller
{
    public function index(AiProvider $ai): View
    {
        return view('student.ai_helper.index', [
            'aiEnabled' => $ai->enabled(),
            'aiProvider' => $ai->name(),
            'aiModel' => $ai->model(),
        ]);
    }

    public function ask(Request $request, AiProvider $ai): JsonResponse
    {
        $validated = $request->validate(['message' => ['required', 'string', 'min:2', 'max:1200']]);
        if (! $ai->enabled()) {
            return response()->json(['message' => __('AI API key is not configured on the server.')], 422);
        }

        $studentId = (int) session('auth_user.id');
        $context = $this->studentContext($studentId);
        $prompt = implode("\n\n", [
            'You are StudentEdge Student AI Helper for a Malaysian polytechnic.',
            'Answer only about the signed-in student and general portal guidance. Never infer missing records, reveal another person, or treat advice as an official decision. Be concise and use the language used in the question.',
            'Signed-in student context JSON: '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Student question: '.$validated['message'],
        ]);

        try {
            $answer = $ai->ask($prompt);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => __('AI service could not be reached. Check the API key, model, quota, or network connection.')], 502);
        }

        return response()->json(['answer' => $answer, 'provider' => $ai->name(), 'model' => $ai->model()]);
    }

    private function studentContext(int $studentId): array
    {
        $context = ['profile' => [], 'scholarships' => [], 'offenses' => [], 'fine_payment_applications' => []];
        if (Schema::hasTable('students')) {
            $context['profile'] = (array) (DB::table('students')->where('id', $studentId)
                ->first(['full_name', 'matric_no', 'program', 'class_name', 'semester', 'academic_session']) ?? []);
        }
        if (Schema::hasTable('scholarships')) {
            $context['scholarships'] = DB::table('scholarships')->where('student_id', $studentId)
                ->latest('created_at')->limit(5)->get(['type', 'provider_name', 'amount', 'status', 'created_at'])
                ->map(fn ($row) => (array) $row)->all();
        }
        if (Schema::hasTable('offenses')) {
            $context['offenses'] = DB::table('offenses')->where('student_id', $studentId)
                ->latest('offense_date')->limit(10)->get(['id', 'offense_date', 'place', 'fine_amount', 'status'])
                ->map(fn ($row) => (array) $row)->all();
        }
        if (Schema::hasTable('fine_payment_applications')) {
            $context['fine_payment_applications'] = DB::table('fine_payment_applications')->where('student_id', $studentId)
                ->latest('created_at')->limit(10)->get(['offense_id', 'status', 'meeting_date', 'created_at'])
                ->map(fn ($row) => (array) $row)->all();
        }

        return $context;
    }
}
