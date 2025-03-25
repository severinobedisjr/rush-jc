<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth facade

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Apply the 'auth' middleware
    }

    public function index(User $user)
    {
        // Optional: Check if the logged-in user is viewing their own profile
        if (Auth::id() !== $user->id) {
            // You can redirect or display an error message
            //abort(403, 'Unauthorized.'); // 403 Forbidden
        }

        return view('profile.index', compact('user'));
    }
}