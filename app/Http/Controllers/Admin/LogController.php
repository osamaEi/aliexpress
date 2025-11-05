<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    /**
     * Display Laravel log files
     */
    public function index(Request $request)
    {
        $logPath = storage_path('logs');
        $logFiles = collect(File::files($logPath))
            ->filter(fn($file) => $file->getExtension() === 'log')
            ->sortByDesc(fn($file) => $file->getMTime())
            ->map(fn($file) => [
                'name' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size' => $this->formatBytes($file->getSize()),
                'modified' => date('Y-m-d H:i:s', $file->getMTime()),
            ])
            ->values();

        $selectedFile = $request->get('file', $logFiles->first()['name'] ?? null);
        $search = $request->get('search', '');
        $level = $request->get('level', ''); // Filter by log level (error, warning, info, debug)

        $logContent = [];
        $totalLines = 0;

        if ($selectedFile) {
            $filePath = storage_path('logs/' . $selectedFile);

            if (File::exists($filePath)) {
                $content = File::get($filePath);
                $lines = explode("\n", $content);
                $totalLines = count($lines);

                // Parse log entries
                $currentEntry = null;
                foreach ($lines as $line) {
                    // Match Laravel log format: [2025-11-05 12:34:56] local.ERROR: message
                    if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)$/', $line, $matches)) {
                        // Save previous entry
                        if ($currentEntry) {
                            $logContent[] = $currentEntry;
                        }

                        // Start new entry
                        $currentEntry = [
                            'timestamp' => $matches[1],
                            'level' => strtoupper($matches[2]),
                            'message' => $matches[3],
                            'stack' => [],
                        ];
                    } elseif ($currentEntry && !empty(trim($line))) {
                        // Stack trace or additional lines
                        $currentEntry['stack'][] = $line;
                    }
                }

                // Add last entry
                if ($currentEntry) {
                    $logContent[] = $currentEntry;
                }

                // Reverse to show newest first
                $logContent = array_reverse($logContent);

                // Apply filters
                if ($search) {
                    $logContent = array_filter($logContent, function($entry) use ($search) {
                        return stripos($entry['message'], $search) !== false ||
                               stripos(implode(' ', $entry['stack']), $search) !== false;
                    });
                }

                if ($level) {
                    $logContent = array_filter($logContent, function($entry) use ($level) {
                        return strtoupper($entry['level']) === strtoupper($level);
                    });
                }
            }
        }

        return view('admin.logs.index', [
            'logFiles' => $logFiles,
            'selectedFile' => $selectedFile,
            'logContent' => $logContent,
            'totalLines' => $totalLines,
            'search' => $search,
            'level' => $level,
        ]);
    }

    /**
     * Download a log file
     */
    public function download(Request $request)
    {
        $fileName = $request->get('file');
        $filePath = storage_path('logs/' . $fileName);

        if (File::exists($filePath)) {
            return response()->download($filePath);
        }

        return redirect()->route('admin.logs.index')->with('error', 'Log file not found');
    }

    /**
     * Delete a log file
     */
    public function delete(Request $request)
    {
        $fileName = $request->get('file');
        $filePath = storage_path('logs/' . $fileName);

        if (File::exists($filePath)) {
            File::delete($filePath);
            return redirect()->route('admin.logs.index')->with('success', 'Log file deleted successfully');
        }

        return redirect()->route('admin.logs.index')->with('error', 'Log file not found');
    }

    /**
     * Clear all log files
     */
    public function clear()
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'log') {
                File::delete($file);
            }
        }

        return redirect()->route('admin.logs.index')->with('success', 'All log files cleared successfully');
    }

    /**
     * Format bytes to human readable size
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
