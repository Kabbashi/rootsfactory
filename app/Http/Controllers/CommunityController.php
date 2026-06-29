<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

/**
 * The public Community directory — a scholarly network of researchers, not a
 * social feed. Lists members (excluding the AI system identity).
 */
class CommunityController extends Controller
{
    public function index(): View
    {
        $members = User::query()
            ->where('role', '!=', 'system')
            ->orderBy('name')
            ->paginate(24);

        return view('public.community.index', ['members' => $members]);
    }
}
