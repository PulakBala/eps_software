<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;

class ZKTecoService
{
    protected $zk;
    protected $ip;
    protected $port;
    protected $isConnected = false;

    public function __construct($ip = '192.168.0.102', $port = 4370)
    {
        if (!function_exists('socket_create')) {
            throw new Exception('PHP Sockets extension is not properly installed. Please check your PHP configuration.');
        }

        // Set maximum execution time to 120 seconds
        set_time_limit(120);

        $this->ip = $ip;
        $this->port = $port;
        $this->zk = new ZKTeco($ip, $port);
    }

    public function isDeviceReachable()
    {
        try {
            $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($socket === false) {
                throw new Exception('Could not create socket');
            }

            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
            
            $result = @socket_connect($socket, $this->ip, $this->port);
            socket_close($socket);
            
            return $result !== false;
        } catch (Exception $e) {
            Log::error('Device reachability check failed: ' . $e->getMessage());
            return false;
        }
    }

    public function testProtocol()
    {
        try {
            if (!$this->isDeviceReachable()) {
                return false;
            }

            $this->connect();
            $version = $this->zk->version();
            return !empty($version);
        } catch (Exception $e) {
            Log::error('Protocol test failed: ' . $e->getMessage());
            return false;
        } finally {
            $this->disconnect();
        }
    }

    public function getDeviceInfo()
    {
        try {
            if (!$this->isDeviceReachable()) {
                return null;
            }

            $this->connect();
            return [
                'version' => $this->zk->version(),
                'platform' => $this->zk->platform(),
                'firmware' => $this->zk->fmVersion(),
                'serial' => $this->zk->serialNumber()
            ];
        } catch (Exception $e) {
            Log::error('Failed to get device info: ' . $e->getMessage());
            return null;
        } finally {
            $this->disconnect();
        }
    }

    public function getDeviceTime()
    {
        try {
            if (!$this->isDeviceReachable()) {
                return null;
            }

            $this->connect();
            return $this->zk->getTime();
        } catch (Exception $e) {
            Log::error('Failed to get device time: ' . $e->getMessage());
            return null;
        } finally {
            $this->disconnect();
        }
    }

    public function connect()
    {
        try {
            if ($this->isConnected) {
                return true;
            }

            if (!$this->isDeviceReachable()) {
                throw new Exception('Device is not reachable at ' . $this->ip . ':' . $this->port);
            }

            $result = $this->zk->connect();
            if ($result) {
                $this->isConnected = true;
                return true;
            }

            throw new Exception('Failed to establish connection with device');
        } catch (Exception $e) {
            Log::error('ZKTeco connection error: ' . $e->getMessage());
            throw new Exception('Could not connect to the attendance device. Please check if the device is powered on and connected to the network. Error: ' . $e->getMessage());
        }
    }

    public function disconnect()
    {
        if ($this->zk && $this->isConnected) {
            $this->zk->disconnect();
            $this->isConnected = false;
        }
    }

    public function getAttendance()
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Could not connect to device');
            }

            // Get attendance data using package's method
            $attendanceData = $this->zk->getAttendances();
            
            // Debug: Log the raw attendance data
            Log::info('Raw attendance data from device:', ['data' => $attendanceData]);
            
            if (empty($attendanceData)) {
                throw new Exception('No attendance data found on device');
            }

            // Format the data according to our needs
            $formattedData = [];
            foreach ($attendanceData as $data) {
                // Debug: Log each attendance record
                Log::info('Processing attendance record:', ['record' => $data]);
                
                if (!isset($data['user_id']) || !isset($data['record_time'])) {
                    Log::warning('Skipping invalid attendance record:', ['record' => $data]);
                    continue; // Skip invalid records
                }

                // Find employee by device_user_id
                $employee = Employee::where('device_user_id', $data['user_id'])->first();
                if (!$employee) {
                    Log::warning('Employee not found for device_user_id: ' . $data['user_id']);
                    continue;
                }

                $formattedData[] = [
                    'employee_id' => $employee->id,
                    'timestamp' => $data['record_time'],
                    'status' => $data['type'] ?? 'check'
                ];
            }

            if (empty($formattedData)) {
                throw new Exception('No valid attendance records found');
            }

            // Debug: Log the formatted data
            Log::info('Formatted attendance data:', ['data' => $formattedData]);

            return $formattedData;
        } catch (Exception $e) {
            Log::error('Error getting attendance: ' . $e->getMessage());
            throw new Exception('Failed to get attendance data: ' . $e->getMessage());
        } finally {
            $this->disconnect();
        }
    }

    public function syncAttendance()
    {
        try {
            $attendanceData = $this->getAttendance();
            
            foreach ($attendanceData as $data) {
                $date = Carbon::parse($data['timestamp'])->format('Y-m-d');
                $time = Carbon::parse($data['timestamp'])->format('H:i:s');
                
                // Find or create attendance record
                $attendance = Attendance::firstOrNew([
                    'employee_id' => $data['employee_id'],
                    'date' => $date
                ]);

                // Update check-in or check-out time
                if (!$attendance->check_in) {
                    $attendance->check_in = $time;
                } else {
                    $attendance->check_out = $time;
                }

                // Set status based on check-in time
                if ($attendance->check_in) {
                    $checkInTime = Carbon::parse($attendance->check_in);
                    $attendance->status = $checkInTime->hour > 9 ? 'late' : 'present';
                }

                $attendance->save();
            }

            return true;
        } catch (Exception $e) {
            Log::error('Error syncing attendance: ' . $e->getMessage());
            throw new Exception('Failed to sync attendance: ' . $e->getMessage());
        }
    }

    public function getEmployees()
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Could not connect to device');
            }

            // Get all employees from device
            $employees = $this->zk->getUsers();
            
            if (empty($employees)) {
                throw new Exception('No employees found on device');
            }

            // Format the data
            $formattedData = [];
            foreach ($employees as $employee) {
                $formattedData[] = [
                    'user_id' => $employee['userid'] ?? null,
                    'name' => $employee['name'] ?? null,
                    'role' => $employee['role'] ?? null,
                    'password' => $employee['password'] ?? null,
                    'card_number' => $employee['cardno'] ?? null,
                    'privilege' => $employee['privilege'] ?? null,
                    'enabled' => $employee['enabled'] ?? true
                ];
            }

            return $formattedData;
        } catch (Exception $e) {
            Log::error('Error getting employees: ' . $e->getMessage());
            throw new Exception('Failed to get employee data: ' . $e->getMessage());
        } finally {
            $this->disconnect();
        }
    }

    public function getEmployee($userId)
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Could not connect to device');
            }

            // Get specific employee from device
            $employee = $this->zk->getUsers($userId);
            
            if (empty($employee)) {
                throw new Exception('Employee not found on device');
            }

            return [
                'user_id' => $employee['userid'] ?? null,
                'name' => $employee['name'] ?? null,
                'role' => $employee['role'] ?? null,
                'password' => $employee['password'] ?? null,
                'card_number' => $employee['cardno'] ?? null,
                'privilege' => $employee['privilege'] ?? null,
                'enabled' => $employee['enabled'] ?? true
            ];
        } catch (Exception $e) {
            Log::error('Error getting employee: ' . $e->getMessage());
            throw new Exception('Failed to get employee data: ' . $e->getMessage());
        } finally {
            $this->disconnect();
        }
    }

    public function syncEmployees()
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Could not connect to device');
            }

            // Get all employees from device
            $employees = $this->zk->getUsers();
            
            if (empty($employees)) {
                throw new Exception('No employees found on device');
            }

            // Debug: Log the raw employee data
            Log::info('Raw employee data from device:', ['data' => $employees]);

            // Format and sync the data
            foreach ($employees as $employee) {
                // Debug: Log each employee record
                Log::info('Processing employee record:', ['record' => $employee]);

                // Find or create employee by device user_id
                $emp = Employee::firstOrNew(['device_user_id' => $employee['user_id']]);
                
                // Update employee information
                $emp->name = $employee['name'] ?? 'Unknown';
                $emp->role = $employee['role'] ?? null;
                $emp->card_number = $employee['cardno'] ?? null;
                $emp->is_active = true;
                
                $emp->save();
            }

            return true;
        } catch (Exception $e) {
            Log::error('Error syncing employees: ' . $e->getMessage());
            throw new Exception('Failed to sync employees: ' . $e->getMessage());
        } finally {
            $this->disconnect();
        }
    }
} 