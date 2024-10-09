<template>
    <div class="chat-container">
        <div class="input-container">
        <textarea v-model="newMessage" placeholder="Escribe un mensaje..." class="message-input"></textarea>
        <button @click="sendMessage" class="send-button">Enviar</button>
      </div>
      <div v-if="messages.length" class="messages-container">
        <div v-for="message in messages" :key="message.id" class="message">
          <strong :style="{ color: getRandomColor(message.user.name) }">
            {{ message.user?.name || 'Usuario desconocido' }}:
          </strong>
          {{ message.message }}
        </div>
      </div>


    </div>
  </template>

  <script>
  import axios from 'axios';

  export default {
    props: ['articleId'],
    data() {
      return {
        messages: [],
        newMessage: '',
        //articleId: 1, // Cambia esto según el anuncio actual
        pollInterval: null,
      };
    },
    mounted() {
      this.fetchMessages();
      this.pollInterval = setInterval(this.fetchMessages, 5000);
    },
    methods: {
      fetchMessages() {
        axios.get(`/chat/messages/${this.articleId}`).then((response) => {
          this.messages = response.data.map((message) => ({
            ...message,
            user: message.user || { name: 'Usuario desconocido' },
          }));
        });
      },
      sendMessage() {
        if (this.newMessage.trim() === '') return;
        axios
          .post('/chat/messages', {
            message: this.newMessage,
            article_id: this.articleId,
          })
          .then((response) => {
            this.messages.push(response.data);
            this.newMessage = '';
          });
      },
      getRandomColor(name) {
        // Generar un color basado en el hash del nombre del usuario
        const hash = [...name].reduce((acc, char) => acc + char.charCodeAt(0), 0);
        const hue = hash % 360; // Usar el hash para determinar el tono (0-360 grados)
        return `hsl(${hue}, 70%, 50%)`; // Color HSL con saturación 70% y brillo 50%
      },
    },
    beforeDestroy() {
      clearInterval(this.pollInterval);
    },
  };
  </script>

  <style scoped>
  .chat-container {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100vh;
    /*max-width: 600px;*/
    margin: 0 auto;
    background-color: #2c2c2c;
    border: 1px solid #555;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    color: #ffffff;
  }

  .messages-container {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background-color: #1e1e1e;
  }

  .message {
    margin-bottom: 10px;
    padding: 10px;
    background-color: #333;
    border-radius: 8px;
    color: #f1f1f1;
  }

  .input-container {
    display: flex;
    padding: 20px;
    background-color: #1e1e1e;
    border-top: 1px solid #555;
  }

  .message-input {
    flex: 1;
    padding: 10px;
    font-size: 1em;
    border-radius: 8px;
    border: 1px solid #444;
    background-color: #333;
    color: #f1f1f1;
    resize: none;
    outline: none;
  }

  .send-button {
    margin-left: 10px;
    padding: 10px 20px;
    background-color: #0288d1;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
  }

  .send-button:hover {
    background-color: #026ca1;
  }
  </style>
