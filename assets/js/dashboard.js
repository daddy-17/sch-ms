// dashboard.js - Frontend logic for fetching and updating UI

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('active');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('active');
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}

// Router & initialization
document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname;

    if (path.includes('dashboard.html')) {
        loadDashboardStats();
        // Optionally load recent activity
    } else if (path.includes('students.html')) {
        loadStudents();
        loadClassesDropdown('class_id');
        setupForm('add-student-form', '../api/create_student.php', loadStudents, 'addStudentModal');
    } else if (path.includes('teachers.html')) {
        loadTeachers();
        setupForm('add-teacher-form', '../api/create_teacher.php', loadTeachers, 'addTeacherModal');
    } else if (path.includes('classes.html')) {
        loadClasses();
        setupForm('add-class-form', '../api/create_class.php', loadClasses, 'addClassModal');
    }
});

// Form Submissions
function setupForm(formId, apiEndpoint, reloadCallback, modalId) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerText;
        submitBtn.innerText = 'Saving...';
        submitBtn.disabled = true;

        const formData = {};
        form.querySelectorAll('input, select').forEach(input => {
            if (input.id) formData[input.id] = input.value;
        });

        try {
            const response = await fetch(apiEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const result = await response.json();

            if (result.success) {
                form.reset();
                closeModal(modalId);
                reloadCallback();
                alert('Created successfully!');
            } else {
                alert('Error: ' + (result.error || 'Unknown error'));
            }
        } catch (err) {
            alert('Request failed');
        } finally {
            submitBtn.innerText = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Formatting helpers
function formatDate(isoString) {
    if (!isoString) return 'N/A';
    return new Date(isoString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getStatusBadge(status) {
    status = status || 'Active';
    if (status === 'Active') {
        return `<span style="background: #dcfce3; color: #10b981; padding: 0.25rem 0.5rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600;">Active</span>`;
    }
    return `<span style="background: #fef3c7; color: #f59e0b; padding: 0.25rem 0.5rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600;">${status}</span>`;
}

// Data Fetching

async function loadDashboardStats() {
    try {
        const res = await fetch('../api/get_stats.php');
        const data = await res.json();
        if (data.success) {
            const stats = data.data;
            const values = document.querySelectorAll('.stat-value');
            if (values.length >= 4) {
                values[0].innerText = stats.total_students || 0;
                values[1].innerText = stats.total_teachers || 0;
                values[2].innerText = stats.total_classes || 0;
                values[3].innerText = stats.attendance_rate || 'N/A';
            }
        }
    } catch (e) {
        console.error('Failed to load stats', e);
    }
}

async function loadClassesDropdown(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;
    try {
        const res = await fetch('../api/get_classes.php');
        const data = await res.json();
        if (data.success && data.data) {
            let options = '<option value="">Select Class...</option>';
            data.data.forEach(c => {
                options += `<option value="${c.id}">${c.name}</option>`;
            });
            select.innerHTML = options;
        }
    } catch (e) {
        console.error('Failed to load classes dropdown', e);
    }
}

async function loadClasses() {
    const tbody = document.getElementById('classes-table-body');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
    try {
        const res = await fetch('../api/get_classes.php');
        const data = await res.json();
        if (data.success && data.data.length > 0) {
            tbody.innerHTML = data.data.map(c => `
                <tr>
                    <td>${c.id.substring(0,8)}...</td>
                    <td>${c.name}</td>
                    <td>${c.grade_level}</td>
                    <td>${formatDate(c.created_at)}</td>
                    <td>
                        <button class="btn btn-sm" style="background: transparent; color: var(--danger-color); border: none;"><i class="ph ph-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">No classes found.</td></tr>';
        }
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load data.</td></tr>';
    }
}

async function loadTeachers() {
    const tbody = document.querySelector('.table tbody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';
    try {
        const res = await fetch('../api/get_teachers.php');
        const data = await res.json();
        if (data.success && data.data.length > 0) {
            tbody.innerHTML = data.data.map(t => `
                <tr>
                    <td>${t.id.substring(0,8)}...</td>
                    <td>${t.name}</td>
                    <td>${t.department}</td>
                    <td>${t.email}</td>
                    <td>${getStatusBadge(t.status)}</td>
                    <td>
                        <button class="btn btn-sm" style="background: transparent; color: var(--danger-color); border: none;"><i class="ph ph-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No teachers found.</td></tr>';
        }
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Failed to load data.</td></tr>';
    }
}

async function loadStudents() {
    const tbody = document.querySelector('.table tbody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';
    try {
        const res = await fetch('../api/get_students.php');
        const data = await res.json();
        if (data.success && data.data.length > 0) {
            tbody.innerHTML = data.data.map(s => `
                <tr>
                    <td>${s.id.substring(0,8)}...</td>
                    <td>${s.name}</td>
                    <td>${s.classes ? s.classes.name : 'Unassigned'}</td>
                    <td>${s.parent_contact}</td>
                    <td>${getStatusBadge(s.status)}</td>
                    <td>
                        <button class="btn btn-sm" style="background: transparent; color: var(--danger-color); border: none;"><i class="ph ph-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No students found.</td></tr>';
        }
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Failed to load data.</td></tr>';
    }
}
