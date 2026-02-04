<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendees = [
            ['attendee_name' => 'Ahmad Zaki', 'matric_no' => 'A12345', 'check_in_time' => '09:00', 'status' => 'Present'],
            ['attendee_name' => 'Sarah Lee', 'matric_no' => 'A12346', 'check_in_time' => '09:05', 'status' => 'Present'],
            ['attendee_name' => 'Michael Chen', 'matric_no' => 'A12347', 'check_in_time' => '09:15', 'status' => 'Present'],
            ['attendee_name' => 'Fatimah Ali', 'matric_no' => 'A12348', 'check_in_time' => '09:20', 'status' => 'Present'],
            ['attendee_name' => 'David Wong', 'matric_no' => 'A12349', 'check_in_time' => null, 'status' => 'Absent'],
            ['attendee_name' => 'Lisa Ibrahim', 'matric_no' => 'A12350', 'check_in_time' => '09:30', 'status' => 'Present'],
            ['attendee_name' => 'John Tan', 'matric_no' => 'A12351', 'check_in_time' => '09:35', 'status' => 'Present'],
            ['attendee_name' => 'Aisha Hassan', 'matric_no' => 'A12352', 'check_in_time' => null, 'status' => 'Absent'],
        ];

        foreach ($attendees as $attendee) {
            Attendance::create(array_merge($attendee, ['event_id' => 1]));
        }
    }
}
