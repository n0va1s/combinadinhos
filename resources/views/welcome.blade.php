<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Combinadinhos - Gestão de Rotina em Família</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                            outfit: ['Outfit', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                50: '#f5f7ff',
                                100: '#ebf0ff',
                                200: '#d6e0ff',
                                300: '#adc2ff',
                                400: '#7599ff',
                                500: '#3b66f5',
                                600: '#2545d9',
                                700: '#1c32b8',
                                800: '#1b2a94',
                                900: '#1b2778',
                            }
                        }
                    }
                }
            }
        </script>
        <style>
            .animated-blob {
                filter: blur(80px);
                opacity: 0.15;
                animation: floatBlob 12s infinite alternate ease-in-out;
            }
            @keyframes floatBlob {
                0% { transform: translate(0, 0) scale(1); }
                100% { transform: translate(40px, 60px) scale(1.2); }
            }
            .glass-card {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.08);
            }
            .light-glass-card {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(0, 0, 0, 0.06);
            }
        </style>
    </head>
    <body class="bg-slate-950 text-slate-100 font-sans min-h-screen relative overflow-x-hidden antialiased">
        <!-- Background Ambient Lights -->
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-violet-600 animated-blob"></div>
        <div class="absolute top-[20%] right-[-10%] w-[600px] h-[600px] rounded-full bg-brand-600 animated-blob" style="animation-delay: -3s;"></div>
        <div class="absolute bottom-[-10%] left-[20%] w-[550px] h-[550px] rounded-full bg-emerald-600 animated-blob" style="animation-delay: -6s;"></div>

        <!-- Navigation Bar -->
        <nav class="sticky top-0 z-50 w-full border-b border-slate-800/60 bg-slate-950/80 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🎯</span>
                    <span class="font-outfit font-extrabold text-xl tracking-tight bg-gradient-to-r from-brand-400 to-violet-400 bg-clip-text text-transparent">Combinadinhos</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="#funcionamento" class="text-sm text-slate-400 hover:text-slate-200 transition-colors hidden sm:block">Como Funciona</a>
                    <a href="#comandos" class="text-sm text-slate-400 hover:text-slate-200 transition-colors hidden sm:block">Comandos</a>
                    <a href="https://t.me/CombinadinhosBOT" target="_blank" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-medium text-sm px-4 py-2 rounded-full transition-all hover:scale-105 shadow-lg shadow-brand-900/40">
                        <span>Falar com o Bot</span>
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.24-5.54 3.65-.52.36-.97.53-1.33.52-.4-.01-1.17-.23-1.74-.41-.7-.23-1.26-.35-1.21-.74.03-.2.3-.41.82-.62 3.2-1.39 5.34-2.31 6.42-2.76 3.07-1.28 3.71-1.5 4.13-1.5.09 0 .3.02.43.13.11.09.14.22.15.31.01.07.01.14-.01.21z"/></svg>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 text-center relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-800/60 border border-slate-700/50 mb-6 hover:border-slate-600 transition-colors">
                <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-semibold text-slate-300 uppercase tracking-widest">Gamificação Familiar Ativa</span>
            </div>
            
            <h1 class="font-outfit font-extrabold text-4xl sm:text-6xl lg:text-7xl tracking-tight leading-none max-w-4xl mx-auto">
                Transforme a rotina de casa em uma <span class="bg-gradient-to-r from-brand-400 via-violet-400 to-emerald-400 bg-clip-text text-transparent">Aventura Divertida!</span>
            </h1>
            
            <p class="mt-6 text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto font-light">
                Ajude as crianças a desenvolverem responsabilidade e bons hábitos acumulando pontos que podem ser trocados por recompensas incríveis na lojinha.
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="#funcionamento" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/60 font-semibold px-8 py-3.5 rounded-full transition-all hover:scale-105">
                    Como funciona?
                </a>
                <a href="https://t.me/CombinadinhosBOT" target="_blank" class="bg-gradient-to-r from-brand-600 to-violet-600 hover:from-brand-500 hover:to-violet-500 text-white font-semibold px-8 py-3.5 rounded-full transition-all hover:scale-105 shadow-xl shadow-brand-900/30 inline-flex items-center gap-2">
                    Iniciar no Telegram
                </a>
            </div>
        </header>

        <!-- Features Grid -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card rounded-3xl p-8 hover:scale-[1.02] transition-transform duration-300">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="font-outfit font-bold text-xl mb-2 text-slate-100">Missões Diárias</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Crie missões automáticas que aparecem nos dias da semana corretos para manter as obrigações sob controle.
                    </p>
                </div>
                <!-- Feature 2 -->
                <div class="glass-card rounded-3xl p-8 hover:scale-[1.02] transition-transform duration-300">
                    <div class="text-4xl mb-4">🎁</div>
                    <h3 class="font-outfit font-bold text-xl mb-2 text-slate-100">Lojinha Virtual</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Cadastre recompensas como "1 hora de videogame" ou "passeio no parque" e estimule o esforço das crianças.
                    </p>
                </div>
                <!-- Feature 3 -->
                <div class="glass-card rounded-3xl p-8 hover:scale-[1.02] transition-transform duration-300">
                    <div class="text-4xl mb-4">⚖️</div>
                    <h3 class="font-outfit font-bold text-xl mb-2 text-slate-100">Controle e Segurança</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Missões realizadas dependem da aprovação direta dos pais, e penalidades só podem ser aplicadas por eles.
                    </p>
                </div>
                <!-- Feature 4 -->
                <div class="glass-card rounded-3xl p-8 hover:scale-[1.02] transition-transform duration-300">
                    <div class="text-4xl mb-4">🤖</div>
                    <h3 class="font-outfit font-bold text-xl mb-2 text-slate-100">100% no Telegram</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Sem apps pesados. Toda a interação ocorre diretamente por botões interativos no aplicativo preferido da família.
                    </p>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="funcionamento" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 border-t border-slate-800/40 relative z-10">
            <div class="text-center mb-16">
                <h2 class="font-outfit font-bold text-3xl sm:text-4xl text-slate-100">Como funciona o ciclo do bem?</h2>
                <p class="text-slate-400 mt-3 max-w-xl mx-auto font-light">Quatro passos simples para transformar a harmonia da sua casa.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 relative">
                <!-- Step 1 -->
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-2xl bg-brand-900/30 border border-brand-500/30 text-brand-400 font-outfit font-bold text-2xl flex items-center justify-center mb-6">
                        1
                    </div>
                    <h3 class="font-outfit font-bold text-lg mb-2 text-slate-100">Pais Adicionam</h3>
                    <p class="text-slate-400 text-sm max-w-xs">
                        Adicione missões diárias com valores positivos (créditos) ou negativos (penalidades/finais).
                    </p>
                </div>
                <!-- Step 2 -->
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-2xl bg-violet-900/30 border border-violet-500/30 text-violet-400 font-outfit font-bold text-2xl flex items-center justify-center mb-6">
                        2
                    </div>
                    <h3 class="font-outfit font-bold text-lg mb-2 text-slate-100">Crianças Fazem</h3>
                    <p class="text-slate-400 text-sm max-w-xs">
                        A criança realiza a missão do dia e clica no botão "Feito" no bot do Telegram.
                    </p>
                </div>
                <!-- Step 3 -->
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-900/30 border border-emerald-500/30 text-emerald-400 font-outfit font-bold text-2xl flex items-center justify-center mb-6">
                        3
                    </div>
                    <h3 class="font-outfit font-bold text-lg mb-2 text-slate-100">Pais Aprovam</h3>
                    <p class="text-slate-400 text-sm max-w-xs">
                        O bot solicita confirmação no chat do grupo. Ao aprovar, as moedas caem no saldo do filho.
                    </p>
                </div>
                <!-- Step 4 -->
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-2xl bg-amber-900/30 border border-amber-500/30 text-amber-400 font-outfit font-bold text-2xl flex items-center justify-center mb-6">
                        4
                    </div>
                    <h3 class="font-outfit font-bold text-lg mb-2 text-slate-100">Troca na Lojinha</h3>
                    <p class="text-slate-400 text-sm max-w-xs">
                        Com saldo suficiente, a criança compra recompensas da lojinha direto no bot.
                    </p>
                </div>
            </div>
        </section>

        <!-- Command Cheat Sheet -->
        <section id="comandos" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 border-t border-slate-800/40 relative z-10">
            <div class="glass-card rounded-3xl p-8 sm:p-12 overflow-hidden relative">
                <div class="absolute top-[-50px] right-[-50px] w-[200px] h-[200px] bg-brand-500/10 rounded-full blur-3xl"></div>
                
                <h2 class="font-outfit font-bold text-2xl sm:text-3xl text-slate-100 mb-2">Comandos Rápidos do Bot</h2>
                <p class="text-slate-400 text-sm mb-8 font-light">Copie e envie estes comandos diretamente no chat com o bot para configurá-lo.</p>

                <div class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800/50 gap-4">
                        <div>
                            <span class="bg-brand-950 text-brand-400 font-mono text-xs px-2.5 py-1 rounded-md border border-brand-800/40 font-semibold">/missoes</span>
                            <p class="text-slate-400 text-sm mt-1.5">Lista todas as missões do dia atual e exibe botões rápidos.</p>
                        </div>
                        <span class="text-xs text-slate-500 uppercase tracking-widest font-semibold">Geral</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800/50 gap-4">
                        <div>
                            <span class="bg-brand-950 text-brand-400 font-mono text-xs px-2.5 py-1 rounded-md border border-brand-800/40 font-semibold">/lojinha</span>
                            <p class="text-slate-400 text-sm mt-1.5">Abre a lojinha de prêmios com opções de compra direta.</p>
                        </div>
                        <span class="text-xs text-slate-500 uppercase tracking-widest font-semibold">Geral</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800/50 gap-4">
                        <div>
                            <span class="bg-brand-950 text-brand-400 font-mono text-xs px-2.5 py-1 rounded-md border border-brand-800/40 font-semibold">/saldo</span>
                            <p class="text-slate-400 text-sm mt-1.5">Mostra a pontuação acumulada atual do membro da família.</p>
                        </div>
                        <span class="text-xs text-slate-500 uppercase tracking-widest font-semibold">Geral</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800/50 gap-4">
                        <div>
                            <span class="bg-violet-950/80 text-violet-400 font-mono text-xs px-2.5 py-1 rounded-md border border-violet-800/40 font-semibold">/missoes-add Descrição, Valor, [Dia]</span>
                            <p class="text-slate-400 text-sm mt-1.5">Cadastra uma nova tarefa de rotina (apenas pais/mães).</p>
                        </div>
                        <span class="text-xs text-violet-500 uppercase tracking-widest font-semibold">Apenas Pais</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="bg-violet-950/80 text-violet-400 font-mono text-xs px-2.5 py-1 rounded-md border border-violet-800/40 font-semibold">/lojinha-add Descrição, Custo</span>
                            <p class="text-slate-400 text-sm mt-1.5">Insere um prêmio legal de recompensa na lojinha (apenas pais/mães).</p>
                        </div>
                        <span class="text-xs text-violet-500 uppercase tracking-widest font-semibold">Apenas Pais</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-900 bg-slate-950 py-12 relative z-10 text-center">
            <p class="text-slate-500 text-sm">
                Combinadinhos &copy; {{ date('Y') }} - Feito com ❤️ para a harmonia da família.
            </p>
        </footer>
    </body>
</html>
