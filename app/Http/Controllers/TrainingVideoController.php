<?php

namespace App\Http\Controllers;

use App\Models\TrainingVideo;
use Illuminate\Http\Request;

class TrainingVideoController extends Controller
{
    public function index()
    {
        $userType = auth()->user()->user_type; // seller / distributor / admin

        $videos = TrainingVideo::active()
            ->visibleTo($userType)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return view('training-videos.index', compact('videos'));
    }
}
