<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PolSim</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-background text-foreground">

{{-- Loading overlay --}}
<style>
    @keyframes _spin { to { transform: rotate(360deg); } }
    #loading-spinner {
        width: 48px; height: 48px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.15);
        border-top-color: #ffffff;
        animation: _spin 0.75s linear infinite;
    }
</style>
<div id="loading-overlay" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);">
    <div style="display:flex;flex-direction:column;align-items:center;gap:24px;width:288px;padding:40px 32px;border-radius:16px;border:1px solid rgba(255,255,255,0.1);background:var(--color-card, #1c1c1c);box-shadow:0 25px 50px rgba(0,0,0,0.5);">
        <div id="loading-spinner"></div>
        <p id="loading-message" style="text-align:center;font-size:1rem;font-weight:600;line-height:1.6;color:var(--color-foreground,#fff);margin:0;"></p>
    </div>
</div>

<div class="w-full p-4 lg:p-6">
    @yield('content')
</div>

<script>
    (function () {
        var overlay = document.getElementById('loading-overlay');
        var message = document.getElementById('loading-message');

        function showLoading(text) {
            message.textContent = text;
            overlay.style.display = 'flex';
        }

        document.addEventListener('click', function (e) {
            var el = e.target.closest('[data-loading]');
            if (!el) return;
            showLoading(el.getAttribute('data-loading'));
        });
    })();
</script>
</body>
</html>
