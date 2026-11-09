<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Перенаправлення на оплату…</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center;
               min-height: 100vh; background: #f1f5f9; margin: 0; }
        .card { background: #fff; border-radius: 1rem; padding: 2.5rem; text-align: center;
                box-shadow: 0 4px 24px rgba(0,0,0,.07); max-width: 380px; width: 100%; }
        .spinner { width: 40px; height: 40px; border: 4px solid #e2e8f0;
                   border-top-color: #6366f1; border-radius: 50%;
                   animation: spin 0.8s linear infinite; margin: 0 auto 1.5rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        p { color: #64748b; font-size: .9rem; margin: 0 0 1rem; }
    </style>
</head>
<body>
<div class="card">
    <div class="spinner"></div>
    <p>Перенаправляємо вас на сторінку оплати WayForPay…</p>
    <p style="font-size:.75rem;color:#94a3b8;">Якщо не перенаправлено автоматично — натисніть кнопку нижче.</p>
    <form id="wfp-form" method="POST" action="{{ $form['pay_url'] }}">
        @foreach($form as $key => $value)
            @if($key !== 'pay_url')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <button type="submit"
                style="background:#6366f1;color:#fff;border:none;padding:.75rem 2rem;border-radius:.75rem;cursor:pointer;font-size:.875rem;">
            Перейти до оплати
        </button>
    </form>
</div>
<script>document.addEventListener('DOMContentLoaded', () => document.getElementById('wfp-form').submit());</script>
</body>
</html>
