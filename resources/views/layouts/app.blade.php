<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Combinadinhos</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">

    <!-- PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0f172a">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxAppearance
    
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            --card-bg: rgba(30, 41, 59, 0.7);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --success: #10b981;
            --shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 0;
            overflow-x: hidden;
        }

        .mobile-container {
            width: 100%;
            max-width: 480px;
            min-height: 100vh;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow);
            border-left: 1px solid var(--card-border);
            border-right: 1px solid var(--card-border);
            display: flex;
            flex-direction: column;
            position: relative;
            padding-bottom: 80px; /* space for bottom nav */
        }

        /* Glassmorphism utility */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin: 15px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .glass-card:active {
            transform: scale(0.98);
        }

        h1, h2, h3, h4 {
            font-family: 'Outfit', sans-serif;
        }

        /* Buttons & Actions */
        .btn-primary {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.2);
        }

        .btn-success {
            background: var(--success);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
        }

        /* Top Header */
        .header {
            padding: 25px 20px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Toast / Notification List Alert Banner */
        .toast-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-150%);
            width: 90%;
            max-width: 400px;
            background: rgba(30, 41, 59, 0.95);
            border: 2px solid var(--accent);
            border-radius: 16px;
            padding: 16px;
            box-shadow: var(--shadow);
            z-index: 1000;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(20px);
        }

        .toast-notification.show {
            transform: translateX(-50%) translateY(0);
        }

        .toast-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .toast-title {
            font-weight: 700;
            color: #818cf8;
            font-size: 1.1rem;
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.2rem;
            cursor: pointer;
        }

        .toast-body {
            font-size: 0.9rem;
            color: var(--text-primary);
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="mobile-container">
        <audio id="applauseSound" preload="auto">
            <source src="/sounds/applause.mp3" type="audio/mpeg">
        </audio>
        <audio id="suspenseSound" preload="auto">
            <source src="/sounds/suspense.mp3" type="audio/mpeg">
        </audio>

        @yield('content')
    </div>
    
    <flux:toast />

    @livewireScripts
    @fluxScripts

    <script>
        window.addEventListener('play-task-alert', event => {
            const soundType = event.detail.sound || 'default';
            let audioId = null;

            if (soundType === 'applause') {
                audioId = 'applauseSound';
            } else if (soundType === 'suspense') {
                audioId = 'suspenseSound';
            }

            if (audioId) {
                const audio = document.getElementById(audioId);
                if (audio) {
                    audio.currentTime = 0;
                    audio.play().catch(err => console.log('Audio blocked', err));
                }
            }
        });
    </script>
</body>
</html>
