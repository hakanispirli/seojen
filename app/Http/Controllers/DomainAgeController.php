<?php

namespace App\Http\Controllers;

use App\Services\DomainAgeService;
use Illuminate\Http\Request;

class DomainAgeController extends Controller
{
    protected $domainAgeService;

    public function __construct(DomainAgeService $domainAgeService)
    {
        $this->domainAgeService = $domainAgeService;
    }

    public function index()
    {
        return view('tools.domain-age.index');
    }

    public function check(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|regex:/^(?!:\/\/)(?:[a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}$/'
        ]);

        $result = $this->domainAgeService->checkDomain($request->domain);
        return response()->json($result);
    }
}
