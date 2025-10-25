Tasks feature added

Files added/changed:
- database/migrations/0001_01_01_000003_create_tasks_table.php — creates tasks table (id, name, priority, timestamps)
- app/Models/Task.php — Eloquent model
- app/Http/Controllers/TaskController.php — index/store/update/destroy/reorder actions
- resources/views/tasks/index.blade.php — UI at /tasks
- resources/js/tasks.js — front-end (create/edit/delete/drag-reorder)
- resources/js/app.js — imports tasks.js
- routes/web.php — routes for tasks

Quick setup

1) Install JS deps and build assets (for development):

```bash
# from project root
npm install
npm run dev
```

2) Run migrations to create the table:

```bash
php artisan migrate
```

3) Visit http://localhost:5173/tasks (vite dev) or the app's URL (if using php artisan serve) to use the tasks UI.

Notes
- Reordering is done in the browser and posted to /tasks/reorder. The server updates priorities so priority 1 is top.
- Deleting a task will renumber remaining tasks so priorities remain consecutive starting from 1.
