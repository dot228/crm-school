<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\User;

class LessonBalanceService
{
    public function credit(Enrollment $enrollment, int $lessons): void
    {
        $enrollment->increment('lessons_balance', $lessons);
    }

    public function creditMoney(int $studentId, float $amount): void
    {
        User::where('id', $studentId)->increment('money_balance', $amount);
    }

    public function deductForSchedule(Schedule $schedule): void
    {
        $enrollment = $this->findEnrollmentForSchedule($schedule);

        if ($enrollment && $enrollment->lessons_balance > 0) {
            $enrollment->decrement('lessons_balance');
            return;
        }

        $lessonPrice = $schedule->lesson?->course?->lesson_price;
        if ($lessonPrice && $lessonPrice > 0) {
            $student = User::find($schedule->student_id);
            if ($student && $student->money_balance >= $lessonPrice) {
                $student->decrement('money_balance', $lessonPrice);
            }
        }
    }

    public function creditFromPayment(Payment $payment): void
    {
        if ($payment->payment_type === 'money') {
            if ($payment->student_id) {
                $this->creditMoney($payment->student_id, (float) $payment->amount);
            }
            return;
        }

        if (!$payment->enrollment_id) {
            return;
        }

        $lessons = $payment->lessons_count > 0
            ? $payment->lessons_count
            : ($payment->tariffPlan?->count_lessons ?? 0);

        if ($lessons > 0) {
            $enrollment = Enrollment::find($payment->enrollment_id);
            if ($enrollment) {
                $this->credit($enrollment, $lessons);
            }
        }
    }

    public function getBalanceForSchedule(int $studentId, int $lessonId): int
    {
        $lesson = Lesson::find($lessonId);
        if (!$lesson) {
            return 0;
        }
        return $this->getBalance($studentId, $lesson->course_id);
    }

    public function getBalance(int $studentId, int $courseId): int
    {
        $enrollment = Enrollment::where('student_id', $studentId)
            ->whereHas('group', fn($q) => $q->where('course_id', $courseId))
            ->first();

        return $enrollment?->lessons_balance ?? 0;
    }

    private function findEnrollmentForSchedule(Schedule $schedule): ?Enrollment
    {
        if (!$schedule->lesson) {
            return null;
        }

        $byCourse = Enrollment::where('student_id', $schedule->student_id)
            ->whereHas('group', fn($q) => $q->where('course_id', $schedule->lesson->course_id))
            ->first();

        if ($byCourse) {
            return $byCourse;
        }

        return Enrollment::where('student_id', $schedule->student_id)
            ->where('lessons_balance', '>', 0)
            ->first();
    }

    private function findEnrollmentForStudent(int $studentId, int $courseId): ?Enrollment
    {
        $byCourse = Enrollment::where('student_id', $studentId)
            ->whereHas('group', fn($q) => $q->where('course_id', $courseId))
            ->first();

        if ($byCourse) {
            return $byCourse;
        }

        return Enrollment::where('student_id', $studentId)
            ->where('lessons_balance', '>', 0)
            ->first();
    }

    public function hasBalance(int $studentId, int $lessonId, ?int $excludeScheduleId = null): bool
    {
        $lesson = Lesson::find($lessonId);
        if (!$lesson) {
            return false;
        }

        $enrollment = $this->findEnrollmentForStudent($studentId, $lesson->course_id);

        if ($enrollment && $enrollment->lessons_balance > 0) {
            $scheduledQuery = Schedule::where('student_id', $studentId)
                ->where('status', 'scheduled')
                ->whereHas('lesson', fn($q) => $q->where('course_id', $lesson->course_id));

            if ($excludeScheduleId) {
                $scheduledQuery->where('id', '!=', $excludeScheduleId);
            }

            if (($enrollment->lessons_balance - $scheduledQuery->count()) > 0) {
                return true;
            }
        }

        $lessonPrice = $lesson->course?->lesson_price;
        if ($lessonPrice && $lessonPrice > 0) {
            $student = User::find($studentId);
            if ($student && $student->money_balance >= $lessonPrice) {
                return true;
            }
        }

        return false;
    }
}
