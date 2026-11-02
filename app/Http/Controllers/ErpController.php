<?php

namespace App\Http\Controllers;

use App\ErpRecord;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ErpController extends Controller
{
    private $modules = [
        'employees', 'departments', 'tasks', 'projects', 'clients', 'payments',
        'files', 'chat', 'attendance', 'payroll', 'calendar', 'inventory',
        'personal_tasks',
    ];

    public function dashboard(Request $request)
    {
        $records = ErpRecord::query();
        $user = $request->user();
        $today = now()->toDateString();
        $clockedIn = ErpRecord::query()
            ->where('module', 'attendance')
            ->where('created_by', auth()->id())
            ->whereDate('record_date', $today)
            ->where('status', 'checked_in')
            ->exists();
        $attendanceRecords = (clone $records)
            ->where('module', 'attendance')
            ->whereDate('record_date', $today);
        if ($user->role !== 'admin') {
            $attendanceRecords->where('created_by', $user->id);
        }

        return response()->json([
            'employees' => $user->canAccess('employees', 'view')
                ? (clone $records)->where('module', 'employees')->where('status', 'active')->count()
                : null,
            'active_projects' => $user->canAccess('projects', 'view')
                ? (clone $records)->where('module', 'projects')->whereNotIn('status', ['completed', 'archived'])->count()
                : null,
            'completed_tasks' => $user->canAccess('tasks', 'view')
                ? (clone $records)->where('module', 'tasks')->where('status', 'completed')->count()
                : null,
            'open_tasks' => $user->canAccess('tasks', 'view')
                ? (clone $records)->where('module', 'tasks')->whereNotIn('status', ['completed', 'archived'])->count()
                : null,
            'revenue' => $user->canAccess('payments', 'view')
                ? (clone $records)->where('module', 'payments')->where('status', 'paid')->whereMonth('record_date', now()->month)->whereYear('record_date', now()->year)->sum('amount')
                : null,
            'projects' => $user->canAccess('projects', 'view')
                ? (clone $records)->where('module', 'projects')->latest()->limit(5)->get()
                : [],
            'tasks' => $user->canAccess('tasks', 'view')
                ? (clone $records)->where('module', 'tasks')->latest()->limit(8)->get()
                : [],
            'attendance' => $user->canAccess('attendance', 'view')
                ? $attendanceRecords->count()
                : null,
            'clocked_in' => $clockedIn,
        ]);
    }

    public function createEmployeeAccount(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403, 'Only administrators can create employee accounts.');
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => 'required|string|min:10|max:72',
            'role' => 'nullable|in:employee,manager',
            'job_title' => 'nullable|string|max:120',
            'department' => 'nullable|string|max:120',
            'department_id' => 'nullable|integer',
            'phone' => 'nullable|string|max:40',
            'permissions' => 'nullable|array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'in:view,write,delete',
        ]);
        $permissions = $this->normalizePermissions($validated['permissions'] ?? null);
        if (strlen($validated['password']) > 72) {
            throw ValidationException::withMessages([
                'password' => ['The password must not exceed 72 bytes.'],
            ]);
        }
        $department = null;
        if (! empty($validated['department_id'])) {
            $department = ErpRecord::query()->where('module', 'departments')->find($validated['department_id']);
            if (! $department) {
                throw ValidationException::withMessages([
                    'department_id' => ['The selected department does not exist.'],
                ]);
            }
        }

        $employee = DB::transaction(function () use ($validated, $request, $department, $permissions) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role' => $validated['role'] ?? 'employee',
                'is_active' => true,
                'permissions' => $permissions,
            ]);

            return ErpRecord::create([
                'module' => 'employees',
                'title' => $validated['name'],
                'description' => $validated['job_title'] ?? null,
                'status' => 'active',
                'created_by' => $request->user()->id,
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_active' => true,
                    'permissions' => $permissions,
                    'job_title' => $validated['job_title'] ?? '',
                    'department_id' => $department ? (string) $department->id : '',
                    'department' => $department ? $department->title : ($validated['department'] ?? ''),
                    'phone' => $validated['phone'] ?? '',
                ],
            ]);
        });

        return response()->json(['data' => $employee], 201);
    }

    public function updateEmployeeAccount(Request $request, $id)
    {
        abort_unless($request->user()->role === 'admin', 403, 'Only administrators can manage employee accounts.');

        $employee = ErpRecord::query()
            ->where('module', 'employees')
            ->findOrFail($id);
        $userId = $employee->metadata['user_id'] ?? null;
        $account = User::query()->findOrFail($userId);
        abort_if($account->role === 'admin', 403, 'Administrator accounts cannot be changed here.');

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email,' . $account->id,
            'password' => 'nullable|string|min:10|max:72',
            'role' => 'required|in:employee,manager',
            'is_active' => 'required|boolean',
            'job_title' => 'nullable|string|max:120',
            'department' => 'nullable|string|max:120',
            'department_id' => 'nullable|integer',
            'phone' => 'nullable|string|max:40',
            'permissions' => 'nullable|array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'in:view,write,delete',
        ]);
        $permissions = array_key_exists('permissions', $validated)
            ? $this->normalizePermissions($validated['permissions'])
            : $account->permissionMatrix();
        if (! empty($validated['password']) && strlen($validated['password']) > 72) {
            throw ValidationException::withMessages([
                'password' => ['The password must not exceed 72 bytes.'],
            ]);
        }
        $department = null;
        if (! empty($validated['department_id'])) {
            $department = ErpRecord::query()->where('module', 'departments')->find($validated['department_id']);
            if (! $department) {
                throw ValidationException::withMessages([
                    'department_id' => ['The selected department does not exist.'],
                ]);
            }
        }

        abort_if((int) $account->id === (int) $request->user()->id && ! $validated['is_active'], 422, 'You cannot deactivate your own account.');

        $employee = DB::transaction(function () use ($validated, $employee, $account, $department, $permissions) {
            $account->name = $validated['name'];
            $account->email = $validated['email'];
            $account->role = $validated['role'];
            $account->is_active = (bool) $validated['is_active'];
            $account->permissions = $permissions;

            if (! empty($validated['password'])) {
                $account->password = bcrypt($validated['password']);
            }

            $account->save();
            $metadata = $employee->metadata ?? [];
            $metadata = array_merge($metadata, [
                'email' => $account->email,
                'role' => $account->role,
                'is_active' => $account->is_active,
                'permissions' => $permissions,
                'job_title' => $validated['job_title'] ?? '',
                'department_id' => $department ? (string) $department->id : '',
                'department' => $department ? $department->title : ($validated['department'] ?? ''),
                'phone' => $validated['phone'] ?? '',
            ]);
            $employee->title = $account->name;
            $employee->description = $validated['job_title'] ?? null;
            $employee->status = $account->is_active ? 'active' : 'archived';
            $employee->metadata = $metadata;
            $employee->save();

            return $employee;
        });

        return response()->json(['data' => $employee]);
    }

    public function index(Request $request, $module)
    {
        if ($module === 'reports') {
            return $this->report($request);
        }

        $this->ensureModule($module);
        $this->authorizeModule($request, $module, 'view');

        $records = ErpRecord::query()
            ->where('module', $module)
            ->with(['creator:id,name', 'assignee:id,name']);

        if ($module === 'personal_tasks') {
            $records->where('created_by', $request->user()->id);
        }

        if ($module === 'attendance' && $request->user()->role !== 'admin') {
            $records->where('created_by', $request->user()->id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $records->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $records->where('status', $request->input('status'));
        }

        $related = [];
        $relatedModules = [
            'employees' => ['departments'],
            'projects' => ['clients'],
            'tasks' => ['projects'],
            'attendance' => ['employees'],
            'payroll' => ['employees'],
        ];

        foreach ($relatedModules[$module] ?? [] as $relatedModule) {
            $related[$relatedModule] = ErpRecord::query()
                ->where('module', $relatedModule)
                ->orderBy('title')
                ->limit(200)
                ->get(['id', 'title']);
        }

        $paginated = $records->latest()->paginate(50);

        if ($module === 'employees' && $request->user()->role !== 'admin') {
            $paginated->getCollection()->each(function ($record) {
                $metadata = $record->metadata ?? [];
                unset($metadata['email'], $metadata['phone'], $metadata['salary'], $metadata['user_id'], $metadata['role'], $metadata['is_active'], $metadata['permissions']);
                $record->metadata = $metadata;
            });
        }

        if ($module === 'files') {
            $paginated->getCollection()->each(function ($record) {
                $metadata = $record->metadata ?? [];
                unset($metadata['file_path']);
                $record->metadata = $metadata;
            });
        }

        return response()->json([
            'data' => $paginated,
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'related' => $related,
            'clocked_in' => $module === 'attendance' && ErpRecord::query()
                ->where('module', 'attendance')
                ->where('created_by', $request->user()->id)
                ->whereDate('record_date', now()->toDateString())
                ->where('status', 'checked_in')
                ->exists(),
        ]);
    }

    public function store(Request $request, $module)
    {
        $this->ensureModule($module);
        $this->authorizeModule($request, $module, 'write');
        abort_if($module === 'attendance', 403, 'Use the attendance clock to record your own shifts.');
        $validated = $this->validateRecord($request, $module);
        $this->validateAssignee($validated);

        $record = ErpRecord::create($validated + [
            'module' => $module,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['data' => $record->load(['creator:id,name', 'assignee:id,name'])], 201);
    }

    public function update(Request $request, $module, $id)
    {
        $this->ensureModule($module);
        $this->authorizeModule($request, $module, 'write');
        abort_if($module === 'attendance', 403, 'Use the attendance clock to record your own shifts.');
        $record = $this->findRecord($request, $module, $id);
        if ($module === 'chat' && (int) $record->created_by !== (int) $request->user()->id && $request->user()->role !== 'admin') {
            abort(403, 'You may only edit messages you created.');
        }
        if ($module === 'employees' && ! empty($record->metadata['user_id'])) {
            abort(422, 'Update linked employee details through the employee account form.');
        }
        $validated = $this->validateRecord($request, $module);
        $this->validateAssignee($validated);
        if ($module === 'inventory' && (int) ($validated['metadata']['quantity'] ?? -1) !== (int) ($record->metadata['quantity'] ?? 0)) {
            throw ValidationException::withMessages([
                'metadata.quantity' => ['Use the stock adjustment action to change the inventory quantity.'],
            ]);
        }
        $record->update($validated);

        return response()->json(['data' => $record->fresh()->load(['creator:id,name', 'assignee:id,name'])]);
    }

    public function destroy(Request $request, $module, $id)
    {
        $this->ensureModule($module);
        $this->authorizeModule($request, $module, 'delete');
        $record = $this->findRecord($request, $module, $id);

        if ((int) $record->created_by !== (int) $request->user()->id && $request->user()->role !== 'admin') {
            abort(403, 'You may only delete records you created.');
        }

        if ($module === 'employees' && ! empty($record->metadata['user_id'])) {
            abort(422, 'Manage linked employee accounts from the employee account form instead of deleting the profile.');
        }

        $references = [
            'departments' => [['employees', 'department_id']],
            'clients' => [['projects', 'client_id']],
            'projects' => [['tasks', 'project_id']],
            'employees' => [['payroll', 'employee_id'], ['attendance', 'employee_id']],
        ];
        foreach ($references[$module] ?? [] as $reference) {
            $isReferenced = ErpRecord::query()
                ->where('module', $reference[0])
                ->where('metadata->' . $reference[1], (string) $record->id)
                ->exists();
            if ($isReferenced) {
                abort(422, 'This record is referenced by other records and cannot be deleted.');
            }
        }

        if ($module === 'files' && isset($record->metadata['file_path'])) {
            Storage::disk('local')->delete($record->metadata['file_path']);
        }

        $record->delete();

        return response()->json(['message' => 'Record deleted.']);
    }

    public function upload(Request $request)
    {
        $this->authorizeModule($request, 'files', 'write');
        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,csv,txt,png,jpg,jpeg,gif,webp',
            'description' => 'nullable|string|max:2000',
        ]);

        $file = $validated['file'];
        $path = $file->store('erp-files', 'local');
        $record = ErpRecord::create([
            'module' => 'files',
            'title' => $file->getClientOriginalName(),
            'description' => $validated['description'] ?? null,
            'status' => 'active',
            'created_by' => $request->user()->id,
            'metadata' => [
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ],
        ]);

        $response = $record->toArray();
        unset($response['metadata']['file_path']);

        return response()->json(['data' => $response], 201);
    }

    public function download(Request $request, $id)
    {
        $this->authorizeModule($request, 'files', 'view');
        $record = $this->findRecord($request, 'files', $id);
        $path = $record->metadata['file_path'] ?? null;

        abort_unless($path && Storage::disk('local')->exists($path), 404, 'File not found.');

        return Storage::disk('local')->download(
            $path,
            $record->metadata['file_name'] ?? $record->title
        );
    }

    public function report(Request $request)
    {
        $this->authorizeModule($request, 'reports', 'view');
        $user = $request->user();
        $visibleModules = array_keys(array_filter($user->permissionMatrix(), function ($actions) {
            return in_array('view', $actions, true);
        }));

        $totalsQuery = ErpRecord::query()
            ->whereIn('module', $visibleModules)
            ->where(function ($query) use ($user) {
                $query->where('module', '!=', 'attendance')
                    ->orWhere(function ($attendance) use ($user) {
                        $attendance->where('module', 'attendance');
                        if ($user->role !== 'admin') {
                            $attendance->where('created_by', $user->id);
                        }
                    });
            })
            ->where(function ($query) use ($user) {
                $query->where('module', '!=', 'personal_tasks')
                    ->orWhere(function ($personalTasks) use ($user) {
                        $personalTasks->where('module', 'personal_tasks')
                            ->where('created_by', $user->id);
                    });
            });
        $totals = $totalsQuery
            ->select('module', DB::raw('count(*) as total'), DB::raw('sum(amount) as amount'))
            ->groupBy('module')
            ->get();

        $payments = ErpRecord::query()->where('module', 'payments');
        $payroll = ErpRecord::query()->where('module', 'payroll');
        $today = now()->toDateString();
        $lowStock = $user->canAccess('inventory', 'view')
            ? ErpRecord::query()->where('module', 'inventory')->get()->filter(function ($item) {
                $quantity = (float) ($item->metadata['quantity'] ?? 0);
                $reorderLevel = $item->metadata['reorder_level'] ?? null;

                return $reorderLevel !== null && $quantity <= (float) $reorderLevel;
            })->count()
            : null;

        return response()->json([
            'data' => $totals,
            'summary' => [
                'payments_received' => $user->canAccess('payments', 'view') ? (clone $payments)->where('status', 'paid')->sum('amount') : null,
                'payments_outstanding' => $user->canAccess('payments', 'view') ? (clone $payments)->whereIn('status', ['unpaid', 'pending'])->sum('amount') : null,
                'payroll_paid' => $user->canAccess('payroll', 'view') ? (clone $payroll)->where('status', 'paid')->sum('amount') : null,
                'payroll_outstanding' => $user->canAccess('payroll', 'view') ? (clone $payroll)->whereIn('status', ['unpaid', 'pending'])->sum('amount') : null,
                'tasks_open' => $user->canAccess('tasks', 'view') ? ErpRecord::query()->where('module', 'tasks')->whereNotIn('status', ['completed', 'archived'])->count() : null,
                'tasks_completed' => $user->canAccess('tasks', 'view') ? ErpRecord::query()->where('module', 'tasks')->where('status', 'completed')->count() : null,
                'attendance_today' => $user->canAccess('attendance', 'view')
                    ? ErpRecord::query()
                        ->where('module', 'attendance')
                        ->whereDate('record_date', $today)
                        ->when($user->role !== 'admin', function ($query) use ($user) {
                            $query->where('created_by', $user->id);
                        })
                        ->count()
                    : null,
                'low_stock' => $user->canAccess('inventory', 'view') ? $lowStock : null,
            ],
        ]);
    }

    public function clock(Request $request)
    {
        $this->authorizeModule($request, 'attendance', 'write');
        $validated = $request->validate([
            'action' => 'required|in:clock_in,clock_out',
        ]);
        $today = now()->toDateString();
        $employeeName = $request->user()->name;
        $record = ErpRecord::query()
            ->where('module', 'attendance')
            ->whereDate('record_date', $today)
            ->where('created_by', $request->user()->id)
            ->latest()
            ->first();

        if ($validated['action'] === 'clock_in') {
            if ($record && empty($record->metadata['clock_out'])) {
                return response()->json(['message' => 'This employee is already clocked in.'], 422);
            }

            $record = ErpRecord::create([
                'module' => 'attendance',
                'title' => $employeeName,
                'status' => 'checked_in',
                'record_date' => $today,
                'created_by' => $request->user()->id,
                'metadata' => ['user_id' => (string) $request->user()->id, 'employee_name' => $employeeName, 'clock_in' => now()->toDateTimeString()],
            ]);
        } else {
            if (! $record || ! empty($record->metadata['clock_out'])) {
                return response()->json(['message' => 'No open attendance record was found.'], 422);
            }

            $record->metadata = array_merge($record->metadata, ['clock_out' => now()->toDateTimeString()]);
            $record->status = 'checked_out';
            $record->save();
        }

        return response()->json(['data' => $record]);
    }

    public function adjustInventory(Request $request, $id)
    {
        $this->authorizeModule($request, 'inventory', 'write');
        $validated = $request->validate([
            'action' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1|max:1000000000',
            'reason' => 'nullable|string|max:500',
        ]);

        $record = DB::transaction(function () use ($validated, $request, $id) {
            $item = ErpRecord::query()
                ->where('module', 'inventory')
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();
            $metadata = $item->metadata ?? [];
            $currentQuantity = (int) ($metadata['quantity'] ?? 0);
            $quantity = (int) $validated['quantity'];
            $change = $validated['action'] === 'increase' ? $quantity : -$quantity;

            if ($currentQuantity + $change < 0) {
                throw ValidationException::withMessages([
                    'quantity' => ['The stock adjustment cannot reduce inventory below zero.'],
                ]);
            }

            $metadata['quantity'] = (string) ($currentQuantity + $change);
            $movements = $metadata['movements'] ?? [];
            $movements[] = [
                'change' => $change,
                'reason' => $validated['reason'] ?? '',
                'user_id' => (string) $request->user()->id,
                'user_name' => $request->user()->name,
                'recorded_at' => now()->toDateTimeString(),
            ];
            $metadata['movements'] = array_slice($movements, -500);
            $item->metadata = $metadata;
            $item->save();

            return $item;
        });

        return response()->json(['data' => $record]);
    }

    private function validateRecord(Request $request, $module)
    {
        $metadataRules = [
            'employees' => [
                'email' => 'nullable|email|max:190',
                'phone' => 'nullable|string|max:40',
                'job_title' => 'nullable|string|max:120',
                'department' => 'nullable|string|max:120',
                'department_id' => 'nullable|integer',
            ],
            'departments' => [
                'manager_id' => 'nullable|integer',
                'location' => 'nullable|string|max:120',
            ],
            'tasks' => [
                'priority' => 'nullable|in:low,medium,high',
                'project_id' => 'nullable|integer',
            ],
            'projects' => [
                'client_id' => 'nullable|integer',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'progress' => 'nullable|numeric|min:0|max:100',
            ],
            'clients' => [
                'company' => 'nullable|string|max:190',
                'email' => 'nullable|email|max:190',
                'phone' => 'nullable|string|max:40',
                'address' => 'nullable|string|max:500',
            ],
            'payments' => [
                'invoice_number' => 'nullable|string|max:120',
                'payment_method' => 'nullable|string|max:120',
            ],
            'attendance' => [
                'employee_id' => 'nullable|integer',
            ],
            'payroll' => [
                'employee_id' => 'required|integer',
                'period' => 'required|date_format:Y-m',
                'base_salary' => 'required|numeric|min:0|max:999999999999.99',
                'allowances' => 'nullable|numeric|min:0|max:999999999999.99',
                'deductions' => 'nullable|numeric|min:0|max:999999999999.99',
            ],
            'calendar' => [
                'location' => 'nullable|string|max:500',
                'attendees' => 'nullable|string|max:1000',
            ],
            'inventory' => [
                'sku' => 'required|string|max:120',
                'quantity' => 'required|integer|min:0|max:1000000000',
                'unit' => 'nullable|string|max:40',
                'reorder_level' => 'nullable|integer|min:0|max:1000000000',
                'category' => 'nullable|string|max:120',
            ],
            'personal_tasks' => [
                'priority' => 'nullable|in:low,medium,high',
            ],
            'files' => [],
            'chat' => [],
        ];
        $rules = [
            'title' => 'required|string|max:190',
            'description' => 'nullable|string|max:10000',
            'status' => 'required|in:active,in_progress,completed,pending,overdue,paid,unpaid,draft,archived,checked_in,checked_out',
            'amount' => 'nullable|numeric|min:0|max:999999999999.99',
            'record_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|integer|exists:users,id',
            'metadata' => 'nullable|array',
        ];
        foreach ($metadataRules[$module] ?? [] as $field => $rule) {
            $rules['metadata.' . $field] = $rule;
        }

        $validated = $request->validate($rules);
        $submittedMetadata = (array) $request->input('metadata', []);
        $unknownMetadata = array_diff(array_keys($submittedMetadata), array_keys($metadataRules[$module] ?? []));
        if ($unknownMetadata) {
            throw ValidationException::withMessages([
                'metadata' => ['The record contains unsupported fields: ' . implode(', ', $unknownMetadata) . '.'],
            ]);
        }

        $relationships = [
            'employees' => ['department_id' => 'departments'],
            'departments' => ['manager_id' => 'users'],
            'projects' => ['client_id' => 'clients'],
            'tasks' => ['project_id' => 'projects'],
            'attendance' => ['employee_id' => 'employees'],
            'payroll' => ['employee_id' => 'employees'],
        ];

        foreach ($relationships[$module] ?? [] as $field => $relatedModule) {
            $relatedId = $validated['metadata'][$field] ?? null;
            $relatedExists = $relatedModule === 'users'
                ? User::query()->where('is_active', true)->whereKey($relatedId)->exists()
                : ErpRecord::query()->where('module', $relatedModule)->whereKey($relatedId)->exists();

            if ($relatedId !== null && $relatedId !== '' && ! $relatedExists) {
                throw ValidationException::withMessages([
                    'metadata.' . $field => ['The selected related record does not exist in this module.'],
                ]);
            }
        }

        if ($module === 'payroll') {
            $metadata = $validated['metadata'];
            $baseSalary = (int) round(((float) $metadata['base_salary']) * 100);
            $allowances = (int) round(((float) ($metadata['allowances'] ?? 0)) * 100);
            $deductions = (int) round(((float) ($metadata['deductions'] ?? 0)) * 100);

            if ($deductions > $baseSalary + $allowances) {
                throw ValidationException::withMessages([
                    'metadata.deductions' => ['Deductions cannot exceed the salary plus allowances.'],
                ]);
            }

            $validated['amount'] = number_format(($baseSalary + $allowances - $deductions) / 100, 2, '.', '');
            $validated['metadata']['allowances'] = (string) ($metadata['allowances'] ?? '0');
            $validated['metadata']['deductions'] = (string) ($metadata['deductions'] ?? '0');
        }

        return $validated;
    }

    private function validateAssignee(array $validated)
    {
        if (isset($validated['assigned_user_id']) && ! User::query()->where('is_active', true)->whereKey($validated['assigned_user_id'])->exists()) {
            abort(422, 'The selected employee could not be found.');
        }
    }

    private function findRecord(Request $request, $module, $id)
    {
        $query = ErpRecord::query()->where('module', $module);

        if ($module === 'personal_tasks') {
            $query->where('created_by', $request->user()->id);
        }

        return $query->findOrFail($id);
    }

    private function ensureModule($module)
    {
        abort_unless(in_array($module, $this->modules, true), 404, 'Unknown ERP module.');
    }

    private function normalizePermissions($permissions)
    {
        $availableModules = config('erp.permission_modules', []);
        if ($permissions === null) {
            return config('erp.default_permissions', []);
        }

        $unknownModules = array_diff(array_keys($permissions), array_keys($availableModules));
        if ($unknownModules) {
            throw ValidationException::withMessages([
                'permissions' => ['Unknown permission modules: ' . implode(', ', $unknownModules) . '.'],
            ]);
        }

        $matrix = [];
        foreach ($availableModules as $module => $actions) {
            $granted = $permissions[$module] ?? [];
            if (! is_array($granted)) {
                throw ValidationException::withMessages([
                    'permissions.' . $module => ['Permissions must be an array of allowed actions.'],
                ]);
            }
            $unknownActions = array_diff($granted, $actions);
            if ($unknownActions) {
                throw ValidationException::withMessages([
                    'permissions.' . $module => ['The selected permission actions are not supported.'],
                ]);
            }
            $matrix[$module] = array_values(array_unique($granted));
        }

        return $matrix;
    }

    private function authorizeModule(Request $request, $module, $action)
    {
        abort_unless($request->user()->canAccess($module, $action), 403, 'You do not have permission to perform this action.');
    }
}
