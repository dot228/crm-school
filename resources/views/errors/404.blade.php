<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Сторінку не знайдено</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-100 rounded-2xl mb-6">
            <i class="fa fa-magnifying-glass text-indigo-500 text-3xl"></i>
        </div>
        <h1 class="text-6xl font-bold text-slate-800 mb-3">404</h1>
        <p class="text-xl text-slate-600 mb-2">Сторінку не знайдено</p>
        <p class="text-slate-400 mb-8">Запитана сторінка не існує або була переміщена.</p>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-3 rounded-xl transition">
            <i class="fa fa-house"></i> На головну
        </a>
    </div>
</body>
</html>
