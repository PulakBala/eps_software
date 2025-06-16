<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
  return view('welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
  ->middleware(['auth', 'verified'])
  ->name('dashboard');

Route::middleware(['auth'])->group(function () {
  Route::redirect('settings', 'settings/profile');

  Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
  Volt::route('settings/password', 'settings.password')->name('settings.password');
  Volt::route('invoice', 'invoice-list')->name('invoice');
  Volt::route('invoice/{id}', 'invoice-view')->name('invoice.view');
  Route::get('/customers', App\Livewire\CustomerList::class)->name('customers');
  Route::get('/sales', App\Livewire\SaleList::class)->name('sales');
  Route::get('/inventory', App\Livewire\InventoryList::class)->name('inventory');
  Route::get('/attendance', App\Livewire\AttendanceList::class)->name('attendance');
  Route::get('/salary', App\Livewire\SalaryList::class)->name('salary');
  Route::get('/payslip/{id}', App\Livewire\PayslipView::class)->name('payslip.view');
  Route::get('/employees', App\Livewire\EmployeeList::class)->name('employees.index');
});

require __DIR__ . '/auth.php';
