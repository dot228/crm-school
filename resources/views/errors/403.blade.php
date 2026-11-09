<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Доступ заборонено</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-2xl mb-6">
            <i class="fa fa-ban text-red-500 text-3xl"></i>
        </div>
        <h1 class="text-6xl font-bold text-slate-800 mb-3">403</h1>
        <p class="text-xl text-slate-600 mb-2">Доступ заборонено</p>
        <p class="text-slate-400 mb-8">У вас недостатньо прав для перегляду цієї сторінки.</p>
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-3 rounded-xl transition">
            <i class="fa fa-arrow-left"></i> Назад
        </a>
    </div>
</body>
</html>
