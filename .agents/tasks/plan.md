# Implementation Plan — Extrair páginas /loja e /missoes + seleção de filho via URL

Worktree (use absolute paths): `/home/novais/projetos/combinadinhos/.worktrees/extrair-paginas`
Branch: `feature/extrair-paginas` (off `main`). Git: `git -C /home/novais/projetos/combinadinhos/.worktrees/extrair-paginas`.

## Pre-requisito de ambiente (o worktree não tem deps)
O worktree não possui `node_modules`, `vendor` nem `.env` (o repositório pai tem). Antes de qualquer verificação, instale as dependências dentro do worktree:
- `composer install` (gera `vendor/`)
- copie o `.env`: `cp /home/novais/projetos/combinadinhos/.env .env` e garanta `APP_KEY` (`php artisan key:generate` se faltar)
- `npm ci` (gera `node_modules/`)
Observação Tailwind v4: `resources/css/app.css` usa `@import 'tailwindcss'` + `@source '../views'`. As páginas novas ficam em `resources/views/...`, já cobertas pelo `@source`. Como o projeto usa estilos inline + classes utilitárias próprias (`.glass-card`, `.header`, `.btn-primary` etc. definidas no `<style>` de `layouts/app.blade.php` e em `app.css`), NÃO introduza classes utilitárias novas do Tailwind que exijam scan adicional; reutilize as classes/estilos inline existentes.

## Decisões de arquitetura (curtas)
- **Lógica compartilhada de resolução/validação do filho vira uma Action** (`App\Actions\ResolverFilhoSelecionado`), por AGENTS.md (lógica pesada/consulta ao banco fora do Volt). As três páginas (extrato, loja, missoes) a usam no `mount` para ler o parâmetro `filho` da URL, validar que o usuário pertence à família do logado e devolver o filho (ou o próprio usuário, se o logado for filho). Motivo: a regra "valide que o filho pertence à família" se repete em 3 páginas; centralizar evita divergência e confiança cega no id da URL.
- **Leitura do parâmetro `filho`**: usar `request()->query('filho')` dentro do `mount` (Volt funcional). Não usar `#[Url]` em propriedade porque a seleção é fonte única no dashboard e as páginas apenas leem o valor na montagem (sem two-way binding). 
- **Validação de família**: reaproveitar a lógica de `carregarFilhos` do extrato (filtra por `family_id` e papéis `S`/`D`). A Action retorna o filho somente se ele constar nessa lista.
- **MissaoSugeridaService permanece**; a página /missoes injeta o serviço no handler `adicionarMissoesSugeridas` igual ao dashboard atual.
- As páginas novas repetem o padrão de header do `extrato.blade.php` (botão voltar + título) e o visual glass/escuro já existente.

## Mapa do dashboard: o que SAI x o que FICA

SAI para /loja (Loja de Recompensas — modal `exibirModalRecompensas`):
- state: `exibirModalRecompensas`, `recompensas`, `descricaoNovaRecompensa`, `custoNovaRecompensa`
- handlers: `carregarRecompensas`, `adicionarRecompensa`, `resgatarRecompensa`, `excluirRecompensa`
- imports usados por eles: `App\Models\Reward` (apenas aqui), `App\Models\Transaction`, `App\Enums\TransactionStatus`
- markup: bloco `<!-- Modal / Seção de Recompensas -->` (o `@if($exibirModalRecompensas) ... @endif`)
- trigger no header: a `<div wire:click="$toggle('exibirModalRecompensas')">` (indicador de moedas 🪙)

SAI para /missoes (Playlists + Nova Missão — modal `exibirModalPlaylists` + seção `exibirSecaoNovaMissao`):
- state: `exibirSecaoNovaMissao`, `descricaoNovaTarefa`, `moedasNovaTarefa`, `diaNovaTarefa`, `exibirModalPlaylists`, `playlistSelecionada`, `missoesSugeridasSelecionadas`
- handlers: `adicionarTarefaRapida`, `abrirModalPlaylists`, `fecharModalPlaylists`, `alternarExpansaoPlaylist`, `alternarSelecaoMissao`, `alternarTodasMissoesDaPlaylist`, `adicionarMissoesSugeridas`
- imports usados por eles: `App\Enums\PlaylistMissao`, `App\Services\MissaoSugeridaService`
- markup: bloco `<!-- Painel dos Pais (Notificar e Convidar) -->` (`@if(... && $exibirSecaoNovaMissao)`) e bloco `<!-- Modal de Playlists de Missões Sugeridas -->` (`@if($exibirModalPlaylists) ... @endif`)
- triggers no header/perfil: botão `💡 Playlists` (`wire:click="abrirModalPlaylists"`), botão `+` de nova missão (`wire:click="$toggle('exibirSecaoNovaMissao')"`), e os botões `abrirModalPlaylists` dentro do estado vazio de "Missões do Dia" e do painel de nova missão.

FICA no dashboard (NÃO remover):
- state: `usuarios`, `idUsuarioSelecionado`, `usuarioSelecionado`, `missoes`, `somVerificado`, `hashUltimasMissoes`, `transacoesPendentes`
- handlers: `carregarUsuarios`, `carregarTransacoesPendentes`, `carregarMissoes`, `selecionarUsuario`, `marcarComoConcluido`, `aprovarTransacao`, `recusarTransacao`, `excluirMissao`, `sair`
- imports: `App\Models\User`, `App\Models\Mission`, `App\Models\Transaction`, `App\Enums\TransactionStatus`, `Illuminate\Support\Facades\Auth`
- markup: seletor de filho, bloco de perfil, saldo (header), lista "Missões do Dia", `marcarComoConcluido`, seção "Aguardando Aprovação", `wire:poll.10s="carregarMissoes"`
- `selecionarUsuario` deve parar de chamar `carregarRecompensas()` (método removido).

## Itens (ordenados por dependência)

- [ ] 1. Criar a Action de resolução/validação do filho a partir da URL.
      Criar `App\Actions\ResolverFilhoSelecionado` com um método (ex.: `executar(?string $idFilhoDaUrl): array`) que: obtém `auth()->user()`; se o logado for filho (`role` S/D) retorna `['filho' => $usuarioLogado, 'ehResponsavel' => false, 'precisaSelecionar' => false]` ignorando a URL; se for responsável (P/M), carrega os filhos da família (query igual ao `carregarFilhos` do extrato: filtra por `family_id` quando existir, `whereIn('role', ['S','D'])`, ordenado por nome) e só devolve o filho se o id da URL constar nessa lista (validação de família); se não houver `filho` válido retorna `['filho' => null, 'ehResponsavel' => true, 'precisaSelecionar' => true]`. Variáveis em pt-BR camelCase.
      Files: app/Actions/ResolverFilhoSelecionado.php
      Verify: `php artisan test` continua passando (nenhum teste quebrado); opcionalmente `php -l app/Actions/ResolverFilhoSelecionado.php` sem erros de sintaxe.

- [ ] 2. Adicionar as rotas /loja e /missoes em routes/web.php.
      Após a rota do extrato, adicionar `Volt::route('/loja', 'loja')->middleware('auth')->name('loja');` e `Volt::route('/missoes', 'missoes')->middleware('auth')->name('missoes');`. Manter /dashboard e /extrato.
      Files: routes/web.php
      Verify: após criadas as páginas (itens 3 e 4) `php artisan route:list` lista `loja`, `missoes`, `extrato`, `dashboard`. (Se rodar antes das views existirem, a rota já aparece, mas acessá-la falharia — por isso criar as views nos próximos itens.)

- [ ] 3. Criar a página /loja com toda a funcionalidade da Loja de Recompensas.
      Criar `resources/views/livewire/loja.blade.php` (Volt full-page, `layout('layouts.app')`). State: `filho` (usuário resolvido), `ehResponsavel`, `precisaSelecionar`, `recompensas`, `descricaoNovaRecompensa`, `custoNovaRecompensa`. `mount`: ler `request()->query('filho')`, chamar a Action do item 1 para resolver/validar o filho, popular `filho`/`ehResponsavel`/`precisaSelecionar`, e carregar recompensas se houver `filho`. Mover os handlers `carregarRecompensas`, `adicionarRecompensa`, `resgatarRecompensa` (mantendo a lógica de saldo/`Transaction`/aprovação idêntica à atual, inclusive `\Flux::toast` e `dispatch('play-task-alert', ['sound'=>'applause'])`), `excluirRecompensa`. Header no padrão do extrato (botão voltar `route('dashboard')` + título "Loja de Recompensas 🎁"). Markup da listagem/resgate/adicionar/excluir reaproveitado do modal atual (parents-only para adicionar/excluir via checagem de role). Quando responsável sem `filho`: exibir aviso acessível "Selecione um filho no painel" com link `wire:navigate` ao dashboard. Textos pt-BR via `__()` onde padronizado. WCAG AAA (aria-labels, foco visível, contraste).
      Files: resources/views/livewire/loja.blade.php
      Verify: `npm run build` sai 0; `php artisan route:list` mostra `loja`.

- [ ] 4. Criar a página /missoes com Playlists + criação avulsa de missão.
      Criar `resources/views/livewire/missoes.blade.php` (Volt full-page, `layout('layouts.app')`). State: `filho`, `ehResponsavel`, `precisaSelecionar`, `exibirSecaoNovaMissao`, `descricaoNovaTarefa`, `moedasNovaTarefa`, `diaNovaTarefa`, `playlistSelecionada` (default `PlaylistMissao::EXPLORADORES_ROTINA->value`), `missoesSugeridasSelecionadas`. `mount`: igual ao item 3 (ler `filho` da URL via Action). Mover handlers `adicionarTarefaRapida`, `abrirModalPlaylists`/`fecharModalPlaylists` (adaptados: aqui a tela já é a própria página; pré-seleção por idade via `PlaylistMissao::porIdade`), `alternarExpansaoPlaylist`, `alternarSelecaoMissao`, `alternarTodasMissoesDaPlaylist`, `adicionarMissoesSugeridas` (injeta `MissaoSugeridaService`, mantém `\Flux::toast`). Markup: seção de criação avulsa (form `adicionarTarefaRapida`) + lista de playlists em accordion (reaproveitar o markup do modal, sem o `position:fixed` de overlay — renderizar como conteúdo da página). Header padrão extrato (voltar + título "Novas Missões 🎯" ou similar). Responsável sem `filho`: mesmo aviso acessível do item 3. pt-BR via `__()`. WCAG AAA.
      Files: resources/views/livewire/missoes.blade.php
      Verify: `npm run build` sai 0; `php artisan route:list` mostra `missoes`.

- [ ] 5. Alinhar o extrato para ler `filho` da URL e remover o seletor próprio.
      Editar `resources/views/livewire/extrato.blade.php`: no `mount`, para responsável, ler `request()->query('filho')` e resolver via Action (item 1) em vez de iniciar sem seleção; remover o `state`/handler do seletor próprio (`selecionarUsuario`, `usuarios`) ou mantê-los apenas se necessários à Action — preferir remover o bloco de markup "Seletor de filho (apenas para pais/responsáveis)". Quando responsável sem `filho` na URL: exibir aviso acessível "Selecione um filho no painel" com link `wire:navigate` ao dashboard (substitui o placeholder "Selecione um filho acima"). Filho logado (S/D) continua vendo o próprio extrato. Manter a `computed` `transacoes` e a validação de família (agora via Action).
      Files: resources/views/livewire/extrato.blade.php
      Verify: `npm run build` sai 0; abrir `/extrato?filho=<id>` logado como pai não deve quebrar (validação manual opcional); `php artisan route:list` ainda mostra `extrato`.

- [ ] 6. Limpar o dashboard: remover os dois modais e estado/handlers movidos; converter triggers em links wire:navigate; adicionar ícone /register.
      Editar `resources/views/livewire/dashboard.blade.php`:
      (a) remover do `state([...])` as chaves movidas (ver mapa acima) e remover o import `use App\Models\Reward;`;
      (b) remover os handlers movidos (`carregarRecompensas`, `adicionarRecompensa`, `resgatarRecompensa`, `excluirRecompensa`, `adicionarTarefaRapida`, `abrirModalPlaylists`, `fecharModalPlaylists`, `alternarExpansaoPlaylist`, `alternarSelecaoMissao`, `alternarTodasMissoesDaPlaylist`, `adicionarMissoesSugeridas`); remover a chamada `carregarRecompensas()` de `selecionarUsuario` e do `mount`;
      (c) remover os blocos de markup dos dois modais e o painel de nova missão;
      (d) converter o indicador de moedas 🪙 (antes `$toggle('exibirModalRecompensas')`) em `<a href="{{ route('loja', ['filho' => $idUsuarioSelecionado]) }}" wire:navigate>` mantendo o visual glass; converter o botão `💡 Playlists` em link `route('missoes', ['filho' => $idUsuarioSelecionado])` `wire:navigate`; o botão `+` de nova missão também aponta para `route('missoes', ['filho' => $idUsuarioSelecionado])`; ajustar os botões "Explorar/Ver Playlists" do estado vazio para o mesmo link;
      (e) adicionar no header um ícone/botão acessível (SVG, fundo glass, `aria-label="Cadastrar familiares"`) com link `route('register')` `wire:navigate`, no mesmo estilo dos demais ícones do header;
      (f) NÃO remover: seletor de filho, saldo, lista "Missões do Dia", `marcarComoConcluido`, "Aguardando Aprovação", `wire:poll`.
      Files: resources/views/livewire/dashboard.blade.php
      Verify: `npm run build` sai 0; `php artisan route:list` mostra dashboard/extrato/loja/missoes; grep de garantia (não é verificação): nenhum `exibirModalRecompensas`/`exibirModalPlaylists`/`abrirModalPlaylists` restante no dashboard.

- [ ] 7. Verificação integrada final.
      Rodar o build e a suíte de testes completos e conferir as rotas.
      Files: (nenhum — verificação)
      Verify: `npm run build` sai 0; `php artisan route:list` lista `dashboard`, `extrato`, `loja`, `missoes` (todas com middleware `auth`); `php artisan test` passa (os testes existentes `MissaoSugeridaServiceTest`, `PlaylistMissaoTest`, `ProfileTest`, Auth não devem quebrar).

## Lacunas / suposições
- O título exato da página /missoes não é especificado na tarefa; assumo "Novas Missões 🎯". Ajustável.
- Uso de `__()` apenas onde já houver chaves padronizadas; os textos atuais são literais pt-BR inline — mantê-los como estão é aceitável, preferindo `__()` quando houver chave equivalente.
