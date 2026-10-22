<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;

class ResolverFilhoSelecionado
{
    /**
     * Resolve e valida o filho selecionado a partir do parâmetro ?filho= da URL.
     *
     * Fonte única de seleção: o dashboard envia o id do filho via URL. Esta Action
     * centraliza a regra "valide que o filho pertence à família do responsável logado",
     * reaproveitada por extrato, loja e missoes.
     *
     * @return array{filho: ?User, ehResponsavel: bool, precisaSelecionar: bool, filhosDaFamilia: \Illuminate\Support\Collection|array}
     */
    public function executar(?string $idFilhoDaUrl): array
    {
        $usuarioLogado = auth()->user();

        if (! $usuarioLogado) {
            return [
                'filho' => null,
                'ehResponsavel' => false,
                'precisaSelecionar' => false,
                'filhosDaFamilia' => [],
            ];
        }

        // Quando o logado é o próprio filho, ignoramos a URL e usamos o usuário.
        if (in_array($usuarioLogado->role->value, [UserRole::FILHO->value, UserRole::FILHA->value], true)) {
            return [
                'filho' => $usuarioLogado,
                'ehResponsavel' => false,
                'precisaSelecionar' => false,
                'filhosDaFamilia' => [],
            ];
        }

        // Responsável (Pai/Mãe): carrega os filhos da família e valida a seleção.
        $consulta = User::query();

        if ($usuarioLogado->family_id) {
            $consulta->where('family_id', $usuarioLogado->family_id);
        }

        $filhosDaFamilia = $consulta
            ->whereIn('role', [UserRole::FILHO->value, UserRole::FILHA->value])
            ->orderBy('name')
            ->get();

        $filhoValidado = ! empty($idFilhoDaUrl)
            ? $filhosDaFamilia->firstWhere('id', $idFilhoDaUrl)
            : null;

        return [
            'filho' => $filhoValidado,
            'ehResponsavel' => true,
            'precisaSelecionar' => ($filhoValidado === null),
            'filhosDaFamilia' => $filhosDaFamilia,
        ];
    }
}
