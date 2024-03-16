<script>
// import { computed, ref } from "vue";
// import { useStore } from "vuex";
// import { useRoute, useRouter } from "vue-router";
import Breadcrumbs from "../Breadcrumbs.vue";
import axios from 'axios';
import BASE_URL from '@/api/config-api';

export default {
  components: {
    Breadcrumbs
  },
  data() {
    return {
      showMenu: false,
      showMenuUser: false,
      userName: ''
    };
  },
  mounted() {
    this.getUser()
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
    async getUser() {
      try {
        const response = await axios.get(`${BASE_URL}/user`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.id = response.data.user.id;
        this.userName = response.data.user.name;
        this.userRole = response.data.user.role;
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
  }
};

</script>

<template>
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl"
    :class="isRTL ? 'top-0 position-sticky z-index-sticky' : ''" v-bind="$attrs" id="navbarBlur" data-scroll="true">
    <div class="px-3 py-1 container-fluid">
      <breadcrumbs :current-page="currentRouteName" :current-directory="currentDirectory" />
      <div class="mt-2 collapse navbar-collapse mt-sm-0 me-md-0 me-sm-4" :class="'px-0 me-sm-4'" id="navbar">
        <div class="pe-md-3 d-flex align-items-center" :class="'me-md-auto ms-md-auto'">

        </div>
        <ul class="navbar-nav justify-content-end">
          <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
            <a @click="minimizeSidebar" class="p-0 nav-link text-black" id="iconNavbarSidenav">
              <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line bg-black"></i>
                <i class="sidenav-toggler-line bg-black"></i>
                <i class="sidenav-toggler-line bg-black"></i>
              </div>
            </a>
          </li>
          <li class="nav-item dropdown d-flex align-items-center" :class="'ps-2 pe-2'">
            <a href="#" class="p-0 nav-link text-black" :class="[showMenu ? 'show' : '']" id="dropdownMenuButton"
              data-bs-toggle="dropdown" aria-expanded="false" @click="showMenu = !showMenu" @blur="closeMenu">
              <i class="cursor-pointer fa fa-user"></i>&nbsp;<b> {{ userName }} </b>
            </a>
            <ul class="px-2 py-3 dropdown-menu dropdown-menu-end me-sm-n4" :class="showMenu ? 'show' : ''"
              aria-labelledby="dropdownMenuButton">
              <li class="mb-2">
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
          <!-- <li class="nav-item dropdown d-flex align-items-center" :class="'ps-2 pe-2'">
            <a class="p-0 nav-link text-black" :class="[showMenuUser ? 'show' : '']" id="dropdownMenuUser"
              data-bs-toggle="dropdown" aria-expanded="false" @click="showMenuUse = !showMenuUser"
              @blur="closeMenuUser">
              <i class="cursor-pointer fa fa-user"></i>
            </a>
            <ul class="px-2 py-3 dropdown-menu dropdown-menu-end me-sm-n4" :class="showMenuUser ? 'show' : ''"
              aria-labelledby="dropdownMenuUser">
              <li class="mb-2">
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
          </li> -->
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
