<?php

namespace Tests\Unit;

use App\Models\Schedule;
use Tests\TestCase;

class ScheduleModelTest extends TestCase
{
    public function test_statuses_include_draft(): void
    {
        $this->assertContains('draft', Schedule::STATUSES);
    }

    public function test_statuses_include_all_expected_values(): void
    {
        foreach (['draft', 'scheduled', 'completed', 'cancelled', 'no_show'] as $status) {
            $this->assertContains($status, Schedule::STATUSES);
        }
    }

    public function test_should_deduct_balance_for_completed(): void
    {
        $schedule = new Schedule(['status' => 'completed']);
        $this->assertTrue($schedule->shouldDeductBalance());
    }

    public function test_should_deduct_balance_for_unexcused_no_show(): void
    {
        $schedule = new Schedule(['status' => 'no_show', 'absence_type' => 'unexcused']);
        $this->assertTrue($schedule->shouldDeductBalance());
    }

    public function test_should_not_deduct_balance_for_excused_no_show(): void
    {
        $schedule = new Schedule(['status' => 'no_show', 'absence_type' => 'excused']);
        $this->assertFalse($schedule->shouldDeductBalance());
    }

    public function test_should_not_deduct_balance_for_scheduled(): void
    {
        $schedule = new Schedule(['status' => 'scheduled']);
        $this->assertFalse($schedule->shouldDeductBalance());
    }

    public function test_should_not_deduct_balance_for_draft(): void
    {
        $schedule = new Schedule(['status' => 'draft']);
        $this->assertFalse($schedule->shouldDeductBalance());
    }

    public function test_should_not_deduct_balance_for_cancelled(): void
    {
        $schedule = new Schedule(['status' => 'cancelled']);
        $this->assertFalse($schedule->shouldDeductBalance());
    }
}
