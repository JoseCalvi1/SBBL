@php
    // Detectamos si es Fantasma (ID null o 0 + Score 99)
    $p1Ghost = ($match->player1_id == 0 || is_null($match->player1_id)) && $match->player1_score == 99;
    $p2Ghost = ($match->player2_id == 0 || is_null($match->player2_id)) && $match->player2_score == 99;

    $p1Name = $match->player1 ? mb_strtoupper($match->player1->name) : '---';
    $p2Name = $match->player2 ? mb_strtoupper($match->player2->name) : '---';

    $p1 = ($match->player1_id && $match->player1_id != 0) ? $p1Name : ($p1Ghost ? 'BYE (DESCANSO)' : 'ESPERANDO RIVAL...');
    $p2 = ($match->player2_id && $match->player2_id != 0) ? $p2Name : ($p2Ghost ? 'BYE (DESCANSO)' : 'ESPERANDO RIVAL...');
    
    $isBye = ($match->status === 'completed' && ($p1Ghost || $p2Ghost) && !($p1Ghost && $p2Ghost));
    
    $w1 = $match->winner_id === $match->player1_id && !$p1Ghost && $match->winner_id != 0;
    $w2 = $match->winner_id === $match->player2_id && !$p2Ghost && $match->winner_id != 0;
    
    $s1 = $p1Ghost ? 0 : ($match->player1_score ?? 0);
    $s2 = $p2Ghost ? 0 : ($match->player2_score ?? 0);
@endphp

<div style="background: #111827; border: 1px solid {{ $isBye ? '#374151' : '#1f2937' }}; border-radius: 8px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5); {{ $isBye ? 'opacity: 0.5;' : '' }}">
    
    <!-- Jugador 1 -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-bottom: 1px solid #1f2937; font-size: 13px; font-family: sans-serif; font-weight: 700; background: {{ $w1 ? '#1f2937' : 'transparent' }}; color: {{ $w1 ? '#ffffff' : '#9ca3af' }};">
        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 170px;">{{ $p1 }}</span>
        <span style="font-family: monospace; font-size: 18px; font-weight: 900; color: {{ $w1 ? '#facc15' : '#4b5563' }};">{{ $s1 }}</span>
    </div>
    
    <!-- Jugador 2 -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; font-size: 13px; font-family: sans-serif; font-weight: 700; background: {{ $w2 ? '#1f2937' : 'transparent' }}; color: {{ $w2 ? '#ffffff' : '#9ca3af' }};">
        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 170px;">{{ $p2 }}</span>
        <span style="font-family: monospace; font-size: 18px; font-weight: 900; color: {{ $w2 ? '#facc15' : '#4b5563' }};">{{ $s2 }}</span>
    </div>
</div>