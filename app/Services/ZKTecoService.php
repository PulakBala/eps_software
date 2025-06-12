<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class ZKTecoService
{
    protected $ip;
    protected $port;
    protected $timeout;
    protected $socket;

    public function __construct($ip = '192.168.1.201', $port = 4370, $timeout = 5)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    public function connect()
    {
        try {
            $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->timeout, 'usec' => 0]);
            return true;
        } catch (Exception $e) {
            Log::error('ZKTeco connection error: ' . $e->getMessage());
            return false;
        }
    }

    public function disconnect()
    {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }

    public function getAttendance()
    {
        try {
            if (!$this->connect()) {
                throw new Exception('Could not connect to device');
            }

            // Send command to get attendance data
            $command = $this->createCommand('CMD_ATTLOG_RRQ');
            socket_sendto($this->socket, $command, strlen($command), 0, $this->ip, $this->port);

            // Receive response
            $response = '';
            socket_recvfrom($this->socket, $response, 1024, 0, $this->ip, $this->port);

            // Parse response and return attendance data
            return $this->parseAttendanceData($response);
        } catch (Exception $e) {
            Log::error('Error getting attendance: ' . $e->getMessage());
            return [];
        } finally {
            $this->disconnect();
        }
    }

    protected function createCommand($command)
    {
        // Create command packet according to ZKTeco protocol
        // This is a simplified version - you'll need to implement the actual protocol
        return pack('H*', '0000000000000000000000000000000000000000');
    }

    protected function parseAttendanceData($data)
    {
        // Parse the raw data from device
        // This is a simplified version - you'll need to implement the actual parsing
        $attendances = [];

        // Example parsing (you need to implement the actual protocol)
        $lines = explode("\n", $data);
        foreach ($lines as $line) {
            if (empty($line)) continue;

            // Parse each line according to ZKTeco protocol
            // This is just an example - you need to implement the actual parsing
            $parts = explode("\t", $line);
            if (count($parts) >= 3) {
                $attendances[] = [
                    'user_id' => $parts[0],
                    'timestamp' => $parts[1],
                    'status' => $parts[2]
                ];
            }
        }

        return $attendances;
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
            return false;
        }
    }
} 