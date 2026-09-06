<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Http\Controllers\Api\V1\ApiController;
use App\Services\Security\AccessControl;
use App\Support\Api\ApiPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class GlController extends ApiController
{
    public function __construct(
        AccessControl $access,
        private readonly GlApplicationService $gl,
    ) {
        parent::__construct($access);
    }

    public function accountsIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_READ);

        return $this->paginated($this->gl->listAccounts($this->company($request), $request));
    }

    public function accountsShow(Request $request, int $account): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_READ);

        return $this->ok($this->gl->findAccount($this->company($request), $account));
    }

    public function journalsIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_READ);

        return $this->paginated($this->gl->listJournals($this->company($request), $this->book($request), $request));
    }

    public function journalsStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_WRITE);
        $data = $request->validate([
            'journal_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'reference' => ['nullable', 'string'],
            'currency' => ['nullable', 'string', 'size:3'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account' => ['required', 'string'],
            'lines.*.debit' => ['nullable', 'numeric'],
            'lines.*.credit' => ['nullable', 'numeric'],
            'post' => ['sometimes', 'boolean'],
        ]);
        $journal = $this->gl->createJournalDraft(
            $this->company($request),
            $this->book($request),
            Arr::except($data, ['lines', 'post']),
            $data['lines'],
        );
        if ($request->boolean('post')) {
            $journal = $this->gl->postJournal($journal, $this->actor($request));
        }

        return $this->created($journal->load('lines'));
    }

    public function journalsShow(Request $request, int $journal): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_READ);

        return $this->ok($this->gl->findJournal($this->company($request), $this->book($request), $journal));
    }

    public function journalsPost(Request $request, int $journal): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_WRITE);
        $doc = $this->gl->findJournal($this->company($request), $this->book($request), $journal);

        return $this->ok($this->gl->postJournal($doc, $this->actor($request))->load('lines'));
    }

    public function journalLinesIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_READ);

        return $this->paginated($this->gl->listJournalLines($this->company($request), $this->book($request), $request));
    }

    public function trialBalance(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_READ);
        $asOf = $request->filled('as_of') ? Carbon::parse((string) $request->input('as_of')) : null;

        return $this->ok($this->gl->trialBalance($this->company($request), $this->book($request), $asOf));
    }

    public function generalLedger(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_READ);

        return $this->paginated($this->gl->generalLedger($this->company($request), $this->book($request), $request));
    }

    public function accountBalances(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_READ);
        $asOf = $request->filled('as_of') ? Carbon::parse((string) $request->input('as_of')) : null;

        return $this->ok($this->gl->accountBalances($this->company($request), $this->book($request), $asOf));
    }

    public function periodsIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::GL_READ);

        return $this->paginated($this->gl->listPeriods($this->company($request), $request));
    }
}
