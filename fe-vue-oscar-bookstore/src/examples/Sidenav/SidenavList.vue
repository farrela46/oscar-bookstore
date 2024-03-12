<script>
import { useRoute } from "vue-router";
import SidenavItem from "./SidenavItem.vue";
import axios from "axios";
import BASE_URL from '@/api/config-api';

export default {
  components: {
    SidenavItem
  },
  data() {
    return {
      userRole: ''
    };
  },
  mounted() {
    this.getUser()
  },
  methods: {
    getRoute() {
      const route = useRoute();
      const routeArr = route.path.split("/");
      return routeArr[1];
    },
    async getUser() {
      try {
        const response = await axios.get(`${BASE_URL}/user`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.id = response.data.user.id;
        this.username = response.data.user.name;
        this.userRole = response.data.user.role;
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
      }
    }

  },
};
</script>


<template>
  <div class="collapse navbar-collapse w-auto h-auto h-100" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li v-if="userRole === 'ADMIN'" class="nav-item">
        <sidenav-item to="/admin/dashboard" :class="getRoute() === 'dashboard' ? 'active' : ''"
          navText="Dashboard">
          <template v-slot:icon>
            <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === 'ADMIN'" class="nav-item">
        <sidenav-item to="/tables" :class="getRoute() === 'tables' ? 'active' : ''" navText="Tables">
          <template v-slot:icon>
            <i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === 'ADMIN'" class="nav-item">
        <sidenav-item to="/billing" :class="getRoute() === 'billing' ? 'active' : ''"
          navText="Billing">
          <template v-slot:icon>
            <i class="ni ni-credit-card text-success text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === 'ADMIN'" class="nav-item">
        <sidenav-item to="/virtual-reality" :class="getRoute() === 'virtual-reality' ? 'active' : ''"
          navText="Virtual Reality">
          <template v-slot:icon>
            <i class="ni ni-app text-info text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      
      <!-- Additional navigation items for other roles -->
      <li v-else-if="userRole === 'USER'" class="nav-item">
        <sidenav-item to="/dashboard" :class="getRoute() === 'dashboard' ? 'active' : ''"
          navText="Dashboard">
          <template v-slot:icon>
            <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
    </ul>
  </div>
</template>
