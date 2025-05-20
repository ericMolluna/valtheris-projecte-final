import { RpgEvent, EventData, RpgPlayer } from '@rpgjs/server'

@EventData({
    name: 'EV-8'
})
export default class ExitHouseEvent extends RpgEvent {
    onInit() {
        this.setHitbox(64, 64)
    }

    async onAction(player: RpgPlayer) {
        await player.showText('Entrando');

        // Cambiar al mapa prueba.tmx
        await player.changeMap('dungeon', { x: 40, y: 48 });
    }
}