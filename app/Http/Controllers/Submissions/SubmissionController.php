<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\User;
use App\Services\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    public function __construct(private SubmissionService $submissionService) {}

    public function index()
    {
        $user = Auth::user();

        $submissions = $this->submissionService->getMySubmissions($user);

        return Inertia::render('Submissions/Index', [
            'submissions' => $submissions,
        ]);
    }

    public function create()
    {
        return Inertia::render('Submissions/Create');
    }

    public function store(StoreSubmissionRequest $request): RedirectResponse
    {
        $submission = $this->submissionService->createSubmission(
            $request->validated(),
            Auth::user()
        );

        return redirect()->route('submissions.show', $submission)->with('success', 'Submission created successfully.');
    }

    public function show(string $id)
    {
        $submission = $this->submissionService->getSubmission($id);

        if (!$submission) {
            abort(404);
        }

        return Inertia::render('Submissions/Show', [
            'submission' => $submission,
        ]);
    }
}
