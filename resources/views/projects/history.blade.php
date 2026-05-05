@extends('layouts.app')

@section('styles')
<!-- Tailwind CSS CDN for Modern UI -->
<script src="https://cdn.tailwindcss.com"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    .project-history-page {
        font-family: 'Inter', sans-serif;
        background-color: #f9fafb;
    }

    /* Scrollbar for the timeline */
    .timeline-scroll {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #e5e7eb transparent;
    }

    .timeline-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .timeline-scroll::-webkit-scrollbar-thumb {
        background-color: #e5e7eb;
        border-radius: 20px;
    }

    /* Timeline styling */
    .timeline-line::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0.75rem;
        height: 100%;
        width: 1px;
        background-color: #e5e7eb;
        z-index: 0;
    }

    .timeline-item:last-child .timeline-line::before {
        height: 1rem;
    }

    /* Card enhancements */
    .hover-lift {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #e2e8f0 !important;
        /* Prominent Slate border - Initial Stage */
    }

    .hover-lift:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08) !important;
        border-color: #6366f1 !important;
    }

    /* Animations */
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-slide-in-right {
        animation: slideInRight 0.6s ease-out forwards;
    }
</style>
@endsection

@section('content')
<div class="project-history-page min-h-screen">
    <!-- Header Summary Bar (Sticky) -->
    <div class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200 px-6 py-4">
        <div class="max-w-[1600px] mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ url()->previous() }}" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <i class="mdi mdi-arrow-left text-gray-600 text-xl"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 leading-none">{{ $project->project_name }}</h1>
                    <p class="text-xs text-gray-500 mt-1">Project Command Center & Analytics</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @php
                $totalTasks = $project->tasks->count();
                $completedTasksCount = $project->tasks->where('status', 'Completed')->count();
                $progress = $totalTasks > 0 ? round(($completedTasksCount / $totalTasks) * 100) : 0;
                @endphp
                <div class="hidden md:flex items-center gap-3 mr-4">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Overall Progress</span>
                    <div class="w-32 bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-indigo-600 h-full rounded-full" style="width: {{ $progress }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-indigo-600">{{ $progress }}%</span>
                </div>
                <button onclick="window.print()" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-800 transition-all flex items-center gap-2">
                    <i class="mdi mdi-printer"></i> Export PDF
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-[1600px] mx-auto p-6">
        <div class="grid grid-cols-1 xl:grid-cols-10 gap-8">

            <!-- LEFT SECTION (70%) -->
            <div class="xl:col-span-7 space-y-8">

                <!-- 1. Project Overview Card -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover-lift transition-all duration-300">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2">
                            <i class="mdi mdi-view-dashboard-outline text-indigo-600"></i> Project Insights
                        </h2>
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-[10px] font-black uppercase border border-indigo-100">
                            {{ $project->status }}
                        </span>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-y-8 gap-x-12">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Client</label>
                                <p class="text-sm font-semibold text-gray-900">{{ $project->clients->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Category</label>
                                <p class="text-sm font-semibold text-gray-900">{{ $project->category->category ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Start Date</label>
                                <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($project->start_date)->format('d M, Y') }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Deadline</label>
                                @php $isOverdue = \Carbon\Carbon::parse($project->end_date)->isPast() && $project->status != 'Completed'; @endphp
                                <p class="text-sm font-bold {{ $isOverdue ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ \Carbon\Carbon::parse($project->end_date)->format('d M, Y') }}
                                    @if($isOverdue) <i class="mdi mdi-alert-circle ml-1"></i> @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Total Duration</label>
                                @php
                                $end = $project->act_end_date ? \Carbon\Carbon::parse($project->act_end_date) : now();
                                $duration = (int) \Carbon\Carbon::parse($project->start_date)->diffInDays($end);
                                @endphp
                                <p class="text-sm font-semibold text-gray-900">{{ $duration }} Days</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Working Hours</label>
                                <p class="text-sm font-bold text-indigo-600">{{ number_format($project->total_working_hours, 1) }}h</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Completed Date</label>
                                <p class="text-sm font-semibold text-gray-900">{{ $project->act_end_date ? \Carbon\Carbon::parse($project->act_end_date)->format('d M, Y') : '-' }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Created By</label>
                                <p class="text-sm font-semibold text-gray-900">{{ $project->creator->name ?? 'System' }}</p>
                            </div>
                        </div>

                        <div class="mt-10 pt-8 border-t border-gray-50">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Milestone Progress</span>
                                <span class="text-sm font-black text-indigo-600">{{ $progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-4 p-1">
                                <div class="bg-gradient-to-r from-indigo-600 to-blue-500 h-2 rounded-full transition-all duration-1000 shadow-sm" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Task Summary Card -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-xl shadow-sm hover-lift">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                <i class="mdi mdi-checkbox-multiple-marked-outline text-xl"></i>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Tasks</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ $project->tasks->count() }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-sm hover-lift">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                                <i class="mdi mdi-check-decagram text-xl"></i>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Completed</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ $project->tasks->where('status', 'Completed')->count() }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-sm hover-lift">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                                <i class="mdi mdi-clock-fast text-xl"></i>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pending</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ $project->tasks->whereIn('status', ['Pending', 'InProgress'])->count() }}</p>
                    </div>
                    @php $overdueTasks = $project->tasks->filter(fn($t) => \Carbon\Carbon::parse($t->enddate)->isPast() && $t->status != 'Completed')->count(); @endphp
                    <div class="bg-white p-5 rounded-xl border border-{{ $overdueTasks > 0 ? 'red-200 bg-red-50/20' : 'gray-200' }} shadow-sm hover-lift">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-{{ $overdueTasks > 0 ? 'red' : 'gray' }}-50 rounded-lg text-{{ $overdueTasks > 0 ? 'red' : 'gray' }}-600">
                                <i class="mdi mdi-alert-circle-outline text-xl"></i>
                            </div>
                            <span class="text-[10px] font-bold text-{{ $overdueTasks > 0 ? 'red' : 'gray' }}-400 uppercase tracking-widest">Overdue</span>
                        </div>
                        <p class="text-2xl font-bold text-{{ $overdueTasks > 0 ? 'red' : 'gray' }}-900">{{ $overdueTasks }}</p>
                    </div>
                </div>

                <!-- 3. Task Details Section -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover-lift transition-all duration-300">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2">
                            <i class="mdi mdi-format-list-bulleted text-indigo-600"></i> Task Analytics
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                                    <th class="px-6 py-4">Task Information</th>
                                    <th class="px-6 py-4">Assignee</th>
                                    <th class="px-6 py-4">Timeline</th>
                                    <th class="px-6 py-4">Work Effort</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Progress</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($project->tasks as $task)
                                @php $isTOverdue = \Carbon\Carbon::parse($task->enddate)->isPast() && $task->status != 'Completed'; @endphp
                                <tr class="hover:bg-gray-50/30 transition-colors {{ $isTOverdue ? 'bg-red-50/10' : '' }}">
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-900">{{ $task->title }}</span>
                                            @php
                                            $tpColor = 'gray';
                                            if(strtolower($task->priority) == 'high') $tpColor = 'red';
                                            elseif(strtolower($task->priority) == 'medium') $tpColor = 'amber';
                                            elseif(strtolower($task->priority) == 'low') $tpColor = 'emerald';
                                            @endphp
                                            <span class="mt-1 inline-flex w-fit text-[9px] font-black uppercase text-{{ $tpColor }}-600 tracking-tighter">{{ $task->priority ?? 'Medium' }} PRIORITY</span>
                                            
                                            @if($task->documents->count() > 0)
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach($task->documents as $tdoc)
                                                <a href="{{ route('documents.download', $tdoc->id) }}" class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-gray-50 border border-gray-100 rounded text-[9px] text-gray-500 hover:text-indigo-600 hover:border-indigo-100 transition-colors" title="{{ $tdoc->original_name }}">
                                                    <i class="mdi mdi-attachment"></i> Doc
                                                </a>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($task->user->name ?? 'U') }}&background=6366f1&color=fff" class="w-6 h-6 rounded-full border border-gray-100 shadow-sm">
                                            <span class="text-xs font-semibold text-gray-600">{{ $task->user->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-[10px] text-gray-400">Due: <span class="font-bold {{ $isTOverdue ? 'text-red-500' : 'text-gray-600' }}">{{ \Carbon\Carbon::parse($task->enddate)->format('d M') }}</span></span>
                                            @if($task->act_enddate)
                                            <span class="text-[10px] text-gray-400">End: <span class="font-bold text-emerald-500">{{ \Carbon\Carbon::parse($task->act_enddate)->format('d M') }}</span></span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-700">{{ number_format($task->total_time, 1) }}h</span>
                                            <span class="text-[9px] text-gray-400 uppercase font-black">Logged</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        @php
                                        $tColor = 'gray';
                                        if($task->status == 'Completed') $tColor = 'emerald';
                                        elseif($task->status == 'InProgress') $tColor = 'blue';
                                        elseif($task->status == 'Pending') $tColor = 'amber';
                                        @endphp
                                        <span class="px-2 py-1 bg-{{ $tColor }}-50 text-{{ $tColor }}-700 rounded text-[9px] font-black uppercase border border-{{ $tColor }}-100">
                                            {{ $task->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-xs font-black text-gray-900">{{ $task->progress }}%</span>
                                            <div class="w-12 bg-gray-100 rounded-full h-1 overflow-hidden">
                                                <div class="bg-indigo-600 h-full" style="width: {{ $task->progress }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">No tasks assigned yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 4. Project Documents Gallery -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover-lift transition-all duration-300 mt-6">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2">
                            <i class="mdi mdi-attachment text-emerald-600"></i> Project Documents
                        </h2>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $project->documents->count() }} Files Attached</span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @forelse($project->documents as $doc)
                            @php
                            $ext = strtolower($doc->file_type);
                            $icon = 'mdi-file-outline';
                            $color = 'blue';
                            if(in_array($ext, ['jpg','jpeg','png','gif'])) { $icon = 'mdi-file-image'; $color = 'blue'; }
                            elseif($ext == 'pdf') { $icon = 'mdi-file-pdf-box'; $color = 'red'; }
                            elseif(in_array($ext, ['doc','docx'])) { $icon = 'mdi-file-word'; $color = 'indigo'; }
                            elseif(in_array($ext, ['xls','xlsx'])) { $icon = 'mdi-file-excel'; $color = 'emerald'; }
                            @endphp
                            <div class="group relative flex items-center p-3 rounded-lg border border-gray-100 hover:border-{{ $color }}-200 hover:bg-{{ $color }}-50/30 transition-all duration-200">
                                <div class="w-10 h-10 rounded-lg bg-{{ $color }}-50 text-{{ $color }}-600 flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                    <i class="mdi {{ $icon }} text-xl"></i>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <p class="text-xs font-bold text-gray-900 truncate pr-6" title="{{ $doc->original_name }}">{{ $doc->original_name }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium">
                                        {{ number_format($doc->file_size / 1024, 1) }} KB • {{ $doc->user->name ?? 'System' }}
                                    </p>
                                </div>
                                <a href="{{ route('documents.download', $doc->id) }}" class="absolute right-3 p-1.5 text-gray-400 hover:text-indigo-600 transition-colors">
                                    <i class="mdi mdi-download text-lg"></i>
                                </a>
                            </div>
                            @empty
                            <div class="col-span-full py-10 text-center bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                                <i class="mdi mdi-cloud-upload-outline text-4xl text-gray-300 block mb-2"></i>
                                <p class="text-sm text-gray-400 font-medium">No documents attached to this project.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SECTION (30%) - ACTIVITY TIMELINE -->
            <div class="xl:col-span-3">
                <div class="sticky top-24 bg-white rounded-xl shadow-sm overflow-hidden h-fit hover-lift transition-all duration-300">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-900 text-white flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-widest flex items-center gap-2">
                            <i class="mdi mdi-history"></i> Activity Logs
                        </h2>
                    </div>

                    <div class="p-6 timeline-scroll">
                        <div class="relative space-y-8 timeline-line">
                            @forelse($project->histories->sortByDesc('created_at') as $history)
                            <div class="timeline-item relative pl-8">
                                <!-- Icon/Point -->
                                @php
                                $icon = 'mdi-record-circle-outline'; $iColor = 'gray';
                                if(str_contains(strtolower($history->comments), 'created')) { $icon = 'mdi-plus-circle'; $iColor = 'indigo'; }
                                elseif(str_contains(strtolower($history->comments), 'completed')) { $icon = 'mdi-check-circle'; $iColor = 'emerald'; }
                                elseif(str_contains(strtolower($history->comments), 'status')) { $icon = 'mdi-refresh'; $iColor = 'amber'; }
                                elseif(str_contains(strtolower($history->comments), 'assigned')) { $icon = 'mdi-account-arrow-right'; $iColor = 'blue'; }
                                @endphp
                                <div class="absolute left-0 top-0.5 z-10 w-6 h-6 bg-white border border-gray-200 rounded-full flex items-center justify-center text-{{ $iColor }}-600">
                                    <i class="mdi {{ $icon }} text-sm"></i>
                                </div>

                                <div class="animate-slide-in-right">
                                    <div class="flex justify-between items-start mb-1">
                                        <p class="text-xs font-black text-gray-900 uppercase leading-tight">
                                            {{ explode(' by ', $history->comments)[0] }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed">{{ $history->comments }}</p>

                                    <div class="mt-3 flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-4 h-4 rounded-full bg-gray-100 flex items-center justify-center text-[8px] font-bold text-gray-500">
                                                {{ substr($history->user->name ?? 'S', 0, 1) }}
                                            </div>
                                            <span class="text-[9px] font-bold text-gray-400 uppercase">{{ $history->user->name ?? 'System' }}</span>
                                        </div>
                                        <span class="text-[9px] font-medium text-gray-400 italic">
                                            {{ \Carbon\Carbon::parse($history->created_at)->format('d M, h:i A') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-10 text-gray-400 italic">No history found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
