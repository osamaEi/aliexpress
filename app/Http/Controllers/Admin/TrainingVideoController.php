<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingVideoController extends Controller
{
    public function index()
    {
        $videos = TrainingVideo::orderBy('sort_order')->orderByDesc('created_at')->paginate(20);
        return view('admin.training-videos.index', compact('videos'));
    }

    public function create()
    {
        return view('admin.training-videos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'title_ar'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'type'        => 'required|in:youtube,upload',
            'youtube_url' => 'required_if:type,youtube|nullable|url',
            'video_file'  => 'required_if:type,upload|nullable|file|mimes:mp4,mov,avi,webm|max:204800',
            'thumbnail'   => 'nullable|image|max:2048',
            'visibility'  => 'required|in:all,sellers,distributors,admins',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $data = $request->only([
            'title', 'title_ar', 'description', 'description_ar',
            'type', 'youtube_url', 'visibility', 'sort_order',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $request->input('sort_order', 0);

        if ($request->type === 'upload' && $request->hasFile('video_file')) {
            $data['video_path'] = $request->file('video_file')->store('training-videos', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('training-thumbnails', 'public');
        }

        TrainingVideo::create($data);

        return redirect()->route('admin.training-videos.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إضافة الفيديو بنجاح' : 'Video added successfully');
    }

    public function edit(TrainingVideo $trainingVideo)
    {
        return view('admin.training-videos.edit', ['video' => $trainingVideo]);
    }

    public function update(Request $request, TrainingVideo $trainingVideo)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'title_ar'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'type'        => 'required|in:youtube,upload',
            'youtube_url' => 'required_if:type,youtube|nullable|url',
            'video_file'  => 'nullable|file|mimes:mp4,mov,avi,webm|max:204800',
            'thumbnail'   => 'nullable|image|max:2048',
            'visibility'  => 'required|in:all,sellers,distributors,admins',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $data = $request->only([
            'title', 'title_ar', 'description', 'description_ar',
            'type', 'youtube_url', 'visibility', 'sort_order',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $request->input('sort_order', 0);

        if ($request->type === 'upload' && $request->hasFile('video_file')) {
            if ($trainingVideo->video_path) {
                Storage::disk('public')->delete($trainingVideo->video_path);
            }
            $data['video_path'] = $request->file('video_file')->store('training-videos', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            if ($trainingVideo->thumbnail) {
                Storage::disk('public')->delete($trainingVideo->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('training-thumbnails', 'public');
        }

        $trainingVideo->update($data);

        return redirect()->route('admin.training-videos.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الفيديو بنجاح' : 'Video updated successfully');
    }

    public function destroy(TrainingVideo $trainingVideo)
    {
        if ($trainingVideo->video_path) {
            Storage::disk('public')->delete($trainingVideo->video_path);
        }
        if ($trainingVideo->thumbnail) {
            Storage::disk('public')->delete($trainingVideo->thumbnail);
        }
        $trainingVideo->delete();

        return redirect()->route('admin.training-videos.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الفيديو' : 'Video deleted');
    }

    public function toggleActive(TrainingVideo $trainingVideo)
    {
        $trainingVideo->update(['is_active' => !$trainingVideo->is_active]);
        return response()->json(['success' => true, 'is_active' => $trainingVideo->is_active]);
    }
}
