<?php

namespace App\Http\Controllers;

use App\Models\DaftarTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DaftarTugasController extends Controller
{
    // private function updateOverdueTasks($userId)
    // {
    //     DaftarTugas::byUser($userId)
    //         ->where('status', 'BELUM SELESAI')
    //         ->where('deadline', '<', Carbon::now())
    //         ->update(['status' => 'TERLAMBAT']);
    // }

    public function index(Request $request)
    {
        // $this->updateOverdueTasks($request->user()->id);

        $query = DaftarTugas::byUser($request->user()->id)->with('user');

        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'TERLAMBAT') {
                $query->byStatus('TERLAMBAT');
            } else {
                $query->byStatus($request->status);
            }
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->byKategori($request->kategori);
        }

        if ($request->has('important') && $request->important == 'true') {
            $query->important();
        }

        if ($request->has('upcoming') && $request->upcoming == 'true') {
            $query->upcoming($request->get('days', 7));
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('isi', 'like', '%' . $request->search . '%');
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 10);
        $tasks = $query->paginate($perPage);

        $tasks->getCollection()->transform(function ($task) {
            $task->deadline = Carbon::parse($task->deadline)->format('Y-m-d H:i:s');
            return $task;
        });

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'sometimes|required|string|max:255',
            'isi' => 'sometimes|nullable|string',
            'status' => 'sometimes|required|in:SELESAI,BELUM SELESAI',
            'deadline' => 'sometimes|required|date_format:Y-m-d H:i:s',
            'is_important' => 'sometimes|boolean',
            'kategori' => 'sometimes|required|in:Work,Study,Personal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $task = DaftarTugas::create([
            'judul' => $request->judul,
            'isi' => $request->isi ?? '',
            'deadline' => $request->deadline,
            'is_important' => $request->get('is_important', false),
            'kategori' => $request->kategori,
            'user_id' => $request->user()->id,
        ]);

        $task->deadline = Carbon::parse($task->deadline)->format('Y-m-d H:i:s');

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task->load('user')
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $task = DaftarTugas::byUser($request->user()->id)->with('user')->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $task->deadline = Carbon::parse($task->deadline)->format('Y-m-d H:i:s');

        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    public function update(Request $request, $id)
    {
        $task = DaftarTugas::byUser($request->user()->id)->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'sometimes|required|string|max:255',
            'isi' => 'sometimes|nullable|string',
            'status' => 'sometimes|required|in:SELESAI,BELUM SELESAI',
            'deadline' => 'sometimes|required|date_format:Y-m-d H:i:s',
            'is_important' => 'sometimes|boolean',
            'kategori' => 'sometimes|required|in:Work,Study,Personal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $task->update($request->only(['judul', 'isi', 'status', 'deadline', 'is_important', 'kategori']));

        $task->deadline = Carbon::parse($task->deadline)->format('Y-m-d H:i:s');

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $task->load('user')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $task = DaftarTugas::byUser($request->user()->id)->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }

    public function markComplete(Request $request, $id)
    {
        $task = DaftarTugas::byUser($request->user()->id)->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $task->update(['status' => 'SELESAI']);

        $task->deadline = Carbon::parse($task->deadline)->format('Y-m-d H:i:s');

        return response()->json([
            'success' => true,
            'message' => 'Task marked as complete',
            'data' => $task->load('user')
        ]);
    }

    public function markIncomplete(Request $request, $id)
    {
        $task = DaftarTugas::byUser($request->user()->id)->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $task->update(['status' => 'BELUM SELESAI']);

        $task->deadline = Carbon::parse($task->deadline)->format('Y-m-d H:i:s');

        return response()->json([
            'success' => true,
            'message' => 'Task marked as incomplete',
            'data' => $task->load('user')
        ]);
    }

    public function statistics(Request $request)
    {
        $userId = $request->user()->id;

        // $this->updateOverdueTasks($userId);

        $totalTasks = DaftarTugas::byUser($userId)->count();
        $completedTasks = DaftarTugas::byUser($userId)->byStatus('SELESAI')->count();
        $pendingTasks = DaftarTugas::byUser($userId)->byStatus('BELUM SELESAI')->count();
        $overdueTasks = DaftarTugas::byUser($userId)->byStatus('TERLAMBAT')->count();
        $importantTasks = DaftarTugas::byUser($userId)->important()->count();
        $upcomingTasks = DaftarTugas::byUser($userId)->upcoming(7)->count();

        $completionRate = $totalTasks > 0
            ? round(($completedTasks / $totalTasks) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'overdue_tasks' => $overdueTasks,
                'important_tasks' => $importantTasks,
                'upcoming_tasks' => $upcomingTasks,
                'completion_rate' => $completionRate,
            ]
        ]);
    }

    public function getKategoriStats(Request $request)
    {
        $userId = $request->user()->id;

        // $this->updateOverdueTasks($userId);

        $categories = ['Work', 'Study', 'Personal'];
        $stats = [];

        foreach ($categories as $category) {
            $totalTasks = DaftarTugas::byUser($userId)->byKategori($category)->count();
            $completedTasks = DaftarTugas::byUser($userId)->byKategori($category)->byStatus('SELESAI')->count();
            $pendingTasks = DaftarTugas::byUser($userId)
                ->byKategori($category)
                ->byStatus('BELUM SELESAI')
                ->count();
            $overdueTasks = DaftarTugas::byUser($userId)
                ->byKategori($category)
                ->byStatus('TERLAMBAT')
                ->count();
            $importantTasks = DaftarTugas::byUser($userId)->byKategori($category)->important()->count();
            $completionRate = $totalTasks > 0
                ? round(($completedTasks / $totalTasks) * 100, 2)
                : 0;

            $stats[] = [
                'category' => $category,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'overdue_tasks' => $overdueTasks,
                'important_tasks' => $importantTasks,
                'completion_rate' => $completionRate
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function exportPDF(Request $request)
    {
        // $this->updateOverdueTasks($request->user()->id);

        $query = DaftarTugas::byUser($request->user()->id)->with('user');

        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'TERLAMBAT') {
                $query->byStatus('TERLAMBAT');
            } else {
                $query->byStatus($request->status);
            }
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->byKategori($request->kategori);
        }

        if ($request->has('important') && $request->important == 'true') {
            $query->important();
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('isi', 'like', '%' . $request->search . '%');
            });
        }

        $tasks = $query->orderBy('created_at', 'desc')->get();

        $tasks->transform(function ($task) {
            $task->deadline = Carbon::parse($task->deadline)->format('Y-m-d H:i:s');
            return $task;
        });

        $data = [
            'tasks' => $tasks,
            'user' => $request->user(),
            'export_date' => Carbon::now()->format('d F Y, H:i'),
            'filters' => [
                'status' => $request->get('status'),
                'kategori' => $request->get('kategori'),
                'important' => $request->get('important'),
                'search' => $request->get('search'),
            ],
            'statistics' => [
                'total' => $tasks->count(),
                'completed' => $tasks->where('status', 'SELESAI')->count(),
                'pending' => $tasks->where('status', 'BELUM SELESAI')->count(),
                'important' => $tasks->where('is_important', true)->count(),
            ]
        ];

        $pdf = Pdf::loadView('exports.tasks-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Tasks_Export_' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }
}
