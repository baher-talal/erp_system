<?php

namespace Tests\Feature;

use App\ErpRecord;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ErpWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function testFirstVisitStartsOneTimeAdministratorSetup()
    {
        $this->get('/')
            ->assertRedirect(route('setup'));

        $longUtf8Password = str_repeat('ك', 37);
        $this->post('/setup', [
            'name' => 'Workspace Admin',
            'email' => 'admin@example.test',
            'password' => $longUtf8Password,
            'password_confirmation' => $longUtf8Password,
        ])->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);

        $this->post('/setup', [
            'name' => 'Workspace Admin',
            'email' => 'admin@example.test',
            'password' => 'StrongPassword123',
            'password_confirmation' => 'StrongPassword123',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.test',
            'role' => 'admin',
            'is_active' => 1,
        ]);
        $this->get('/setup')->assertRedirect('/');

        $this->post('/logout')->assertRedirect(route('login'));
        $this->post('/setup', [
            'name' => 'Unexpected second admin',
            'email' => 'second-admin@example.test',
            'password' => 'AnotherStrongPassword123',
            'password_confirmation' => 'AnotherStrongPassword123',
        ])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function testDashboardRequiresAuthenticationAndRendersTheBilingualModules()
    {
        $this->get('/')->assertRedirect(route('setup'));

        $admin = User::create([
            'name' => 'Workspace Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'admin',
        ]);
        $this->assertTrue($admin->fresh()->is_active);

        auth()->logout();
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/workspace/tasks')->assertRedirect(route('login'));

        $this->actingAs($admin);
        $response = $this->get('/');
        $response->assertOk()
            ->assertSee('data-i18n-key="Overview"', false)
            ->assertSee('data-module="inventory"', false)
            ->assertSee('id="themeToggle"', false)
            ->assertSee('data-user-permissions=', false)
            ->assertSee('/workspace/dashboard', false);
    }

    public function testEmployeesTasksAndDashboardDataPersist()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin);

        $this->postJson('/workspace/employees/account', [
            'name' => 'New Employee',
            'email' => 'staff@example.test',
            'password' => 'EmployeePassword456',
            'role' => 'manager',
            'job_title' => 'Designer',
            'department' => 'Product',
        ])->assertCreated();
        $manager = User::where('email', 'staff@example.test')->firstOrFail();

        $client = $this->postJson('/workspace/clients', [
            'title' => 'Acme',
            'status' => 'active',
            'metadata' => ['company' => 'Acme Corporation', 'email' => 'client@example.test'],
        ])->assertCreated()->json('data');
        $project = $this->postJson('/workspace/projects', [
            'title' => 'Client onboarding',
            'status' => 'in_progress',
            'metadata' => ['client_id' => (string) $client['id'], 'progress' => '25'],
        ])->assertCreated()->json('data');

        $this->postJson('/workspace/tasks', [
            'title' => 'Review client proposal',
            'description' => 'Review the revised scope',
            'status' => 'completed',
            'assigned_user_id' => $manager->id,
            'metadata' => ['priority' => 'high', 'project_id' => (string) $project['id']],
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Review client proposal')
            ->assertJsonPath('data.assignee.id', $manager->id)
            ->assertJsonPath('data.metadata.project_id', (string) $project['id']);
        $this->deleteJson('/workspace/projects/' . $project['id'])
            ->assertStatus(422);
        $this->deleteJson('/workspace/clients/' . $client['id'])
            ->assertStatus(422);

        $this->postJson('/workspace/payments', [
            'title' => 'Invoice 1001',
            'status' => 'paid',
            'amount' => 1250,
            'record_date' => now()->toDateString(),
        ])->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'staff@example.test',
            'role' => 'manager',
        ]);
        $this->assertDatabaseHas('erp_records', [
            'module' => 'employees',
            'title' => 'New Employee',
        ]);

        $this->getJson('/workspace/dashboard')
            ->assertOk()
            ->assertJsonPath('employees', 1)
            ->assertJsonPath('active_projects', 1)
            ->assertJsonPath('completed_tasks', 1)
            ->assertJsonPath('revenue', '1250');

        $this->getJson('/workspace/tasks?search=client')
            ->assertOk()
            ->assertJsonPath('data.data.0.title', 'Review client proposal');
        $this->actingAs($manager)
            ->getJson('/workspace/payments')
            ->assertForbidden();
    }

    public function testRecordsAreValidatedAndPersonalTasksArePrivate()
    {
        $firstUser = User::create([
            'name' => 'First User',
            'email' => 'first@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'admin',
        ]);
        $secondUser = User::create([
            'name' => 'Second User',
            'email' => 'second@example.test',
            'password' => bcrypt('StrongPassword456'),
            'role' => 'employee',
        ]);

        $this->actingAs($firstUser)
            ->postJson('/workspace/tasks', ['title' => '', 'status' => 'unexpected'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status']);

        $this->postJson('/workspace/projects', [
            'title' => 'Unlinked project',
            'status' => 'active',
            'metadata' => ['client_id' => '999'],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['metadata.client_id']);

        $personalTask = ErpRecord::create([
            'module' => 'personal_tasks',
            'title' => 'Private reminder',
            'status' => 'pending',
            'created_by' => $firstUser->id,
        ]);

        $this->actingAs($secondUser)
            ->getJson('/workspace/personal_tasks')
            ->assertOk()
            ->assertJsonCount(0, 'data.data');

        $this->deleteJson('/workspace/personal_tasks/' . $personalTask->id)
            ->assertNotFound();
    }

    public function testEmployeeCannotAccessFinancialRecordsOrCreateAccounts()
    {
        $employee = User::create([
            'name' => 'Employee',
            'email' => 'employee@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'employee',
        ]);

        $this->actingAs($employee)
            ->getJson('/workspace/payroll')
            ->assertForbidden();
        $this->get('/')
            ->assertOk()
            ->assertSee('data-user-permissions=', false);

        $this->postJson('/workspace/employees/account', [
            'name' => 'Unauthorized account',
            'email' => 'other@example.test',
            'password' => 'StrongPassword456',
        ])->assertForbidden();
    }

    public function testAdministratorCanAssignPerModuleActionsAndFinancialAccess()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin);

        $this->postJson('/workspace/employees/account', [
            'name' => 'Finance Viewer',
            'email' => 'viewer@example.test',
            'password' => 'StrongPassword456',
            'permissions' => [
                'tasks' => ['view'],
                'payments' => ['view'],
                'reports' => ['view'],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.metadata.permissions.tasks', ['view'])
            ->json('data');

        ErpRecord::create([
            'module' => 'payments',
            'title' => 'Visible invoice',
            'status' => 'paid',
            'amount' => 120,
            'created_by' => $admin->id,
        ]);
        ErpRecord::create([
            'module' => 'payroll',
            'title' => 'Private payroll',
            'status' => 'paid',
            'amount' => 900,
            'created_by' => $admin->id,
        ]);

        $viewer = User::where('email', 'viewer@example.test')->firstOrFail();
        $this->actingAs($viewer)
            ->getJson('/workspace/tasks')
            ->assertOk();
        $this->postJson('/workspace/tasks', [
            'title' => 'Unauthorized task',
            'status' => 'pending',
        ])->assertForbidden();
        $this->getJson('/workspace/payments')
            ->assertOk()
            ->assertJsonPath('data.data.0.title', 'Visible invoice');
        $this->getJson('/workspace/payroll')->assertForbidden();
        $this->getJson('/workspace/reports')
            ->assertOk()
            ->assertJsonPath('summary.payments_received', '120')
            ->assertJsonPath('summary.payroll_paid', null)
            ->assertJsonMissing(['module' => 'payroll']);
    }

    public function testAttendanceClockRequiresAnOpenShiftToClockOut()
    {
        $employee = User::create([
            'name' => 'Employee',
            'email' => 'employee@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'employee',
        ]);

        $this->actingAs($employee)
            ->postJson('/workspace/attendance/clock', [
                'employee_name' => $employee->name,
                'action' => 'clock_in',
            ])->assertOk()
            ->assertJsonPath('data.status', 'checked_in');
        $this->getJson('/workspace/attendance')
            ->assertOk()
            ->assertJsonPath('clocked_in', true);

        $this->postJson('/workspace/attendance/clock', [
            'employee_name' => $employee->name,
            'action' => 'clock_in',
        ])->assertStatus(422);

        $this->postJson('/workspace/attendance/clock', [
            'employee_name' => $employee->name,
            'action' => 'clock_out',
        ])->assertOk()
            ->assertJsonPath('data.status', 'checked_out');
        $this->getJson('/workspace/attendance')
            ->assertOk()
            ->assertJsonPath('clocked_in', false);
    }

    public function testRecordCreatorCanDeleteTheirOwnRecord()
    {
        $employee = User::create([
            'name' => 'Employee',
            'email' => 'employee@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'employee',
        ]);
        $task = ErpRecord::create([
            'module' => 'tasks',
            'title' => 'My task',
            'status' => 'pending',
            'created_by' => $employee->id,
        ]);

        $this->actingAs($employee)
            ->deleteJson('/workspace/tasks/' . $task->id)
            ->assertOk();

        $this->assertDatabaseMissing('erp_records', ['id' => $task->id]);
    }

    public function testChatMessagesShowTheirSenderAndCannotBeEditedByOtherEmployees()
    {
        $sender = User::create([
            'name' => 'Message sender',
            'email' => 'sender@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'employee',
        ]);
        $recipient = User::create([
            'name' => 'Message recipient',
            'email' => 'recipient@example.test',
            'password' => bcrypt('StrongPassword456'),
            'role' => 'employee',
        ]);
        $message = ErpRecord::create([
            'module' => 'chat',
            'title' => 'Project update',
            'status' => 'active',
            'created_by' => $sender->id,
        ]);

        $this->actingAs($recipient)
            ->getJson('/workspace/chat')
            ->assertOk()
            ->assertJsonPath('data.data.0.creator.name', 'Message sender');

        $this->putJson('/workspace/chat/' . $message->id, [
            'title' => 'Changed message',
            'status' => 'active',
        ])->assertForbidden();
    }

    public function testEmployeeAccountUpdatesStayLinkedAndDeactivationBlocksAccess()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin)
            ->postJson('/workspace/departments', [
                'title' => 'Design',
                'status' => 'active',
            ])->assertCreated();
            $department = ErpRecord::where('module', 'departments')->firstOrFail();

            $this->actingAs($admin)
                ->postJson('/workspace/employees/account', [
                'name' => 'Employee One',
                'email' => 'employee@example.test',
                'password' => 'EmployeePassword123',
                'job_title' => 'Designer',
                'department_id' => (string) $department->id,
            ])->assertCreated();
        $this->deleteJson('/workspace/departments/' . $department->id)
            ->assertStatus(422);

        $account = User::where('email', 'employee@example.test')->firstOrFail();
        $profile = ErpRecord::where('module', 'employees')->where('title', 'Employee One')->firstOrFail();

        $this->putJson('/workspace/employees/' . $profile->id, [
            'title' => 'Changed without account sync',
            'status' => 'active',
        ])->assertStatus(422);

        $this->putJson('/workspace/employees/' . $profile->id . '/account', [
            'name' => 'Updated Employee',
            'email' => 'updated@example.test',
            'password' => '',
            'role' => 'manager',
            'is_active' => false,
            'job_title' => 'Lead Designer',
            'department_id' => (string) $department->id,
            'phone' => '555-1234',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Updated Employee')
            ->assertJsonPath('data.status', 'archived');

        $this->assertDatabaseHas('users', [
            'id' => $account->id,
            'name' => 'Updated Employee',
            'email' => 'updated@example.test',
            'role' => 'manager',
            'is_active' => 0,
        ]);
        $this->assertSame((string) $department->id, $profile->fresh()->metadata['department_id']);
        $this->assertSame('Design', $profile->fresh()->metadata['department']);

        $this->actingAs($account->fresh())
            ->getJson('/workspace/dashboard')
            ->assertForbidden();
        $this->assertGuest();

        $this->post('/login', [
            'email' => 'updated@example.test',
            'password' => 'EmployeePassword123',
        ])->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'employee@example.test']);
    }

    public function testAttendanceUsesTheSignedInIdentityAndIsPrivateToThatEmployee()
    {
        $firstEmployee = User::create([
            'name' => 'First Employee',
            'email' => 'first@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'employee',
        ]);
        $secondEmployee = User::create([
            'name' => 'Second Employee',
            'email' => 'second@example.test',
            'password' => bcrypt('StrongPassword456'),
            'role' => 'employee',
        ]);

        $this->actingAs($firstEmployee)
            ->postJson('/workspace/attendance/clock', [
                'employee_name' => 'Second Employee',
                'action' => 'clock_in',
            ])->assertOk()
                ->assertJsonPath('data.title', 'First Employee')
                ->assertJsonPath('data.metadata.employee_name', 'First Employee');

        $this->postJson('/workspace/attendance', [
            'title' => 'Spoofed shift',
            'status' => 'checked_in',
        ])->assertForbidden();

        $this->actingAs($secondEmployee)
            ->getJson('/workspace/attendance')
            ->assertOk()
            ->assertJsonCount(0, 'data.data');

        $this->assertDatabaseHas('erp_records', [
            'module' => 'attendance',
            'created_by' => $firstEmployee->id,
            'title' => 'First Employee',
        ]);
    }

    public function testPayrollCalculatesNetSalaryAndRejectsExcessDeductions()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'admin',
        ]);
        $employee = ErpRecord::create([
            'module' => 'employees',
            'title' => 'Payroll Employee',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->postJson('/workspace/payroll', [
                'title' => 'November salary',
                'status' => 'unpaid',
                'amount' => '999999',
                'metadata' => [
                    'employee_id' => (string) $employee->id,
                    'period' => '2026-11',
                    'base_salary' => '1500.25',
                    'allowances' => '200',
                    'deductions' => '100',
                ],
            ])->assertCreated()
                ->assertJsonPath('data.amount', '1600.25');

        $this->postJson('/workspace/payroll', [
            'title' => 'Invalid salary',
            'status' => 'unpaid',
            'metadata' => [
                'employee_id' => (string) $employee->id,
                'period' => '2026-11',
                'base_salary' => '100',
                'allowances' => '0',
                'deductions' => '101',
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('metadata.deductions');
        $this->deleteJson('/workspace/employees/' . $employee->id)
            ->assertStatus(422);
    }

    public function testInventoryAdjustmentsRecordMovementsAndPreventNegativeStock()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->postJson('/workspace/inventory', [
                'title' => 'Printer paper',
                'status' => 'active',
                'metadata' => [
                    'sku' => 'PAPER-A4',
                    'quantity' => 10,
                    'unit' => 'box',
                    'reorder_level' => 8,
                ],
            ])->assertCreated();

        $item = ErpRecord::where('module', 'inventory')->firstOrFail();

        $this->putJson('/workspace/inventory/' . $item->id, [
            'title' => 'Printer paper',
            'status' => 'active',
            'metadata' => [
                'sku' => 'PAPER-A4',
                'quantity' => 100,
                'reorder_level' => 8,
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('metadata.quantity');

        $this->postJson('/workspace/inventory/' . $item->id . '/adjust', [
            'action' => 'decrease',
            'quantity' => 3,
            'reason' => 'Issued to office',
        ])->assertOk()
            ->assertJsonPath('data.metadata.quantity', '7')
            ->assertJsonPath('data.metadata.movements.0.change', -3);

        $this->postJson('/workspace/inventory/' . $item->id . '/adjust', [
            'action' => 'decrease',
            'quantity' => 8,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('quantity');

        $this->getJson('/workspace/reports')
            ->assertOk()
            ->assertJsonPath('summary.low_stock', 1);
    }

    public function testModuleListsCanNavigateBeyondTheFirstPage()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'admin',
        ]);

        for ($index = 1; $index <= 51; $index++) {
            ErpRecord::create([
                'module' => 'inventory',
                'title' => 'Item ' . $index,
                'status' => 'active',
                'created_by' => $admin->id,
            ]);
        }

        $this->actingAs($admin)
            ->getJson('/workspace/inventory?page=2')
            ->assertOk()
            ->assertJsonPath('data.current_page', 2)
            ->assertJsonCount(1, 'data.data');
    }

    public function testFileUploadsAreStoredPrivatelyAndRequireAuthentication()
    {
        Storage::fake('local');
        Storage::fake('public');
        config(['filesystems.default' => 'public']);
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('StrongPassword123'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post('/workspace/files/upload', [
                'file' => UploadedFile::fake()->createWithContent('handbook.txt', 'Company handbook'),
                'description' => 'Internal document',
            ])
            ->assertCreated();

        $record = ErpRecord::where('module', 'files')->firstOrFail();
        Storage::disk('local')->assertExists($record->metadata['file_path']);
        $this->assertSame('Company handbook', Storage::disk('local')->get($record->metadata['file_path']));
        $this->getJson('/workspace/files')
            ->assertOk()
            ->assertJsonMissing(['file_path' => $record->metadata['file_path']]);
        $this->putJson('/workspace/files/' . $record->id, [
            'title' => 'Changed path',
            'status' => 'active',
            'metadata' => ['file_path' => '../../.env'],
        ])->assertStatus(422);

        $download = $this->get('/workspace/files/' . $record->id . '/download');
        $download->assertOk();
        $this->assertStringContainsString('handbook.txt', $download->headers->get('content-disposition'));

        auth()->logout();
        $this->get('/workspace/files/' . $record->id . '/download')
            ->assertRedirect(route('login'));
    }
}
