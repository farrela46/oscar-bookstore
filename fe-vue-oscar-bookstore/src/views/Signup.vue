<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';

import AppFooter from "@/examples/PageLayout/Footer.vue";
import ArgonInput from "@/components/ArgonInput.vue";
// import ArgonCheckbox from "@/components/ArgonCheckbox.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import Navbar from "@/examples/Navbars/Navbar.vue";

export default {
  name: 'Register',
  components: {
    Navbar,
    AppFooter,
    ArgonInput,
    // ArgonCheckbox,
    ArgonButton
  },
  data() {
    return {
      name: '',
      email: '',
      password: '',
      store: null,
      body: null,
      loading: false,
      showPasswordError: false,
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
    async onSubmit() {
      if (this.password !== this.confirmPassword) {
        this.showPasswordError = true;
        return; // Prevent form submission if passwords do not match
      } else {
        this.showPasswordError = false;
      }

      this.loading = true;
      try {
        const response = await axios.post(`${BASE_URL}/register`, {
          name: this.name,
          email: this.email,
          password: this.password
        });
        this.$notify({
          type: 'success',
          title: 'Success',
          text: response.data.message,
          color: 'green'
        });
        this.$router.push('/login');
      } catch (error) {
        console.error(error);

        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          this.$notify({
            type: 'error',
            title: 'Gagal',
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
      this.store.state.showNavbar = false;
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
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
  <main class="main-content mt-0">

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
            <h1 class="text-white mb-2 mt-2">Register</h1>
            <p class="text-lead text-white">
              Silahkan membuat akun terlebih dahulu untuk mengakses <br> Website Toko Buku Oscar!
            </p>
          </div>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="row mt-lg-n10 mt-md-n11 mt-n10 justify-content-center">
        <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
          <div class="card z-index-0 shadow">
            <div class="card-header text-center pt-4">
              <h5>Register</h5>
            </div>
            <div class="card-body">
              <form role="form" @submit.prevent="onSubmit">
                <argon-input v-model="name" id="name" type="text" placeholder="Name" aria-label="Name" />
                <argon-input v-model="email" id="email" type="email" placeholder="Email" aria-label="Email" />

                <argon-input v-model="password" id="password" type="password" placeholder="Password"
                  aria-label="Password" />
                <argon-input v-model="confirmPassword" id="password" type="password" placeholder="Confirm Password"
                  aria-label="Password" />
                <a v-if="showPasswordError" style="font-size: 12px; color:red;"><i class="fas fa-info-circle"
                    style="color: #ff0000;"></i>&nbsp;Password tidak sesuai!</a>
                <div class="text-center">
                  <argon-button v-if="!loading" fullWidth color="dark" type="submit" variant="gradient"
                    class="my-4 mb-2">Sign up</argon-button>
                  <argon-button v-else fullWidth color="dark" variant="gradient" class="my-4 mb-2"
                    disabled><v-progress-circular indeterminate></v-progress-circular></argon-button>
                </div>
                <p class="text-sm mt-3 mb-0">
                  Sudah punya akun?
                  <router-link to="/login" class="text-dark font-weight-bolder">Login</router-link>
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