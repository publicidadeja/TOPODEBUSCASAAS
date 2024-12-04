<?php
namespace App\Http\Controllers;

use App\Models\AIFeedback;
use App\Services\AIAnalysisService;
use Illuminate\Http\Request;

class AIFeedbackController extends Controller
{
    protected $aiService;

    public function __construct(AIAnalysisService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'suggestion_id' => 'required|string',
            'suggestion_type' => 'required|string',
            'feedback_type' => 'required|in:helpful,not_helpful',
            'comments' => 'nullable|string',
            'applied' => 'boolean'
        ]);

        $feedback = AIFeedback::create([
            ...$validated,
            'user_id' => auth()->id()
        ]);

        // Atualizar o modelo de IA com o feedback
        $this->aiService->updateModelWithFeedback($feedback);

        return response()->json([
            'success' => true,
            'message' => 'Feedback registrado com sucesso'
        ]);
    }

    public function dashboard()
    {
        $metrics = [
            'total_suggestions' => AIFeedback::count(),
            'helpful_rate' => AIFeedback::where('feedback_type', 'helpful')->count() / AIFeedback::count() * 100,
            'applied_rate' => AIFeedback::where('applied', true)->count() / AIFeedback::count() * 100,
            'suggestion_types' => AIFeedback::groupBy('suggestion_type')
                ->selectRaw('suggestion_type, count(*) as count')
                ->get(),
            'recent_feedback' => AIFeedback::with(['business', 'user'])
                ->latest()
                ->take(10)
                ->get()
        ];

        return view('automation.dashboard', compact('metrics'));
    }

    public function export(Request $request)
    {
        $dateRange = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $feedbacks = AIFeedback::whereBetween('created_at', [
            $dateRange['start_date'],
            $dateRange['end_date']
        ])->get();

        return Excel::download(
            new AIFeedbackExport($feedbacks),
            'ai-feedback-report.xlsx'
        );
    }
}