<script>
// import { useRoute } from "vue-router";
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";
import * as bootstrap from 'bootstrap';

export default {
  components: {
    Navbar
  },
  data() {
    return {
      overlay: false,
      orders: [],
      totalPayment: {},
      selectedProductId: null
    };
  },
  mounted() {
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
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    openDeleteConfirmation(index) {
      const orderId = this.orders[index].id;
      this.selectedProductId = orderId
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmationModal'))
      modal.show();
    },
    confirmDelete() {
      if (this.selectedProductId) {
        this.removeOrder(this.selectedProductId);
        this.closeModalDelete();
      }
    },
    closeModalDelete() {
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmationModal'))
      modal.hide();
    },
    async updateQuantity(index, id, newQuantity) {
      if (newQuantity < 1) return;

      try {
        await axios.put(`${BASE_URL}/cart/update/` + id, { quantity: newQuantity }, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.orders[index].quantity = newQuantity;
        this.orders[index].totalPrice = newQuantity * this.orders[index].harga;

        this.retrieveCart();
      } catch (error) {
        console.error(error);
      }
    },
    updateTotalPayment() {
      this.totalPayment = this.orders.reduce((total, order) => {
        return order.selected ? total + order.totalPrice : total;
      }, 0);
    },
    async proceedToCheckout() {
      const selectedOrders = this.orders.filter(order => order.selected);
      if (selectedOrders.length > 0) {
        try {
          const selectedIds = selectedOrders.map(order => order.id);
          await axios.put(`${BASE_URL}/cart/select`,
            { selected_ids: selectedIds }, {
            headers: {
              Authorization: "Bearer " + localStorage.getItem('access_token')
            }
          });
          this.$router.push('/checkout');
        } catch (error) {
          console.error('Error updating selected items:', error);
        }
      } else {
        this.$notify({
          type: 'danger',
          title: 'Gagal',
          text: 'Pilih salah satu item untuk checkout',
          color: 'green'
        });
      }
    },
    incrementQuantity(index) {
      const order = this.orders[index];
      const newQuantity = order.quantity + 1;
      this.updateQuantity(index, order.id, newQuantity);
    },
    decrementQuantity(index) {
      const order = this.orders[index];
      const newQuantity = order.quantity - 1;
      if (newQuantity > 0) {
        this.updateQuantity(index, order.id, newQuantity);
      }
    },
    async retrieveCart() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/cart/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.orders = response.data.data
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
    async removeOrder(id) {
      try {
        await axios.delete(`${BASE_URL}/cart/delete/` + id, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.retrieveCart();
      } catch (error) {
        console.error(error);
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
        <div class="row">
          <div class="col-lg-8">
            <h4 v-if="orders.length === 0">Anda belum memiliki barang</h4>
            <div v-for="(order, index) in orders" :key="index" class="mb-4 card">
              <div class="card-body">
                <h5 class="card-title">Pesanan {{ index + 1 }}</h5>
                <div class="row">
                  <div class="col-md-3">
                    <div class="row">
                      <div class="col-1 align-items-center d-flex">
                        <input type="checkbox" class="large-checkbox" v-model="order.selected"
                          @change="updateTotalPayment" />
                      </div>
                      <div class="col">
                        <img :src="order.foto" class="img-fluid" alt="Book image">
                      </div>
                    </div>
                  </div>
                  <div class="col-md-9">
                    <div class="row">
                      <div class="col">
                        <h6>{{ order.judul }}</h6>
                        <p>Rp {{ formatPrice(order.harga) }}</p>
                      </div>
                      <div class="col">
                        <div class="d-flex align-items-center">
                          <i class="fas fa-minus-circle" style="cursor: pointer;" @click="decrementQuantity(index)"></i>
                          <span class="mx-2">{{ order.quantity }}</span>
                          <i class="fas fa-plus-circle" style="cursor: pointer;" @click="incrementQuantity(index)"></i>
                          <button class="btn btn-link text-danger ms-3" @click="openDeleteConfirmation(index)">
                            <i class="bi bi-trash"></i> Hapus
                          </button>
                          <span class="ms-auto">Rp {{ formatPrice(order.totalPrice) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Rincian Belanja</h5>
                <p>Ringkasan Pembayaran</p>
                <p>Rp {{ formatPrice(totalPayment) }}</p>
                <button class="btn btn-primary w-100" @click="proceedToCheckout">Lanjut ke Pembayaran</button>
              </div>
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
<style>
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
</style>