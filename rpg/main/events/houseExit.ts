import { RpgEvent, EventData, RpgPlayer } from '@rpgjs/server'

@EventData({
    name: 'EV-4'
})
export default class ExitHouseEvent extends RpgEvent {
    onInit() {
        this.setHitbox(32, 32)
    }

    async onAction(player: RpgPlayer) {
        await player.showText('Saliendo');

        // Cambiar al mapa prueba.tmx
        await player.changeMap('aaaaaaa', { x: 265, y: 260 });
    }
}