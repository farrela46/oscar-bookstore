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
      showMenuUser: false
    };
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
      <div class="mt-2 collapse navbar-collapse mt-sm-0 me-md-0 me-sm-4" :class="isRTL ? 'px-0' : 'me-sm-4'"
        id="navbar">
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
              <i class="cursor-pointer fa fa-bell"></i>
            </a>
            <ul class="px-2 py-3 dropdown-menu dropdown-menu-end me-sm-n4" :class="showMenu ? 'show' : ''"
              aria-labelledby="dropdownMenuButton">
              <li class="mb-2">
                <a class="dropdown-item border-radius-md" href="javascript:;">
                  <div class="py-1 d-flex">
                    <div class="my-auto">
                      <img src="../../assets/img/team-2.jpg" class="avatar avatar-sm me-3" alt="user image" />
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                      <h6 class="mb-1 text-sm font-weight-normal">
                        <span class="font-weight-bold">New message</span> from
                        Laur
                      </h6>
                      <p class="mb-0 text-xs text-secondary">
                        <i class="fa fa-clock me-1"></i>
                        13 minutes ago
                      </p>
                    </div>
                  </div>
                </a>
              </li>
              <li class="mb-2">
                <a class="dropdown-item border-radius-md" href="javascript:;">
                  <div class="py-1 d-flex">
                    <div class="my-auto">
                      <img src="../../assets/img/small-logos/logo-spotify.svg"
                        class="avatar avatar-sm bg-gradient-dark me-3" alt="logo spotify" />
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                      <h6 class="mb-1 text-sm font-weight-normal">
                        <span class="font-weight-bold">New album</span> by
                        Travis Scott
                      </h6>
                      <p class="mb-0 text-xs text-secondary">
                        <i class="fa fa-clock me-1"></i>
                        1 day
                      </p>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <a class="dropdown-item border-radius-md" href="javascript:;">
                  <div class="py-1 d-flex">
                    <div class="my-auto avatar avatar-sm bg-gradient-secondary me-3">
                      <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <title>credit-card</title>
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                          <g transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF" fill-rule="nonzero">
                            <g transform="translate(1716.000000, 291.000000)">
                              <g transform="translate(453.000000, 454.000000)">
                                <path class="color-background"
                                  d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z"
                                  opacity="0.593633743" />
                                <path class="color-background"
                                  d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z" />
                              </g>
                            </g>
                          </g>
                        </g>
                      </svg>
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                      <h6 class="mb-1 text-sm font-weight-normal">
                        Payment successfully completed
                      </h6>
                      <p class="mb-0 text-xs text-secondary">
                        <i class="fa fa-clock me-1"></i>
                        2 days
                      </p>
                    </div>
                  </div>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item dropdown d-flex align-items-center" :class="'ps-2 pe-2'">
            <a href="#" class="p-0 nav-link text-black" :class="[showMenuUser ? 'show' : '']" id="dropdownMenuUser"
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
