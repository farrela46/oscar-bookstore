<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
// import Navbar from "@/examples/PageLayout/HomeNavbar.vue";
// import Navbar from "@/examples/Navbars/Navbar.vue";
import AppFooter from "@/examples/PageLayout/Footer.vue";
// import ArgonInput from "@/components/ArgonInput.vue";
// import ArgonCheckbox from "@/components/ArgonCheckbox.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import Navbar from "@/examples/Navbars/Navbar.vue";

export default {
  name: 'LoginPage',
  components: {
    Navbar,
    AppFooter,
    // ArgonInput,
    // ArgonCheckbox,
    ArgonButton
  },
  data() {
    return {
      username: '',
      password: '',
      store: null,
      body: null,
      loading: false,
      showPassword: false,
    };
  },
  created() {
    this.store = this.$store;
    this.body = document.getElementsByTagName("body")[0];
    this.setupPage();
  },
  beforeUnmount() {
    this.restorePage();
  },
  methods: {
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },
    async onSubmit() {
      this.loading = true;
      try {
        console.log("test")
        const response = await axios.post(`${BASE_URL}/login`, {
          email: this.username,
          password: this.password
        });

        this.$notify({
          type: 'success',
          title: 'Success',
          text: response.data.message,
          color: 'green'
        });

        const { role } = response.data.user;

        if (role === 'ADMIN') {
          localStorage.setItem('access_token', response.data.access_token);
          localStorage.setItem('username', response.data.user.name);
          this.$router.push('/admin/dashboard');
        } else if (role === 'USER') {
          localStorage.setItem('access_token', response.data.access_token);
          localStorage.setItem('username', response.data.user.name);
          this.$router.push('/home');
        }
      } catch (error) {
        console.error(error);

        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          this.$notify({
            type: 'error',
            title: 'Error',
            text: errorMessage,
            color: 'red'
          });
        }
      } finally {
        this.loading = false;
      }
    },

    setupPage() {
      this.store.state.hideConfigButton = true;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = false;
      this.store.state.showFooter = false;
      this.body.classList.remove("bg-gray-100");
    },
    restorePage() {
      this.store.state.hideConfigButton = false;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = true;
      this.store.state.showFooter = true;
      this.body.classList.add("bg-gray-100");
    }
  }
}

</script>

<template>
  <!-- <div class="container top-0 position-sticky z-index-sticky">
    <div class="row">
      <div class="col-12">
        <navbar isBlur="blur  border-radius-lg my-3 py-2 start-0 end-0 mx-4 shadow" v-bind:darkMode="true" />
      </div>
    </div>
  </div> -->
  <main class="main-content mt-0">
    <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
    <div class="mx-3 mt-2 position-relative" :style="{
      backgroundImage: 'linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5) ) ,url(' + require('@/assets/img/library.png') + ')',
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      height: '65vh',
      borderRadius: '30px 30px 0 0'
    }">
      <div class="container-fluid h-100">
        <div class="row h-100 justify-content-center align-items-center">
          <div class="col-auto text-left mb-5">
            <h1 class="text-white mb-2 mt-2">Login</h1>
            <p class="text-lead text-white">
              Silahkan login terlebih dahulu untuk mengakses <br> Website Toko Buku Oscar!
            </p>
          </div>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="row mt-lg-n10 mt-md-n11 justify-content-center">
        <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
          <div class="card z-index-0 shadow">
            <div class="card-header text-center pt-4">
              <h5>Login</h5>
            </div>
            <div class="card-body">
              <form role="form" @submit.prevent="onSubmit">
                <v-text-field v-model="username" label="E-mail" variant="underlined"></v-text-field>
                <v-text-field v-model="password" label="Password" :type="showPassword ? 'text' : 'password'"
                  :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'" required
                  @click:append="togglePasswordVisibility"></v-text-field>
                <div class="text-center">
                  <argon-button v-if="!loading" fullWidth color="dark" type="submit" variant="gradient"
                    class="my-4 mb-2">Login</argon-button>
                  <argon-button v-else fullWidth color="dark" variant="gradient" class="my-4 mb-2"
                    disabled><v-progress-circular indeterminate></v-progress-circular></argon-button>
                </div>
                <p class="text-sm mt-3 mb-0">
                  Tidak punya akun?
                  <router-link to="/signup" class="text-dark font-weight-bolder">Register</router-link>
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <app-footer />
</template>

<style scoped>
.mt-lg-n10 {
  margin-top: -185px !important;
}
</style>