const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  devServer: {
    historyApiFallback: true, // Esto asegura que todas las rutas devuelvan index.html
  },
  transpileDependencies: true
})
