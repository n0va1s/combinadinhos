<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FamilyInvitation;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    public function show(Request $request, $code)
    {
        $invitation = FamilyInvitation::where('code', $code)->where('used', false)->firstOrFail();
        $role = $request->query('role', $invitation->role);

        return view('invite', [
            'invitation' => $invitation,
            'role' => $role
        ]);
    }

    public function accept(Request $request, $code)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $invitation = FamilyInvitation::where('code', $code)->where('used', false)->firstOrFail();
        $role = $request->input('role', $invitation->role);

        // Cria o usuário vinculado à família do convite
        User::create([
            'name' => $request->input('name'),
            'role' => $role,
            'family_id' => $invitation->family_id,
            'balance' => 0
        ]);

        // Marca convite como utilizado
        $invitation->used = true;
        $invitation->save();

        return redirect('/')->with('success', 'Você entrou na família com sucesso!');
    }
}
