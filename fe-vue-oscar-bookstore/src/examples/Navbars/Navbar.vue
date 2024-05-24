<script>
// import { computed, ref } from "vue";
// import { useStore } from "vuex";
// import { useRoute, useRouter } from "vue-router";
// import Breadcrumbs from "../Breadcrumbs.vue";
import axios from 'axios';
import BASE_URL from '@/api/config-api';

export default {
  components: {
    // Breadcrumbs
  },
  data() {
    return {
      showMenu: false,
      showMenuUser: false,
      userName: '',
      hasAccessToken: false,
      isLogin: false,
      role: ''
    };
  },
  mounted() {
    this.getUsername();
    this.checkLoginStatus();
    this.getUser();

  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
    currentDirectory() {
      return this.$route.name;
    }
  },
  methods: {
    checkLoginStatus() {
      const accessToken = localStorage.getItem('access_token');
      this.hasAccessToken = !!accessToken;
    },
    minimizeSidebar() {
      this.$store.commit("sidebarMinimize");
    },
    closeMenu() {
      setTimeout(() => {
        this.showMenu = false;
      }, 100);
    },
    closeMenuUser() {
      setTimeout(() => {
        this.showMenuUser = false;
      }, 100);
    },
    getUsername() {
      this.userName = localStorage.getItem('username')
    },
    async getUser() {
      try {
        const response = await axios.get(`${BASE_URL}/user`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.id = response.data.user.id;
        this.role = response.data.user.role;
      } catch (error) {
        console.error(error);

        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          console.log(errorMessage)
        }
      }
    },
    goAdminDashboard() {
      this.$router.push('/admin/dashboard')
    },
    goManageProducts() {
      this.$router.push('/admin/products')
    },
    goManageUsers() {
      this.$router.push('/admin/users')
    },
    goCarts() {
      this.$router.push('/cart')
    },
    goOrders() {
      this.$router.push('/profile')
    },
    goProfile() {
      this.$router.push('/profile')
    },
    async onLogout() {
      try {
        await axios.post(`${BASE_URL}/logout`, {}, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token'),
          }
        });

        localStorage.removeItem('access_token');
        localStorage.removeItem('username');
        this.$router.push('/login');
        this.checkLoginStatus();
      } catch (error) {
        console.error('Logout failed:', error);
      }
    }
  }
};

</script>

<template>
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow blur border-radius-md" v-bind="$attrs"
    id="navbarBlur" data-scroll="true">
    <div class="px-3 py-1 container-fluid">
      <router-link class="navbar-brand font-weight-bolder ms-lg-0 ms-3" :class="darkMode ? 'text-black' : 'text-black'"
        to="/">OSCAR BOOKSTORE</router-link>
      <div class="mt-2 collapse navbar-collapse mt-sm-0 me-md-0 me-sm-4" :class="'px-0 me-sm-4'" id="navbar">
        <div class="pe-md-3 d-flex align-items-center" :class="'me-md-auto ms-md-auto'">
        </div>
        <ul class="navbar-nav justify-content-end">
          <!-- <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
            <a @click="minimizeSidebar" class="p-0 nav-link text-black" id="iconNavbarSidenav">
              <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line bg-black"></i>
                <i class="sidenav-toggler-line bg-black"></i>
                <i class="sidenav-toggler-line bg-black"></i>
              </div>
            </a>
          </li> -->
          <li class="nav-item dropdown d-flex align-items-center" :class="'ps-2 pe-2'">
            <div v-if="hasAccessToken" class="div" :class="[showMenu ? 'show' : '']" id="dropdownMenuButton"
              data-bs-toggle="dropdown" aria-expanded="false" @click="showMenu = !showMenu" @blur="closeMenu">
              <a href="#" class="p-0 nav-link text-black">
                <i class="cursor-pointer fa fa-user"></i>&nbsp;<b> {{ userName }} </b>
              </a>
            </div>
            <div v-else class=" me-2">
              <router-link to="/login">
                Login
              </router-link>
              <router-link to="/signup">
                / Register
              </router-link>
            </div>

            <!-- <router-link v-else to="/login" class="px-0 nav-link font-weight-bold text-white"
              target="_blank">
              <i class="fa fa-user"></i>
              <span class="d-sm-inline d-none">Sign In</span>
            </router-link> -->
            <ul class="px-2 py-3 dropdown-menu dropdown-menu-end me-sm-n4" :class="showMenu ? 'show' : ''"
              aria-labelledby="dropdownMenuButton">

              <li v-if="role === 'ADMIN'" class="mb-2">
                <a class="dropdown-item border-radius-md" @click="goAdminDashboard">
                  <div class="py-1 d-flex">
                    <div class="my-auto mx-3">
                      <span style="font-size: 1rem;">
                        <span style="color: black;">
                          <i class="fas fa-home"></i>
                        </span>
                      </span>
                    </div>
                    <div class="d-flex flex-column ml-4 justify-content-center">
                      <h6 class="mb-1 text-sm font-weight-normal">
                        Home
                      </h6>
                    </div>
                  </div>
                </a>
              </li>
              <li v-if="role === 'ADMIN'" class="mb-2">
                <a class="dropdown-item border-radius-md" @click="goManageProducts">
                  <div class="py-1 d-flex">
                    <div class="my-auto mx-3">
                      <span style="font-size: 1rem;">
                        <span style="color: black;">
                          <i class="fas fa-book"></i>
                        </span>
                      </span>
                    </div>
                    <div class="d-flex flex-column ml-4 justify-content-center">
                      <h6 class="mb-1 text-sm font-weight-normal">
                        Manage Products
                      </h6>
                    </div>
                  </div>
                </a>
              </li>
              <li v-if="role === 'ADMIN'" class="mb-2">
                <a class="dropdown-item border-radius-md" @click="goManageUsers">
                  <div class="py-1 d-flex">
                    <div class="my-auto mx-3">
                      <span style="font-size: 1rem;">
                        <span style="color: black;">
                          <i class="fas fa-users"></i>
                        </span>
                      </span>
                    </div>
                    <div class="d-flex flex-column ml-4 justify-content-center">
                      <h6 class="mb-1 text-sm font-weight-normal">
                        Manage Users
                      </h6>
                    </div>
                  </div>
                </a>
              </li>

              <li v-if="role === 'USER'" class="mb-2">
                <a class="dropdown-item border-radius-md" @click="goCarts">
                  <div class="py-1 d-flex">
                    <div class="my-auto mx-3">
                      <span style="font-size: 1rem;">
                        <span style="color: black;">
                          <i class="fas fa-shopping-cart"></i>
                        </span>
                      </span>
                    </div>
                    <div class="d-flex flex-column ml-4 justify-content-center">
                      <h6 class="mb-1 text-sm font-weight-normal">
                        My Cart
                      </h6>
                    </div>
                  </div>
                </a>
              </li>
              <li v-if="role === 'USER'" class="mb-2">
                <a class="dropdown-item border-radius-md" @click="goProfile">
                  <div class="py-1 d-flex">
                    <div class="my-auto mx-3">
                      <span style="font-size: 1rem;">
                        <span style="color: black;">
                          <i class="fas fa-shopping-basket"></i>
                        </span>
                      </span>
                    </div>
                    <div class="d-flex flex-column ml-4 justify-content-center">
                      <h6 class="mb-1 text-sm font-weight-normal">
                        My Order
                      </h6>
                    </div>
                  </div>
                </a>
              </li>
              <li class="mb-2" v-if="role === 'USER'">
                <a class="dropdown-item border-radius-md" href="/profile">
                  <div class="py-1 d-flex">
                    <div class="my-auto mx-3">
                      <span style="font-size: 1rem;">
                        <span style="color: black;">
                          <i class="fas fa-user"></i>
                        </span>
                      </span>
                    </div>
                    <div class="d-flex flex-column ml-4 justify-content-center">
                      <h6 class="mb-1 text-sm font-weight-normal">
                        My Profile
                      </h6>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <a class="dropdown-item border-radius-md" @click="onLogout">
                  <div class="py-1 d-flex">
                    <div class="my-auto mx-3">
                      <span style="font-size: 1rem;">
                        <span style="color: black;">
                          <i class="fas fa-running"></i>
                        </span>
                      </span>
                    </div>
                    <div class="d-flex flex-column ml-4 justify-content-center">
                      <h6 class="mb-1 text-sm font-weight-normal">
                        Logout
                      </h6>
                    </div>
                  </div>
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.dropdown-menu {
  top: 0.25rem !important;
}
</style>
