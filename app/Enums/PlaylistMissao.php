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
            self::PEQUENOS_CONQUISTADORES => 'Incentivo à coordenação, primeiros hábitos de higiene e guardar brinquedos.',
            self::EXPLORADORES_ROTINA => 'Consolidação de hábitos diários, tarefas escolares e organização do próprio quarto.',
            self::MESTRES_AUTONOMIA => 'Desenvolvimento de disciplina pessoal, cooperação com a casa e incentivo à leitura.',
            self::JOVENS_PROTAGONISTAS => 'Gestão do próprio tempo, responsabilidades maduras e educação financeira.',
            self::MANHAS_SEM_ESTRESSE => 'Tudo o que a criança precisa para iniciar o dia com pontualidade e leveza.',
            self::FIM_DE_SEMANA_FAMILIA => 'Tarefas de colaboração e organização coletiva para o sábado e domingo.',
        };
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
                ['descricao' => 'Guardar os brinquedos após brincar', 'moedas' => 5, 'dia' => null],
                ['descricao' => 'Escovar os dentes de manhã e à noite', 'moedas' => 5, 'dia' => null],
                ['descricao' => 'Colocar a roupa suja no cesto', 'moedas' => 5, 'dia' => null],
                ['descricao' => 'Colocar os sapatos no lugar', 'moedas' => 5, 'dia' => null],
                ['descricao' => 'Comer a frutinha do lanche', 'moedas' => 5, 'dia' => null],
            ],
            self::EXPLORADORES_ROTINA => [
                ['descricao' => 'Fazer a lição de casa com capricho', 'moedas' => 15, 'dia' => null],
                ['descricao' => 'Arrumar a própria cama ao acordar', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Tomar banho sem precisar de insistência', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Organizar a mochila para o dia seguinte', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Comer legumes e verduras na refeição', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Desligar as telas no horário combinado', 'moedas' => 10, 'dia' => null],
            ],
            self::MESTRES_AUTONOMIA => [
                ['descricao' => 'Leitura diária de 20 minutos', 'moedas' => 20, 'dia' => null],
                ['descricao' => 'Ajudar a tirar ou colocar a mesa das refeições', 'moedas' => 15, 'dia' => null],
                ['descricao' => 'Manter o quarto organizado e gavetas fechadas', 'moedas' => 15, 'dia' => null],
                ['descricao' => 'Cuidar da água e comida do pet', 'moedas' => 15, 'dia' => null],
                ['descricao' => 'Estudar para as matérias da semana com antecedência', 'moedas' => 20, 'dia' => null],
                ['descricao' => 'Guardar o próprio calçado e casaco ao chegar da rua', 'moedas' => 10, 'dia' => null],
            ],
            self::JOVENS_PROTAGONISTAS => [
                ['descricao' => 'Gerenciar horário de estudos sem lembretes', 'moedas' => 25, 'dia' => null],
                ['descricao' => 'Lavar e secar a própria louça após as refeições', 'moedas' => 20, 'dia' => null],
                ['descricao' => 'Praticar atividade física ou exercício físico', 'moedas' => 20, 'dia' => null],
                ['descricao' => 'Trocar a roupa de cama e arrumar o quarto completo', 'moedas' => 30, 'dia' => 'Sábado'],
                ['descricao' => 'Economizar parte das moedas para um objetivo maior', 'moedas' => 25, 'dia' => null],
            ],
            self::MANHAS_SEM_ESTRESSE => [
                ['descricao' => 'Levantar no primeiro toque do alarme com bom humor', 'moedas' => 15, 'dia' => null],
                ['descricao' => 'Escovar os dentes e lavar o rosto', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Trocar de roupa e vestir o uniforme', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Tomar café da manhã sem distrações no celular', 'moedas' => 10, 'dia' => null],
                ['descricao' => 'Conferir mochila e materiais antes de sair', 'moedas' => 10, 'dia' => null],
            ],
            self::FIM_DE_SEMANA_FAMILIA => [
                ['descricao' => 'Ajudar na organização geral da casa no sábado', 'moedas' => 25, 'dia' => 'Sábado'],
                ['descricao' => 'Recolher e separar o lixo reciclável', 'moedas' => 15, 'dia' => 'Domingo'],
                ['descricao' => 'Ajudar a preparar uma refeição especial em família', 'moedas' => 20, 'dia' => 'Domingo'],
                ['descricao' => 'Organizar os armários de brinquedos ou livros', 'moedas' => 20, 'dia' => 'Sábado'],
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
