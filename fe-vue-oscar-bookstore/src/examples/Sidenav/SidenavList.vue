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
          console.log(errorMessage)
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
        <sidenav-item to="/admin/dashboard" :class="getRoute() === 'dashboard' ? 'active' : ''" navText="Dashboard">
          <template v-slot:icon>
            <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === 'ADMIN'" class="nav-item">
        <sidenav-item to="/admin/products" :class="getRoute() === 'manage products' ? 'active' : ''"
          navText="Manage Products">
          <template v-slot:icon>
            <i class="fa fa-book text-warning text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === 'ADMIN'" class="nav-item">
        <sidenav-item to="/admin/banners" :class="getRoute() === 'manage banners' ? 'active' : ''"
          navText="Manage Banners">
          <template v-slot:icon>
            <i class="fas fa-images text-danger text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === 'ADMIN'" class="nav-item">
        <sidenav-item to="/admin/orders" :class="getRoute() === 'manage orders' ? 'active' : ''"
          navText="Manage Orders">
          <template v-slot:icon>
            <i class="fas fa-shopping-cart text-info text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === 'ADMIN'" class="nav-item">
        <sidenav-item to="/admin/reviews" :class="getRoute() === 'manage reviews' ? 'active' : ''"
          navText="Manage Reviews">
          <template v-slot:icon>
            <i class="fas fa-comments text-danger text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === 'ADMIN'" class="nav-item">
        <sidenav-item to="/admin/users" :class="getRoute() === 'manage users' ? 'active' : ''" navText="Manage Users">
          <template v-slot:icon>
            <i class="fas fa-users text-success text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === 'ADMIN'" class="nav-item">
        <sidenav-item to="/admin/cart" :class="getRoute() === 'cashier' ? 'active' : ''" navText="Cashier Cart">
          <template v-slot:icon>
            <i class="fas fa-shopping-basket text-dark success text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-else-if="userRole === 'USER'" class="nav-item">
        <sidenav-item to="/dashboard" :class="getRoute() === 'dashboard' ? 'active' : ''" navText="Home">

          <template v-slot:icon>
            <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === 'USER'" class="nav-item">
        <sidenav-item to="/cart" :class="getRoute() === 'cart' ? 'active' : ''" navText="Cart">
          <template v-slot:icon>
            <i class="fas fa-shopping-cart text-info text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
      <li v-if="userRole === ''" class="nav-item">
        <sidenav-item to="/home" :class="getRoute() === 'cart' ? 'active' : ''" navText="Home">
          <template v-slot:icon>
            <i class="ni ni-tv-2 text-info text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>
    </ul>
  </div>
</template>
