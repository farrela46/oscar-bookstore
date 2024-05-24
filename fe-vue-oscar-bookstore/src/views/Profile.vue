<script>
import setNavPills from '@/assets/js/nav-pills.js';
import setTooltip from '@/assets/js/tooltip.js';
import ProfileCard from './components/ProfileCard.vue';
import ArgonInput from '@/components/ArgonInput.vue';
import ArgonButton from '@/components/ArgonButton.vue';
import axios from 'axios';
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";

export default {
  name: 'Profile',
  components: {
    ProfileCard,
    ArgonInput,
    ArgonButton,
    Navbar
  },
  data() {
    return {
      store: null,
      body: null,
      users: '',
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
  mounted() {
    setNavPills();
    setTooltip();
    this.getUser();
  },

  methods: {
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
    },
    async getUser() {
      try {
        const response = await axios.get(`${BASE_URL}/user`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.users = response.data.user;
      } catch (error) {
        console.error(error);

        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          console.log(errorMessage)
        }
      }
    },
    async onLogout() {
      try {
        await axios.post(`${BASE_URL}/logout`, {}, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token'),
          }
        });

        localStorage.removeItem('access_token');
        this.$router.push('/login');
      } catch (error) {
        console.error('Logout failed:', error);
      }
    }
  },
};
</script>

<template>
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
  <main>
    <div class="container-fluid">
      <div class="card shadow-lg mt-5">
        <div class="card-body p-3">
          <div class="row gx-4">
            <div class="col-auto">
              <div class="avatar avatar-xl position-relative">
                <img src="../assets/img/team-1.jpg" alt="profile_image" class="shadow-sm w-100 border-radius-lg" />
              </div>
            </div>
            <div class="col-auto my-auto">
              <div class="h-100">
                <h5 class="mb-1">{{ users.name }}</h5>
                <p class="mb-0 font-weight-bold text-sm">{{ users.role }}</p>
              </div>
            </div>
            <div class="mx-auto mt-3 col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0">
              <div class="nav-wrapper position-relative end-0">
                <argon-button color="danger" @click="onLogout"><span class="mx-3"
                    style="font-size: 1rem; cursor: pointer;">
                    <span style="color: WHITE;">
                      <i class="fas fa-running"></i>
                    </span>
                  </span>Logout</argon-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="py-4 container-fluid">
      <div class="row">
        <div class="col-md-8">
          <div class="card shadow-lg">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Edit Profile</p>
                <argon-button color="success" size="sm" class="ms-auto">Settings</argon-button>
              </div>
            </div>
            <div class="card-body">
              <p class="text-uppercase text-sm">User Information</p>
              <div class="row">
                <div class="col-md-6">
                  <label for="example-text-input" class="form-control-label">Username</label>
                  <argon-input type="text" value="lucky.jesse" />
                </div>
                <div class="col-md-6">
                  <label for="example-text-input" class="form-control-label">Email address</label>
                  <argon-input type="email" value="jesse@example.com" />
                </div>
                <div class="col-md-6">
                  <label for="example-text-input" class="form-control-label">First name</label>
                  <input class="form-control" type="text" value="Jesse" />
                </div>
                <div class="col-md-6">
                  <label for="example-text-input" class="form-control-label">Last name</label>
                  <argon-input type="text" value="Lucky" />
                </div>
              </div>
              <hr class="horizontal dark" />
              <p class="text-uppercase text-sm">Contact Information</p>
              <div class="row">
                <div class="col-md-12">
                  <label for="example-text-input" class="form-control-label">Address</label>
                  <argon-input type="text" value="Bld Mihail Kogalniceanu, nr. 8 Bl 1, Sc 1, Ap 09" />
                </div>
                <div class="col-md-4">
                  <label for="example-text-input" class="form-control-label">City</label>
                  <argon-input type="text" value="New York" />
                </div>
                <div class="col-md-4">
                  <label for="example-text-input" class="form-control-label">Country</label>
                  <argon-input type="text" value="United States" />
                </div>
                <div class="col-md-4">
                  <label for="example-text-input" class="form-control-label">Postal code</label>
                  <argon-input type="text" value="437300" />
                </div>
              </div>
              <hr class="horizontal dark" />
              <p class="text-uppercase text-sm">About me</p>
              <div class="row">
                <div class="col-md-12">
                  <label for="example-text-input" class="form-control-label">About me</label>
                  <argon-input type="text" value="A beautiful Dashboard for Bootstrap 5. It is Free and Open Source." />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <profile-card />
        </div>
      </div>
    </div>
  </main>
</template>
<style>
main {
  background-color: #42BADB;
  height: 20vh;
}
</style>
