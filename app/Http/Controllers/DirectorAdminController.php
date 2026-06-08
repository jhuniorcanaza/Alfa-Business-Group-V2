<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Team;
use App\Models\User;
use App\Models\KpiConfig;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class DirectorAdminController extends Controller
{
    // ==========================================
    // GESTIÓN DE OFICINAS
    // ==========================================
    public function officesIndex()
    {
        $offices = Office::withCount(['users' => function($q) {
            $q->where('role', 'asesor');
        }])->get();
        return view('director.offices.index', compact('offices'));
    }

    public function officesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        Office::create($request->only(['name', 'city', 'address', 'phone']));

        return redirect()->route('director.offices.index')->with('success', 'Oficina creada exitosamente.');
    }

    public function officesUpdate(Request $request, Office $office)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        $office->update($request->only(['name', 'city', 'address', 'phone', 'is_active']));

        return redirect()->route('director.offices.index')->with('success', 'Oficina actualizada exitosamente.');
    }

    // ==========================================
    // GESTIÓN DE USUARIOS
    // ==========================================
    public function usersIndex()
    {
        $users = User::with(['office', 'team'])->get();
        $offices = Office::where('is_active', true)->get();
        $teams = Team::where('is_active', true)->get();
        return view('director.users.index', compact('users', 'offices', 'teams'));
    }

    public function usersStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:asesor,team_leader,director',
            'phone' => 'nullable|string|max:20',
            'office_id' => 'nullable|exists:offices,id',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'office_id' => $request->office_id,
            'team_id' => $request->team_id,
            'is_active' => true,
        ]);

        return redirect()->route('director.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function usersUpdate(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:asesor,team_leader,director',
            'phone' => 'nullable|string|max:20',
            'office_id' => 'nullable|exists:offices,id',
            'team_id' => 'nullable|exists:teams,id',
            'is_active' => 'required|boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'office_id' => $request->office_id,
            'team_id' => $request->team_id,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('director.users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    // ==========================================
    // CONFIGURACIÓN DE KPIS
    // ==========================================
    public function kpisIndex()
    {
        $kpis = KpiConfig::all();
        return view('director.kpis.index', compact('kpis'));
    }

    public function kpisUpdate(Request $request, KpiConfig $kpi)
    {
        $request->validate([
            'weekly_goal' => 'required|integer|min:0',
            'yellow_threshold_pct' => 'required|integer|min:0|max:100',
            'red_threshold_pct' => 'required|integer|min:0|max:100',
            'is_active' => 'required|boolean',
        ]);

        $kpi->update($request->only(['weekly_goal', 'yellow_threshold_pct', 'red_threshold_pct', 'is_active']));

        return redirect()->route('director.kpis.index')->with('success', 'KPI configurado exitosamente.');
    }

    // ==========================================
    // GESTIÓN DE EQUIPOS (TEAMS)
    // ==========================================
    public function teamsIndex()
    {
        $teams = Team::with(['office', 'leader'])
            ->withCount(['members' => function($q) {
                $q->where('role', 'asesor');
            }])->get();

        $offices = Office::where('is_active', true)->get();
        
        // Obtener todos los Team Líderes activos con sus equipos liderados
        $leaders = User::where('role', 'team_leader')
            ->where('is_active', true)
            ->with(['ledTeams' => function($q) {
                $q->where('is_active', true);
            }])
            ->get();

        return view('director.teams.index', compact('teams', 'offices', 'leaders'));
    }

    public function teamsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'office_id' => 'required|exists:offices,id',
            'leader_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $alreadyLeading = Team::where('leader_id', $value)->where('is_active', true)->exists();
                        if ($alreadyLeading) {
                            $fail('Este Team Líder ya está asignado a otro equipo activo.');
                        }
                    }
                }
            ],
        ]);

        $team = Team::create([
            'name' => $request->name,
            'office_id' => $request->office_id,
            'leader_id' => $request->leader_id,
            'is_active' => true,
        ]);

        // Si se asignó un líder, actualizar su team_id para mantener la coherencia
        if ($request->leader_id) {
            User::where('id', $request->leader_id)->update(['team_id' => $team->id]);
        }

        return redirect()->route('director.teams.index')->with('success', 'Equipo creado exitosamente.');
    }

    public function teamsUpdate(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'office_id' => 'required|exists:offices,id',
            'leader_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($team) {
                    if ($value) {
                        $alreadyLeading = Team::where('leader_id', $value)
                            ->where('is_active', true)
                            ->where('id', '!=', $team->id)
                            ->exists();
                        if ($alreadyLeading) {
                            $fail('Este Team Líder ya está asignado a otro equipo activo.');
                        }
                    }
                }
            ],
            'is_active' => 'required|boolean',
        ]);

        // Desvincular el team anterior del líder anterior si cambió de líder
        if ($team->leader_id && $team->leader_id != $request->leader_id) {
            User::where('id', $team->leader_id)->update(['team_id' => null]);
        }

        $team->update([
            'name' => $request->name,
            'office_id' => $request->office_id,
            'leader_id' => $request->leader_id,
            'is_active' => $request->is_active,
        ]);

        // Si se asignó un líder, actualizar su team_id
        if ($request->leader_id) {
            User::where('id', $request->leader_id)->update(['team_id' => $team->id]);
        }

        return redirect()->route('director.teams.index')->with('success', 'Equipo actualizado exitosamente.');
    }
}
