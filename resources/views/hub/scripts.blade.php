<!-- El Toast lleva style="pointer-events: none !important;" para que JAMÁS bloquee la botonera superior -->
<div id="toast" style="pointer-events: none !important;" class="fixed top-16 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-4 py-3 rounded-lg shadow-2xl z-[120] font-bold border-l-4 transition-opacity duration-300 opacity-0 pointer-events-none text-sm w-11/12 max-w-sm text-center">Mensaje</div>

@php
    $hubPaypalService = app(\App\Services\Payment\PayPalService::class);
    $hubPaypalClientId = $hubPaypalService->getClientId();
    $hubPaypalCurrency = $hubPaypalService->getCurrency();
@endphp
<script>
    window.SBBL_CONFIG = Object.assign(window.SBBL_CONFIG || {}, {
        csrfToken: @json(csrf_token()),
        userId: @json(Auth::id()),
        userRoles: @json(Auth::check() ? Auth::user()->roles->pluck('name')->values() : []),
        paypalEnabled: @json(!empty($hubPaypalClientId))
    });
</script>
@if(!empty($hubPaypalClientId))
<script src="https://www.paypal.com/sdk/js?client-id={{ $hubPaypalClientId }}&currency={{ $hubPaypalCurrency }}"></script>
@endif
<script src="{{ asset('js/hub.js') }}?v={{ file_exists(public_path('js/hub.js')) ? filemtime(public_path('js/hub.js')) : '1' }}"></script>
