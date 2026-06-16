<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;

class OrganizationController extends Controller
{
    public function index()
    {
        $orgs = Organization::where('status','active')->latest()->paginate(15);
        return response()->json(['success' => true, 'data' => $orgs]);
    }

    public function show($id)
    {
        $org = Organization::findOrFail($id);
        return response()->json(['success' => true, 'data' => $org]);
    }
}
