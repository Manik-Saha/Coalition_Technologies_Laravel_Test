const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

function qs(selector, el = document) { return el.querySelector(selector); }
function qsa(selector, el = document) { return Array.from(el.querySelectorAll(selector)); }

function postJson(url, data, method = 'POST') {
    return fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    }).then(r => r.json());
}

function init() {
    const form = qs('#createForm');
    const input = qs('#taskName');
    const list = qs('#tasksList');
    const projectSelect = qs('#filterProject');
    const createProjectBtn = qs('#createProjectBtn');

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!input.value.trim()) return;
        const projectId = projectSelect?.value || null;
        await postJson('/tasks', { name: input.value.trim(), project_id: projectId });
        // refresh to show created task with server-provided priority and selected project
        if (projectId) {
            window.location.href = '?project=' + projectId;
        } else {
            window.location.reload();
        }
    });

    // Create project flow (prompt-based)
    createProjectBtn?.addEventListener('click', async (e) => {
        const name = prompt('New project name');
        if (!name || !name.trim()) return;
        try {
            const project = await postJson('/projects', { name: name.trim() });
            if (project && project.id) {
                // redirect to the new project's view
                window.location.href = '?project=' + project.id;
            } else {
                window.location.reload();
            }
        } catch (err) {
            console.error('Create project failed', err);
            window.location.reload();
        }
    });

    // Edit / Delete buttons
    list?.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        const id = btn.getAttribute('data-id');
        if (action === 'edit') {
            const el = btn.closest('.task');
            const nameEl = el.querySelector('.task-name');
            const newName = prompt('Edit task name', nameEl.textContent);
            if (newName !== null) {
                await postJson(`/tasks/${id}`, { name: newName }, 'PATCH');
                nameEl.textContent = newName;
            }
        }
        if (action === 'delete') {
            if (!confirm('Delete this task?')) return;
            await fetch(`/tasks/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } });
            window.location.reload();
        }
    });

    // Drag & drop
    let dragEl = null;

    list?.addEventListener('dragstart', (e) => {
        const el = e.target.closest('.task');
        if (!el) return;
        dragEl = el;
        // visual affordance while dragging
        el.classList.add('opacity-60', 'shadow-lg', 'scale-95');
        e.dataTransfer?.setData('text/plain', el.getAttribute('data-id'));
        e.dataTransfer?.setDragImage(el, 10, 10);
    });

    list?.addEventListener('dragend', async (e) => {
        if (dragEl) {
            dragEl.classList.remove('opacity-60', 'shadow-lg', 'scale-95');
        }
        // send new order and update priority badges on success
        const ids = qsa('.task', list).map(el => Number(el.getAttribute('data-id')));
        try {
            const res = await postJson('/tasks/reorder', { order: ids });
            if (res && res.ok) {
                // update priority badges instantly
                qsa('.task', list).forEach((el, idx) => {
                    const p = el.querySelector('.priority');
                    if (p) p.textContent = `#${idx + 1}`;
                });
            }
        } catch (err) {
            // ignore silently; server may respond with error
            console.error('Reorder failed', err);
        }

        dragEl = null;
    });

    list?.addEventListener('dragover', (e) => {
        e.preventDefault();
        const afterEl = getDragAfterElement(list, e.clientY);
        if (afterEl == null) {
            list.appendChild(dragEl);
        } else {
            list.insertBefore(dragEl, afterEl);
        }
    });

    function getDragAfterElement(container, y) {
        const draggableElements = qsa('.task:not(.dragging)', container);
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element || null;
    }
}

document.addEventListener('DOMContentLoaded', init);

export default {};
