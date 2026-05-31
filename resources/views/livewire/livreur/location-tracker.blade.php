<?php

use Livewire\Volt\Component;

new class extends Component {
    public bool $isTracking = false;
    public ?string $error = null;

    public function updateLocation($lat, $lng)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user && $user->isLivreur()) {
            $user->update([
                'current_lat' => $lat,
                'current_lng' => $lng,
            ]);
            $this->isTracking = true;
            $this->error = null;
        }
    }

    public function setError($message)
    {
        $this->isTracking = false;
        $this->error = $message;
    }
}; ?>

<div class="flex items-center">
    @if($isTracking)
        <flux:badge size="sm" color="success" icon="map-pin">GPS Actif</flux:badge>
    @elseif($error)
        <flux:badge size="sm" color="danger" icon="exclamation-triangle">GPS Inactif</flux:badge>
    @else
        <flux:badge size="sm" color="zinc" icon="map-pin">Recherche GPS...</flux:badge>
    @endif

    @script
    <script>
        document.addEventListener('livewire:initialized', () => {
            if ("geolocation" in navigator) {
                const watchId = navigator.geolocation.watchPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        // Envoi de la position au composant Livewire
                        $wire.updateLocation(lat, lng);
                    },
                    (error) => {
                        let errorMessage = "Erreur GPS";
                        if (error.code === error.PERMISSION_DENIED) {
                            errorMessage = "Accès refusé";
                        }
                        $wire.setError(errorMessage);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
                
                // Nettoyage optionnel si le composant est détruit
                document.addEventListener('livewire:navigating', () => {
                    navigator.geolocation.clearWatch(watchId);
                });
            } else {
                $wire.setError("Non supporté");
            }
        });
    </script>
    @endscript
</div>
