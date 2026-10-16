@php
    $p1Ghost = ($match->player1_id == 0 || is_null($match->player1_id)) && $match->player1_score == 99;
    $p2Ghost = ($match->player2_id == 0 || is_null($match->player2_id)) && $match->player2_score == 99;

    // AQUI ES DONDE SE PONDRÁ EL "Ganador del Match X" EN EL FUTURO
    $p1EmptyText = $p1Ghost ? 'BYE (DESCANSO)' : 'ESPERANDO RIVAL';
    $p2EmptyText = $p2Ghost ? 'BYE (DESCANSO)' : 'ESPERANDO RIVAL';

    $p1 = ($match->player1_id && $match->player1_id != 0) ? mb_strtoupper($match->player1->name) : $p1EmptyText;
    $p2 = ($match->player2_id && $match->player2_id != 0) ? mb_strtoupper($match->player2->name) : $p2EmptyText;
    
    $isBye = ($match->status === 'completed' && ($p1Ghost || $p2Ghost) && !($p1Ghost && $p2Ghost));
    $isDone = $match->status === 'completed';
    
    // Semáforo En Curso (Solo si tiene Juez)
    $isLive = !is_null($match->locked_by) && !$isDone;
    
    $w1 = $match->winner_id === $match->player1_id && !$p1Ghost && $match->winner_id != 0;
    $w2 = $match->winner_id === $match->player2_id && !$p2Ghost && $match->winner_id != 0;
    
    $s1 = $p1Ghost ? 0 : ($match->player1_score ?? 0);
    $s2 = $p2Ghost ? 0 : ($match->player2_score ?? 0);

    $themeColor = isset($theme) ? $theme : 'gray';
    $borderLiveClass = $isLive ? "border-{$themeColor}-500 shadow-[0_0_15px_rgba(255,255,255,0.15)]" : "border-gray-700";
    
    $bgWinnerClass = "bg-{$themeColor}-900/30";
    $textWinnerClass = "text-{$themeColor}-400";
@endphp

<div class="bg-gray-800 {{ $borderLiveClass }} rounded-lg overflow-hidden flex flex-col shadow-lg border relative transition-all {{ $isBye ? 'opacity-40' : '' }}">
    
    <!-- Etiqueta superior -->
    <div class="bg-gray-900 border-b border-gray-700/80 px-2 py-1 flex justify-between items-center z-10">
        <span class="text-[8px] font-bold text-gray-500 tracking-widest">MATCH #{{ $match->match_number ?? '-' }}</span>
        
        @if($isLive)
            <span class="text-[8px] font-bold text-yellow-500 animate-pulse tracking-widest"><i class="fas fa-bolt"></i> EN CURSO</span>
        @elseif($isDone && !$isBye)
            <span class="text-[8px] font-bold text-gray-500 tracking-widest"><i class="fas fa-check"></i> FIN</span>
        @elseif($isBye)
            <span class="text-[8px] font-bold text-gray-500 tracking-widest">BYE</span>
        @endif
    </div>
    
    <!-- Jugador 1 -->
    <div class="flex justify-between items-center p-2.5 border-b border-gray-700/50 transition-colors {{ $w1 ? $bgWinnerClass : ($isDone && !$w1 && !$isBye ? 'opacity-50' : '') }}">
        <span class="text-[11px] font-bold truncate pr-2 {{ $w1 ? 'text-white' : 'text-gray-400' }}">
            {{ $p1 }}
            @if($w1) <i class="fas fa-caret-left text-[10px] text-gray-500 ml-1"></i> @endif
        </span>
        <span class="font-mono text-sm font-black {{ $w1 ? $textWinnerClass : 'text-gray-500' }}">
            {{ $s1 }}
        </span>
    </div>
    
    <!-- Jugador 2 -->
    <div class="flex justify-between items-center p-2.5 transition-colors {{ $w2 ? $bgWinnerClass : ($isDone && !$w2 && !$isBye ? 'opacity-50' : '') }}">
        <span class="text-[11px] font-bold truncate pr-2 {{ $w2 ? 'text-white' : 'text-gray-400' }}">
            {{ $p2 }}
            @if($w2) <i class="fas fa-caret-left text-[10px] text-gray-500 ml-1"></i> @endif
        </span>
        <span class="font-mono text-sm font-black {{ $w2 ? $textWinnerClass : 'text-gray-500' }}">
            {{ $s2 }}
        </span>
    </div>

    <!-- Barra inferior decorativa -->
    @if(!$isBye)
        <div class="h-1 w-full absolute bottom-0 left-0 bg-gradient-to-r {{ $themeColor === 'fuchsia' ? 'from-fuchsia-600 to-gray-800' : ($themeColor === 'orange' ? 'from-orange-600 to-gray-800' : 'from-yellow-500 to-gray-800') }} opacity-30"></div>
    @endif
</div>