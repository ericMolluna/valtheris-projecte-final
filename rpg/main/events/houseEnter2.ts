import { RpgEvent, EventData, RpgPlayer } from '@rpgjs/server'

@EventData({
    name: 'EV-6'
})
export default class ExitHouseEvent extends RpgEvent {
    onInit() {
        this.setHitbox(32, 64)
    }

    async onAction(player: RpgPlayer) {
        await player.showText('Entrando');

        // Cambiar al mapa prueba.tmx
        await player.changeMap('casa2', { x: 175, y: 305 });
    }
}