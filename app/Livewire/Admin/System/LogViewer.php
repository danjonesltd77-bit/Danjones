<?php

namespace App\Livewire\Admin\System;

use Livewire\Component;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LogViewer extends Component
{
    use WithPagination;

    public $selectedFile = null;
    public $search = '';
    public $level = 'all';
    public $perPage = 100;

    protected $queryString = [
        'selectedFile' => ['except' => ''],
        'search' => ['except' => ''],
        'level' => ['except' => 'all'],
    ];

    public function mount()
    {
        if (!$this->selectedFile) {
            $files = $this->logFiles;
            $this->selectedFile = $files[0] ?? null;
        }
    }

    #[Computed]
    public function logFiles()
    {
        $files = glob(storage_path('logs/*.log'));
        return array_reverse(array_map('basename', $files));
    }

    public function selectFile($file)
    {
        $this->selectedFile = $file;
        $this->resetPage();
    }

    public function clearLogs()
    {
        if ($this->selectedFile) {
            File::put(storage_path('logs/' . $this->selectedFile), '');
            $this->dispatch('toast', message: 'Logs cleared successfully', type: 'success');
        }
    }

    public function deleteFile($file)
    {
        if ($file && $file !== 'laravel.log') {
            File::delete(storage_path('logs/' . $file));
            $this->selectedFile = $this->logFiles[0] ?? null;
            $this->dispatch('toast', message: 'Log file deleted', type: 'success');
        }
    }

    public function render()
    {
        $logs = $this->getParsedLogs();

        return view('livewire.admin.system.log-viewer', [
            'logs' => $logs,
        ])->layout('layouts.app');
    }

    protected function getParsedLogs()
    {
        if (!$this->selectedFile) {
            return $this->paginate(collect());
        }

        $path = storage_path('logs/' . $this->selectedFile);
        if (!File::exists($path)) {
            return $this->paginate(collect());
        }

        try {
            // Read the file. For very large files, we might want to tail it instead.
            // But for most app logs, this is okay.
            $content = File::get($path);
            
            // Regex to split by Laravel log entry header: [YYYY-MM-DD HH:MM:SS]
            // We capture the date to keep it in the resulting array.
            $entries = preg_split('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/m', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            
            $parsed = [];
            for ($i = 0; $i < count($entries); $i += 2) {
                $date = trim($entries[$i] ?? '');
                $rest = $entries[$i + 1] ?? '';
                
                // Extract level and message
                // Format is usually:  env.LEVEL: message
                // We make it more lenient to capture various formats
                if (preg_match('/^\s*(\w+)\.(\w+): (.*)/s', $rest, $matches)) {
                    $env = $matches[1];
                    $level = strtoupper($matches[2]);
                    $message = trim($matches[3]);
                } else {
                    $env = 'unknown';
                    $level = 'INFO';
                    $message = trim($rest);
                    
                    // Try to find level if it's there but in a different format
                    if (preg_match('/(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)/i', $rest, $levelMatches)) {
                        $level = strtoupper($levelMatches[1]);
                    }
                }
                
                $parsed[] = [
                    'date' => $date,
                    'env' => $env,
                    'level' => $level,
                    'message' => $message,
                    'full' => "[{$date}]" . $rest
                ];
            }

            $collection = collect(array_reverse($parsed));
        } catch (\Exception $e) {
            return $this->paginate(collect([[
                'date' => now()->toDateTimeString(),
                'env' => 'system',
                'level' => 'ERROR',
                'message' => 'Failed to read log file: ' . $e->getMessage(),
                'full' => $e->getTraceAsString()
            ]]));
        }

        if ($this->search) {
            $collection = $collection->filter(function($entry) {
                return str_contains(strtolower($entry['message']), strtolower($this->search)) ||
                       str_contains(strtolower($entry['level']), strtolower($this->search)) ||
                       str_contains(strtolower($entry['date']), strtolower($this->search));
            });
        }

        if ($this->level !== 'all') {
            $collection = $collection->filter(fn($entry) => strtolower($entry['level']) === strtolower($this->level));
        }

        return $this->paginate($collection);
    }

    protected function paginate(Collection $items)
    {
        $page = Paginator::resolveCurrentPage() ?: 1;
        return new LengthAwarePaginator(
            $items->forPage($page, $this->perPage),
            $items->count(),
            $this->perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }
}
