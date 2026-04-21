<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    public function __construct(private SubmissionService $submissionService) {}

    /**
     * Display list of submissions for the current user.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();

        $submissions = $this->submissionService->getMySubmissions($user);

        return Inertia::render('Submissions/Index', [
            'submissions' => $submissions,
        ]);
    }

    /**
     * Show form to create a new submission.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Submissions/Create');
    }

    /**
     * Store a new submission with file uploads.
     * Automatically submits for approval after creation.
     *
     * @param StoreSubmissionRequest $request
     * @return RedirectResponse
     */
    public function store(StoreSubmissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $files = $request->file('files');

        $submission = $this->submissionService->createSubmission(
            $validated,
            Auth::user(),
            $files
        );

        // Auto-submit into approval workflow
        $this->submissionService->submitForApproval($submission);

        return redirect()->route('submissions.show', $submission)
            ->with('success', 'Submission created and submitted for approval.');
    }

    /**
     * Display the specified submission.
     *
     * @param Submission $submission
     * @return \Inertia\Response
     */
    public function show(Submission $submission)
    {
        return Inertia::render('Submissions/Show', [
            'submission' => $submission,
        ]);
    }
}
