import { RpgEvent, EventData, RpgPlayer } from '@rpgjs/server'

@EventData({
    name: 'EV-2'
})
export default class ReturnStairsEvent extends RpgEvent {
    onInit() {
        this.setHitbox(64, 64)
    }

    async onAction(player: RpgPlayer) {
        await player.showText('Bajando al primer piso...');

        // Cambiar al mapa casa.tmx
        await player.changeMap('casa', { x: 270, y: 290 });

        // Guardar el piso actual del jugador
        player.setVariable('currentFloor', 1);
    }
}