<script>

import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";

export default {
  components: {
    Navbar
  },
  data() {
    return {
      overlay: false,
      products: [],
      totalPayment: {},
      selectedProductId: null,
      orders: ''
    };
  },
  mounted() {
    this.retrieveBuku();
    this.retrieveCart();
  },
  created() {
    this.store = this.$store;
    this.body = document.getElementsByTagName("body")[0];
    this.setupPage();
  },
  beforeUnmount() {
    this.restorePage();
  },
  computed: {
    limitedProducts() {
      return this.products.slice(0, 3);
    },
  },
  methods: {
    setupPage() {
      this.store.state.hideConfigButton = true;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = true;
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
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    proceedToCashier() {
      this.$router.push('/admin/cashier')
    },
    async retrieveCart() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/cart/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.orders = response.data
        this.totalPayment = 0;


      } catch (error) {
        console.error(error);
        this.$notify({
          type: 'danger',
          title: 'Notif',
          text: 'Anda belum menambahkan barang',
          color: 'green'
        });
      } finally {
        this.overlay = false;
      }
    },
    searchProduct() {
      this.retrieveBuku(this.searchQuery);
    },
    async retrieveBuku(searchQuery = '') {
      try {
        this.overlay = true;
        const params = {};
        if (searchQuery) params.search = searchQuery;

        const response = await axios.get(`${BASE_URL}/buku/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          },
          params
        });

        this.products = response.data.data;

        if (response.data.length > 0) {
          this.fotoUrl = response.data[0].foto;
        }
      } catch (error) {
        console.error(error);
      } finally {
        this.overlay = false;
      }
    },
    async addCart(id) {
      try {
        const token = localStorage.getItem('access_token');
        if (!token) {
          this.$notify({
            type: 'error',
            title: 'Notif',
            text: 'Anda belum login. Silakan login terlebih dahulu.',
            color: 'red'
          });

          this.$router.push('/login');
          return;
        }
        const response = await axios.post(`${BASE_URL}/cart/add`, {
          buku_id: id,
        }, {
          headers: {
            Authorization: "Bearer " + token
          }
        });
        console.log(response)
        this.retrieveCart();
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Buku ditambahkan ke Keranjang',
          color: 'green'
        });
      } catch (error) {
        console.error("Error adding product to cart:", error);

        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          this.$notify({
            type: 'error',
            title: 'Error',
            text: errorMessage,
            color: 'red'
          });
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'An error occurred while adding the product to the cart.',
            color: 'red'
          });
        }
      }
    },

  },
};
</script>

<template>
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
  <div class="py-4 container">
    <div class="row mt-3">
      <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
        <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
      </v-overlay>
      <div class="container">
        <div class="row" style="height: 60px;">
          <form class="search-container" @submit.prevent="searchProduct" style="max-width: 350px;">
            <div class="input-group">
              <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
              <input type="text" class="form-control" placeholder="Search Product" @input="searchProduct"
                v-model="searchQuery">
            </div>
          </form>
        </div>
        <div class="container">
          <div class="row">
            <div class="mb-2 card" v-for="(item) in limitedProducts" :key="item.id">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3 col-4">
                    <div class="row">
                      <div class="col">
                        <img :src="item.foto" class="img-fluid" alt="Book image" style="max-width: 100px;
    max-height: 134px;
    width: 100%;
    height: auto;
    object-fit: cover;
    overflow: hidden;">
                      </div>
                    </div>
                  </div>
                  <div class="col-md-9 col-8">
                    <div class="row">
                      <div class="col-9">
                        <div class="row">
                          <a class="text-truncate text-bold" style="font-size: 16px; color: black;">{{ item.judul
                            }}</a>
                          <p><strong>Rp {{ formatPrice(item.harga) }}</strong> </p>
                        </div>
                      </div>
                      <div class="col-3 d-flex align-items-center">
                        <i class="fas fa-plus-square" style="font-size: 30px; color: green; cursor: pointer;"
                          @click.prevent="addCart(item.id)"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12 d-block d-lg-none position-fixed bottom-0 "
            style="margin-bottom:10px; padding-right: 35px;">
            <div class="card shadow px-2">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-shopping-basket fa-lg mx-2"
                    style=" color: #42BADB ; cursor: pointer;"></i>{{ orders.totalItems }} Barang Dikeranjang</h6>
                <button class="btn btn-primary mt-2" @click="proceedToCashier">Cashier</button>
              </div>
            </div>
          </div>
        </div>
        <div class="row ">
          <div class="card shadow px-2 d-none d-lg-block">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0"><i class="fas fa-shopping-basket fa-lg mx-2"
                  style=" color: #42BADB ; cursor: pointer;"></i>{{ orders.totalItems }} Barang Dikeranjang</h6>
              <button class="btn btn-primary mt-2" @click="proceedToCashier">Cashier</button>
            </div>
          </div>

        </div>
      </div>
      <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title text-black" id="deleteConfirmationModalLabel">Confirm Delete</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              Are you sure you want to remove this product?
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" @click="confirmDelete">Remove</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.user-select-none {
  user-select: none;
}

a {
  text-decoration: none;
  color: unset;
}

.large-checkbox {
  width: 1.5rem;
  height: 1.5rem;
}

.sticky-menu {
  position: sticky;
  top: 100px;
  padding: 3px;

}


@media (max-width: 576px) {


  .row img {
    width: 30px;
    margin-right: 10px;
    max-width: 100px;
    max-height: 134px;
    width: 100%;
    height: auto;
    object-fit: cover;
    overflow: hidden;
  }
}
</style>