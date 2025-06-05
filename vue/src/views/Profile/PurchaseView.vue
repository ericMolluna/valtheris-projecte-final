<template>
  <div class="purchase-container">
    <NavBar />

    <div class="purchase-content">
      <h1 class="purchase-title">Finalizar Suscripción - {{ tierName }}</h1>
      <div class="pricing-options">
        <div class="pricing-tab" :class="{ 'active': paymentOption === 'monthly' }" @click="paymentOption = 'monthly'">
          Mensual
        </div>
        <div class="pricing-tab" :class="{ 'active': paymentOption === 'yearly' }" @click="paymentOption = 'yearly'">
          Anual (Ahorra 20%)
        </div>
      </div>
      <p class="pricing-detail">
        Precio: 
        <span v-if="tierName === 'Básico' && paymentOption === 'monthly'">Gratis</span>
        <span v-if="tierName === 'Básico' && paymentOption === 'yearly'">Gratis</span>
        <span v-if="tierName === 'Avanzado' && paymentOption === 'monthly'">$5/mes</span>
        <span v-if="tierName === 'Avanzado' && paymentOption === 'yearly'">$48/año (equivalente a $4/mes)</span>
        <span v-if="tierName === 'Premium' && paymentOption === 'monthly'">$10/mes</span>
        <span v-if="tierName === 'Premium' && paymentOption === 'yearly'">$96/año (equivalente a $8/mes)</span>
      </p>

      <div class="payment-form">
        <h2 class="form-title">Datos de Pago (Simulado)</h2>
        <p class="note"><strong>Nota:</strong> Esto es una simulación. No se realizará ningún cargo real.</p>
        <div class="form-group">
          <label for="card-number">Número de Tarjeta</label>
          <input type="text" id="card-number" v-model="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" @input="formatCardNumber" required />
        </div>
        <div class="form-group">
          <label for="card-holder">Nombre en la Tarjeta</label>
          <input type="text" id="card-holder" v-model="cardHolder" placeholder="Nombre Apellido" required />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="expiry-date">Fecha de Expiración</label>
            <input type="text" id="expiry-date" v-model="expiryDate" placeholder="MM/AA" maxlength="5" @input="formatExpiryDate" required />
          </div>
          <div class="form-group">
            <label for="cvv">CVV</label>
            <input type="text" id="cvv" v-model="cvv" placeholder="123" maxlength="3" required />
          </div>
        </div>
        <div class="form-group">
          <label for="email">Correo Electrónico para Verificación</label>
          <input type="email" id="email" v-model="email" placeholder="tuemail@ejemplo.com" required />
        </div>
        <div class="button-group">
          <button class="back-btn" @click="goBack">Volver</button>
          <button class="submit-btn" @click="submitPayment" :disabled="isSubmitting">
            {{ isSubmitting ? 'Procesando...' : 'Confirmar Suscripción' }}
          </button>
        </div>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
        <p v-if="successMessage" class="success-message">
          {{ successMessage }}
          <a href="#" @click.prevent="checkVerification">Verificar Ahora</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue';
import axios from 'axios';

export default {
  name: 'PurchaseView',
  components: {
    NavBar,
  },
  data() {
    return {
      tier: '',
      tierName: '',
      paymentOption: 'monthly',
      cardNumber: '',
      cardHolder: '',
      expiryDate: '',
      cvv: '',
      email: '',
      isSubmitting: false,
      errorMessage: '',
      successMessage: '',
      verificationToken: null,
    };
  },
  created() {
    this.tier = this.$route.query.tier || 'Tier 1';
    this.tierName = this.tier === 'Tier 1' ? 'Básico' : this.tier === 'Tier 2' ? 'Avanzado' : 'Premium';
    this.email = this.$root.user?.email || '';
  },
  methods: {
    formatCardNumber() {
      let value = this.cardNumber.replace(/\D/g, '');
      value = value.replace(/(\d{4})/g, '$1 ').trim();
      this.cardNumber = value;
    },
    formatExpiryDate() {
      let value = this.expiryDate.replace(/\D/g, '');
      if (value.length >= 3) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
      }
      this.expiryDate = value;
    },
    goBack() {
      this.$router.push('/perfil');
    },
    async submitPayment() {
  this.errorMessage = '';
  this.successMessage = '';
  this.isSubmitting = true;

  try {
    axios.defaults.baseURL = 'http://localhost:8000';
    axios.defaults.withCredentials = true;
    await axios.get('sanctum/csrf-cookie');
    const token = localStorage.getItem('auth_token');
    if (!token) {
      throw new Error('No hay token de autenticación');
    }

    const response = await axios.post('/api/send-verification', {
      email: this.email,
      tier: this.tier,
      paymentOption: this.paymentOption,
    }, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    this.verificationToken = response.data.token;
    this.successMessage = `Suscripción procesada con éxito. Se ha enviado un correo de verificación a ${this.email}.`;
  } catch (error) {
    console.error('Error en submitPayment:', error);
    this.errorMessage = `Error al enviar el correo de verificación: ${error.response?.data?.message || error.message}`;
  } finally {
    this.isSubmitting = false;
  }
},
    async checkVerification() {
      if (!this.verificationToken) {
        this.errorMessage = 'No se ha generado un token de verificación. Por favor, confirma la suscripción primero.';
        return;
      }

      try {
        const token = localStorage.getItem('auth_token');
        const response = await axios.get(`/api/verify-email/${this.verificationToken}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        if (response.data.success) {
          this.successMessage = `Correo verificado con éxito. ¡Tu suscripción a ${this.tierName} ha sido activada!`;
          this.$root.user.tier = this.tier;
          setTimeout(() => {
            this.$router.push('/perfil');
          }, 2000);
        } else {
          this.errorMessage = 'La verificación falló. Revisa tu correo o intenta de nuevo.';
        }
      } catch (error) {
        this.errorMessage = 'Error al verificar el correo. Intenta de nuevo.';
        console.error('Error en checkVerification:', error);
      }
    },
  },
};
</script>

<style scoped>
.purchase-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
  display: flex;
  flex-direction: column;
  width: 100%;
  color: #e0e0e0;
  font-family: 'Arial', sans-serif;
}

.purchase-content {
  max-width: 700px;
  margin: 40px auto;
  padding: 30px;
  background: rgba(20, 20, 20, 0.9);
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 255, 204, 0.2);
  position: relative;
  z-index: 1;
}

.purchase-title {
  color: #00ffcc;
  font-size: 2.2em;
  font-weight: 700;
  text-align: center;
  margin-bottom: 20px;
  text-transform: uppercase;
}

.pricing-options {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-bottom: 20px;
}

.pricing-tab {
  padding: 12px 25px;
  background: #333;
  color: #fff;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s, transform 0.2s;
}

.pricing-tab.active {
  background: #00ffcc;
  color: #1a1a1a;
  transform: scale(1.05);
}

.pricing-tab:hover {
  background: #00cc99;
  color: #fff;
}

.pricing-detail {
  text-align: center;
  font-size: 1.3em;
  color: #00ffcc;
  margin-bottom: 20px;
  font-weight: 500;
}

.payment-form {
  background: rgba(30, 30, 30, 0.95);
  padding: 25px;
  border-radius: 10px;
  border: 1px solid #00ffcc;
}

.form-title {
  color: #ff0066;
  font-size: 1.8em;
  font-weight: 700;
  margin-bottom: 15px;
}

.note {
  color: #ff9999;
  font-size: 0.9em;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  color: #00ffcc;
  font-size: 1.1em;
  margin-bottom: 5px;
}

.form-group input {
  width: 100%;
  padding: 12px;
  background: #222;
  border: 2px solid #00ffcc;
  border-radius: 6px;
  color: #e0e0e0;
  font-size: 1em;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.form-group input:focus {
  border-color: #ff0066;
  box-shadow: 0 0 5px rgba(255, 0, 102, 0.5);
  outline: none;
}

.form-row {
  display: flex;
  gap: 15px;
}

.form-row .form-group {
  flex: 1;
}

.button-group {
  display: flex;
  gap: 15px;
  margin-top: 20px;
}

.back-btn {
  flex: 1;
  padding: 12px;
  background: #666;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1.1em;
  font-weight: 500;
  transition: background 0.3s, transform 0.2s;
}

.back-btn:hover {
  background: #999;
  transform: translateY(-2px);
}

.submit-btn {
  flex: 2;
  background: linear-gradient(90deg, #ff0066, #00ffcc);
  color: #fff;
  padding: 12px 30px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1.2em;
  font-weight: 600;
  transition: transform 0.3s, background 0.3s;
}

.submit-btn:hover {
  transform: translateY(-2px);
  background: linear-gradient(90deg, #00ffcc, #ff0066);
}

.submit-btn:disabled {
  background: #666;
  cursor: not-allowed;
  transform: none;
}

.error-message {
  color: #ff5555;
  font-size: 1em;
  text-align: center;
  margin-top: 15px;
}

.success-message {
  color: #00ff99;
  font-size: 1em;
  text-align: center;
  margin-top: 15px;
}

.success-message a {
  color: #ff0066;
  text-decoration: underline;
  margin-left: 5px;
  transition: color 0.3s;
}

.success-message a:hover {
  color: #ff9999;
}

@media (max-width: 600px) {
  .purchase-content {
    margin: 20px;
    padding: 20px;
  }

  .form-row {
    flex-direction: column;
    gap: 10px;
  }

  .pricing-tab {
    padding: 8px 15px;
    font-size: 0.9em;
  }

  .button-group {
    flex-direction: column;
  }

  .back-btn, .submit-btn {
    width: 100%;
  }
}
</style>