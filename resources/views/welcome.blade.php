<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Combinadinhos 🤝</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">

    <!-- PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0f172a">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            --card-bg: rgba(30, 41, 59, 0.7);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent: #6366f1;
            --success: #10b981;
            --shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .landing-container {
            width: 100%;
            max-width: 480px;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 40px 24px;
            text-align: center;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 16px;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: 1.05rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .cta-btn {
            display: block;
            background: var(--accent);
            color: #fff;
            text-decoration: none;
            border-radius: 14px;
            padding: 16px 24px;
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
            transition: all 0.2s ease;
            margin-bottom: 16px;
        }

        .cta-btn:active {
            transform: translateY(2px);
            box-shadow: 0 2px 10px rgba(99, 102, 241, 0.2);
        }

        .sec-btn {
            display: block;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            text-decoration: none;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 14px 24px;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .sec-btn:active {
            background: rgba(255, 255, 255, 0.1);
        }

        .features {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .feature-icon {
            font-size: 1.4rem;
        }

        .feature-text strong {
            display: block;
            font-size: 0.95rem;
            color: var(--text-primary);
        }

        .feature-text span {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <h1>Combinadinhos 🤝</h1>
        <p>Cadastre sua Família e comece a transformação de quem você ama!</p>

        @auth
            <a href="/dashboard" class="cta-btn">Missões do Dia 🎯</a>
            <a href="/register" class="sec-btn">Cadastrar os Demais 🏠</a>
        @else
            <a href="/register" class="cta-btn">Começar Agora Grátis 🚀</a>
            <a href="/login" class="sec-btn">Já tenho conta / Acessar 🔑</a>
        @endauth

        <div class="features">
            <div class="feature-item">
                <span class="feature-icon">🎯</span>
                <div class="feature-text">
                    <strong>Desenvolva melhores comportamentos</strong>
                    <span>Não consegue acordar no horário? Não ajuda em casa? Crie missões e transforme em algo mais divertido</span>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon">🎁</span>
                <div class="feature-text">
                    <strong>Recompense o esforço</strong>
                    <span>Defina recompensas e aprove tudo pelo app!</span>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon">👨‍👩‍👧‍👦</span>
                <div class="feature-text">
                    <strong>Reforce o diálogo</strong>
                    <span>Aproveite esse momento para conversarem sobre as recompensas e os esforços.</span>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon">🌱</span>
                <div class="feature-text">
                    <strong>Crie novos hábitos</strong>
                    <span>Faça desse jogo uma fonte de bons hábitos</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
