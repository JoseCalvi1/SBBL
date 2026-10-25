@php
    $isPlaceholder = isset($match->is_placeholder) && $match->is_placeholder;
    $isTeam = !is_null($match->team1_id ?? null) && !is_null($match->team2_id ?? null);
    $p1Id = $isTeam ? ($match->team1_id ?? null) : ($match->player1_id ?? null);
    $p2Id = $isTeam ? ($match->team2_id ?? null) : ($match->player2_id ?? null);

    $p1Ghost = !$isPlaceholder && ($p1Id == 0 || is_null($p1Id)) && ($match->player1_score ?? 0) == 99;
    $p2Ghost = !$isPlaceholder && ($p2Id == 0 || is_null($p2Id)) && ($match->player2_score ?? 0) == 99;

    $p1EmptyText = $p1Ghost ? 'BYE (AVANZA)' : ($isPlaceholder ? 'PENDIENTE CRUCE' : 'ESPERANDO...');
    $p2EmptyText = $p2Ghost ? 'BYE (AVANZA)' : ($isPlaceholder ? 'PENDIENTE CRUCE' : 'ESPERANDO...');

    if ($isTeam) {
        $p1 = ($p1Id && isset($match->team1)) ? mb_strtoupper($match->team1->name) : $p1EmptyText;
        $p2 = ($p2Id && isset($match->team2)) ? mb_strtoupper($match->team2->name) : $p2EmptyText;
    } else {
        $p1 = ($p1Id && isset($match->player1)) ? mb_strtoupper($match->player1->name) : $p1EmptyText;
        $p2 = ($p2Id && isset($match->player2)) ? mb_strtoupper($match->player2->name) : $p2EmptyText;
    }
    
    $isBye = (!$isPlaceholder && $match->status === 'completed' && ($p1Ghost || $p2Ghost || is_null($p2Id)));
    if ($isBye && is_null($p2Id)) $p2 = 'BYE (AVANZA)';

    $isDone = !$isPlaceholder && $match->status === 'completed';
    $isLive = !$isPlaceholder && !is_null($match->locked_by ?? null) && !$isDone;
    
    $w1 = $isDone && $match->winner_id && $match->winner_id == $p1Id && !$p1Ghost;
    $w2 = $isDone && $match->winner_id && $match->winner_id == $p2Id && !$p2Ghost;
    
    $s1 = ($p1Ghost || $isPlaceholder) ? '-' : ($match->player1_score ?? 0);
    $s2 = ($p2Ghost || $isBye || $isPlaceholder) ? '-' : ($match->player2_score ?? 0);

    $themeColor = $theme ?? 'fuchsia';
    $borderClass = $isPlaceholder 
        ? "border-gray-800/80 border-dashed bg-gray-900/40 opacity-60" 
        : ($isLive ? "border-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.4)] bg-gray-900" : ($isDone ? "border-gray-700 bg-gray-900" : "border-{$themeColor}-900/80 bg-gray-900"));
    
    $bgWinner = $themeColor === 'fuchsia' ? 'bg-fuchsia-950/35' : ($themeColor === 'orange' ? 'bg-orange-950/35' : 'bg-yellow-950/35');
    $badgeWinner = $themeColor === 'fuchsia' ? 'bg-fuchsia-500 text-white' : ($themeColor === 'orange' ? 'bg-orange-500 text-white' : 'bg-yellow-500 text-black');
    $canClick = !$isPlaceholder && !$isBye && $p1Id && $p2Id;
@endphp

<div class="arena-match-node {{ $borderClass }} rounded-xl overflow-hidden flex flex-col shadow-xl border-2 relative transition-all {{ $isBye ? 'opacity-45' : '' }} {{ $canClick ? 'cursor-pointer hover:border-cyan-400 active:scale-[0.98]' : '' }}"
     data-match-id="{{ $match->id }}"
     data-status="{{ $match->status }}"
     data-winner="{{ $match->winner_id ?? '' }}"
     data-p1="{{ $p1Id ?? '' }}"
     data-p2="{{ $p2Id ?? '' }}"
     @if($canClick) onclick="accionRapidaDesdeCuadro({{ $match->id }}, '{{ $match->status }}')" @endif>
    
    <!-- Cabecera -->
    <div class="bg-gray-950/90 border-b border-gray-800 px-2.5 py-1 flex justify-between items-center">
        <span class="text-[9px] font-mono font-bold text-gray-500">
            {{ $isPlaceholder ? 'PRÓXIMO CRUCE' : 'PARTIDA #' . ($match->match_number ?? $match->id) }}
        </span>
        @if($isLive)
            <span class="text-[8px] font-bold text-cyan-400 animate-pulse tracking-widest"><i class="fas fa-circle text-[6px] mr-1"></i> EN JUEGO</span>
        @elseif($isDone && !$isBye)
            <span class="text-[8px] font-bold text-green-500 tracking-widest"><i class="fas fa-check"></i> FIN</span>
        @elseif($isBye)
            <span class="text-[8px] font-bold text-gray-600 tracking-widest">BYE</span>
        @else
            <span class="text-[8px] font-bold text-gray-600 tracking-widest">PENDIENTE</span>
        @endif
    </div>
    
    <!-- Jugador 1 -->
    <div class="flex justify-between items-center px-3 py-2 border-b border-gray-800/70 {{ $w1 ? $bgWinner : '' }}">
        <span class="text-[11px] font-bold truncate pr-2 {{ $w1 ? 'text-white' : ($p1Id ? 'text-gray-300' : 'text-gray-600 italic') }}">
            @if($w1)<i class="fas fa-caret-right text-cyan-400 mr-1"></i>@endif{{ $p1 }}
        </span>
        <span class="font-mono text-xs font-black px-1.5 py-0.5 rounded {{ $w1 ? $badgeWinner : 'text-gray-500 bg-gray-950' }}">{{ $s1 }}</span>
    </div>
    
    <!-- Jugador 2 -->
    <div class="flex justify-between items-center px-3 py-2 {{ $w2 ? $bgWinner : '' }}">
        <span class="text-[11px] font-bold truncate pr-2 {{ $w2 ? 'text-white' : ($p2Id ? 'text-gray-300' : 'text-gray-600 italic') }}">
            @if($w2)<i class="fas fa-caret-right text-cyan-400 mr-1"></i>@endif{{ $p2 }}
        </span>
        <span class="font-mono text-xs font-black px-1.5 py-0.5 rounded {{ $w2 ? $badgeWinner : 'text-gray-500 bg-gray-950' }}">{{ $s2 }}</span>
    </div>
</div>