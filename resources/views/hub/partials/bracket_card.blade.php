@php
    $theme = $theme ?? ($match->bracket_type === 'losers' ? 'orange' : ($match->bracket_type === 'winners' ? 'fuchsia' : 'yellow'));
    $isPlaceholder = isset($match->is_placeholder) && $match->is_placeholder;
    $isTeam = !is_null($match->team1_id ?? null) || !is_null($match->team2_id ?? null);
    
    $p1Id = $isTeam ? ($match->team1_id ?? null) : ($match->player1_id ?? null);
    $p2Id = $isTeam ? ($match->team2_id ?? null) : ($match->player2_id ?? null);
    
    $p1Ghost = is_null($p1Id) && ($match->player1_score ?? 0) == 99;
    $p2Ghost = is_null($p2Id) && ($match->player2_score ?? 0) == 99;

    // MAGIA: Mostramos "GANADOR #4" o "PERDEDOR #6" en lugar de un texto genérico
    $p1Lbl = $match->p1_placeholder ?? 'ESPERANDO...';
    $p2Lbl = $match->p2_placeholder ?? 'ESPERANDO...';
    $p1Name = $isTeam ? ($match->team1->name ?? $p1Lbl) : ($match->player1->name ?? $p1Lbl);
    $p2Name = $isTeam ? ($match->team2->name ?? $p2Lbl) : ($match->player2->name ?? $p2Lbl);
    
    $p1 = mb_strtoupper($p1Name);
    $p2 = mb_strtoupper($p2Name);

    $w1 = (!$isPlaceholder && $match->status === 'completed' && $match->winner_id && $match->winner_id == $p1Id);
    $w2 = (!$isPlaceholder && $match->status === 'completed' && $match->winner_id && $match->winner_id == $p2Id);
    
    $bothEmpty = is_null($p1Id) && is_null($p2Id);
    $canClick = !is_null($p1Id) && !is_null($p2Id) && !$isPlaceholder;
    
    $isLive = ($match->status === 'playing' || !empty($match->locked_by)) && $match->status !== 'completed';
    $isDone = ($match->status === 'completed');
    $isBye = $isDone && ($p1Ghost || $p2Ghost || is_null($p1Id) || is_null($p2Id));

    $s1 = ($isPlaceholder || $bothEmpty) ? '-' : (($match->player1_score ?? 0) == 99 ? '-' : ($match->player1_score ?? 0));
    $s2 = ($isPlaceholder || $bothEmpty) ? '-' : (($match->player2_score ?? 0) == 99 ? '-' : ($match->player2_score ?? 0));

    if ($theme === 'orange') {
        $borderIdle = 'border-orange-600/80 hover:border-cyan-400';
        $bgWinner = 'bg-orange-950/30';
        $badgeWinner = 'bg-orange-500 text-white shadow-[0_0_8px_rgba(249,115,22,0.5)]';
        $caretColor = 'text-orange-400';
        $placeholderColor = 'text-orange-300/60 italic text-[10px]';
    } elseif ($theme === 'yellow') {
        $borderIdle = 'border-yellow-500/80 hover:border-cyan-400';
        $bgWinner = 'bg-yellow-950/30';
        $badgeWinner = 'bg-yellow-500 text-black shadow-[0_0_8px_rgba(234,179,8,0.6)]';
        $caretColor = 'text-yellow-400';
        $placeholderColor = 'text-yellow-200/60 italic text-[10px]';
    } else {
        $borderIdle = 'border-fuchsia-600/80 hover:border-cyan-400';
        $bgWinner = 'bg-fuchsia-950/30';
        $badgeWinner = 'bg-fuchsia-500 text-white shadow-[0_0_8px_rgba(217,70,239,0.5)]';
        $caretColor = 'text-fuchsia-400';
        $placeholderColor = 'text-fuchsia-300/60 italic text-[10px]';
    }

    $cardStyle = ($bothEmpty || $isPlaceholder)
        ? 'border-gray-800 border-dashed bg-gray-900/50 opacity-75' 
        : ($isLive 
            ? 'border-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.5)] bg-gray-900/95 cursor-pointer' 
            : ($isDone 
                ? 'border-gray-700 bg-gray-900/95 ' . ($canClick ? 'cursor-pointer hover:border-cyan-500/60' : '') 
                : $borderIdle . ' bg-gray-900/95 ' . ($canClick ? 'cursor-pointer' : '')));
@endphp

<div class="arena-match-node match-node relative border-2 {{ $cardStyle }} rounded-xl overflow-hidden shadow-lg transition-all"
     id="arena-match-{{ $match->id }}"
     data-match-id="{{ $match->id }}"
     data-next-win="{{ $match->next_win_match_id ?? '' }}"
     data-next-lose="{{ $match->next_lose_match_id ?? '' }}"
     data-bracket="{{ $match->bracket_type }}"
     data-round="{{ $roundNumber ?? $match->round_number }}"
     data-index="{{ $index ?? 0 }}"
     data-status="{{ $match->status }}"
     data-winner="{{ $match->winner_id ?? '' }}"
     data-p1="{{ $p1Ghost ? '999999' : ($p1Id ?? '') }}"
     data-p2="{{ $p2Ghost ? '999999' : ($p2Id ?? '') }}"
     @if($canClick) onclick="accionRapidaDesdeCuadro({{ $match->id }}, '{{ $match->status }}')" @endif>
    
    <!-- Cabecera -->
    <div class="bg-gray-950/90 border-b border-gray-800 px-2.5 py-1 flex justify-between items-center">
        <span class="text-[9px] font-mono font-bold text-gray-500">
            {{ $isPlaceholder ? 'PRÓXIMO CRUCE' : 'PARTIDA #' . ($match->match_number ?? $match->id) }}
        </span>
        @if($isLive)
            <span class="text-[8px] font-bold text-cyan-400 animate-pulse tracking-widest"><i class="fas fa-circle text-[6px] mr-1"></i> EN JUEGO</span>
        @elseif($isDone && !$isBye)
            <span class="text-[8px] font-bold text-green-400 tracking-widest"><i class="fas fa-check"></i> FIN</span>
        @elseif($isBye)
            <span class="text-[8px] font-bold text-gray-600 tracking-widest">BYE</span>
        @else
            <span class="text-[8px] font-bold text-gray-500 tracking-widest">PENDIENTE</span>
        @endif
    </div>
    
    <!-- Jugador 1 -->
    <div class="flex justify-between items-center px-2.5 py-1.5 border-b border-gray-800/80 {{ $w1 ? $bgWinner : '' }}">
        <span class="text-[11px] font-bold truncate pr-2 {{ $w1 ? 'text-white' : ($p1Id ? 'text-gray-300' : $placeholderColor) }}">
            @if($w1)<i class="fas fa-caret-right {{ $caretColor }} mr-1"></i>@endif{{ $p1 }}
        </span>
        <span class="font-mono text-xs font-black px-1.5 py-0.5 rounded {{ $w1 ? $badgeWinner : 'text-gray-500 bg-gray-950' }}">{{ $s1 }}</span>
    </div>
    
    <!-- Jugador 2 -->
    <div class="flex justify-between items-center px-2.5 py-1.5 {{ $w2 ? $bgWinner : '' }}">
        <span class="text-[11px] font-bold truncate pr-2 {{ $w2 ? 'text-white' : ($p2Id ? 'text-gray-300' : $placeholderColor) }}">
            @if($w2)<i class="fas fa-caret-right {{ $caretColor }} mr-1"></i>@endif{{ $p2 }}
        </span>
        <span class="font-mono text-xs font-black px-1.5 py-0.5 rounded {{ $w2 ? $badgeWinner : 'text-gray-500 bg-gray-950' }}">{{ $s2 }}</span>
    </div>
</div>