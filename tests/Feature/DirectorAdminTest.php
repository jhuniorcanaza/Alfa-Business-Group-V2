<?php

use App\Models\User;
use App\Models\Team;
use App\Models\Office;

test('a director can create a team leader and assign them to a team, synchronizing leader_id', function () {
    $director = User::factory()->create([
        'role' => 'director',
    ]);

    $office = Office::create([
        'name' => 'Oficina Central',
        'city' => 'Santa Cruz',
        'is_active' => true,
    ]);

    $team = Team::create([
        'name' => 'Equipo Alfa',
        'office_id' => $office->id,
        'leader_id' => null,
        'is_active' => true,
    ]);

    $this->assertNull($team->fresh()->leader_id);

    // Create a new collaborator as team_leader and assign them to the team
    $response = $this->actingAs($director)->post(route('director.users.store'), [
        'name' => 'Lider Test',
        'email' => 'lider@example.com',
        'password' => 'password123',
        'role' => 'team_leader',
        'phone' => '+59177000000',
        'office_id' => $office->id,
        'team_id' => $team->id,
    ]);

    $response->assertRedirect(route('director.users.index'));

    $lider = User::where('email', 'lider@example.com')->first();
    $this->assertNotNull($lider);
    $this->assertEquals($team->id, $lider->team_id);

    // Verify the team's leader_id is now updated
    $this->assertEquals($lider->id, $team->fresh()->leader_id);
});

test('a director can update a team leader\'s team and synchronize leader_id', function () {
    $director = User::factory()->create([
        'role' => 'director',
    ]);

    $office = Office::create([
        'name' => 'Oficina Central',
        'city' => 'Santa Cruz',
        'is_active' => true,
    ]);

    $team1 = Team::create([
        'name' => 'Equipo 1',
        'office_id' => $office->id,
        'leader_id' => null,
        'is_active' => true,
    ]);

    $team2 = Team::create([
        'name' => 'Equipo 2',
        'office_id' => $office->id,
        'leader_id' => null,
        'is_active' => true,
    ]);

    $lider = User::factory()->create([
        'role' => 'team_leader',
        'office_id' => $office->id,
        'team_id' => $team1->id,
    ]);
    
    // Set initially as leader of team1
    $team1->update(['leader_id' => $lider->id]);

    $this->assertEquals($lider->id, $team1->fresh()->leader_id);
    $this->assertNull($team2->fresh()->leader_id);

    // Update the collaborator to be in team2
    $response = $this->actingAs($director)->put(route('director.users.update', $lider->id), [
        'name' => $lider->name,
        'email' => $lider->email,
        'role' => 'team_leader',
        'phone' => $lider->phone,
        'office_id' => $office->id,
        'team_id' => $team2->id,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('director.users.index'));

    // Verify old team leader is null
    $this->assertNull($team1->fresh()->leader_id);

    // Verify new team leader is updated
    $this->assertEquals($lider->id, $team2->fresh()->leader_id);
    $this->assertEquals($team2->id, $lider->fresh()->team_id);
});

test('deactivating a team leader removes them as team leader', function () {
    $director = User::factory()->create([
        'role' => 'director',
    ]);

    $office = Office::create([
        'name' => 'Oficina Central',
        'city' => 'Santa Cruz',
        'is_active' => true,
    ]);

    $team = Team::create([
        'name' => 'Equipo Alfa',
        'office_id' => $office->id,
        'leader_id' => null,
        'is_active' => true,
    ]);

    $lider = User::factory()->create([
        'role' => 'team_leader',
        'office_id' => $office->id,
        'team_id' => $team->id,
    ]);

    $team->update(['leader_id' => $lider->id]);

    // Deactivate the user
    $response = $this->actingAs($director)->put(route('director.users.update', $lider->id), [
        'name' => $lider->name,
        'email' => $lider->email,
        'role' => 'team_leader',
        'phone' => $lider->phone,
        'office_id' => $office->id,
        'team_id' => $team->id,
        'is_active' => 0, // Inactive
    ]);

    $response->assertRedirect(route('director.users.index'));

    // Verify user is no longer active and has no team
    $this->assertFalse($lider->fresh()->is_active);
    $this->assertNull($lider->fresh()->team_id);

    // Verify team has no leader
    $this->assertNull($team->fresh()->leader_id);
});

test('deactivating a team removes all members and leader from the team', function () {
    $director = User::factory()->create([
        'role' => 'director',
    ]);

    $office = Office::create([
        'name' => 'Oficina Central',
        'city' => 'Santa Cruz',
        'is_active' => true,
    ]);

    $team = Team::create([
        'name' => 'Equipo Alfa',
        'office_id' => $office->id,
        'leader_id' => null,
        'is_active' => true,
    ]);

    $lider = User::factory()->create([
        'role' => 'team_leader',
        'office_id' => $office->id,
        'team_id' => $team->id,
    ]);
    $team->update(['leader_id' => $lider->id]);

    $asesor = User::factory()->create([
        'role' => 'asesor',
        'office_id' => $office->id,
        'team_id' => $team->id,
    ]);

    $this->assertEquals($lider->id, $team->fresh()->leader_id);
    $this->assertEquals($team->id, $lider->fresh()->team_id);
    $this->assertEquals($team->id, $asesor->fresh()->team_id);

    // Deactivate the team
    $response = $this->actingAs($director)->put(route('director.teams.update', $team->id), [
        'name' => $team->name,
        'office_id' => $office->id,
        'leader_id' => $lider->id,
        'is_active' => 0, // Inactive
    ]);

    $response->assertRedirect(route('director.teams.index'));

    // Verify team is inactive and has no leader
    $this->assertFalse($team->fresh()->is_active);
    $this->assertNull($team->fresh()->leader_id);

    // Verify members are freed from team
    $this->assertNull($lider->fresh()->team_id);
    $this->assertNull($asesor->fresh()->team_id);
});

