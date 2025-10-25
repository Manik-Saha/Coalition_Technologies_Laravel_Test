<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tasks</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="/resources/css/app.css">
    @endif
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen py-10">
    <div class="max-w-3xl mx-auto px-4">
        <header class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Tasks</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Create, edit, delete and reorder tasks — drag to set priority (top = #1)</p>
        </header>

        <section class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <form id="createForm" class="flex gap-3 items-center">
                <div class="w-48 flex items-center gap-2">
                    <label for="filterProject" class="sr-only">Project</label>
                    <select id="filterProject" class="flex-1 block rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none" onchange="(function(v){ if(v) window.location.href='?project='+v }(this.value))">
                        <option value="">Select project</option>
                        @foreach($projects ?? collect() as $project)
                            <option value="{{ $project->id }}" @if(isset($selectedProject) && $selectedProject == $project->id) selected @endif>{{ $project->name }}</option>
                        @endforeach
                    </select>

                    <button id="createProjectBtn" type="button" title="Create project" class="inline-flex items-center justify-center p-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
                        <span class="sr-only">Create project</span>
                    </button>
                </div>

                <div class="flex-1">
                    <label for="taskName" class="sr-only">Task name</label>
                    <input id="taskName" name="name" type="text" required placeholder="Describe the task..." class="block w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md shadow"> 
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        Add
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <ul id="tasksList" class="space-y-3" data-project="{{ $selectedProject ?? '' }}">
                    @foreach($tasks as $task)
                        <li class="task flex items-center gap-3 p-3 rounded-md bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 shadow-sm" draggable="true" data-id="{{ $task->id }}">
                            <div class="flex items-center gap-3 w-full">
                                <div class="flex items-center gap-3">
                                    <span class="priority inline-flex items-center justify-center w-9 h-9 rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-sm font-medium text-gray-800 dark:text-gray-100">#{{ $task->priority }}</span>
                                    <button class="drag-handle text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Drag to reorder" aria-label="Drag to reorder">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M7 4a1 1 0 100 2 1 1 0 000-2zM7 9a1 1 0 100 2 1 1 0 000-2zM7 14a1 1 0 100 2 1 1 0 000-2zM13 4a1 1 0 100 2 1 1 0 000-2zM13 9a1 1 0 100 2 1 1 0 000-2zM13 14a1 1 0 100 2 1 1 0 000-2z"/></svg>
                                    </button>
                                </div>

                                <div class="flex-1">
                                    <div class="task-name text-gray-900 dark:text-gray-100 font-medium">{{ $task->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Created {{ $task->created_at->diffForHumans() }}</div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button data-action="edit" data-id="{{ $task->id }}" title="Edit" class="p-2 rounded-md bg-green-500 hover:bg-green-600 text-white shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-green-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M17.414 2.586a2 2 0 010 2.828l-9.9 9.9a1 1 0 01-.464.26l-4 1a1 1 0 01-1.213-1.213l1-4a1 1 0 01.26-.464l9.9-9.9a2 2 0 012.828 0zM15.121 5.121L14 4l1.121 1.121z"/></svg>
                                        <span class="sr-only">Edit</span>
                                    </button>
                                    <button data-action="delete" data-id="{{ $task->id }}" title="Delete" class="p-2 rounded-md bg-red-500 hover:bg-red-600 text-white shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H3a1 1 0 100 2h14a1 1 0 100-2h-2V3a1 1 0 00-1-1H6zm2 6a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 10-2 0v6a1 1 0 102 0V8z" clip-rule="evenodd"/></svg>
                                        <span class="sr-only">Delete</span>
                                    </button>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    </div>

    <script type="module" src="{{ asset('build/manifest.json') ? '/build/resources/js/app.js' : asset('resources/js/app.js') }}"></script>
</body>
</html>
