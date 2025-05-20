import { RpgPlayer, type RpgPlayerHooks, Control, Components } from '@rpgjs/server'
import axios from 'axios'

const player: RpgPlayerHooks = {
    async onConnected(player: RpgPlayer) {
        player.name = 'YourName'
        player.setComponentsTop(Components.text('{name}'))

        // Cargar partida desde el backend de Laravel
        try {
            const response = await axios.get(`http://127.0.0.1:8000/api/save/${player.id}`)
            const savedData = response.data
            player.load(savedData) // Carga los datos del jugador desde el JSON devuelto
            player.showNotification('Progress loaded successfully')
            console.log('Cargando la partida guardada para', player.name)
        } catch (err) {
            console.log('No hay partida guardada o error al cargar:', err.message)
            player.showNotification('No previous save found')
        }
    },

    onInput(player: RpgPlayer, { input }) {
        if (input == Control.Back) {
            player.callMainMenu()
        }
    },

    async onDisconnected(player: RpgPlayer) {
        // Guardar partida en el backend de Laravel
        const json = player.save() // Obtiene los datos del jugador en formato JSON
        try {
            const response = await axios.post('http://127.0.0.1:8000/api/save', {
                playerId: player.id,
                data: json
            })
            console.log('Partida guardada en el servidor para', player.name)
            player.showNotification('Your progress has been saved') // Nota: Esto no se verá porque el jugador ya está desconectado
        } catch (err) {
            console.log('Error al guardar la partida:', err.message)
        }
    },
}

export default player