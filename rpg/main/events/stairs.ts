import { RpgEvent, EventData, RpgPlayer } from '@rpgjs/server'

@EventData({
    name: 'EV-1'
})
export default class StairsEvent extends RpgEvent {
    onInit() {
        this.setHitbox(32, 32)
    }

    async onAction(player: RpgPlayer) {
        await player.showText('Subiendo al segundo piso...');

        // Cambiar al mapa pis2.tmx
        await player.changeMap('pis2', { x: 305, y: 275});

        // Guardar el piso actual del jugador
        player.setVariable('currentFloor', 2);
    }
}