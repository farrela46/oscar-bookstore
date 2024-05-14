<script>
import { computed } from "vue";
import { useStore } from "vuex";
// import Sidenav from "./examples/Sidenav";
// import Navbar from "@/examples/Navbars/Navbar.vue";
import AppFooter from "@/examples/Footer.vue";
// import Configurator from "@/examples/Configurator.vue";


export default {
  components: {
    // Sidenav,
    // Navbar,
    AppFooter,
    // Configurator
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
      username: null
    };
  },

  mounted() {
    const store = useStore();
    // this.showSidenav = computed(() => store.state.showSidenav);
    this.layout = computed(() => store.state.layout);
    this.showNavbar = computed(() => store.state.showNavbar);
    this.showFooter = computed(() => store.state.showFooter);
    this.showConfig = computed(() => store.state.showConfig);
    // this.hideConfigButton = computed(() => store.state.hideConfigButton);
  },
  methods: {
toggleWhatsapp() {
  window.open('https://wa.me/6285179684793', '_blank');
}
  }
};
</script>

<template>
  <notifications />
  <div v-show="layout === 'landing'" class="landing-bg h-100 bg-gradient-primary position-fixed w-100"></div>

  <sidenav v-if="showSidenav" />

  <main class="main-content position-relative h-100 border-radius-lg">
    <!-- nav -->

    <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" v-if="showNavbar" />

    <router-view />

    <app-footer v-show="showFooter" />

    <div class="fixed-plugin">
      <a class="px-3 py-2 fixed-plugin-button text-dark position-fixed" @click="toggleWhatsapp">
        <i class=" py-2 fab fa-whatsapp"></i>
      </a>
    </div>
  </main>
</template>