<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
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
            $attendanceData = $this->zk->getAttendanceData();
            
            if (empty($attendanceData)) {
                throw new Exception('No attendance data found on device');
            }

            // Format the data according to our needs
            $formattedData = [];
            foreach ($attendanceData as $data) {
                if (!isset($data['user_id']) || !isset($data['timestamp'])) {
                    continue; // Skip invalid records
                }

                $formattedData[] = [
                    'user_id' => $data['user_id'],
                    'timestamp' => $data['timestamp'],
                    'status' => $data['status'] ?? 'check'
                ];
            }

            if (empty($formattedData)) {
                throw new Exception('No valid attendance records found');
            }

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
                    'employee_id' => $data['user_id'],
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
} 