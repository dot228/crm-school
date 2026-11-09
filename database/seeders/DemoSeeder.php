<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // --- Users ---
        $admin = User::create([
            'name'     => 'Адміністратор',
            'email'    => 'admin@school.com',
            'password' => Hash::make('password'),
            'role_id'  => User::ROLE_ADMIN,
            'status'   => 'active',
            'phone'    => '+380991234567',
        ]);

        $manager = User::create([
            'name'     => 'Оксана Коваль',
            'email'    => 'manager@school.com',
            'password' => Hash::make('password'),
            'role_id'  => User::ROLE_MANAGER,
            'status'   => 'active',
            'phone'    => '+380992345678',
        ]);

        $teachers = collect([
            ['name' => 'Іван Петренко',  'email' => 'ivan@school.com',   'bio' => 'Досвідчений викладач PHP та Laravel'],
            ['name' => 'Марія Сидоренко','email' => 'maria@school.com',  'bio' => 'Frontend-розробник, 8 років досвіду'],
            ['name' => 'Олег Бондаренко','email' => 'oleg@school.com',   'bio' => 'Спеціаліст з Python та Data Science'],
        ])->map(fn($d) => User::create([
            'name'     => $d['name'],
            'email'    => $d['email'],
            'password' => Hash::make('password'),
            'role_id'  => User::ROLE_TEACHER,
            'status'   => 'active',
            'bio'      => $d['bio'],
        ]));

        $students = collect([
            ['name' => 'Андрій Мельник',    'email' => 'andrii@gmail.com',   'phone' => '+380671111111'],
            ['name' => 'Наталія Шевченко',  'email' => 'natalia@gmail.com',  'phone' => '+380672222222'],
            ['name' => 'Дмитро Ковальчук',  'email' => 'dmytro@gmail.com',   'phone' => '+380673333333'],
            ['name' => 'Олена Левченко',    'email' => 'olena@gmail.com',    'phone' => '+380674444444'],
            ['name' => 'Тарас Гриценко',    'email' => 'taras@gmail.com',    'phone' => '+380675555555'],
            ['name' => 'Вікторія Поліщук',  'email' => 'viktoria@gmail.com', 'phone' => '+380676666666'],
        ])->map(fn($d) => User::create([
            'name'     => $d['name'],
            'email'    => $d['email'],
            'password' => Hash::make('password'),
            'role_id'  => User::ROLE_STUDENT,
            'status'   => 'active',
            'phone'    => $d['phone'],
        ]));

        // --- Courses ---
        $courses = collect([
            [
                'title'          => 'PHP та Laravel з нуля',
                'description'    => 'Повний курс веб-розробки на PHP та фреймворку Laravel. Від основ до готових проектів.',
                'price'          => 4500,
                'duration_hours' => 80,
                'level'          => 'beginner',
                'teacher_id'     => $teachers[0]->id,
            ],
            [
                'title'          => 'React.js для початківців',
                'description'    => 'Сучасна розробка інтерфейсів з React. Хуки, стан, маршрутизація.',
                'price'          => 3800,
                'duration_hours' => 60,
                'level'          => 'beginner',
                'teacher_id'     => $teachers[1]->id,
            ],
            [
                'title'          => 'Python та Machine Learning',
                'description'    => 'Аналіз даних, машинне навчання, нейронні мережі на Python.',
                'price'          => 6000,
                'duration_hours' => 100,
                'level'          => 'intermediate',
                'teacher_id'     => $teachers[2]->id,
            ],
        ])->map(fn($d) => Course::create($d + ['status' => 'active']));

        // --- Groups ---
        $groups = collect([
            [
                'name'         => 'PHP-2024-A',
                'course_id'    => $courses[0]->id,
                'teacher_id'   => $teachers[0]->id,
                'start_date'   => '2024-02-01',
                'end_date'     => '2024-05-31',
                'max_students' => 15,
                'status'       => 'active',
            ],
            [
                'name'         => 'React-2024-A',
                'course_id'    => $courses[1]->id,
                'teacher_id'   => $teachers[1]->id,
                'start_date'   => '2024-03-01',
                'end_date'     => '2024-06-30',
                'max_students' => 12,
                'status'       => 'active',
            ],
            [
                'name'         => 'Python-2024-A',
                'course_id'    => $courses[2]->id,
                'teacher_id'   => $teachers[2]->id,
                'start_date'   => '2024-01-15',
                'end_date'     => '2024-07-15',
                'max_students' => 10,
                'status'       => 'active',
            ],
        ])->map(fn($d) => Group::create($d));

        // --- Lessons ---
        foreach ($courses as $i => $course) {
            foreach (range(1, 4) as $order) {
                Lesson::create([
                    'title'            => "Урок {$order}: " . $course->title,
                    'course_id'        => $course->id,
                    'group_id'         => $groups[$i]->id,
                    'order'            => $order,
                    'scheduled_at'     => now()->addDays($order * 7),
                    'duration_minutes' => 90,
                    'status'           => $order <= 2 ? 'completed' : 'scheduled',
                    'zoom_link'        => 'https://zoom.us/j/example' . rand(100000, 999999),
                ]);
            }
        }

        // --- Enrollments & Payments ---
        $pairs = [
            [$students[0], $groups[0]],
            [$students[1], $groups[0]],
            [$students[2], $groups[1]],
            [$students[3], $groups[1]],
            [$students[4], $groups[2]],
            [$students[5], $groups[2]],
        ];

        foreach ($pairs as [$student, $group]) {
            $enrollment = Enrollment::create([
                'student_id'  => $student->id,
                'group_id'    => $group->id,
                'status'      => 'active',
                'enrolled_at' => now()->subDays(rand(10, 60))->format('Y-m-d'),
            ]);

            Payment::create([
                'student_id'    => $student->id,
                'enrollment_id' => $enrollment->id,
                'amount'        => $group->course->price * 0.5,
                'status'        => 'paid',
                'method'        => collect(['cash', 'card', 'bank_transfer'])->random(),
                'payment_date'  => now()->subDays(rand(5, 30))->format('Y-m-d'),
            ]);

            if (rand(0, 1)) {
                Payment::create([
                    'student_id'    => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'amount'        => $group->course->price * 0.5,
                    'status'        => 'pending',
                    'method'        => 'card',
                    'payment_date'  => now()->addDays(rand(5, 20))->format('Y-m-d'),
                ]);
            }
        }
    }
}
