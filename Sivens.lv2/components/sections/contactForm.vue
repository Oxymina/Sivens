<template>
  <v-form @submit.prevent="submitForm">
    <v-row class="mb-n6">
      <v-col>
        <v-text-field v-model="name" label="Name" dense outlined></v-text-field>
      </v-col>
      <v-col>
        <v-text-field
          v-model="email"
          label="Email"
          dense
          outlined
        ></v-text-field>
      </v-col>
    </v-row>

    <v-row class="mb-n6">
      <v-col>
        <v-text-field
          v-model="subject"
          label="Subject"
          dense
          outlined
        ></v-text-field>
      </v-col>
    </v-row>

    <v-row class="mb-n6">
      <v-col>
        <v-textarea
          v-model="message"
          dense
          label="Your Message"
          auto-grow
          outlined
          rows="8"
          row-height="20"
        ></v-textarea>
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <v-btn type="submit" class="my-2" color="primary">SEND MESSAGE</v-btn>
        <v-btn type="reset" class="my-2 ml-3" @click="clearForm">Clear</v-btn>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
export default {
  data() {
    return {
      name: '',
      email: '',
      subject: '',
      message: '',
    }
  },
  methods: {
    submitForm() {
      const formData = {
        name: this.name,
        email: this.email,
        subject: this.subject,
        message: this.message,
      }

      import('axios')
        .then(({ default: axios }) => {
          return axios.post('http://localhost:8000/api/messages', formData)
        })
        .then((response) => {
          alert('Message sent successfully:', response.data)
          this.clearForm()
        })
        .catch((error) => {
          alert('Error sending message:', error.message)
        })
    },
    clearForm() {
      this.name = ''
      this.email = ''
      this.subject = ''
      this.message = ''
    },
  },
}
</script>
