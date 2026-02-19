<?php
// app/Http/Controllers/Admin/AdminController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Block;
use App\Models\User;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-pages|view-users');
    }

    public function index()
    {
        $stats = [
            'total_pages' => Page::count(),
            'active_pages' => Page::where('is_active', true)->count(),
            'total_blocks' => Block::count(),
            'total_users' => User::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
