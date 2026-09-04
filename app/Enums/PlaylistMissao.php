<?php

namespace App\Enums;

enum PlaylistMissao: string
{
    case PEQUENOS_CONQUISTADORES = 'pequenos_conquistadores';
    case EXPLORADORES_ROTINA = 'exploradores_rotina';
    case MESTRES_AUTONOMIA = 'mestres_autonomia';
    case JOVENS_PROTAGONISTAS = 'jovens_protagonistas';
    case MANHAS_SEM_ESTRESSE = 'manhas_sem_estresse';
    case FIM_DE_SEMANA_FAMILIA = 'fim_de_semana_familia';

    public function titulo(): string
    {
        return match ($this) {
            self::PEQUENOS_CONQUISTADORES => 'Pequenos Conquistadores',
            self::EXPLORADORES_ROTINA => 'Exploradores da Rotina',
            self::MESTRES_AUTONOMIA => 'Mestres da Autonomia',
            self::JOVENS_PROTAGONISTAS => 'Jovens Protagonistas',
            self::MANHAS_SEM_ESTRESSE => 'Manhãs sem Estresse',
            self::FIM_DE_SEMANA_FAMILIA => 'Fim de Semana em Família',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::PEQUENOS_CONQUISTADORES => '🧸',
            self::EXPLORADORES_ROTINA => '🎒',
            self::MESTRES_AUTONOMIA => '🎯',
            self::JOVENS_PROTAGONISTAS => '🚀',
            self::MANHAS_SEM_ESTRESSE => '☀️',
            self::FIM_DE_SEMANA_FAMILIA => '🏡',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::PEQUENOS_CONQUISTADORES => 'Primeiros hábitos de autocuidado (ABVDs), organização motora e socialização básica (Portage / VB-MAPP).',
            self::EXPLORADORES_ROTINA => 'Consolidação das ABVDs com autonomia, rotina escolar, regulação de telas e cooperação (Habilidades Sociais).',
            self::MESTRES_AUTONOMIA => 'Desenvolvimento de disciplina pessoal, cooperação no lar, empatia e Atividades Instrumentais (AIVDs).',
            self::JOVENS_PROTAGONISTAS => 'Gestão autônoma do tempo, responsabilidades maduras, autocuidado integral e diálogo em família.',
            self::MANHAS_SEM_ESTRESSE => 'Sequenciamento prático de ABVDs matinais para iniciar o dia com pontualidade, autonomia e leveza.',
            self::FIM_DE_SEMANA_FAMILIA => 'Tarefas coletivas, cooperação comunitária e fortalecimento de vínculos familiares no fim de semana.',
        };
    }

    public function baseTerapeutica(): string
    {
        return match ($this) {
            self::PEQUENOS_CONQUISTADORES => 'ABVDs e marcos do Inventário Portage / VB-MAPP (higiene básica, organização inicial e primeiros passos sociais).',
            self::EXPLORADORES_ROTINA => 'Consolidação autônoma de ABVDs, marcos de desenvolvimento do Portage e regulação de conduta (higiene, rotina e limites).',
            self::MESTRES_AUTONOMIA => 'Habilidades Sociais avançadas, cooperação no ambiente familiar e Atividades Instrumentais de Vida Diária (AIVDs).',
            self::JOVENS_PROTAGONISTAS => 'Protagonismo juvenil, gestão autônoma do tempo, autocuidado integral e convivência empática.',
            self::MANHAS_SEM_ESTRESSE => 'Sequenciamento previsível de ABVDs matinais e autorregulação para iniciar o dia com leveza e pontualidade.',
            self::FIM_DE_SEMANA_FAMILIA => 'Habilidades Sociais de trabalho em equipe, empatia, cooperação nas tarefas do lar e convivência sem telas.',
        };
    }

    /**
     * @return array<string, array{nome: string, sigla: string, descricao: string}>
     */
    public static function basesTerapeuticas(): array
    {
        return [
            'abvds' => [
                'nome' => 'Atividades Básicas de Vida Diária',
                'sigla' => 'ABVDs',
                'descricao' => 'Tarefas essenciais de autocuidado, higiene pessoal, alimentação e sobrevivência que mantêm a independência básica.',
            ],
            'vbmapp' => [
                'nome' => 'Verbal Behavior Milestones Assessment and Placement Program',
                'sigla' => 'VB-MAPP',
                'descricao' => 'Avaliação de marcos do desenvolvimento e repertório comportamental baseada na análise do comportamento.',
            ],
            'portage' => [
                'nome' => 'Inventário Portage Operacionalizado',
                'sigla' => 'IPO',
                'descricao' => 'Mapeamento do desenvolvimento infantil (0 a 6 anos) em autocuidado, socialização, cognição e linguagem.',
            ],
            'habilidades_sociais' => [
                'nome' => 'Habilidades Sociais Infantis',
                'sigla' => 'Habilidades Sociais',
                'descricao' => 'Capacidade de comunicar sentimentos com respeito, cooperar com a família, resolver conflitos e demonstrar empatia.',
            ],
        ];
    }

    public function faixaEtaria(): ?string
    {
        return match ($this) {
            self::PEQUENOS_CONQUISTADORES => '3 a 5 anos',
            self::EXPLORADORES_ROTINA => '6 a 8 anos',
            self::MESTRES_AUTONOMIA => '9 a 12 anos',
            self::JOVENS_PROTAGONISTAS => '13+ anos',
            self::MANHAS_SEM_ESTRESSE, self::FIM_DE_SEMANA_FAMILIA => 'Todas as idades',
        };
    }

    public function ehTematica(): bool
    {
        return match ($this) {
            self::MANHAS_SEM_ESTRESSE, self::FIM_DE_SEMANA_FAMILIA => true,
            default => false,
        };
    }

    /**
     * @return array<int, array{descricao: string, moedas: int, dia: ?string}>
     */
    public function missoes(): array
    {
        return match ($this) {
            self::PEQUENOS_CONQUISTADORES => [
                ['descricao' => 'Guardar os brinquedos na caixa após brincar', 'moedas' => 5, 'dia' => null],
                ['descricao' => 'Escovar os dentes com orientação e capricho', 'moedas' => 5, 'dia' => null],
                ['descricao' => 'Lavar as mãos antes das refeições e após o banheiro', 'moedas' => 5, 'dia' => null],
                ['descricao' => 'Colocar a roupa suja no cesto ao trocar de roupa', 'moedas' => 5, 'dia' => null],
                ['descricao' => 'Guardar os sapatos no lugar ao chegar em casa', 'moedas' => 5, 'dia' => null],
                ['descricao' => 'Comer a frutinha ou refeição à mesa sem telas', 'moedas' => 5, 'dia' => null],
                ['descricao' => 'Usar as palavrinhas mágicas: por favor e obrigado', 'moedas' => 5, 'dia' => null],
            ],
            self::EXPLORADORES_ROTINA => [
                ['descricao' => 'Fazer a lição de casa com capricho no horário combinado', 'moedas' => 15, 'dia' => null],
                ['descricao' => 'Arrumar a própria cama ao acordar', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Tomar banho e se vestir de forma independente', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Organizar a mochila e materiais para a aula do dia seguinte', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Comer legumes e verduras na refeição', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Desligar as telas no horário combinado com calma', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Escovar os dentes após as refeições e antes de dormir', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Ajudar a tirar o próprio prato e copo da mesa', 'moedas' => 10, 'dia' => null],
            ],
            self::MESTRES_AUTONOMIA => [
                ['descricao' => 'Leitura diária de 20 minutos com foco e atenção', 'moedas' => 20, 'dia' => null],
                ['descricao' => 'Ajudar a pôr ou tirar a mesa das refeições da família', 'moedas' => 15, 'dia' => null],
                ['descricao' => 'Manter o quarto organizado, cama arrumada e gavetas fechadas', 'moedas' => 15, 'dia' => null],
                ['descricao' => 'Cuidar da água, comida ou higiene do pet da casa', 'moedas' => 15, 'dia' => null],
                ['descricao' => 'Estudar para as matérias da semana com planejamento prévio', 'moedas' => 20, 'dia' => null],
                ['descricao' => 'Guardar calçados e pendurar casacos ao chegar da rua', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Resolver desentendimentos conversando com calma e respeito', 'moedas' => 15, 'dia' => null],
            ],
            self::JOVENS_PROTAGONISTAS => [
                ['descricao' => 'Gerenciar horários de estudo e prazos escolares sem lembretes', 'moedas' => 25, 'dia' => null],
                ['descricao' => 'Lavar, secar e guardar a louça após as refeições', 'moedas' => 20, 'dia' => null],
                ['descricao' => 'Praticar atividade física e manter sono regular', 'moedas' => 20, 'dia' => null],
                ['descricao' => 'Trocar a roupa de cama e fazer a faxina completa do quarto', 'moedas' => 30, 'dia' => 'Sábado'],
                ['descricao' => 'Planejar e poupar parte das moedas para metas futuras', 'moedas' => 25, 'dia' => null],
                ['descricao' => 'Praticar escuta ativa e diálogo respeitoso na convivência familiar', 'moedas' => 20, 'dia' => null],
            ],
            self::MANHAS_SEM_ESTRESSE => [
                ['descricao' => 'Levantar no primeiro toque do alarme com disposição e dar bom dia', 'moedas' => 15, 'dia' => null],
                ['descricao' => 'Higiene matinal: escovar os dentes e lavar o rosto com capricho', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Trocar de roupa e vestir o uniforme do dia sem demora', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Tomar café da manhã à mesa com calma e sem distrações no celular', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Conferir mochila, materiais e chaves antes de sair de casa', 'moedas' => 10, 'dia' => null],
            ],
            self::FIM_DE_SEMANA_FAMILIA => [
                ['descricao' => 'Ajudar na organização geral e limpeza coletiva da casa', 'moedas' => 25, 'dia' => 'Sábado'],
                ['descricao' => 'Recolher e separar o lixo reciclável da residência', 'moedas' => 15, 'dia' => 'Domingo'],
                ['descricao' => 'Ajudar a preparar uma refeição ou lanche especial em família', 'moedas' => 20, 'dia' => 'Domingo'],
                ['descricao' => 'Organizar armários, separar itens ou livros para doação', 'moedas' => 20, 'dia' => 'Sábado'],
                ['descricao' => 'Participar de momento de lazer ou jogo em família sem telas', 'moedas' => 20, 'dia' => 'Domingo'],
            ],
        };
    }

    public static function porIdade(?int $idade): self
    {
        if ($idade === null) {
            return self::EXPLORADORES_ROTINA;
        }

        if ($idade <= 5) {
            return self::PEQUENOS_CONQUISTADORES;
        }

        if ($idade <= 8) {
            return self::EXPLORADORES_ROTINA;
        }

        if ($idade <= 12) {
            return self::MESTRES_AUTONOMIA;
        }

        return self::JOVENS_PROTAGONISTAS;
    }
}
