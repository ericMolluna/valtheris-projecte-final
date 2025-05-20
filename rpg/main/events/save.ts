import { RpgEvent, EventData, RpgPlayer } from '@rpgjs/server'
import axios from 'axios'

@EventData({
    name: 'EV-7'
})
export default class CharaEvent extends RpgEvent {
    onInit() {
        this.setHitbox(32, 32)
    }

    async onAction(player: RpgPlayer) {
        const choice = await player.showChoices('Do you want to save your progress?', [
            { text: 'Yes', value: true },
            { text: 'No', value: false }
        ])

        if (choice.value) {
            const json = player.save() // Obtiene los datos del jugador en JSON
            try {
                await axios.post('http://localhost:8000/api/save', { // URL de Laravel
                    data: json,
                    playerId: player.id // Usa el ID del jugador dinámico
                })
                player.showNotification('Your progress has been saved')
            } catch (err) {
                console.log(err)
                player.showNotification('Save failed')
            }
        }
    }
}