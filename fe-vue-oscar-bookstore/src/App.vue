<script>
import { computed } from "vue";
import { useStore } from "vuex";
import Sidenav from "./examples/Sidenav";
// import Navbar from "@/examples/Navbars/Navbar.vue";
import AppFooter from "@/examples/Footer.vue";
// import Configurator from "@/examples/Configurator.vue";
// import ArgonInput from "@/components/ArgonInput.vue";
// import ArgonButton from "@/components/ArgonButton.vue";


export default {
  components: {
    Sidenav,
    // Navbar,
    AppFooter,
    // ArgonInput,
    // ArgonButton
  },
  data() {
    return {
      showSidenav: false,
      layout: null,
      showNavbar: false,
      showFooter: false,
      showConfig: false,
      hideConfigButton: false,
      userRole: '',
      id: null,
      username: null,
      showWhatsapp: false,
      inputChat: ''

    };
  },

  mounted() {
    const store = useStore();
    this.showSidenav = computed(() => store.state.showSidenav);
    this.layout = computed(() => store.state.layout);
    this.showNavbar = computed(() => store.state.showNavbar);
    this.showFooter = computed(() => store.state.showFooter);
    this.showConfig = computed(() => store.state.showConfig);
    // this.hideConfigButton = computed(() => store.state.hideConfigButton);
  },
  methods: {
    // toggleWhatsapp() {
    //   window.open('https://wa.me/6285179684793', '_blank');
    // },
    toggleWhatsapp() {
      this.showWhatsapp = !this.showWhatsapp;
    },
    sendWhatsapp() {
      const baseUrl = 'https://wa.me/6285179684793';
      const encodedText = encodeURIComponent(this.inputChat.trim());
      const url = `${baseUrl}?text=${encodedText}`;
      window.open(url, '_blank');
      this.inputChat = '',
      this.showWhatsapp = false
    },

  }
};
</script>

<template>
  <notifications position="top left" />
  <div v-show="layout === 'landing'" class="landing-bg h-100 bg-gradient-primary position-fixed w-100"></div>

  <sidenav v-if="showSidenav" />

  <main class="main-content position-relative h-100 border-radius-lg">
    <!-- nav -->

    <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" v-if="showNavbar" />

    <router-view />

    <app-footer v-show="showFooter" />

    <!-- <div class="fixed-plugin">
      <a class="px-3 py-2 fixed-plugin-button text-dark position-fixed" @click="toggleWhatsapp">
        <i class=" py-2 fab fa-whatsapp"></i>
      </a>
    </div> -->
    <!-- <div v-if="showWhatsapp" class="whatsapp-chat-window position-fixed">
      <div class="whatsapp-header">
        <i class="fab fa-whatsapp"></i> WhatsApp
        <button @click="toggleWhatsapp" class="close-button">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="whatsapp-body">
        <p>Hai, Kak...</p>
        <p>Ada yang bisa kami bantu?</p>
      </div>
      <div class="row ">
        <div class="col-7">
          <argon-input v-model="inputChat" class="ms-1" id="Text" type="text" placeholder="Tanya disini" name="email"
            size="md" />
        </div>
        <div class="col-5">
          <argon-button color="success" size="sm" variant="contained" @click="sendWhatsapp">Kirim</argon-button>
        </div>
      </div>
    </div> -->
  </main>
</template>

<style>
.whatsapp-chat-window {
  width: 300px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  background-color: white;
  bottom: 90px;
  right: 30px;
}

.whatsapp-header {
  background-color: #25d366;
  color: white;
  padding: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
}

.whatsapp-body {
  padding: 10px;
  font-size: 14px;
}



.open-chat-button {
  background-color: #25d366;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
}

.close-button {
  background: none;
  border: none;
  color: white;
  font-size: 20px;
  cursor: pointer;
}
</style>